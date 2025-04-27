<?php

namespace App\Http\Controllers\landing;

use App\Http\Controllers\Controller;
use App\Http\Requests\landingRequest\AddQuestion;
use App\Http\Requests\landingRequest\GetSubscriptionRequest;
use App\Mail\NotifySubscriptors;
use App\Models\Question;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;


class LandingPageController extends Controller
{
    //
    public function getQuestions():JsonResponse
    {
        $questions = Question::all()->sortBy('points', 0, true);
        foreach ($questions as $question) {
            $question->answer = $question->answers()->value('answer');
        }
        return response()->json([
            "data" => [
                $questions
            ],
        ], 200);
    }

    public function addPoint(Request $request):JsonResponse
    {
        $question = Question::find($request->id);
        $question->points += 1;
        $question->save();
        return response()->json([
            'status' => true,
        ], 201);

    }

    public function addQuestion(AddQuestion $request): JsonResponse
    {
        $data = $request->validated();

        $isStoredInDatabase = Question::create($data);

        if (!$isStoredInDatabase) {
            return response()->json([
                'message' => "the record wasn't sorted in database"
            ], 500);
        }

        return response()->json([
            'message' => 'the record was stored in database successfully'
        ], 201);
    }

    public function getSubscription(GetSubscriptionRequest $request): JsonResponse
    {
        $email = $request->input("email");

        DB::table('subscriptors')->insert(['email' => $email]);
        return response()->json([
            'status' => true,
            'message' => 'Thank you for subscribing to our newsletter'
        ]);
    }
}
