<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArticleBlock extends Model
{
    protected $fillable = ['article_id', 'type', 'content', 'file_path', 'sort_order'];

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }
}