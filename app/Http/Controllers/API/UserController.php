<?php

namespace App\Http\Controllers\API;
use App\Http\Requests\loginValidate;
use App\Http\Requests\RegisterValidate;
use App\Http\Resources\UserResource;
use GuzzleHttp\Psr7\Response;
use Hash;
use Laravel\Sanctum\HasApiTokens;
use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Validator;

class UserController extends Controller
{
    use HasApiTokens;
    public function register(RegisterValidate $request)
    {
        // the request is coming validated by request form 
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password)//dont forget to hashing the password
        ]);
        return new UserResource($user);
    }

    public function login(loginValidate $request)
    {
        //the Auth::attempt : check if the user are register or not by (email and password)
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json(['message' => 'invaild email or password'], 401);
        }

        $user = User::where('email', $request->email)->firstOrFail();//get the user by email
        $token = $user->createToken('auth-token')->plainTextToken;
        return response()->json([
            'User' => new UserResource($user),
            'token' => $token
        ], 200);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'the user logout succesfully']);
    }


}
