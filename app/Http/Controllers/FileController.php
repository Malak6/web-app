<?php

namespace App\Http\Controllers;
use App\Models\File;
use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;


class FileController extends Controller
{

    public function upload(Request $request)
{
    if ($request->hasFile('file')) {
        $file = $request->file('file');
        $filename = $file->getClientOriginalName();

        $existingFile = File::where('file_name', $filename)->first();
        if ($existingFile) {
            return response()->json(["message" => "Error: File already exists"], 400);
        }

        $path = $file->storeAs("public", $filename);
        $group_id = $request->input('group_id');
        $group = Group::find($group_id);
        if (!$group) {
            return response()->json(["message" => "Error: Group not found"], 404);
        }

        $user = Auth::user();
        if (!$group->users()->where('user_id', $user->id)->exists()) {
            return response()->json(["message" => "Error: You are not a member of this group"], 403);
        }

        $filestatus = strtolower($request->input('file_status'));
        if (!in_array($filestatus, ['free', 'reserved'])) {
            return response()->json(["message" => "Error: Invalid file status"], 400);
        }

        $user_id = Auth::id();
        $newfile = File::create([
            'file_name' => $filename,
            'file_status' => $filestatus,
            'user_id' => $user_id,
            'group_id' => $group->id,
        ]);

        if (!$newfile) {
            return response()->json(["message" => "Error: Failed to upload file"], 500);
        }

        return response()->json(["message" => "Success"]);
    } else {
        return response()->json(["message" => "Error: No file found"], 400);
    }
}

//////////////////////////////////////////////////
public function download(Request $request)
{
    $filename = $request->input('filename');
    $groupId = $request->input('group_id');
    $user = Auth::user();

    $file = File::where('file_name', $filename)->first();

    if (!$file) {
        return response()->json(["message" => "Error: File not found"], 404);
    }

    $group = Group::find($groupId);

    if (!$group) {
        return response()->json(["message" => "Error: Group not found"], 404);
    }

    if (!$group->users()->where('user_id', $user->id)->exists()) {
        return response()->json(["message" => "Error: You are not a member of this group"], 403);
    }
    if ($file->group_id != $groupId) {
        return response()->json(["message" => "Error: File does not belong to this group"], 403);
    }

    $downloadedFile = Storage::download("public/{$filename}");
    return $downloadedFile;
}
////////////////////////////////////////////////
    public function getFileStatus(Request $request)
    {
        $filename = $request->input('filename');

        $file = File::where('file_name', $filename)->first();

        if (!$file) {
            return response()->json(["message" => "Error: File not found"], 404);
        }

        $filestatus = $file->file_status;

        return response()->json(["file_status" => $filestatus]);
    }
///////////////////////////////////////////////
    public function getUserFiles(Request $request)
{
    $userId = Auth::id();

    $userFiles = File::where('user_id', $userId)->get();

    return response()->json(["user_files" => $userFiles]);
}


}
