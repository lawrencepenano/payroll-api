<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Hash;
use Validator;
use Response;

class UserController extends Controller
{
    function login(Request $request)
    {
        $user= User::where('email', $request->email)->first();
        // print_r($data);
            if (!$user || !Hash::check($request->password, $user->password)) {
                return response([
                    'message' => ['These credentials do not match our records.']
                ], 404);
            }
        
             $token = $user->createToken('my-app-token')->plainTextToken;
        
            $response = [
                'user' => $user,
                'token' => $token
            ];
            
            return Response::json(["status"=>"Success","data"=>$response]);
    }

    function register(Request $request)
    {
        $details = $request->post();
        // Validattion of field
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required',  
            'password' => 'required',
        ]);
        if ($validator->fails()) {
            return Response::json(['status' => 'fail', 'data' => [$validator->errors()]], 400);
        }
        // Validate duplicate emails
        if (User::where('email', $details['email'])->first()) {
            return Response::json(['status' => 'error', 'data' => ['Email is already used.']], 400);
        }
        // To encrypt the password
        $details['password'] = Hash::make($details['password']);
        // Saving
        $user = User::create($details);
        $user->save();

        $token = $user->createToken('my-app-token')->plainTextToken;

        $response = [
            'user' => $user,
            'token' => $token
        ];

        return Response::json(["status"=>"Success","data"=>$response]);
    }
}
