<?php

namespace App\Http\Controllers;

use App\Article;
use EloquentBuilder;
use Illuminate\Http\Request;

class ArticleController extends Controller
{

    /**
     * Display a listing of the resource.
     * @param  Request  $request
     * @return Response
     */
    public function index(Request $request)
    {
        $articles = EloquentBuilder::to(Article::class, request()->filter);
        if ($request->has('order') && $request->has('sort')) {
            $response = $articles->orderBy($request->order, $request->sort);
        }
        // Nest relations data
        $response = $articles->with('author:id,first_name,last_name')->get();

        // Enable pagination
        if ($request->has('paginate') && $request->paginate > 0) {
            $response = $articles->paginate($request->paginate); // Paginate with limit
        }

        return response()->json($response, 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        $response = Article::where('id', $id)->with('author:id,first_name,last_name')->first();
        return response()->json($response, 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     * @return Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'author' => 'int',
            'title' => 'required|string',
            'body' => 'required|string',
            'date' => 'required|date_format:Y-m-d H:i',
        ]);

        $article = Article::create($request->all());
        return response()->json($article, 201);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request  $request
     * @param  Article $article
     * @return Response
     */
    public function update(Request $request, Article $article)
    {
        $request->validate([
            'author' => 'int',
            'title' => 'string',
            'body' => 'string',
            'date' => 'date_format:Y-m-d H:i',
        ]);
        $article->update($request->all());
        return response()->json($article, 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Article $article
     * @return Response
     */
    public function destroy(Article $article)
    {
        $article->delete(); // forceDelete for permanentlyx
        return response()->json(null, 204);
    }

}
