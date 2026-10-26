<?php

namespace App\Http\Controllers;

use App\Services\CartPricing;
use App\Services\Geocoder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    public function show(Request $request, CartPricing $pricing)
    {
        if (! session('cart')) {
            return redirect()->route('cart.index');
        }
        try {
            $quote = $pricing->quote(session('cart'));
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->route('cart.index')->withErrors($e->errors());
        }
        session(['checkout_quote' => hash('sha256', json_encode(array_map(fn ($line) => [
            $line['product_id'], $line['purchase_type'], $line['quantity'], $line['price'], $line['unit_weight_kg'],
        ], $quote['items'])))]);
        if (! session('checkout_token')) {
            session(['checkout_token' => (string) Str::uuid()]);
        }

        return view('checkout.show', $quote);
    }

    public function address(Request $request, Geocoder $geocoder)
    {
        session()->forget('checkout_address');
        $data = $request->validate(['delivery_address' => 'required_without:latitude|nullable|string|min:8|max:255',
            'latitude' => 'nullable|numeric|between:-90,90|required_with:longitude',
            'longitude' => 'nullable|numeric|between:-180,180|required_with:latitude']);
        $query = isset($data['latitude']) ? $data['longitude'].','.$data['latitude'] : $data['delivery_address'];
        $address = $geocoder->resolve($query);
        session(['checkout_address' => [...$address, 'verified_at' => time()]]);

        return back()->withInput()->with('success', 'Дом найден. Проверьте адрес и подтвердите оформление.');
    }
}
