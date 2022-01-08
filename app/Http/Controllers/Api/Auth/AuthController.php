<?php

namespace App\Http\Controllers\Api\Auth;
use JWTAuth;
use Validator;
use Auth;
use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\RegisterAuthRequest;
use Tymon\JWTAuth\Exceptions\JWTException;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    public $token = true;
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), 
        [ 
        'name' => 'required',
        'email' => 'required|email',
        'dob' => 'required',
        'image' => 'required',
        'role' => 'required',  
        'password' => 'required',  
        'c_password' => 'required|same:password', 
        ]);  
        if ($validator->fails()) {  
            return response()->json(['error'=>$validator->errors()], 401); 
        }  
        $email = User::where('email',$request->email)->first();
        if($email)
        {
            return response()->json([
                'success' => false,
                'message' => 'email exist'
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
        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->dob = $request->dob;
        $user->image = $fileName;
        $user->role = $request->role;
        $user->password = bcrypt($request->password);
        $user->save();

        return response()->json([
        'success' => true,
        'data' => $user
        ], Response::HTTP_OK);
    }

    public function login(Request $request)
    {
        $input = $request->only('email', 'password');
        $jwt_token = null;
        if (!$jwt_token = JWTAuth::attempt($input)) {
        return response()->json([
        'success' => false,
        'message' => 'Invalid Email or Password',
        ], Response::HTTP_UNAUTHORIZED);
        }
        return response()->json([
        'success' => true,
        'message' => 'Successfully Logged In.',
        'token' => $jwt_token,
        ]);
    }

    public function logout(Request $request)
    {
        $this->validate($request, [
        'token' => 'required'
        ]);
        try {
        JWTAuth::invalidate($request->token);
        return response()->json([
        'success' => true,
        'message' => 'User logged out successfully'
        ]);
        } catch (JWTException $exception) {
        return response()->json([
        'success' => false,
        'message' => 'Sorry, the user cannot be logged out'
        ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getProfile(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        return response()->json(['user' => $user]);
    }
}