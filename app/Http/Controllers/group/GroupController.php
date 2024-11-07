<?php

namespace App\Http\Controllers\group;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use function Laravel\Prompts\table;
use function PHPUnit\Framework\isEmpty;
use function PHPUnit\Framework\isNan;
use function PHPUnit\Framework\isNull;

class GroupController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Get all groups a user belongs to
        $user = Auth::user();
        $groups = $user->groups;
        $data=array();
        foreach ($groups as $group) {
            $group->users=$group->users;
            array_push($data,['group'=>$group]);
        }
//        dd($data);
        return response()->json([
            'status'=>true,
            'data'=>$data,
            'numberOfGroups'=>count($groups)

        ],200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        //
        $user = Auth::user();
        $validator=validator($request->all(), [
            'title' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);
        if($validator->fails()){
            return response()->json([
                'status'=>false,
                'message'=>$validator->errors(),
            ],422);
        }
        if($request->hasFile('image')){
            $imageName = time().'.'.$request->file('image')->extension();
            Storage::putFileAs('groups_image', $request->file('image'),$imageName,'public' );
            $imageUrl = Storage::url('groups_image/' . $imageName);

        }else{
            $imageUrl = null;
        }
        $group=Group::create([
            'title'=>$request->input('title'),
            'doc_id'=>$request->input('doc_id'),
            'image'=>$imageUrl,
            'description'=>$request->input('description')
            ]
        );

        $user->groups()->attach($group->id);

        return response()->json([
            'status'=>true,
            'group'=>$group,

        ],201);

    }

    /**
     * Display the specified resource.
     */
    public function getGroupWithID(int $id)
    {
        //
        $user_id = Auth::id();
        $group=Group::find($id);
        if(!$group){
            return response()->json([
                'status'=>false,
                'message'=>'group not found'
            ],404);
        }
//        dd($group);
        $members = $group->users;
//        dd($members);
        return response()->json([
            'status'=>true,
            'group'=>$group,
//            'members'=>$members,
        ]);

    }
    function searchUsingName(Request $request): \Illuminate\Http\JsonResponse
    {

        $groupName=$request->input('name');
        $groups=Group::where('title','like','%'.$groupName.'%')->get();
        if ($groups->isEmpty()) {
            return response()->json(['message' => 'No matching groups found'],404);
        }
        return response()->json([
            'status'=>true,
            'groups'=>$groups,
        ]);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request): \Illuminate\Http\JsonResponse
    {
        //
        $groupId=$request->input('groupId');
        $group=Group::find($groupId);


        $validator=validator($request->all(),[
            'title' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'doc_id' => 'required|exists:users,id'
        ]);
        if($validator->fails()){
            return response()->json([
                'status'=>false,
                'message'=>$validator->errors(),
            ],422);
        }
        if($request->hasFile('image')){
            $imageName = time().'.'.$request->file('image')->extension();
            Storage::putFileAs('groups_image', $request->file('image'),$imageName,'public' );
            $imageUrl = Storage::url('groups_image/' . $imageName);
            $group->image=$imageUrl;
        }
        $group->title=$request->input('title');
        $group->save();

        return response()->json([
            'status'=>true,
            'group'=>$group,
            'message'=>'updated successfully',
        ],200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request): \Illuminate\Http\JsonResponse
    {
        $userId = Auth::id();

        $groupId=$request->input('groupId');
       $group=Group::find($groupId);

        // delete every user in the pivot belongs to the $group and delete the group
        $usersInGroup=$group->users->pluck('id')->toArray();
        $group->users()->detach($usersInGroup);
        Group::destroy($groupId);

        return response()->json([
            'status'=>true,
            'message'=>'Group deleted',
        ],200);

    }
//    public function getOut(Request $request): \Illuminate\Http\JsonResponse
//    {
//        $userId = Auth::id();
//        $groupId=$request->input('group_id');
//        $group=Group::findorfail($groupId);
//
//
//
//
//    }

    public function getGroupMembers(int $groupId): \Illuminate\Http\JsonResponse
    {
        $group=Group::find($groupId);
        return $group->users;
    }
}
