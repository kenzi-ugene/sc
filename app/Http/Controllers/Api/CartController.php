<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use App\Traits\LogsActivity;

class CartController extends Controller
{
    use LogsActivity;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $cartItems = Cart::with('product')
            ->where('user_id', $request->user()->id)
            ->get();

        $this->logActivity('view_cart', 'User viewed their cart', [
            'user_id' => $request->user()->id,
            'item_count' => $cartItems->count()
        ]);

        return response()->json($cartItems);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1'
        ]);

        $product = Product::findOrFail($request->product_id);

        if ($product->stock < $request->quantity) {
            return response()->json([
                'message' => 'Not enough stock available'
            ], 422);
        }

        $cartItem = Cart::updateOrCreate(
            [
                'user_id' => $request->user()->id,
                'product_id' => $request->product_id
            ],
            [
                'quantity' => $request->quantity
            ]
        );

        $this->logActivity('add_to_cart', 'User added item to cart', [
            'user_id' => $request->user()->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'quantity' => $request->quantity
        ]);

        return response()->json($cartItem->load('product'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Cart $cart)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1'
        ]);

        if ($cart->product->stock < $request->quantity) {
            return response()->json([
                'message' => 'Not enough stock available'
            ], 422);
        }

        $cart->update([
            'quantity' => $request->quantity
        ]);

        $this->logActivity('update_cart', 'User updated cart item quantity', [
            'user_id' => $request->user()->id,
            'product_id' => $cart->product_id,
            'product_name' => $cart->product->name,
            'old_quantity' => $cart->getOriginal('quantity'),
            'new_quantity' => $request->quantity
        ]);

        return response()->json($cart->load('product'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Cart $cart)
    {
        $this->logActivity('remove_from_cart', 'User removed item from cart', [
            'user_id' => $cart->user_id,
            'product_id' => $cart->product_id,
            'product_name' => $cart->product->name,
            'quantity' => $cart->quantity
        ]);

        $cart->delete();

        return response()->json(['message' => 'Item removed from cart']);
    }

    /**
     * Process the checkout for the user's cart.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function checkout(Request $request)
    {
        $cartItems = Cart::with('product')
            ->where('user_id', $request->user()->id)
            ->get();

        if ($cartItems->isEmpty()) {
            return response()->json([
                'message' => 'Cart is empty'
            ], 422);
        }

        // Validate stock
        foreach ($cartItems as $item) {
            if ($item->product->stock < $item->quantity) {
                return response()->json([
                    'message' => "Not enough stock for {$item->product->name}"
                ], 422);
            }
        }

        // Create order
        $order = Order::create([
            'user_id' => $request->user()->id,
            'total_amount' => $cartItems->sum(function ($item) {
                return $item->product->price * $item->quantity;
            }),
            'status' => 'pending',
            'shipping_address' => $request->shipping_address,
            'billing_address' => $request->billing_address,
            'payment_method' => $request->payment_method,
            'payment_status' => 'pending'
        ]);

        // Create order items and update stock
        foreach ($cartItems as $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item->product_id,
                'quantity' => $item->quantity,
                'price' => $item->product->price
            ]);

            $item->product->decrement('stock', $item->quantity);
        }

        // Clear cart
        $cartItems->each->delete();

        $this->logActivity('checkout', 'User completed checkout', [
            'user_id' => $request->user()->id,
            'order_id' => $order->id,
            'total_amount' => $order->total_amount,
            'item_count' => $cartItems->count(),
            'payment_method' => $request->payment_method
        ]);

        return response()->json([
            'message' => 'Order placed successfully',
            'order' => $order->load('items.product')
        ]);
    }
}
