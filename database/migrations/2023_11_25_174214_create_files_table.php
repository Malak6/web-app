<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFilesTable extends Migration
{
    public function up()
    {
        Schema::create('files', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('file_name')->unique();
            $table->enum('file_status', ['free', 'reserved']);
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('group_id')->constrained('groups', 'id');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('files');
    }
}
