<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Traits\LogsActivity;

class ProductController extends Controller
{
    use LogsActivity;

    public function index(Request $request)
    {
        $query = Product::with(['categories', 'brands', 'attributes.attribute'])
            ->filter($request->all());

        $products = $query->paginate(12);
        $categories = Category::all();
        $brands = Brand::all();

        // Transform prices based on user's price tier
        $products->getCollection()->transform(function ($product) {
            $product->price = $product->getPriceForUser(auth()->user());
            return $product;
        });

        $this->logActivity('view_products', 'Viewed product list', [
            'filters' => $request->all()
        ]);

        return response()->json([
            'products' => $products,
            'categories' => $categories,
            'brands' => $brands
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'base_price' => 'required|numeric|min:0',
            'prices' => 'array',
            'prices.*.price_tier' => 'required|string',
            'prices.*.price' => 'required|numeric|min:0',
        ]);

        $product = Product::create([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'base_price' => $validated['base_price'],
        ]);

        if (isset($validated['prices'])) {
            foreach ($validated['prices'] as $priceData) {
                $product->prices()->create([
                    'price_tier' => $priceData['price_tier'],
                    'price' => $priceData['price'],
                ]);
            }
        }

        $product->load('prices');

        $this->logActivity('create_product', 'Created new product', [
            'product_id' => $product->id
        ]);

        return response()->json($product->load(['categories', 'brands', 'attributes.attribute', 'prices']));
    }

    public function show(Product $product)
    {
        $product->load(['categories', 'brands', 'attributes.attribute', 'prices']);
        $product->price = $product->getPriceForUser(auth()->user());

        $this->logActivity('view_product', 'Viewed product details', [
            'product_id' => $product->id
        ]);

        return response()->json($product);
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'image' => 'nullable|image|max:2048',
        ]);

        $product->name = $validated['name'];
        $product->description = $validated['description'];
        $product->price = $validated['price'];
        $product->stock = $validated['stock'];

        if ($request->hasFile('image')) {
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $path = $request->file('image')->store('products', 'public');
            $product->image = $path;
        }

        $product->save();

        $this->logActivity('update_product', 'Updated product', [
            'product_id' => $product->id
        ]);

        return response()->json($product->load('prices'));
    }

    public function destroy(Product $product)
    {
        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        $this->logActivity('delete_product', 'Deleted product', [
            'product_id' => $product->id
        ]);

        $product->delete();

        return response()->json(['message' => 'Product deleted successfully']);
    }
}