<?php
namespace App\Http\Controllers;
use App\Models\Article;

class ArticleController extends Controller
{
    public function index()
    {
        $articles = Article::where('is_published', true)->latest()->paginate(9);
        return view('articles.index', compact('articles'));
    }

    public function show(Article $article)
    {
        if (!$article->is_published && (auth()->id() !== $article->author_id)) {
            abort(404);
        }
        $article->load('blocks');
        return view('articles.show', compact('article'));
    }
}