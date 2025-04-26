<?php

namespace App\Http\Controllers;

use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Imagick\Driver;
use Intervention\Image\Laravel\Facades\Image;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use App\Models\Cart;
use App\Models\Brand;

use Illuminate\Support\Str;
use Storage;

class ProductController extends Controller
{
    public function create()
    {
        return view('backend.product.create');
    }

    public function store(Request $request)
    {   
        $this->validate($request,[
            'title'=>'string|required',
            'description'=>'required',
            'photo'=>'image|required',
            'stock'=>"required|numeric",
            'price'=>'required|numeric',
        ]);
        
        
        $data = $request->all();
        $input = $request->file('photo');
        $path = $this->imageStore($input);
        $data['photo'] = $path;
        $data['status'] = 'active';
        Product::create($data);

        return redirect()->route('admin');
    }

    public function imageStore($input)
    {
        $manager = new ImageManager(new Driver());
        $image = $manager->read($input);
        $width = $image->width();
        $height = $image->height();
        if($width>=$height){
            $image->pad($width, $width, 'fff');
        }
        else{
            $image->pad($height, $height, 'fff');
        }
        $imageName = date('ymdis').'.jpg';
        $image->save('backend/storage/images/'.$imageName);
        $path = 'backend/storage/images/'.$imageName;
        
        return $path;
    }

    public function edit($id)
    {
        $product=Product::findOrFail($id);

        return view('backend.product.edit')->with('product',$product);
    }

    public function update(Request $request, $id)
    {
        $this->validate($request,[
            'title'=>'string|required',
            'description'=>'required',
            'photo'=>'image',
            'stock'=>"required|numeric",
            'price'=>'required|numeric',
        ]);

        $product=Product::findOrFail($id);
        $data = $request->all();
        if (isset($data['photo'])){
            $this->imageDelete($product);
            $input = $request->file('photo');
            $path = $this->imageStore($input);
            $data['photo'] = $path;
        }
        $product->fill($data)->save();

        return redirect()->route('admin');
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $this->imageDelete($product);
        $product->delete();
        
        return redirect()->route('admin');
    }

    public function destroyProducts(Request $request)
    {
        $ids = $request->check;
        foreach($ids as $id){
            $product = Product::findOrFail($id);
            $this->imageDelete($product);
            $product->delete();
        }
        return redirect()->route('admin');
    }

    public function imageDelete($product){
        $path = public_path($product->photo);

        if (file_exists($path)) {
            unlink($path);
        }
    }

    public function toInactive($id)
    {
        $product = Product::where('status', 'active')
            ->where('id', $id)
            ->first();
        $product->status = 'inactive';
        $product->save();

        $carts = Cart::where('product_id', $id)->get();
        foreach ($carts as $cart) {
            $cart->delete();
        }

        return redirect()->route('admin');
    }

    public function toActive($id)
    {
        $product = Product::where('status', 'inactive')
            ->where('id', $id)
            ->first();
        $product->status = 'active';
        $product->save();

        return redirect()->route('admin', ['type' => 'unlisted']);
    }
}
