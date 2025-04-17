<?php

namespace App\Http\Controllers\auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\ConfirmEmailRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use App\Notifications\SendOTPcode;
use App\Notifications\SendVerificationEmail;
use App\SaveSocialiteData;
use GuzzleHttp\Exception\ClientException;
use http\Env\Response;
use Illuminate\Auth\SessionGuard;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
//use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;
use Laravel\Socialite\Facades\Socialite;
use Ichtrojan\Otp\Otp;
use \Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    //
//    use SaveSocialiteData;
    public function Register(RegisterRequest $request): JsonResponse
    {
        $data=$request->validated();
        $data['type'] = $request->type != '' ? $request->type : 'regular';
        $data['phone'] =  $request->phone != '' ? $request->phone : null;
        try {
            DB::beginTransaction();
            $user = User::create($data);
//            Notification::route('mail',$request->email)
//                ->notify(new SendVerificationEmail($request->email));
            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Registered Successfully',
            ], 201);

        }catch (\Exception $e){
            DB::rollBack();

            return response()->json([
                'status'=>false,
                'message'=>$e->getMessage()
            ],422);
        }
    }

    public function confirmEmail(ConfirmEmailRequest $request)
    {
        $user = User::where('email',$request->get('email'))->update([
            'email_verified_at'=> now()
        ]);

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
//        try {
//            if($provider=='twitter'){
//                $user = Socialite::driver($provider)->user();
//            }else{
//                $user = Socialite::driver($provider)->stateless()->user();
//            }
//
//        } catch (ClientException $exception) {
//            return response()->json(['status'=>false,'error' => 'Invalid credentials provided.'], 422);
//        }
//
//        try {
//            User::firstOrCreate([
//                'user_id' => "$user->id",
//                'name' => $user->name,
//                'email' => $user->email,
//                'avatar' => $user->avatar,
//                'email_verified_at' => now()
//            ]);
//        }catch (UniqueConstraintViolationException $e){
//
//        } finally {
//            $storedUserInDB = User::where('email',$user->email)->first();
//            if(isset($storedUserInDB->password)&&$storedUserInDB->password){
//                Cache::store('database')->put('message',"need to enter password",600);
//                return response()->json([
//                    'message'=>[
//                        'error'=>'Need To Enter Password'
//                    ]
//                ],406);
//            }
//            $token = $storedUserInDB->createToken('user_token')->plainTextToken;
//            Cache::store('database')->put('user',$storedUserInDB,600);
//            Cache::store('database')->put('token',$token,600);
//            return response()->json([
//                "data"=>[
//                    "user"=> $storedUserInDB
//                ],
//                "token"=>[
//                    "access_token"=>$token,
//                    "type"=>"Bearer",
//                    "expires_in"=>"infinity"
//                ]
//            ],201);
//        }
    }

    public function getCredentialsUser(){
        if(Cache::store('database')->has('message')){
            return response()->json([
                'message'=>[
                    'error'=>'Need To Enter Password'
                ]
            ],406);
        }
        return response()->json([
            "data"=>[
                "user"=> Cache::store('database')->get('user'),
            ],
            "token"=>[
                "access_token"=>Cache::store('database')->get('token'),
                "type"=>"Bearer",
                "expires_in"=>"infinity"
            ]
        ],201);    }
    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->only('email', 'password');
        if (!Auth::attempt($credentials)) {
            return response()->json([
                'status' => false,
                'message' => 'Email or password is not correct'
            ], 401);
        }

        $user = auth()->user();
        if (is_null($user->email_verified_at)) {
            return response()->json([
                'status' => false,
                'message' => 'Email has not been verified yet!'
            ], 422);
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
