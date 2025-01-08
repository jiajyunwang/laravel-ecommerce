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
use App\Events\ShippingStatusUpdated;
use App\Services\ElasticsearchService;

class FrontendController extends Controller
{
    public function testConnection(){
        $results = app(ElasticsearchService::class)->search('products', '統漿');
        return view('frontend.ccc', compact('results'));
    }
    public function ship()
    {
        $userId = 2;
        $user = user::findOrFail($userId);

        broadcast(new ShippingStatusUpdated($user));

        return response()->json([
            'status' => 'success',
            'message' => '898'
        ]);
    }

    public function index(){
        $products = Product::all();
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

    public function fetchProducts(Request $request)
    {
        $sortBy = $request->query('sort_by', 'created_at'); // 預設按時間排序
        $sortOrder  = $request->query('sort_order ', 'desc'); 
        $productId = $request->query('product_id');

        $reviews = ProductsReview::with('users')
            ->Where('product_id', $productId)
            ->orderBy($sortBy, $sortOrder)
            ->paginate(5);
        foreach($reviews as $review){
            $review['percentage'] = ($review->rate)/5*100;
        }

        return response()->json($reviews);
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
            'role' => 'user',
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
        $product = Product::firstWhere('slug', $slug);
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
            $product = Product::where('slug', $slug)->first();
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
            $product = Product::where('slug', $slug)->first();
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

            // 按日期分組
            $groupedMessages = $messages->groupBy('date');

            return response()->json($groupedMessages);
        }
        return response()->json([]);
    }

    public function fetchMessages($roomId){
        return Message::where('room_id', $roomId)
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($message) {
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
            'roomId'=>'nullable',
        ]);

        if (auth()->user()->role == 'user') {
            $roomExists = Chat::where('buyer_id', auth()->user()->id)->exists();
            if (!$chatExists) {
                Chat::create([
                    'buyer_id' => auth()->user()->id
                ]);
            }

            $chatId  = Chat::where('buyer_id', auth()->user()->id)->select('id')->first();
            $chatId = $chatId->id;

            // 假設您有 Chat 和 Message 模型
            $message = Message::create([
                'chat_id' => $chatId,
                'sender_id' => auth()->user()->id, // 假設有登入系統
                'content' => $validated['message'],
            ]);
            // 回傳訊息資料
            return response()->json([
                'message' => $message->content,
                'time' => $message->created_at->format('H:i'),
                'date' => $message->created_at->format('Y-m-d'),
            ]);

        }elseif (auth()->user()->role == 'admin') {
            $message = Message::create([
                'chat_id' => $validated['chatId'],
                'sender_id' => auth()->user()->id, // 假設有登入系統
                'content' => $validated['message'],
            ]);
            return response()->json([
                'message' => $message->content,
                'time' => $message->created_at->format('H:i'),
                'date' => $message->created_at->format('Y-m-d'),
            ]);
        }
    }

    public function fetchChatList()
    {
        // 取得未讀訊息
        $unreadMessages = Message::where('is_read', 'false')
            ->whereNotIn('sender_id', [auth()->user()->id])
            ->get()
            ->groupBy('chat_id')
            ->map(function ($messages) {
                return count($messages);
            });
    
        // 取得聊天清單並初始化未讀數為 0
        $rooms = Room::with('users')
            ->orderBy('updated_at', 'desc')
            ->get()
            ->map(function ($room) use ($unreadMessages) {
                $unreadCount = $unreadMessages->get($room->id, 0); // 取得未讀數
                return [
                    'id' => $room->id,
                    'nickname' => $room->users->nickname,
                    'unreadCount' => $unreadCount,
                ];
            });
    
        return response()->json($rooms);
    }

    public function TokenCreate(Request $request){
        $this->validate($request,[
            'email'=>'required|email',
            'password'=>'required|min:6',
            'tokenName'=>'required|string',
        ]);
        $data=$request->all();
        if(Auth::guard('web')->attempt(['email' => $data['email'], 'password' => $data['password'], 'status'=>'active'])){
            $user = User::where('email', $data['email'])->first();
            $token = $request->user()->createToken($data['tokenName']);
 
            return ['token' => $token->plainTextToken];
        }
        else{
            return redirect()->back();
        }
    }
}
