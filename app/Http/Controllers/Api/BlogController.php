<?php

namespace App\Http\Controllers\Api;
use JWTAuth;
use Validator;
use App\Models\User;
use App\Models\Blogs;
use App\Models\FavouriteBlogs;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\RegisterAuthRequest;
use Tymon\JWTAuth\Exceptions\JWTException;
use Symfony\Component\HttpFoundation\Response;

class BlogController extends Controller
{
    //public $token = true;
    public function createBlog(Request $request)
    {
        //dd('sjgas');
        $user = JWTAuth::parseToken()->authenticate();
        $validator = Validator::make($request->all(), 
        [ 
        'title' => 'required',
        'description' => 'required',
        'start_date' => 'required',
        'end_date' => 'required',
        'is_active' => 'required',  
        'image' => 'required',  
        ]);  
        if ($validator->fails()) {  
            return response()->json(['error'=>$validator->errors()], 401); 
        } 
        if(strtotime($request->start_date) < strtotime(date('Y-m-d')))  
        {
            return response()->json([
                'success' => true,
                'message' => 'Please enter valid start date',
                'data' => ''
                ], Response::HTTP_OK);exit;
        }
        $image = $request->file('image');
        $fileName = '';
        if($request->file('image'))
        {
            $fileName = $image->getClientOriginalName();
            $destinationPath = base_path() . '/public/images/' . $fileName;
            $image->move($destinationPath, $fileName);
        }
        $blog = new Blogs();
        $blog->title = $request->title;
        $blog->description = $request->description;
        $blog->start_date = $request->start_date;
        $blog->end_date = $request->end_date;
        $blog->is_active = $request->is_active;
        $blog->user_id = $user->id;
        $blog->image = $fileName;
        $blog->save();

        return response()->json([
        'success' => true,
        'message' => 'blog created successfully.',
        'data' => $blog
        ], Response::HTTP_OK);
    }

    public function updateBlog(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $validator = Validator::make($request->all(), 
        [ 
        'id' => 'required',
        'title' => 'required',
        'description' => 'required',
        'start_date' => 'required',
        'end_date' => 'required',
        'is_active' => 'required',  
        'image' => 'required',  
        ]);  
        if ($validator->fails()) {  
            return response()->json(['error'=>$validator->errors()], 401); 
        }  
         
        $image = $request->file('image');
        $fileName = '';
        if($request->file('image'))
        {
            $fileName = $image->getClientOriginalName();
            $destinationPath = base_path() . '/public/images/' . $fileName;
            $image->move($destinationPath, $fileName);
        }

        $blog = Blogs::find($request->id);
        $blog->title = $request->title;
        $blog->description = $request->description;
        $blog->start_date = $request->start_date;
        $blog->end_date = $request->end_date;
        $blog->is_active = $request->is_active;
        $blog->user_id = $user->id;
        $blog->image = $fileName;
        $blog->save();

        return response()->json([
        'success' => true,
        'message' => 'blog updated successfully.',
        'data' => $blog
        ], Response::HTTP_OK);
    }

    public function deleteBlog(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $validator = Validator::make($request->all(), 
        [ 
            'id' => 'required',
        ]);  
        if ($validator->fails()) {  
            return response()->json(['error'=>$validator->errors()], 401); 
        }   
        if($user->role == '1'){
            $blog = Blogs::where('id',$request->id)->delete();
        }else if($user->role == '0')
        {
            $blog = Blogs::where('id',$request->id)->where('user_id',$user->id)->get();
            if($blog){
                $blog->delete();
            }else{
                return response()->json([
                    'success' => true,
                    'message' => 'Invalid blog id.',
                    'data' => $blog
                    ], Response::HTTP_OK);
                exit;
            }
        }
        return response()->json([
        'success' => true,
        'message' => 'blog deleted successfully.',
        'data' => $blog
        ], Response::HTTP_OK);
    }

    public function blogList(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $validator = Validator::make($request->all(), 
        [ 
            'offset' => 'required',
        ]);  
        if ($validator->fails()) {  
            return response()->json(['error'=>$validator->errors()], 401); 
        }  
        if(isset($request->type) && $request->type=='1')
        {
            $blogid = FavouriteBlogs::where('user_id',$user->id)->get();
            //dd($blogid);
        }
        $offset = $request->offset+10;
        $date_time = date('Y-m-d');
        if(isset($request->type) && $request->type=='1')
        {
            $blog = Blogs::whereIn('id',$blogid->pluck('blog_id'))->where('end_date','>=',$date_time)->where('is_active','1')->offset($request->offset)->limit('10')->get();
        }else{
            $blog = Blogs::where('user_id',$user->id)->where('end_date','>=',$date_time)->where('is_active','1')->offset($request->offset)->limit('10')->get();
        }
        return response()->json(['blog' => $blog,'offset'=>$offset]);
    }

    public function allBlogList(Request $request)
    {
        $validator = Validator::make($request->all(), 
        [ 
            'offset' => 'required',
        ]);  
        if ($validator->fails()) {  
            return response()->json(['error'=>$validator->errors()], 401); 
        }  
        $offset = $request->offset+10;
        $date_time = date('Y-m-d');
        $blog = Blogs::where('end_date','>=',$date_time)->where('is_active','1')->offset($request->offset)->limit('10')->get();
        return response()->json(['blog' => $blog,'offset'=>$offset]);
    }

    public function favouriteBlog(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $validator = Validator::make($request->all(), 
        [ 
            'blog_id' => 'required', 
        ]);  
        if ($validator->fails()) {  
            return response()->json(['error'=>$validator->errors()], 401); 
        } 
        
        $blog = new FavouriteBlogs();
        $blog->blog_id = $request->blog_id;
        $blog->user_id = $user->id;
        $blog->save();

        return response()->json([
        'success' => true,
        'message' => 'Add to favorite successfully.',
        'data' => $blog
        ], Response::HTTP_OK);
    }
}