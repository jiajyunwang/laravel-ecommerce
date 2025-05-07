<?php

namespace App\Services;

use Illuminate\Support\Str;
use App\Repositories\OrderRepository;
use Auth;
use Carbon\Carbon;

class OrderService
{
    protected $order;
    public function __construct(OrderRepository $order)
    {
        $this->order = $order;
    }

    public function userPaginate($type, $page) 
    {
        return $this->order->userPaginate($type, $page);
    }

    public function userFind($id) 
    {
        return $this->order->userFind($id);
    }

    public function userUpdateStatus($id, $status) 
    {
        $order = $this->userFind($id);
        $order->update($status);
        return $order;
    }

    public function create($data) 
    {
        $orderNumber = $this->createOrderNumber();
        $order['order_number'] = $orderNumber;
        $order['user_id'] = Auth::user()->id;
        $order['total_amount'] = $data['totalAmount'];
        $order['name'] = $data['name'];
        $order['phone'] = $data['cellphone'];
        $order['address'] = $data['address'];
        $order['note'] = $data['note'];
        $order['payment_method'] = $data['paymentMethod'];
        $order['sub_total'] = $data['subTotal'];
        $order['shipping_fee'] = $data['shippingFee'];

        return $this->order->create($order);
    }

    public function createOrderNumber() 
    {
        $orderNumber = null;
        $count = 1;
        while($count>0){
            $random = Str::random(8);
            $random = Str::upper($random);
            $carbon = Carbon::now('Asia/Taipei')->isoFormat('YMMDD');
            $orderNumber = $carbon.$random;
            $count = collect($this->findByOrderNumber($orderNumber))->count();
        }

        return $orderNumber;
    }

    public function findByOrderNumber($orderNumber) 
    {
        return $this->order->findByOrderNumber($orderNumber);
    }
}