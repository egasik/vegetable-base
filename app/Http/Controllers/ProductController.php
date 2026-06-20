<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('category')->whereNull('deleted_at');

        // Поиск по названию
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        // Фильтр по категории
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        // Фильтр по розничной цене
        if ($request->filled('min_price')) {
            $query->where('retail_price', '>=', $request->min_price);
        }
        if ($request->filled('max_price')) {
            $query->where('retail_price', '<=', $request->max_price);
        }

        $products = $query->paginate(9)->withQueryString();
        $categories = Category::all();

        return view('products.index', compact('products', 'categories'));
    }

    public function show(Product $product)
    {
        return view('products.show', compact('product'));
    }
}