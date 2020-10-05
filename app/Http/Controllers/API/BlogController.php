<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Blog;
use App\Http\Resources\BlogResourceCollection;
use Validator;
use Response;

class BlogController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request  )
    {
        $search = $request->query('q');

        $blogs = Blog::when(!empty($search), function ($q) use ($search) {
            return $q->orWhere('subject', 'LIKE', '%' . $search . '%')
                    ->orWhere('body', 'LIKE', '%' . $search . '%');
        })
        ->join('users as b','b.id','user')
        ->select('name', 'blogs.id', 'blogs.created_at', 'user', 'subject')
        ->when($request->query('sortField') &&  $request->query('sortOrder'), function ($q) use ($request) {
            return $q->orderBy($request->query('sortField'), $request->query('sortOrder'));
        })
        /* Pagination */
        ->paginate($request->query('sizePerPage'));

        return (new BlogResourceCollection($blogs))->response()->setStatusCode(200);
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
            'subject' => 'required',  
            'body' => 'required',
            'user' => 'required'
        ]);

        if ($validator->fails()) {
            return Response::json(['status' => 'fail', 'data' => $validator->errors()], 400);
        }

        // Saving
        $blog = Blog::create($data);
        $blog->save();

        return Response::json(['status' => 'success', 'data' => $blog], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $blog = Blog::where('id', $id)->first();

        // Check if found
        if (!$blog){
            return Response::json(['status' => 'fail', 'data' => 'No record found'], 400);
        }
        $blog->comments;
        return Response::json(['status' => 'success', 'data' => $blog], 200);
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
            'subject' => 'required',  
            'body' => 'required',
            'user' => 'required'
        ]);
        
        // To get the blog
        $blog = Blog::where('id', $data['id'])->first();
        
        // To update
        $blog->update($data);

        return Response::json(['status' => 'success', 'data' => $blog], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $blog = Blog::where('id', $id)->delete();
        return Response::json(['status' => 'success', 'data' => $blog], 200);
    }
}
