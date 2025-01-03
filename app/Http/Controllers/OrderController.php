<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\ProductsReview;
use Auth;
use Carbon\Carbon;

class OrderController extends Controller
{
    public function index(Request $request){
        $type = $request->query('type');
        $unhandledCol = $shippingCol = $completedCol = $cancelCol = 'col';
        if ($type==null || $type=='unhandled'){
            $orders =  Order::with('order_details')
                ->where('user_id', Auth::user()->id)
                ->where('status', 'unhandled')
                ->orderBy('created_at', 'desc')
                ->paginate(5);
            $unhandledCol = 'border';
            $type = 'unhandled';
        }
        elseif ($type == 'shipping'){
            $orders =  Order::with('order_details')
                ->where('user_id', Auth::user()->id)
                ->where('status', 'shipping')
                ->orderBy('created_at', 'desc')
                ->paginate(5);
            $shippingCol = 'border';
        }
        elseif ($type == 'completed'){
            $orders =  Order::with('order_details')
                ->where('user_id', Auth::user()->id)
                ->where('status', 'completed')
                ->orderBy('created_at', 'desc')
                ->paginate(5);
            $completedCol = 'border';
        }
        elseif ($type == 'cancel'){
            $orders =  Order::with('order_details')
                ->where('user_id', Auth::user()->id)
                ->where('status', 'cancel')
                ->orderBy('created_at', 'desc')
                ->paginate(5);
            $cancelCol = 'border';
        }

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
        $orders = Order::with('order_details')
            ->where('user_id', Auth::user()->id)
            ->where('status', $type)
            ->orderBy('created_at', 'desc')
            ->paginate(5);

        return view('frontend.layouts.order', compact('orders', 'type'))->render();
    }

    public function orderDetail($id){
        $order = Order::with('order_details')
            ->where('user_id', Auth::user()->id)
            ->where('id', $id)
            ->first();
        $type = $order['status'];
        return view('frontend.order.order_detail')
                ->with('order', $order)
                ->with('type', $type);
    }

    public function toCompleted($id)
    {
        $order = Order::where('user_id', Auth::user()->id)
            ->where('status', 'shipping')
            ->where('id', $id)
            ->first();
        $order['status'] = 'completed';
        $order->save();
        return redirect()->route('user.order', ['type' => 'shipping']);
    }

    public function toCancel($id)
    {
        $order = Order::where('user_id', Auth::user()->id)
            ->where('status', 'unhandled')
            ->where('id', $id)
            ->first();
        $order['status'] = 'cancel';
        $order->save();
        foreach ($order['order_details'] as $orderDetail) {
            $product = Product::where('slug', $orderDetail->slug)->first();
            if ( $product) {
                $product['stock'] += $orderDetail['quantity'];
                $product->save();
            }
        }
        return redirect()->route('user.order', ['type' => 'unhandled']);
    }

    public function aaa($id)
    {
        $order = Order::where('user_id', Auth::user()->id)
            ->where('id', $id)
            ->first();
        $order['status'] = 'unhandled';
        $order->save();
    }

    public function bbb($id)
    {
        $order = Order::where('user_id', Auth::user()->id)
            ->where('id', $id)
            ->first();
        $order['status'] = 'shipping';
        $order->save();
    }

    public function create(Request $request)
    {
        $carts = [];
        $ids = $request->check;
        $subTotal = 0;
        foreach($ids as $id){
            $cart = Cart::with('product')->findOrFail($id);
            array_push($carts, $cart); 
            $subTotal += $cart->amount;
            $cart['title'] = $cart->product['title'];
            $cart['price'] = $cart->product['price'];
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
        ]);
        $data = $request->all();

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

        $order = new Order;
        $count = 1;
        while($count>0){
            $random = Str::random(8);
            $random = Str::upper($random);
            $carbon = Carbon::now('Asia/Taipei')->isoFormat('YMD');
            $order_number = $carbon.$random;
            $count = collect(Order::firstWhere('order_number',$order_number))->count();
        }
        $order->order_number = $order_number;
        $order->user_id = auth()->user()->id;
        $order->total_amount = $data['totalAmount'];
        $order->name = $data['name'];
        $order->phone = $data['cellphone'];
        $order->address = $data['address'];
        $order->note = $data['note'];
        $order->payment_method = $data['paymentMethod'];
        $order->sub_total = $data['subTotal'];
        $order->shipping_fee = $data['shippingFee'];
        $order->save();

        $index = 0;
        foreach($ids as $id){
            $orderDetail = new orderDetail;
            $orderDetail->order_number = $order_number;
            $product = Product::findOrFail($id);
            $orderDetail->slug = $product['slug'];
            $orderDetail->title = $product['title'];
            $orderDetail->price = $product['price'];
            $orderDetail->quantity = $data['quantity'][$index];
            $index += 1;
            $orderDetail->amount = ($orderDetail->price)*($orderDetail->quantity);
            $orderDetail->save();
        }
        $products = Product::all();
        
        return $this->index(new Request());
    }

    public function repurchase($id) 
    {
        $order = Order::with('order_details')
            ->where('user_id', Auth::user()->id)
            ->where('id', $id)
            ->first();
        foreach ($order['order_details'] as $item) {
            $productExists = Product::where('slug', $item->slug)
                ->where('status', 'active')
                ->exists();
            if (!$productExists) {
                return response()->json(['productExists' => false]);
            }
        }
        foreach ($order->order_details as $item) {
            $product = Product::where('slug', $item->slug)
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
        $order = Order::with('order_details')
            ->where('user_id', Auth::user()->id)
            ->where('id', $data['order_id'])
            ->first();
        $count = 0;
        foreach($order['order_details'] as $order_detail){
            $productId = Product::where('slug', $order_detail['slug'])
                ->select('id')
                ->first();
            ProductsReview::create([
                'user_id'=>$order['user_id'],
                'product_id'=>$productId->id,
                'rate'=>$data['rate'][$count],
                'review'=>$data['review'][$count],
                ]);
            $count++;
        }
        return redirect()->back();
    }
}