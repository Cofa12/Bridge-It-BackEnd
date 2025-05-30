<?php

namespace App\Http\Controllers\group;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\Group_User;
use App\Models\User;
use App\Notifications\SendJoinGroupInvitation;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Notifications\SendCheckJoinUser;

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
            'description' => 'required|string|max:255',
            'deadline' => 'required|date',
            'stage' => 'required|in:planning,research,development,review,finalization',
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
        //        dd($request->input('title'));
        //['planning','research','development','review','finalization']
        $group=new Group();
        $group->title=$request->input('title');
        $group->image=$imageUrl;
        $group->description=$request->input('description');
        $group->deadline=$request->input('deadline');
        $group->stage=$request->input('stage');
        $group->save();


//        $group=Group::create([
//            'title'=>,
//            'image'=>$imageUrl,
//            'description'=>$request->input('description'),
//            'deadline'=>$request->input('deadline'),
//            'stage'=>$request->input('stage'),
//            ]
//        );

//        $link=$this->linkCreation($group->id, $user->getAuthIdentifier());

        $user->groups()->attach($group->id);

        return response()->json([
            'status'=>true,
            'group'=>$group,
//            'link'=>$link,
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
            'title' => 'sometimes|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'doc_id' => 'sometimes|exists:users,id'
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
//        dd($group);

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

    public function sendJoinInvitation(Request $request): \Illuminate\Http\JsonResponse
    {
        $invitationMails = $request->input('membersMails');
        foreach ($invitationMails as $invitationMail){
            Notification::route('mail',$invitationMail)->notify(new SendJoinGroupInvitation($request->SenderName,$request->groupName,$invitationMail,$request->groupId));
        }

        Notification::route('mail',$request->doctorMail)->notify(new SendJoinGroupInvitation($request->SenderName,$request->groupName,$request->doctorMail,$request->groupId,'doctor'));


        return response()->json([
            "message"=>"Invitations have been send successfully"
        ],200);

    }

    public function acceptInvitation(Request $request): JsonResponse
    {
        $receiverEmail =  $request->get('receiverEmail');
        $groupId = $request->get('groupId');

        $userId = User::where('email',$receiverEmail)->pluck('id');

        if($request->get('position')=='member'){
            $UserFoundInDb = Group_User::where('group_id',$groupId)->where('user_id',$userId[0])->get();
            if($UserFoundInDb){
                return response()->json([
                    'message'=>"You have already in this group"
                ],200);
            }
            Group_User::create([
                'group_id'=>$groupId,
                'user_id'=>$userId[0],
                'position'=>'member'
            ]);

        }
        Group::where('id',$groupId)->update([
            'doc_id'=>$userId[0]
        ]);
        return response()->json([
            'message'=>'You have been added to the group successfully'
        ],200);

    }

    function linkCreation($GroupId,$AdminId): string
    {
        return "https://bridgeit.site/api/confirm/Invitation/link/$GroupId/$AdminId";
    }
    function joinView($adminId,$groupId): JsonResponse
    {
        $group=Group::findorFail($groupId);
        $admin=User::FindOrFail($adminId);
        return response()->json([
            'status'=>true,
            'group'=>$group,
            'invited from'=>$admin->name,
            'adminId'=>$adminId,
        ]);

    }

    function joinFromLink(Request $request): string
    {

        $groupId=$request->input('groupId');
        $groupName=Group::findOrFail($groupId)->title;

        $adminId=$request->input('adminId');
        $adminEmail=User::findOrFail($adminId)->email;
        $adminName=User::findOrFail($adminId)->name;

        $userId=$request->input('userId');
        $userName=User::findOrFail($userId)->name;
        $userEmail=User::findOrFail($userId)->email;

        Notification::route('mail',$adminEmail)->notify(
            new SendCheckJoinUser($userName,$userEmail,$groupName,$adminName,$groupId,'member')
        );

        return response()->json([
            'status'=>true,
            'message'=>'wait the confirmation from Admin'
        ],200);

    }
}
