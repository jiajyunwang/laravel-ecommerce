<?php

namespace App\Http\Controllers;

use Auth;
use App\Models\Cart;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index(){
        return view('frontend.pages.cart');
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

    public function destroy($id)
    {
        $cart = Cart::where('user_id', Auth::user()->id)->findOrFail($id);
        $cart->delete();
        
        return redirect()->route('cart');
    }

    public function destroyCarts(Request $request)
    {
        $ids = $request->check;
        foreach($ids as $id){
            Cart::where('user_id', Auth::user()->id)->findOrFail($id)->delete();
        }
        return redirect()->route('cart');
    }
}
