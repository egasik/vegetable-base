<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\ArticleBlock;
use App\Http\Requests\StoreArticleRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ArticleController extends Controller
{
    public function index()
    {
        $articles = Article::with('author')->latest()->paginate(10);
        return view('admin.articles.index', compact('articles'));
    }

    public function create()
    {
        return view('admin.articles.create');
    }

    public function store(StoreArticleRequest $request)
    {
        $article = Article::create([
            'title' => $request->title,
            'author_id' => auth()->id(),
            'is_published' => $request->boolean('is_published'),
        ]);

        $this->saveBlocks($article, $request->blocks);

        return redirect()->route('admin.articles.index')->with('success', 'Статья опубликована!');
    }

    public function edit(Article $article)
    {
        $article->load('blocks');
        return view('admin.articles.edit', compact('article'));
    }

    public function update(StoreArticleRequest $request, Article $article)
    {
        $article->update([
            'title' => $request->title,
            'is_published' => $request->boolean('is_published'),
        ]);

        // Удаляем старые блоки и их файлы
        foreach ($article->blocks as $oldBlock) {
            if ($oldBlock->type === 'image' && $oldBlock->file_path) {
                Storage::disk('public')->delete($oldBlock->file_path);
            }
        }
        $article->blocks()->delete();

        $this->saveBlocks($article, $request->blocks);

        return redirect()->route('admin.articles.index')->with('success', 'Статья обновлена!');
    }

    public function destroy(Article $article)
    {
        foreach ($article->blocks as $block) {
            if ($block->type === 'image' && $block->file_path) {
                Storage::disk('public')->delete($block->file_path);
            }
        }
        $article->delete();
        return redirect()->route('admin.articles.index')->with('success', 'Статья удалена!');
    }

    /**
     * Сохраняет блоки статьи с загрузкой файлов
     */
    private function saveBlocks(Article $article, array $blocks)
    {
        foreach ($blocks as $order => $block) {
            $data = [
                'article_id' => $article->id,
                'type' => $block['type'],
                'sort_order' => $order,
            ];

            if (in_array($block['type'], ['header', 'text'])) {
                $data['content'] = $block['content'];
            }

            if ($block['type'] === 'image') {
                if (!empty($block['existing_image'])) {
                    $data['file_path'] = $block['existing_image'];
                } elseif (isset($block['file'])) {
                    $path = $block['file']->store('articles/' . $article->id, 'public');
                    $data['file_path'] = $path;
                }
            }

            ArticleBlock::create($data);
        }
    }
}