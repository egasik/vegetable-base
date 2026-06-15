<?php
namespace App\Http\Controllers;
use App\Models\Product;

class HomeController extends Controller
{
    public function index()
    {
        // Выводим 8 новых товаров на главную
        $products = Product::latest()->take(8)->get();
        return view('home', compact('products'));
    }
}