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
        Schema::create('tag_topic', function (Blueprint $table) {
            $table->foreignId('tag_id');
            $table->foreignId('topic_id');
            $table->timestamps();

            $table->foreign('tag_id')->references('id')->on('tags')
                ->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreign('topic_id')->references('id')->on('topics')
                ->cascadeOnUpdate()->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tag_topic');
    }
};
