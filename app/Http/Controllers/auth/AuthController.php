<?php

namespace App\Http\Controllers\auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\AuthRequests\ConfirmEmailRequest;
use App\Http\Requests\AuthRequests\LoginRequest;
use App\Http\Requests\AuthRequests\RegisterRequest;
use App\Models\User;
use App\Notifications\SendOTPcode;
use Ichtrojan\Otp\Otp;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;
use Laravel\Socialite\Facades\Socialite;

//use App\SaveSocialiteData;
//use http\Env\Response;
//use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    //
//    use SaveSocialiteData;
    public function Register(RegisterRequest $request): JsonResponse
    {
        $data=$request->validated();
        $data['type'] = $request->type != '' ? $request->type : 'regular';
        $data['phone'] =  $request->phone != '' ? $request->phone : null;
        $data['email_verified_at'] = now();
        try {
            DB::beginTransaction();
            $user = User::create($data);
//            Notification::route('mail',$request->email)
//                ->notify(new SendVerificationEmail($request->email));
            $deviceToken = $request->input('device_token');
            $user->UserTokens()->create([
                'token'=>$deviceToken,
                'user_id'=>$user->id,
            ]);

            $token = $user->createToken('User_token')->plainTextToken;

            DB::commit();

            return response()->json([
                'user'=> $user,
                'status' => true,
                'message' => 'Registered Successfully',
                "token"=>[
                    "access_token"=>$token,
                    "type"=>"Bearer",
                    "expires_in"=>"infinity"
                ],
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
        $user = User::where('email', $request->get('email'))->update([
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
        $user = User::where('email',$request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid credentials'
            ], 401);
        }

        $token = $user->createToken('User_token')->plainTextToken;

        if (is_null($user->email_verified_at)) {
            return response()->json([
                'status' => false,
                'message' => 'Email has not been verified yet!'
            ], 422);
        }

        $deviceToken=$request->input('device_token');
        $user->UserTokens()->create([
            'token'=>$deviceToken,
            'user_id'=>$user->id,
        ]);

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

    public function getAllUsers(): JsonResponse
    {
        $users = User::where('type', '!=', 'admin')->get();
        return response()->json([
            'status' => true,
            'users' => $users
        ], 200);
    }
}
