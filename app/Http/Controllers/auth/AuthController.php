<?php

namespace App\Http\Controllers\auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\SendOTPcode;
use App\Notifications\SendVerificationEmail;
use GuzzleHttp\Exception\ClientException;
use http\Env\Response;
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
//            Notification::route('mail',$request->email)->notify(new SendVerificationEmail($request->email));
            $user = User::create([
                'name'=>$request->name,
                'email'=>$request->email,
                'password'=>$request['password'],
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
            ],201);
        }
    }

    public function confirmEmail(Request $request){

        $user = User::where('email',$request->get('email'))->update([
            'email_verified_at'=> now()
        ]);

//        echo $request->get('email');

        if($user){
            return view('mails.verified');
        }
    }


    public function providerRegister($provider){
        if($provider=='twitter'){
            return Socialite::driver($provider)->redirect();
        }else{
            return Socialite::driver($provider)->stateless()->redirect();
        }

    }
    public function providerRegisterRedirection($provider){
        try {
            if($provider=='twitter'){
                $user = Socialite::driver($provider)->user();
            }else{
                $user = Socialite::driver($provider)->stateless()->user();
            }

        } catch (ClientException $exception) {
            return response()->json(['status'=>false,'error' => 'Invalid credentials provided.'], 422);
        }

        try {
            User::firstOrCreate([
                'user_id' => "$user->id",
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => $user->avatar,
                'email_verified_at' => now()
            ]);
        }catch (UniqueConstraintViolationException $e){

        } finally {
            $storedUserInDB = User::where('email',$user->email)->first();
            if(isset($storedUserInDB->password)&&$storedUserInDB->password){
                return response()->json([
                    'message'=>[
                        'error'=>'Need To Enter Password'
                    ]
                ],406);
            }
            $token = $storedUserInDB->createToken('user_token')->plainTextToken;
            return response()->json([
                "data"=>[
                    "user"=> $storedUserInDB
                ],
                "token"=>[
                    "access_token"=>$token,
                    "type"=>"Bearer",
                    "expires_in"=>"infinity"
                ]
            ],201);
        }
    }
    public function login(Request $request){
        $validator = validator::make($request->all(),[
            'email'=>'required|email',
            'password'=>'required|string'
        ]);


        if($validator->fails()){
            return response()->json([
                'status'=>false,
                'message'=>$validator->errors(),
            ],422);
        }


        // see this bug
        $user = Auth::attempt($request->only(['email','password']));

        if(!$user){
            return response()->json([
                'status' => false,
                'message' => 'Email or password is not correct'
            ], 401);
        }

        $user = User::where('email',$request->email)->whereNotNull('email_verified_at')->first();
        if(!$user){
            return response()->json([
                'status'=>true,
                'message'=>'Email has not been verified yet!'
            ],422);
        }
        $token = $user->createToken('User_token')->plainTextToken;
        return response()->json([
            "data"=>[
                "user"=> $user
            ],
            "token"=>[
                "access_token"=>$token,
                "type"=>"Bearer",
                "expires_in"=>"infinity"
            ]
        ],200);
    }


    public function requireOTP(Request $request){
        $validator = validator::make($request->all(),[
            'email'=>'required'
        ]);

        if($validator->fails()){
            return response()->json([
                'status'=>false,
                'message'=>$validator->errors()
            ],401);
        }

        $user = User::where('email',$request->email)->first();
        if(!$user){
            return response()->json([
                'status'=>false,
                'message'=>'This email needs to be registered'
            ],422);
        }

        $emailHasUser_id = $user->user_id;
        if($emailHasUser_id){
            return response()->json([
                'error'=>[
                    'message'=>'email can\'t reset password because of registering by platform [google - github ]'
                ]
            ],406);
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

    public function resendOtp(Request $request){
        $this->requireOTP($request);
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
