<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreArticleRequest;
use App\Http\Requests\UpdateRequest;
use App\Models\Article;
use App\Models\Category;
use App\Models\TemporaryFile;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ArticleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Application|Factory|View
     */
    public function index()
    {
        $articles = Article::select(['id', 'title', 'created_at', 'fulltext', 'image'])->withCount('tags')->paginate(20);
        return view('articles.index', compact('articles'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Application|Factory|View
     */
    public function create()
    {
        $categories = Category::get(['name', 'id']);
        return view('articles.create', ['categories' => $categories]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreArticleRequest $request
     * @return RedirectResponse
     */
    public function store(StoreArticleRequest $request)
    {
        $article = Article::create([
            'title' => $request->title,
            'fulltext' => $request->fulltext,
            'category_id' => $request->category_id
        ]);

        abort_if(!$this->storeImage($request, $article), 500);
        return redirect()->route('article.index')->with(['message' => 'Successfully created']);
    }

    /**
     * @param Request $request
     * @param $article
     * @return bool
     */
    public function storeImage(Request $request, $article): bool
    {
        /* Debugbar css styles concatenating with $request->article_image :( */
        if (strpos($request->article_image, '<')) {
            $request->article_image = explode('<', $request->article_image)[0];
        }

        $tempFile = TemporaryFile::where('folder', $request->article_image)->first();
        if ($tempFile) {
            Storage::disk('public_uploads')->putFileAs('/image',
                storage_path('app/public/image/tmp/' . $request->article_image . '/' . $tempFile->filename),
                Auth::id() . '_' . $tempFile->filename);

            $article->update([
                'image' => Auth::id() . '_' . $tempFile->filename
            ]);
            unlink(storage_path('app/public/image/tmp/' . $request->article_image . '/' . $tempFile->filename));
            rmdir(storage_path('app/public/image/tmp/' . $request->article_image));
            $tempFile->delete();
        }
        return true;
    }

    /**
     * Display the specified resource.
     *
     * @param $id
     * @return Application|Factory|View
     */
    public function show($id)
    {
        return view('articles.show', ['article' => Article::findOrFail($id)]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param $id
     * @return Application|Factory|View
     */
    public function edit($id)
    {
        $categories = Category::get(['name', 'id']);
        return view('articles.edit', ['article' => Article::findOrFail($id), 'categories' => $categories]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateRequest $request
     * @param $id
     * @return RedirectResponse
     */
    public function update(UpdateRequest $request, $id)
    {
        $article = Article::findOrfail($id);
        $article->update([
            'title' => $request->title,
            'fulltext' => $request->fulltext,
            'category_id' => $request->category_id
        ]);

        $this->storeImage($request, $article);
        return redirect()->route('article.index')->with(['message' => 'Successfully updated']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param $id
     * @return RedirectResponse
     */
    public function destroy($id): RedirectResponse
    {
        Article::findOrFail($id)->delete();
        return redirect()->route('article.index')->with(['message' => 'Successfully deleted']);
    }

    /**
     * @param Request $request
     * @return string
     */
    public function upload(Request $request): string
    {
        if ($request->hasFile('article_image')) {
            $file = $request->file('article_image');
            $fileName = $file->getClientOriginalName();
            $folder = uniqid() . '-' . now()->timestamp;
            $file->storeAs('public/image/tmp/' . $folder, $fileName);

            TemporaryFile::create([
                'filename' => $fileName,
                'folder' => $folder
            ]);

            return $folder;
        }
        return '';
    }
}
