<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'category_id', 
        'name', 
        'description', 
        'image_path', 
        'is_retail', 
        'retail_price', 
        'is_wholesale', 
        'wholesale_unit_kg', 
        'wholesale_price'
    ];

    protected $casts = [
        'is_retail' => 'boolean',
        'is_wholesale' => 'boolean',
        'retail_price' => 'decimal:2',
        'wholesale_price' => 'decimal:2',
    ];

    public function category() { return $this->belongsTo(Category::class); }
}