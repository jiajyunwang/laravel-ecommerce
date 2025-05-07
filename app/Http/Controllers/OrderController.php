<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\ProductReview;
use App\Services\OrderService;
use Auth;
use Carbon\Carbon;
use Stripe;

class OrderController extends Controller
{
    protected $order;
    public function __construct(OrderService $order)
    {
        $this->order = $order;
    }

    public function index(Request $request){
        $type = $request->query('type');
        $page = $request->query('page');
        $unhandledCol = $shippingCol = $completedCol = $cancelCol = 'col';
        if ($type==null || $type=='unhandled'){
            $type = 'unhandled';
            $unhandledCol = 'border';
        }
        elseif ($type == 'shipping'){
            $shippingCol = 'border';
        }
        elseif ($type == 'completed'){
            $completedCol = 'border';
        }
        elseif ($type == 'cancel'){
            $cancelCol = 'border';
        }
        $orders = $this->order->userPaginate($type, $page);

        return view('frontend.order.index')
                    ->with('unhandledCol', $unhandledCol)
                    ->with('shippingCol', $shippingCol)
                    ->with('completedCol', $completedCol)
                    ->with('cancelCol', $cancelCol)
                    ->with('orders', $orders)
                    ->with('type', $type);
    }

    public function fetchOrders(Request $request)
    { 
        $type = $request->query('type');
        $page = $request->query('page');
        $orders = $this->order->userPaginate($$type, $page);

        return view('frontend.layouts.order', compact('orders', 'type'))->render();
    }

    public function orderDetail($id){
        $order = $this->order->userFind($id);
        $type = $order->status;

        return view('frontend.order.order_detail')
                ->with('order', $order)
                ->with('type', $type);
    }

    public function toCompleted($id)
    {
        $status = ['status' => 'completed'];
        $order = $this->order->userUpdateStatus($id, $status);

        return redirect()->route('user.order', ['type' => 'shipping']);
    }

    public function toCancel($id)
    {
        $status = ['status' => 'cancel'];
        $order = $this->order->userUpdateStatus($id, $status);
        foreach ($order->order_details as $orderDetail) {
            $product = Product::where('id', $orderDetail->slug)->first();
            if ( $product) {
                $product->stock += $orderDetail->quantity;
                $product->save();
            }
        }
        return redirect()->route('user.order', ['type' => 'unhandled']);
    }

    public function create(Request $request)
    {
        $carts = [];
        $ids = $request->check;
        $subTotal = 0;
        foreach($ids as $id){
            $cart = Cart::where('user_id', Auth::user()->id)
                ->with('product')
                ->findOrFail($id);
            array_push($carts, $cart); 
            $subTotal += $cart->amount;
            $cart->title = $cart->product->title;
            $cart->price = $cart->product->price;
        }
        $fromCart = 1;
        $homeDeliveryFee = config('shipping.home_delivery');

        return view('frontend.pages.checkout')
                ->with('carts', $carts)
                ->with('subTotal', $subTotal)
                ->with('fromCart', $fromCart)
                ->with('homeDeliveryFee', $homeDeliveryFee);
    }

    public function store(Request $request)
    {   
        $this->validate($request,[
            'name'=>'string|required',
            'cellphone'=>'string|digits:10|required',
            'address'=>'string|required',
            'paymentMethod'=>'string|required'
        ]);

        $data = $request->all();

        if ($request->paymentMethod === 'creditCard') {
            unset($data['stripeToken']);
            Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
            $charge= Stripe\Charge::create ([
                "amount" => $request->totalAmount*100,
                "currency" => "TWD",
                "source" => $request->stripeToken,
                "description" => "Stripe Test Card Payment"
            ]);
        }

        $ids = $data['product_id'];
        $index = 0;
        foreach($ids as $id){
            $product = Product::findOrFail($id);
            $product->stock -= $data['quantity'][$index];
            $index += 1;
            $product->save();
            if($data['fromCart']){
                Cart::where('user_id', Auth::user()->id)->where('product_id', $id)->delete();
            }
        }

        $order = $this->order->create($data);

        $index = 0;
        foreach($ids as $id){
            $orderDetail = new orderDetail;
            $orderDetail->order_number = $order->order_number;
            $product = Product::findOrFail($id);
            $orderDetail->slug = $product->id;
            $orderDetail->title = $product->title;
            $orderDetail->price = $product->price;
            $orderDetail->quantity = $data['quantity'][$index];
            $index += 1;
            $orderDetail->amount = ($orderDetail->price)*($orderDetail->quantity);
            $orderDetail->save();
        }
        $products = Product::all();
        
        return redirect()->route('user.order');
    }

    public function repurchase($id) 
    {
        $order = $this->order->userFind($id);

        foreach ($order->order_details as $item) {
            $productExists = Product::where('id', $item->slug)
                ->where('status', 'active')
                ->exists();
            if (!$productExists) {
                return response()->json(['productExists' => false]);
            }
        }
        foreach ($order->order_details as $item) {
            $product = Product::where('id', $item->slug)
                ->where('status', 'active')
                ->first();
            $already_cart = Cart::where('user_id', Auth::user()->id)
                ->where('product_id', $product->id)
                ->first();
            if($already_cart) {
                $already_cart->quantity += 1;
                $already_cart->amount = $product->price * $already_cart->quantity;
    
                if ($product->stock < $already_cart->quantity) {
                    $already_cart->quantity = $product->stock;
                } 
                $already_cart->save();
            } else {
                $cart = new Cart;
                $cart->user_id = auth()->user()->id;
                $cart->product_id = $product->id;
                $cart->price = $product->price;
                $cart->quantity = 1;
                $cart->amount= $product->price;
                $cart->save();
            }
        }
        return response()->json(['productExists' => true]);
    }

    public function review(Request $request){
        $data = $request->all();
        $order = $this->order->userFind($data['order_id']);

        $count = 0;
        foreach($order->order_details as $order_detail){
            $productId = Product::where('id', $order_detail->slug)
                ->select('id')
                ->first();
            $review = ProductReview::create([
                'user_id'=>$order->user_id,
                'product_id'=>$productId->id,
                'rate'=>$data['rate'][$count],
                'review'=>$data['review'][$count],
            ]);
            $count++;
        }
        $order->isReview = true;
        $order->save();

        return redirect()->back();
    }
}