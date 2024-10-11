<?php

namespace App\Http\Controllers\landing;

use App\Http\Controllers\Controller;
use App\Models\Question;
use Illuminate\Http\Request;


class LandingPageController extends Controller
{
    //
    public function getQuestions(){
        $questions = Question::all();
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
}
