<?php

namespace App\Http\Controllers;

use Auth;
use App\Models\Cart;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index(){
        $cart = Cart::with('product')
            ->where('user_id',auth()->user()->id)
            ->orderBy('updated_at', 'desc')
            ->get();

        return response()->json(['success' => true, 'cart' => $cart]);
    }

    public function update(Request $request)
    {
        $product_id = $request->product_id;
        $cart = Cart::where('user_id', Auth::user()->id)->where('product_id', $product_id)->first();
        $cart->quantity = $request->quantity;
        $cart->amount = $request->amount;
        $cart->save();

        return response()->json(['success' => true, 'message' => $cart->quantity]);  
    }

    public function destroy(Request $request)
    {
        $id = $request->cart_id;
        $cart = Cart::where('user_id', Auth::user()->id)->findOrFail($id);
        $cart->delete();
        
        return response()->json(['success' => true]);  
    }

    public function destroyCarts(Request $request)
    {
        $ids = $request->check;
        foreach($ids as $id){
            Cart::where('user_id', Auth::user()->id)->findOrFail($id)->delete();
        }
        return response()->json(['success' => true]);  
    }
}
