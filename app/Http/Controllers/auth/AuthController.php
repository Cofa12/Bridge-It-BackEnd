<?php

namespace App\Http\Controllers\auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\SendOTPcode;
use App\Notifications\SendVerificationEmail;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Auth\SessionGuard;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
//use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;
use Laravel\Socialite\Facades\Socialite;
use Ichtrojan\Otp\Otp;

class AuthController extends Controller
{
    //
    public function Register(Request $request){
        $validator = validator::make($request->all(),[
            'name'=>'required|string|max:40',
            'email' => 'required|email',
            'password'=>'required|string',
            'type' => 'string',
            'phone' => 'string'
        ]);

        if($validator->fails()){
            return response()->json([
                'status'=>false,
                'message'=>$validator->errors(),
            ],422);
        }
        try {
            Notification::route('mail',$request->email)->notify(new SendVerificationEmail($request->email));
            $user = User::create([
                'name'=>$request->name,
                'email'=>$request->email,
                'password'=>Hash::make($request->password),
                'type'=> $request->type != '' ? $request->type : 'regular',
                'phone'=> $request->phone != '' ? $request->phone : null,
            ]);
        }catch (UniqueConstraintViolationException $e){
            return response()->json([
                'status'=>false,
                'message'=>'This email is already exist'
            ],422);
        }

        if($user){
            return response()->json([
                'status'=>true,
                'message'=>'Successfully Signed Up'
            ],200);
        }
    }

    public function confirmEmail(Request $request){

        $user = User::where('email',$request->get('email'))->update([
            'email_verified_at'=> now()
        ]);

        echo $request->get('email');

        if($user){
            return view('mails.verified');
        }
    }








    public function providerRegister($provider){
        if($provider == 'twitter'){

            return Socialite::driver($provider)->redirect();
        }
        return Socialite::driver($provider)->stateless()->redirect();
    }
    public function providerRegisterRedirection($provider){
        try {
            if($provider == 'twitter'){
                $user = Socialite::driver($provider)->user();
            }else{
                $user = Socialite::driver($provider)->stateless()->user();
                dd($user);
            }
        } catch (ClientException $exception) {
            return response()->json(['status'=>false,'error' => 'Invalid credentials provided.'], 422);
        }

        User::firstOrCreate([
            'user_id'=>"$user->id",
            'name'=>$user->name,
            'email'=>$user->email,
            'avatar'=>$user->avatar,
            'email_verified_at'=>now()
        ]);

        $storedUserInDB = User::where('email',$user->email)->first();
        $token = $storedUserInDB->createToken('user_token')->plainTextToken;
        $storedUserInDB->token=$token;
        return response()->json([
            'status'=>true,
            'User'=>$storedUserInDB
        ],200);

    }
    public function login(Request $request){
        $validator = validator::make($request->all(),[
            'email'=>'required|email',
            'password'=>'required|string'
        ]);

//        dd(Hash::make('Mahmoud2024##'));

        if($validator->fails()){
            return response()->json([
                'status'=>false,
                'message'=>$validator->errors(),
            ],422);
        }

//        dd($request);

        $user = Auth::attempt($request->only(['email','password']));
        if(!$user){
            return response()->json([
                'status'=>false,
                'message'=>'Email or password is not correct'
            ],401);
        }

        $user = User::where('email',$request->email)->whereNotNull('email_verified_at')->first();
        if(!$user){
            return response()->json([
                'status'=>true,
                'message'=>'Email has not been verified yet!'
            ],422);
        }
        $token = $user->createToken('User_token')->plainTextToken;
        $user->token = $token;
        return response()->json([
            'status'=>true,
            'user'=>$user
        ],200);
    }

    public function requireOTP(Request $request){
        $validator = validator::make($request->all(),[
            'email'=>'required|exists:users'
        ]);

        if($validator->fails()){
            return response()->json([
                'status'=>false,
                'message'=>'This email needs to be registered'
            ],422);
        }

        $data = (new Otp)->generate($request->email,'numeric',4,2);
        Notification::route('mail',$request->email)->notify(new SendOTPcode($data->token));
        return response()->json([
            'status'=>true
        ],200);
    }

    public function validateOTP(Request $request){
        return response()->json([
            'status'=>((new Otp)->validate($request->email, $request->token))->status=='false'
        ],200);
    }

    public function changePassword(Request $request){
        $validator = validator::make($request->all(),[
            'email'=>'email|required|exists:users',
            'password'=>'string|required'
        ]);

        if($validator->fails()){
            return response()->json([
                'status'=>false,
                'message'=>$validator->errors(),
            ],422);
        }

        $passwordUpdate = User::where('email',$request->email)->update([
            'password'=>Hash::make($request->password)
        ]);
        if(!$passwordUpdate){
            return response()->json([
                'status'=>false,
                'message'=>'can\'t change the password'
            ],422);
        }
        return response()->json([
            'status'=>true,
            'message'=>'Password has been changed successfully'
        ],200);
    }
}
