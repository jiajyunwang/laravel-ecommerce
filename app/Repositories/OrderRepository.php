<?php

namespace App\Repositories;

use App\Models\Order;
use Auth;


class OrderRepository
{
    public function userPaginate($type) 
    {
        $orders =  Order::with('order_details')
            ->where('user_id', Auth::user()->id)
            ->where('status', $type)
            ->orderBy('created_at', 'desc')
            ->paginate(5);

        return $orders;
    }

    public function userFind($id) 
    {
        $order = Order::with('order_details')
            ->where('user_id', Auth::user()->id)
            ->where('id', $id)
            ->first();

        return $order;
    }

    public function findByOrderNumber($orderNumber) 
    {
        return Order::firstWhere('order_number', $orderNumber);
    }

    public function create($order) 
    {
        $order = Order::create($order);

        return $order;
    }
}