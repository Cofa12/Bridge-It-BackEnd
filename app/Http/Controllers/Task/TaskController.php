<?php

namespace App\Http\Controllers\Task;

use App\Http\Controllers\Controller;
use App\Http\Requests\TaskRequest\DeleteRequest;
use App\Http\Requests\TaskRequest\StoreRequest;
use App\Http\Requests\TaskRequest\UpdateRequest;
use App\Models\Group;
use App\Models\Task;
use App\Notifications\Tasks\Taskdeleted;
use App\Notifications\Tasks\TaskUpdated;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Notifications\Tasks\TaskAssigned;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request):JsonResponse
    {
        $group=$request->attributes->get('group');
        $tasks= $group->tasks()->with(['author', 'assignedTo'])->get();
        return response()->json([
            'tasks' => $tasks,
        ]);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequest $request):JsonResponse
    {
        $data = $request->validated();
        $group = $request->attributes->get('group');

        $data['author_id'] = $request->user()->id;
        $task = $group->tasks()->create($data);
        $task->load(['author', 'assignedTo']);

        if($task->assignedTo->id !== $task->author->id) {
            $task->assignedTo->notify(new TaskAssigned($task));
        }

        return response()->json(['task' => $task], 201);
    }


    /**
     * Display the specified resource.
     */
    public function show($groupId, $id):JsonResponse
    {
        $task = Task::with(['author', 'assignedTo'])->findOrFail($id);
        return response()->json(['task' => $task], 200);

    }



    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRequest $request ,$groupId, $id):JsonResponse
    {
        $data = $request->validated();
        $task = Task::findOrFail($id);
        $task->Urgency = $data['Urgency'];
        $task->update($data);
        $task->load(['author', 'assignedTo']);

        if($task->assignedTo->id !== auth()->user()->id) {
            $task->assignedTo->notify(new TaskUpdated($task));
        }
        return response()->json(['task' => $task], 200);

    }

    public function updateTaskStatus(Request $request ,$TaskId,$groupId):JsonResponse
    {
        $validatedData = $request->validate([
            'status' => 'required|string|in:ToDo,Ongoing,Done,Canceled'
        ]);
        $task = Task::findOrFail($TaskId);
        $task->status = request('status');
        $task->save();

        return response()->json(['message' => 'Task status updated successfully', 'task' => $task], 200);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DeleteRequest $request, $groupId,string $id):JsonResponse
    {
        $task = Task::findOrFail($id);

        $task->delete();

        if($task->assignedTo->id !== auth()->user()->id) {
            $task->assignedTo->notify(new Taskdeleted($task));
        }

        return response()->json(['message' => 'Task deleted successfully'], 200);
        //
    }

    public function getTasksByUrgency($groupId,$Urgency): JsonResponse
    {

        $validator = validator()->make(['Urgency' => $Urgency], [
            'Urgency' => 'required|in:Later,Normal,Urgent',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 422);
        }
        $group = Group::findOrFail($groupId);
        $tasks = $group->tasks()->where('Urgency', $Urgency)->with(['author', 'assignedTo'])->get();

        return response()->json(['tasks' => $tasks], 200);
    }
}
