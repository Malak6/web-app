<?php

namespace App\Http\Controllers;

use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
    
    public function upload(Request $request){
        if($request->hasFile('file')) {
            $file = $request->file('file');
            $path = $file->storeAs("public", $file->getClientOriginalName());
            $user_id = $request->input('user_id');
            $group_id = $request->input('group_id');
            $newfile =File::create([
                'file_name' =>$file->getClientOriginalName(),
                'file_status' => "free",
                'users_id' => $user_id,
                'groups_id' => $group_id,
            ]);
            return response()->json(["message" => "Success"]);
        }
        else return response()->json(["message" => "Error"]);
        }

    public function download(Request $request){
        $downloadedfile = Storage::download("public\\". $request->input('filename'));
        return $downloadedfile;
    }


}
