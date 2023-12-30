<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Group;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
#[\App\Aspects\logging]
#[\App\Aspects\performance]
#[\App\Aspects\transaction]

class GroupController extends Controller
{
    public function createGroup(Request $request)
    {
        $groupName = $request->input('group_name');
        $userId = Auth::id();


        $group = Group::create([
            'group_name' => $request->input('group_name'),
            'user_id'=> $userId    ]);
        $group->save();

        $group->users()->attach($userId, ['group_id' => $group->id]);

        return response()->json(['message' => 'Success', 'group'=>$group], 201);
    }

    public function addUserToGroup(Request $request)
    {
        $userId = $request->input('user_id');

        $groupName = $request->input('group_name');

        $group = Group::where('group_name', $groupName)->first();

        if (!$group) {
            return response()->json(['message' => 'Group not found'], 404);
        }

        if (Auth::id() !== $group->user_id) {
            return response()->json(['message' => 'You are not allowed to add users to this group'], 403);
        }

        $user = User::find($userId);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        if ($group->users()->where('user_id', $user->id)->exists()) {

            return response()->json(['message' => 'User is already added to this group'], 422);

        }

        $group->users()->attach($user->id, ['group_id' => $group->id]);

        return response()->json(['message' => 'User added successfully', 'group_id' => $group->id], 200);
    }

    public function getUserGroups()
{
    $userId = Auth::id();

    $groups = Group::whereHas('users', function ($query) use ($userId) {
        $query->where('user_id', $userId);
    })->get();

    return response()->json(['groups' => $groups], 200);
}
}
