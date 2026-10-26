<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryPhoto extends Model
{
    protected $fillable = ['order_id', 'uploaded_by', 'file_path', 'comment'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
