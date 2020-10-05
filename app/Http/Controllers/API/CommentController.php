<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use Response;
use App\Models\Comment;

class CommentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
      //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = $request->post();
        // Validattion of field
        $validator = Validator::make($request->all(), [
            'blog_id' => 'required',  
            'comment' => 'required',
            'commentor' => 'required'
        ]);

        if ($validator->fails()) {
            return Response::json(['status' => 'fail', 'data' => $validator->errors()], 400);
        }

        // Saving
        $comment = Comment::create($data);
        $comment->save();

        return Response::json(['status' => 'success', 'data' => $comment], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $data = $request->post();
        // Validattion of field
        $validator = Validator::make($request->all(), [
            'blog_id' => 'required',  
            'comment' => 'required',
            'commentor' => 'required'
        ]);
        
        // To get the blog
        $comment = Comment::where('id', $data['id'])->first();
        
        // To update
        $comment->update($data);

        return Response::json(['status' => 'success', 'data' => $comment], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $comment = Comment::where('id', $id)->delete();
        return Response::json(['status' => 'success', 'data' => $comment], 200);
    }
}
