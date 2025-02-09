<?php

namespace App\Http\Controllers;
use App\Models\Cart;
use App\Models\Room;
use App\Models\Message;
use App\Models\Product;
use App\Models\ProductsReview;
use App\Models\User;
use Auth;
use Session;
use Newsletter;
use DB;
use Hash;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Events\SendMessage;
use App\Events\ShippingStatusUpdated;
use App\Services\ElasticsearchService;

class FrontendController extends Controller
{
    public function productSearch(Request $request, ElasticsearchService $elasticsearchService){
        
        $search = $request->query('search');
        $sortBy = $request->query('sortBy', '_score');
        $sortOrder = $request->query('sortOrder', 'desc');
        $perPage = 30;
        $products = $elasticsearchService->searchProducts($request, $perPage);
        
        foreach ($products as $product){
            $reviewCount = count(ProductsReview::where('product_id', $product->id)->get());
            $average = round(ProductsReview::where('product_id', $product->id)->avg('rate'), 1);
            $percentage = ($average/5)*100;
            $product->reviewCount = $reviewCount;
            $product->average = $average;
            $product->percentage = $percentage; 
        }

        return view('frontend.index', compact('products', 'search', 'sortBy', 'sortOrder'));
    }

    public function index(){
        $products = Product::paginate(30);
        foreach ($products as $product) {
            $reviewCount = count(ProductsReview::with('users')->Where('product_id', $product['id'])->get());
            $average = round(ProductsReview::Where('product_id', $product['id'])->avg('rate'), 1);
            $percentage = $average/5*100;
            $product['reviewCount'] = $reviewCount;
            $product['average'] = $average;
            $product['percentage'] = $percentage;
        }
        
        return view('frontend.index')
            ->with('products', $products);
    }

    public function register(){
        return view('frontend.pages.register');
    }

    public function registerSubmit(Request $request){
        $this->validate($request,[
            'nickname'=>'string|required',
            'email'=>'email|required|unique:users,email',
            'password'=>'required|min:6|confirmed',
        ]);

        $data=$request->all();
        
        $check=$this->create($data);
        Session::put('user',$data['email']);
        if($check){
            request()->session()->flash('success','Successfully registered');
            return redirect()->route('home');
        }
        else{
            request()->session()->flash('error','Please try again!');
            return back();
        }
    }

    public function create(array $data){
        $user = User::create([
            'nickname'=>$data['nickname'],
            'email'=>$data['email'],
            'password'=>Hash::make($data['password']),
            'role' => 'admin',
            ]);
        return $user;
    }

    public function login(){
        return view('frontend.pages.login');
    }

    public function loginSubmit(Request $request){
        $this->validate($request,[
            'email'=>'email',
            'password'=>'min:6',
        ]);
        $data= $request->all();
        if(Auth::attempt(['email' => $data['email'], 'password' => $data['password'], 'status'=>'active'])){
            Session::put('user',$data['email']);
            //request()->session()->flash('success','Successfully login');
            return redirect()->route('home');
        }
        else{
            request()->session()->flash('error','電子郵件和密碼無效，請重試！');
            return redirect()->back();
        }
    }

    public function logout(){
        Session::forget('user');
        Auth::logout();
        request()->session()->flash('success','Logout successfully');
        return back();
    }

    public function account(){
        return view('frontend.pages.account');
    }

    public function accountSubmit(Request $request){
        $this->validate($request,[
            'name'=>'string|nullable',
            'cellphone'=>'string|digits:10|nullable',
            'address'=>'string|nullable',
        ]);
        
        $user=Auth::user();
        $data = $request->all();
        $user->fill($data)->save();

        return response()->json(['success' => true, 'message' => 'Account updated successfully.']);
    }

    public function productDetail($slug){
        $product = Product::firstWhere('id', $slug);
        $reviewCount = count(ProductsReview::with('users')->Where('product_id', $product['id'])->get());
        $average = round(ProductsReview::Where('product_id', $product['id'])->avg('rate'), 1);
        $percentage = $average/5*100;
        $homeDeliveryFee = config('shipping.home_delivery');

        $reviews = ProductsReview::with('users')
            ->Where('product_id', $product['id'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        foreach($reviews as $review){
            $review['percentage'] = ($review->rate)/5*100;
        }

        return view('frontend.pages.product_detail')
                ->with('product', $product)
                ->with('homeDeliveryFee', $homeDeliveryFee)
                ->with('reviews', $reviews)
                ->with('average', $average)
                ->with('percentage', $percentage)
                ->with('reviewCount', $reviewCount);
    }

    public function fetchReviews(Request $request)
    {
        $sortBy = $request->query('sort_by');
        $sortOrder  = $request->query('sort_order'); 
        $productId = $request->query('product_id');

        $reviews = ProductsReview::with('users')
            ->Where('product_id', $productId)
            ->orderBy($sortBy, $sortOrder)
            ->paginate(10);
        foreach($reviews as $review){
            $review['percentage'] = ($review->rate)/5*100;
        }

        return response()->json($reviews);
    }

    public function requestAction(Request $request, $slug){
        if(!Auth::check()) {
            return response()->json(['notLongin' => true, 'message' => '請登入']);
        }
        
        $request->validate([
            'quantity' => 'required',
        ]);

        if($request->requestAction=="checkout") {
            $products = [];
            $product = Product::where('id', $slug)->first();
            $product['quantity'] = $request->quantity;
            $product['amount'] = $product['quantity'] * $product->price;
            array_push($products, $product); 
            $subTotal = $product['amount'];
            $fromCart = 0;
            $product['product_id'] = $product->id;
            $homeDeliveryFee = config('shipping.home_delivery');

            return view('frontend.pages.checkout')
                    ->with('carts', $products)
                    ->with('subTotal', $subTotal)
                    ->with('fromCart', $fromCart)
                    ->with('homeDeliveryFee', $homeDeliveryFee);
        }
        else {
            $product = Product::where('id', $slug)->first();
            if($product->stock < $request->quantity){
                return response()->json(['notEnough' => true, 'message' => '庫存不足，請重新選取數量。']);
            }
            $already_cart = Cart::where('user_id', Auth::user()->id)->where('product_id', $product->id)->first();
            if($already_cart) {
                $already_cart->quantity = $already_cart->quantity + $request->quantity;
                $already_cart->amount = ($product->price * $request->quantity)+ $already_cart->amount;
    
                if ($already_cart->product->stock < $already_cart->quantity) {
                    return response()->json(['notEnough' => true, 'message' => '庫存不足，請重新選取數量。']);
                } elseif ($already_cart->product->stock <= 0) {
                    return response()->json(['finish' => true, 'message' => '已售完']);
                }
                $already_cart->save();
            }else{
                $cart = new Cart;
                $cart->user_id = auth()->user()->id;
                $cart->product_id = $product->id;
                $cart->price = $product->price;
                $cart->quantity = $request->quantity;
                $cart->amount=($product->price * $request->quantity);
                if ($product->stock < $cart->quantity) {
                    return response()->json(['notEnough' => true, 'message' => '庫存不足，請重新選取數量。']);
                } elseif ($product->stock <= 0) { 
                    return response()->json(['finish' => true, 'message' => '已售完']);
                }
                $cart->save();
            }
        }
        $count = count(Cart::where('user_id',auth()->user()->id)->get());
        return response()->json(['success' => true, 'count' => $count]);  
    } 

    public function fetchRoomMessages(Request $request){
        $roomId  = Room::where('buyer_id', auth()->user()->id)->select('id')->first();
        $roomId = $roomId->id;
        $messageExists = Message::where('room_id', $roomId)->exists();
        if ($messageExists) {
            $messages = $this->fetchMessages($roomId);

            $groupedMessages = $messages->groupBy('date');

            return response()->json($groupedMessages);
        }
        return response()->json([]);
    }

    public function fetchMessages($roomId){
        $messages = Message::where('room_id', $roomId)
            ->orderBy('created_at', 'asc')
            ->get();
        foreach ($messages as $message) {
            if ($message->sender_id !== auth()->user()->id){
                $message->is_read = true;
                $message->save();
            }
        }
        return $messages->map(function ($message) {
                return [
                    'user_id' => auth()->user()->id,
                    'id' => $message->id,
                    'sender_id' => $message->sender_id,
                    'message' => $message->content,
                    'time' => $message->created_at->format('H:i'),
                    'date' => $message->created_at->format('Y-m-d'),
                ];
            });
    }

    public function sendMessage(Request $request){
        $validated = $request->validate([
            'message' => 'required|string|max:1000',
            'roomId' => 'nullable|integer',
        ]);
        
        if (auth()->user()->role == 'user') {
            $roomExists = Room::where('buyer_id', auth()->user()->id)->exists();
            if (!$roomExists) {
                Room::create([
                    'buyer_id' => auth()->user()->id
                ]);
            }
            
            $roomId  = Room::where('buyer_id', auth()->user()->id)->select('id')->first();
            $roomId = $roomId->id;

            $message = Message::create([
                'room_id' => $roomId,
                'sender_id' => auth()->user()->id, 
                'content' => $validated['message'],
            ]);  
            $messageId = $message->id;

            $message = [
                'message' => $message->content,
                'time' => $message->created_at->format('H:i'),
                'date' => $message->created_at->format('Y-m-d'),
            ];

            broadcast(new SendMessage([
                'message' => $message,
                'roomId' => $roomId,
                'messageId' => $messageId,
                'role' => 'user'
            ]));

            return response()->json($message);

        } elseif (auth()->user()->role == 'admin') {
            $message = Message::create([
                'room_id' => $validated['roomId'],
                'sender_id' => auth()->user()->id, 
                'content' => $validated['message'],
            ]);
            $messageId = $message->id;

            $message = [
                'message' => $message->content,
                'time' => $message->created_at->format('H:i'),
                'date' => $message->created_at->format('Y-m-d'),
            ];

            broadcast(new SendMessage([
                'message' => $message,
                'roomId' => $validated['roomId'],
                'messageId' => $messageId,
                'role' => 'admin'
            ]));

            return response()->json($message);
        }
    }

    public function TokenCreate(Request $request){
        $this->validate($request,[
            'email'=>'required|email',
            'password'=>'required|min:6',
            'tokenName'=>'required|string',
        ]);
        $data=$request->all();
        if (Auth::guard('web')->attempt([
            'email' => $data['email'], 
            'password' => $data['password'], 
            'status'=>'active'
        ])) {
            $user = User::where('email', $data['email'])->first();
            $token = $user->createToken($data['tokenName']);
 
            return ['token' => $token->plainTextToken];
        } else {
            return redirect()->back();
        }
    }

    public function fetchUnreadCount()
    {
        $roomId = Room::where('buyer_id', auth()->user()->id)
            ->select('id')
            ->first();
        $unreadMessages = Message::where('room_id', $roomId->id)
            ->where('is_read', 'false')
            ->whereNotIn('sender_id', [auth()->user()->id])
            ->get();
        $unreadCount = count($unreadMessages);

        return response()->json($unreadCount);
    }

    public function markAsRead(Request $request)
    {
        $this->validate($request,[
            'messageId' => 'required|integer',
        ]);

        $messageId = $request->messageId;
        $message = Message::findOrFail($messageId);
        $message->is_read = true;
        $message->save();

        return response()->json(['status' => 'success']);
    }
}

