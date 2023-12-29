<?php

namespace App\Http\Controllers\Api;

//import Model "Product"
use App\Models\Product;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

//import Resource "ProductResource"
use App\Http\Resources\ProductResource;
use Illuminate\Support\Facades\Storage;
//import Facade "Validator"
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    /**
     * index
     *
     * @return void
     */
    public function index()
    {
        //get all products
        $products = Product::latest()->paginate(5);

        //return collection of products as a resource
        return new ProductResource(true, 'List Data Products', $products);
    }

    /**
     * store
     *
     * @param  mixed $request
     * @return void
     */
    public function store(Request $request)
    {
        //define validation rules
        $validator = Validator::make($request->all(), [
            'image'     => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:4048',
            'name'     => 'required',
            'content'   => 'required',
        ]);

        //check if validation fails
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        //upload image
        $image = $request->file('image');
        $image->storeAs('public/products', $image->hashName());

        //create product
        $products = Product::create([
            'image'     => $image->hashName(),
            'name'     => $request->name,
            'content'   => $request->content,
        ]);

        //return response
        return new ProductResource(true, 'Data Product Berhasil Ditambahkan!', $products);
    }
    
    /**
     * show
     *
     * @param  mixed $product
     * @return void
     */
    public function show($id)
    {
        //find post by ID
        $products = Product::find($id);

        //return single product as a resource
        return new ProductResource(true, 'Detail Data Product!', $products);
    }

    /**
     * update
     *
     * @param  mixed $request
     * @param  mixed $product
     * @return void
     */
    public function update(Request $request, $id)
    {
        //define validation rules
        $validator = Validator::make($request->all(), [
            'name'     => 'required',
            'content'   => 'required',
        ]);

        //check if validation fails
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        //find post by ID
        $products = Product::find($id);

        //check if image is not empty
        if ($request->hasFile('image')) {

            //upload image
            $image = $request->file('image');
            $image->storeAs('public/products', $image->hashName());

            //delete old image
            Storage::delete('public/products/'.basename($products->image));

            //update post with new image
            $products->update([
                'image'     => $image->hashName(),
                'name'     => $request->name,
                'content'   => $request->content,
            ]);

        } else {

            //update post without image
            $products->update([
                'name'     => $request->name,
                'content'   => $request->content,
            ]);
        }

        //return response
        return new ProductResource(true, 'Data Product Berhasil Diubah!', $products);
    }
    
    /**
     * destroy
     *
     * @param  mixed $product
     * @return void
     */
    public function destroy($id)
    {

        //find product by ID
        $products = Product::find($id);

        //delete image
        Storage::delete('public/products/'.basename($products->image));

        //delete product
        $products->delete();

        //return response
        return new ProductResource(true, 'Data Product Berhasil Dihapus!', null);
    }
}