<?php

namespace App\Http\Controllers;

use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Imagick\Driver;
use Intervention\Image\Laravel\Facades\Image;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
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
        
        $manager = new ImageManager(new Driver());
        $data = $request->all();
        $input = $request->file('photo');
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
        $image->save('storage/images/'.$imageName);
        $path = 'storage/images/'.$imageName;
        $data['photo'] = $path;
        Product::create($data);

        return redirect()->route('admin');
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
            $path = $request->file('photo')->store('public/images');
            $data['photo'] = str_replace('public', 'storage', $path);
        }
        $product->fill($data)->save();

        return redirect()->route('admin');
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $photo = str_replace('storage', 'public', $product->photo);
        Storage::delete($photo);
        $product->delete();
        
        return redirect()->route('admin');
    }

    public function destroyProducts(Request $request)
    {
        $ids = $request->check;
        foreach($ids as $id){
            Product::findOrFail($id);
            $photo = str_replace('storage', 'public', $product->photo);
            Storage::delete($photo);
            $product->delete();
        }
        return redirect()->route('admin');
    }

    public function toInactive($id)
    {
        $product = Product::where('status', 'active')
            ->where('id', $id)
            ->first();
        $product['status'] = 'inactive';
        $product->save();
        return redirect()->route('admin');
    }

    public function toActive($id)
    {
        $product = Product::where('status', 'inactive')
            ->where('id', $id)
            ->first();
        $product['status'] = 'active';
        $product->save();
        return redirect()->route('admin', ['type' => 'unlisted']);
    }
}
