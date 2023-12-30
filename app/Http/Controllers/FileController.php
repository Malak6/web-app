<?php

namespace App\Http\Controllers;
use ZipArchive;
use App\Models\File;
use App\Models\Group;
use App\Models\ReservedFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use  App\Aspects\logging;
#[\App\Aspects\performance]
#[\App\Aspects\transaction]
class FileController extends Controller
{

    #[logging]
    public function upload(Request $request){
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
                'group_id'  => $group->id,
            ]);

            if (!$newfile) return response()->json(["message" => "Error: Failed to upload file"], 500);

            $path = $file->storeAs("public", $filename);

            return response()->json(["message" => "File upload succeeded"]);
        }
        else return response()->json(["message" => "Error: No file found"], 400);
    }
////////////////////////////////////////////////////////
#[logging]
public function checkOut(Request $request)
{
    $fileid = $request->input('file_id');
    $groupId = $request->input('group_id');
    $user_id = Auth::id();
    $result = $this->conditions($request);

    if ($result == "true") {
        $reservedFile = ReservedFile::where(['files_id' => $fileid, 'users_id' => $user_id])->first();

        if ($reservedFile) {
            $existingFile = File::find($fileid);

            if ($existingFile) {
                $existingFile->file_status = 'free';
                $existingFile->save();

                if ($request->hasFile('file')) {
                    $file = $request->file('file');
                    $filename = $file->getClientOriginalName();
                    $reservedFile->delete();
                    $path = $file->storeAs("public", $filename);

                    return response()->json(["message" => "The file has been updated"], 200);
                }
            } else {
                return response()->json(["message" => "Error: File not found"], 404);
            }
        } else {
            return response()->json(["message" => "Error: You cannot modify this file"], 403);
        }
    } else {
        return $result;
    }
}
////////////////////////////////////////////////////////
    #[logging]
    public function download(Request $request){
        $result = $this->conditions($request);
        if($result == "true"){
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
            return response()->json(["message" => "Error: You do not download this file"], 404);
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

        else return "true";
    }
///////////////////////////////////////////////
#[logging]
    public function checkIn(Request $request){
        $user = Auth::user();
        $rollback =[];
            $fileids = $request->input('file_ids');
            $groupId = $request->input('group_id');
            foreach ($fileids as $fileid) {
                $req = ['file_id' => $fileid , 'group_id' =>$groupId ];
                $request->request->add(['file_id' => $fileid]);
                $result = $this->conditions($request);
                if($result == "true"){
                    $file = File::where('id', $fileid)->first();
                    if(( $file->file_status == 'reserved')){
                        foreach ($rollback as $id) {
                            $file = File::where('id', $id)->first();
                            $file->file_status ='free';
                            $file->save();
                        }
                    return response()->json(["message" => "You can not check in this file , it is reserved"], 403);
                    }
                    $filename = $file->file_name;
                    $file->file_status ='reserved';
                    $file->save();
                    $rollback[] = $fileid;
                }
                else return $result;
            }
            foreach ($fileids as $fileid) {
                $reservedFile = ReservedFile::create([
                    'users_id' => $user->id,
                    'files_id' => $fileid
                ]);
            }
            return response()->json(["message" => "You reserved files"], 200);
    }
 ////////////////////////////////////////////////
    public function getFileStatus(Request $request){
        $fileId = $request->input('file_id');

        $file = File::where('id', $fileId)->first();

        if (!$file) {
            return response()->json(["message" => "Error: File not found"], 404);
        }

        $filestatus = $file->file_status;

        return response()->json(["file_status" => $filestatus]);
    }
///////////////////////////////////////////////
    public function getUserFiles(Request $request){
        $userId = Auth::id();

        $userFiles = File::where('user_id', $userId)->get();

        return response()->json(["user_files" => $userFiles]);
    }
//////////////////////////////////////////////////////////////////
    public function readFile(Request $request){
        $fileId = $request->input('file_id');
        $user = Auth::user();
        $groupId = $request->input('group_id');
        $group = Group::find($groupId);
        $file = File::where('id', $fileId)->first();
        $result = $this->conditions($request);
        if($result == "true"){
        if($file->file_status == 'reserved') return response()->json(["message" => "You can not read this file , it is reserved"], 403);
        $projectDirectory = base_path();
        $filename =  $file->file_name;
        return file_get_contents($projectDirectory .'\\storage\\app\\public\\'.$filename);
        }
        else return $result;
    }
//////////////////////////////////////////////////////////////////
    public function downlaodManyFiles (Request $request){
        $user_id = Auth::id();
        $fileids = $request->input('file_ids');
        foreach ($fileids as $id) {
            $request->request->add(['file_id' => $id]);
            $result = $this->conditions($request);
            if ($result !== "true"){
                return $result;
            }
        }
        foreach ($fileids as $id) {
            $reservedFile = ReservedFile::where(['files_id' => $id,'users_id' => $user_id])->first();
            if(! $reservedFile) return response()->json(["message" => "Error: You do not download these files"], 404);
        }
        $zip = new ZipArchive;
        $fileName = 'Files.zip';
        if ($zip->open(public_path($fileName), ZipArchive::CREATE) === TRUE) {
            foreach ($fileids as $id) {
                $file = File::find($id);
                $filename = $file -> file_name;
                $zip->addFile(storage_path('app/public/'.$filename), $filename);
            }
            $zip->close();
        }
        return response()->download(public_path($fileName))->deleteFileAfterSend(true);
    }
}
