<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFileManagersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('file_managers', function (Blueprint $table) {
            $table->id();
            $table->string("uuid");
            $table->string("tableable");
            $table->unsignedBigInteger("tableable_id");
            $table->string("mime_type");
            $table->string("size");
            $table->string("file_url");
            $table->boolean("is_profile_pic")->default(false);
            $table->boolean("is_cover_pic")->default(false);
            $table->unsignedBigInteger("causarable_id")->nullable();
            $table->string("causarable")->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('file_managers');
    }
}
