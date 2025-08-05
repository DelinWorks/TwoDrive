<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('files', function (Blueprint $table) {
            $table->string('uuid', 16)->unique();
            $table->string('filename');
            
            $table->string('parent_uuid')->default('0');
            $table->unsignedBigInteger('owner_id');
            
            $table->unsignedBigInteger('file_size')->nullable();
            $table->boolean('is_folder')->default(false);
            $table->boolean('is_shared')->default(false);

            $table->timestamps();

            $table->unique(['filename', 'parent_uuid', 'owner_id', 'is_folder']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('files');
    }
};
