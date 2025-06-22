<?php

namespace App\Http\Controllers\Task;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChallengeStoreRequest;
use App\Http\Requests\ChallengeUpdateRequest;
use App\Http\Resources\TaskChallengeResource;
use App\Models\Challenge;
use App\Models\ChallengeSolution;
use App\Models\Task;
use Carbon\Unit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ChallengeController extends Controller
{

    public function index($task_id):TaskChallengeResource
    {
        $task = Task::find($task_id);
        if (!$task) {
            throw new NotFoundHttpException('Task not found');
        }

        return new TaskChallengeResource($task);
    }
    public function store(ChallengeStoreRequest $request):JsonResponse
    {
        $challengeSolution = ChallengeSolution::create([
            'contents'=>$request->challenge_solution,
        ]);

        Challenge::create([
            'content'=>$request->challenge_title,
            'solution_id'=>$challengeSolution->id,
            'task_id'=>$request->task_id,
        ]);

        return response()->json(['success'=>true,'message'=>'Challenge Solution Created'],JsonResponse::HTTP_CREATED);
    }

    public function update(ChallengeUpdateRequest $request,$id):JsonResponse
    {
        $challenge = Challenge::find($id);
        if(!$challenge){
            throw new  NotFoundHttpException('Challenge not found');
        }
        if(isset($request->challenge_title)){
            $challenge->update([
                'content'=>$request->challenge_title,
            ]);
        }

        if(isset($request->challenge_solution)){
            $challenge->solution()->update([
                'contents'=>$request->challenge_solution,
            ]);
        }

        return response()->json(['success'=>true,'message'=>'Challenge Solution updated'],JsonResponse::HTTP_OK);
    }

    public function destroy($id):JsonResponse
    {
        $challenge = Challenge::find($id);
        if(!$challenge){
            throw new  NotFoundHttpException('Challenge not found');
        }

        $temp = $challenge->solution();
        $challenge->delete();
        $temp->delete();

        return response()->json(['success'=>true,'message'=>'Challenge Solution deleted'],JsonResponse::HTTP_OK);
    }
}
