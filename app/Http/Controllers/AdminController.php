<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Room;
use App\Models\Message;
use App\Models\Settings;
use App\Models\Order;
use App\Models\Product;
use App\User;
use App\Rules\MatchOldPassword;
use Barryvdh\DomPDF\Facade\Pdf;
use Dompdf\Dompdf;
use Hash;
use Carbon\Carbon;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class AdminController extends Controller
{
    public function purchaseType(Request $request){
        $products = Product::all();
        $type = $request->query('type');
        $productCol = $unlistedCol = $unhandledCol = $shippingCol = $completedCol = $cancelCol = 'col';
        if ($type==null || $type=='listed' || $type=='unlisted'){
            if ($type==null || $type=='listed'){
                $products =  $products->where('status', 'active');
                $type = 'listed';
                $productCol = 'border';
            }
            elseif ($type == 'unlisted'){
                $products =  $products->where('status', 'inactive');
                $unlistedCol = 'border';
            }

            return view('backend.product.index')
                ->with('productCol', $productCol)
                ->with('unlistedCol', $unlistedCol)
                ->with('unhandledCol', $unhandledCol)
                ->with('shippingCol', $shippingCol)
                ->with('completedCol', $completedCol)
                ->with('cancelCol', $cancelCol)
                ->with('type', $type)
                ->with('products', $products);
        }
        elseif ($type=='unhandled' || $type=='shipping' || $type=='completed' || $type == 'cancel'){
            if ($type == 'unhandled'){
                $orders = Order::with('order_details')
                    ->where('status', 'unhandled')
                    ->paginate(10);
                $unhandledCol = 'border';
            }
            elseif ($type == 'shipping'){
                $orders = Order::with('order_details')
                    ->where('status', 'shipping')
                    ->paginate(10);
                $shippingCol = 'border';
            }
            elseif ($type == 'completed'){
                $orders = Order::with('order_details')
                    ->where('status', 'completed')
                    ->paginate(10);
                $completedCol = 'border';
            }
            elseif ($type == 'cancel'){
                $orders = Order::with('order_details')
                    ->where('status', 'cancel')
                    ->paginate(10);
                $cancelCol = 'border';
            }

            return view('backend.order.index')
                ->with('productCol', $productCol)
                ->with('unlistedCol', $unlistedCol)
                ->with('unhandledCol', $unhandledCol)
                ->with('shippingCol', $shippingCol)
                ->with('completedCol', $completedCol)
                ->with('cancelCol', $cancelCol)
                ->with('type', $type)
                ->with('orders', $orders);
        }
    }

    public function orderDetail($id){
        $order = Order::with('order_details')
            ->where('id', $id)
            ->first();
        $type = $order['status'];
        $data = [
            'order' => $order,
            'type' => $type,
        ];
        $pdf = Pdf::loadView('backend.order.invoice', $data)
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isPhpEnabled' => true
            ]);
        $pdf->getOptions()->set('isHtml5ParserEnabled', true);
        $pdf->getOptions()->set('defaultFont', 'NotoSansTC-Regular');
        
        return $pdf->stream();
    }

    public function toCancel($id)
    {
        $order = Order::with('order_details')
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
        return redirect()->route('admin.order', ['type' => 'unhandled']);
    }

    public function toShipping($id)
    {
        $order = Order::where('status', 'unhandled')
            ->where('id', $id)
            ->first();
        $order['status'] = 'shipping';
        $order->save();
        return redirect()->route('admin', ['type' => 'unhandled']);
    }
    
    public function fetchRoomList()
    {
        $unreadMessages = Message::where('is_read', 'false')
            ->whereNotIn('sender_id', [auth()->user()->id])
            ->get()
            ->groupBy('room_id')
            ->map(function ($messages) {
                return count($messages);
            });
    
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

    public function fetchRoomMessages(Request $request){
        $roomId = $request->query('id');
        $roomId  = Room::where('id', $roomId)->select('id')->first();
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

    public function searchByOrderNumber(Request $request) 
    {
        $this->validate($request,[
            'orderNumber'=>'string|size:16|required',
        ]);
        $orderNumber = $request->orderNumber;
        $order = Order::where('order_number', $orderNumber)->first();
        if (!$order) {
            return redirect()->back()->with('error', '訂單不存在');
        }

        $orders =  Order::with('order_details')
            ->where('order_number', $orderNumber)
            ->paginate(1);
        $type = $order->status;
        return view('backend.order.index')
                    ->with('orders', $orders)
                    ->with('type', $type);
    }
}
