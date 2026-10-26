<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\CartPricing;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CartController extends Controller
{
    public function __construct(private CartPricing $pricing) {}

    public function index()
    {
        $cart = session('cart', []);
        $unavailable = [];
        foreach ($cart as $key => $entry) {
            $product = Product::find($entry['product_id']);
            try {
                if (! $product) {
                    throw ValidationException::withMessages(['cart' => 'Товар удалён']);
                }
                $this->pricing->line($product, $entry['purchase_type'], $entry['quantity']);
            } catch (ValidationException $e) {
                $unavailable[] = ($product?->name ?? 'Товар').': '.$e->validator->errors()->first();
                unset($cart[$key]);
            }
        }
        session(['cart' => $cart]);

        return view('cart.index', [...$this->pricing->quote($cart), 'unavailable' => $unavailable]);
    }

    public function add(Request $request, Product $product)
    {
        $data = $request->validate(['purchase_type' => 'required|in:retail,wholesale', 'quantity' => 'required|numeric']);
        $this->pricing->line($product, $data['purchase_type'], $data['quantity']);
        $cart = session('cart', []);
        $key = $product->id.'_'.$data['purchase_type'];
        $quantity = ($cart[$key]['quantity'] ?? 0) + $data['quantity'];
        $this->pricing->line($product, $data['purchase_type'], $quantity);
        $cart[$key] = ['product_id' => $product->id, 'purchase_type' => $data['purchase_type'], 'quantity' => $quantity];
        session(['cart' => $cart]);

        return $this->respond($request, 'Товар добавлен в корзину');
    }

    public function update(Request $request, string $cartKey)
    {
        $data = $request->validate(['quantity' => 'required|numeric']);
        $cart = session('cart', []);
        abort_unless(isset($cart[$cartKey]), 404);
        $product = Product::findOrFail($cart[$cartKey]['product_id']);
        $line = $this->pricing->line($product, $cart[$cartKey]['purchase_type'], $data['quantity']);
        $cart[$cartKey]['quantity'] = $line['quantity'];
        session(['cart' => $cart]);

        return $this->respond($request, 'Количество обновлено');
    }

    public function remove(Request $request, string $cartKey)
    {
        $cart = session('cart', []);
        unset($cart[$cartKey]);
        session(['cart' => $cart]);

        return $this->respond($request, 'Товар удалён из корзины');
    }

    public function clear(Request $request)
    {
        session()->forget('cart');

        return $this->respond($request, 'Корзина очищена');
    }

    private function respond(Request $request, string $message)
    {
        return $request->expectsJson()
            ? response()->json(['message' => $message, 'count' => count(session('cart', []))])
            : back()->with('success', $message);
    }
}
