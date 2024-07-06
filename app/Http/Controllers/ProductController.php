<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::all();
        return view('products.index', compact('products'));
    }

    public function create()
    {
        return view('products.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_name' => 'required',
            'product_price' => 'required|numeric',
            'product_description' => 'required',
            'product_images' => 'required',
            'product_images.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $images = [];
        if ($request->hasFile('product_images')) {
            foreach ($request->file('product_images') as $image) {
                $path = $image->store('products', 'public');
                $images[] = $path;
            }
        }

        Product::create([
            'product_name' => $request->product_name,
            'product_price' => $request->product_price,
            'product_description' => $request->product_description,
            'product_images' => json_encode($images),
        ]);

        return response()->json(['success' => 'Product added successfully.']);
    }

    public function edit($id)
    {
        $product = Product::find($id);
        return response()->json($product);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'product_name' => 'required',
            'product_price' => 'required|numeric',
            'product_description' => 'required',
            'product_images' => 'sometimes',
            'product_images.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $product = Product::find($id);
        $images = json_decode($product->product_images, true);

        if ($request->hasFile('product_images')) {
            foreach ($request->file('product_images') as $image) {
                $path = $image->store('products', 'public');
                $images[] = $path;
            }
        }

        $product->update([
            'product_name' => $request->product_name,
            'product_price' => $request->product_price,
            'product_description' => $request->product_description,
            'product_images' => json_encode($images),
        ]);

        return response()->json(['success' => 'Product updated successfully.']);
    }

    public function destroy($id)
    {
        $product = Product::find($id);
        $images = json_decode($product->product_images, true);
        foreach ($images as $image) {
            Storage::disk('public')->delete($image);
        }
        $product->delete();

        return response()->json(['success' => 'Product deleted successfully.']);
    }

    public function deleteImage(Request $request)
    {
        $product = Product::find($request->product_id);
        $images = json_decode($product->product_images, true);

        if (($key = array_search($request->image_name, $images)) !== false) {
            unset($images[$key]);

            // Delete the image file from storage
            Storage::disk('public')->delete($request->image_name);

            // Update the product's image list
            $product->product_images = json_encode(array_values($images));
            $product->save();

            return response()->json(['success' => 'Image deleted successfully.']);
        }

        return response()->json(['error' => 'Image not found.'], 404);
    }
}
