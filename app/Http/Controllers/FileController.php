<?php

namespace App\Http\Controllers;
use ZipArchive;
use App\Models\File;
use App\Models\Group;
use App\Models\ReservedFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;


class FileController extends Controller
{

    public function upload(Request $request)
    {
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filename = $file->getClientOriginalName();
            $user_id = Auth::id();
            $group_id = $request->input('group_id');
            $group = Group::find($group_id);

            $existingFile = File::where('file_name', $filename)->first();
            if($existingFile) return response()->json(["message" => "This file is already exist"], 400);
        
            if (!$group) return response()->json(["message" => "Error: Group not found"], 404);

            if (!$group->users()->where('user_id', $user_id)->exists()) return response()->json(["message" => "Error: You are not a member of this group"], 403);
        
            $filestatus = strtolower($request->input('file_status'));
            if (!in_array($filestatus, ['free', 'reserved'])) {
                return response()->json(["message" => "Error: Invalid file status"], 400);
            }
            $newfile = File::create([
                'file_name' => $filename,
                'file_status' => $filestatus,
                'user_id' => $user_id,
                'group_id' => $group->id,
            ]);

            if (!$newfile) return response()->json(["message" => "Error: Failed to upload file"], 500);

            $path = $file->storeAs("public", $filename);

            return response()->json(["message" => "File upload succeeded"]);
        } 
        else return response()->json(["message" => "Error: No file found"], 400);
    }   

////////////////////////////////////////////////////////

    public function checkOut(Request $request){
        $fileid = $request->input('file_id');
        $groupId = $request->input('group_id');
        $user_id = Auth::id();
        $existingFile = File::where('id' ,$fileid)->first();
            if($existingFile){
                $reservedFile = ReservedFile::where(['files_id' => $fileid,'users_id' => $user_id,])->first();
                if ($reservedFile) {
                    $existingFile->file_status = 'free';
                    $existingFile->save();
                    if ($request->hasFile('file')) {
                        $file = $request->file('file');
                        $filename = $file->getClientOriginalName();
                        $reservedFile->delete();
                        $path = $file->storeAs("public", $filename);
                        return response()->json(["message" => "The file has been updated"], 200);
                    }
                    else return response()->json(["message" => "Error: No file selected"], 400);
                }
                else return response()->json(["message" => "Error: You can not modify this file"], 403);
            }
            else return response()->json(["message" => "Error: File not found"], 500);
    }
////////////////////////////////////////////////////////

    public function download(Request $request)
    {
        $result = $this->conditions($request);
        if($result == "yes"){
            $fileid = $request->input('file_id');
            $groupId = $request->input('group_id');
            $user_id = Auth::id();
            $reservedFile = ReservedFile::where([
                'files_id' => $fileid ,
                'users_id' => $user_id ])->first();
            if($reservedFile){
                $file = File::where('id', $fileid)->first();
                $filename = $file->file_name;
                $downloadedFile = Storage::download("public/{$filename}");
                return $downloadedFile;
            }
            return response()->json(["message" => "Error: You do not reserve this file"], 404);
        }
        else return $result;
    }
/////////////////////////////////////////////////////
    public function conditions($request){
        $fileid = $request->input('file_id');
        $groupId = $request->input('group_id');
        $user = Auth::user();
        $file = File::where('id', $fileid)->first();
        $group = Group::find($groupId);

        if (!$file) return response()->json(["message" => "Error: File not found"], 404);
    
        else if (!$group) return response()->json(["message" => "Error: Group not found"], 404);
        
        else if (!$group->users()->where('user_id', $user->id)->exists()) return response()->json(["message" => "Error: You are not a member of this group"], 403);
        
        else if ($file->group_id != $groupId) return response()->json(["message" => "Error: File does not belong to this group"], 403);

        else return "yes";
    }

///////////////////////////////////////////////
    public function checkIn(Request $request){
        $result = $this->conditions($request);
        if($result == "yes"){
            $fileid = $request->input('file_id');
            $groupId = $request->input('group_id');
            $user = Auth::user();
            $file = File::where('id', $fileid)->first();
            if($file->file_status == 'reserved') return response()->json(["message" => "You can not check in this file , it is reserved"], 403);
            $filename = $file->file_name;
            $file->file_status ='reserved';
            $file->save();
            $reservedFile = ReservedFile::create([
                'users_id' => $user->id,
                'files_id' => $file->id
            ]);
            return response()->json(["message" => "You reserved this file"], 200);
        }
        else return $result;
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
//////////////////////////////////////////////////////////////////
    public function readFile(Request $request)
    {
        $filename = $request->input('filename');
        $user = Auth::user();
        $groupId = $request->input('group_id');
        $group = Group::find($groupId);
        $file = File::where('file_name', $filename)->first();

        //    ///////////////call condition function
        // if (!$file) {
        //     return response()->json(["message" => "Error: File not found"], 404);
        // }

        // if($file->file_status == 'reserved'){
        //     return response()->json(["message" => "You can not read this file , it is reserved"], 403);
        // }


        // if (!$group) {
        //     return response()->json(["message" => "Error: Group not found"], 404);
        // }

        // if (!$group->users()->where('user_id', $user->id)->exists()) {
        //     return response()->json(["message" => "Error: You are not a member of this group"], 403);
        // }
        // if ($file->group_id != $groupId) {
        //     return response()->json(["message" => "Error: File does not belong to this group"], 403);
        // }

        $projectDirectory = base_path();
        return file_get_contents($projectDirectory .'\\storage\\app\\public\\'.$filename);
    }

    public function downlaodManyFiles (Request $request){ 

    $ids_array = $request->input('ids');
    $files_to_zip = [];

    foreach ($ids_array as $id) {
        $file = File::find($id);
        if ($file) {
            if ($file->file_status == "reserved") {
                foreach ($files_to_zip as $file_name) {
                $updatedfile = File::where('file_name' , $file_name)->first();
                $updatedfile->file_status = 'free' ;
                $updatedfile-> save();
                }
                return "the file {$file->file_name} is reserved ";
            }
            // /////////////////////////////////////////////call checkin
            $file->file_status = "reserved";
            $file->save();
            $files_to_zip[] = $file->file_name;
        }
    }

    $zip = new ZipArchive;
    $fileName = 'Files.zip';
    if ($zip->open(public_path($fileName), ZipArchive::CREATE) === TRUE) {
        foreach ($files_to_zip as $file) {
            $zip->addFile(storage_path('app/public/'.$file), $file);
        }
        $zip->close();
    }
    return response()->download(public_path($fileName))->deleteFileAfterSend(true);
}

}
