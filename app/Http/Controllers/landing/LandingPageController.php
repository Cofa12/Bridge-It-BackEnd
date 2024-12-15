<?php

namespace App\Http\Controllers\landing;

use App\Http\Controllers\Controller;
use App\Mail\NotifySubscriptors;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;


class LandingPageController extends Controller
{
    //
    public function getQuestions(){
        $questions = Question::all()->sortBy('points',0,true);
        foreach($questions as $question){
            $question->answer = $question->answers()->value('answer');
        }
        return response()->json([
            "data"=>[
                $questions
            ],
        ],200);
    }

    public function addPoint(Request $request){
        $question = Question::find($request->id);
        $question->points+=1;
        $question->save();
        return response()->json([
            'status'=>true,
        ],201);

    }

    public function addQuestion(Request $request){
        $validator = validator($request->all(),[
            'email'=>'Email',
            'name'=>'String',
            'question'=>'required',
            'subject'=>'String'
        ]);

        if($validator->fails()){
            return response()->json([
                'status'=>false,
                'message'=>$validator->errors(),
            ],422);
        }

        $isStoredInDatabase = Question::create([
            'email'=>$request->email!=null? $request->email: "",
            'name'=>$request->name!=null? $request->name: "",
            'question'=>$request->question,
            'subject'=>$request->subject!=null? $request->subject: ""
        ]);

        if(!$isStoredInDatabase){
            return response()->json([
                'message'=>'the recored wasn\'t sorted in database'
            ],500);
        }

        return response()->json([
            'message'=>'the recored was sorted in database successfully'
        ],201);
    }

    public function getSubscription(Request $request){
        $validator=validator($request->all(),[
            'email'=>'required|email|unique:subscriptors,email'
        ]);
        if($validator->fails()){
            return response()->json([
                'status'=>false,
                'message'=>$validator->errors(),
            ],422);
        }
        DB::table('subscriptors')->insert(['email'=>$request->input('email')]);
        return response()->json([
            'status'=>true,
            'message'=>'Thank you for subscribing to our newsletter'
        ]);
    }
    //@todo: change the content of mails sent to the subscribers
/*
    public function sendSubscription()
    {
        $content='123456';
        $subject='654321';
        $emails=DB::table('subscriptors')->pluck('email');
            foreach ($emails as $email) {
            Mail::to($email)->send(new NotifySubscriptors($content,$subject));
        }
        return response()->json([
            'status'=>true,
            'message'=>'all subscriptors have been sent '
        ]);


    }
*/
}
