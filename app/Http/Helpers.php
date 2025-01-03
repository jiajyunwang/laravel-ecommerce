<?php

use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;
Use App\Models\User;

class Helper{

    public static function getAllProductFromCart(){
        return Cart::with('product')
            ->where('user_id',auth()->user()->id)
            ->orderBy('updated_at', 'desc')
            ->get();
    }

    public static function getOrderCount($status){
        $orders = Order::with('order_details')
                ->where('user_id', auth()->user()->id)
                ->where('status', $status)
                ->get();
        return count($orders);
    }

    public static function getProductCount($status){
        $products = Product::where('status', $status)->get();
        return count($products);
    }

    public static function getAdminOrdertCount($status){
        $orders = Order::where('status', $status)->get();
        return count($orders);
    }
}
?>