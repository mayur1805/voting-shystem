<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('votes', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->string('email')->unique();
            $table->string('phone');
            $table->string('member_id');
            $table->boolean('is_verified')->default(false);
            $table->timestamps();
        });

        Schema::create('votes_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vote_id')->constrained('votes')->onDelete('cascade');
            $table->enum('person_1', ['yes', 'no'])->nullable();
            $table->enum('person_2', ['yes', 'no'])->nullable();
            $table->enum('selected_person', ['person_3', 'person_4'])->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('votes_details');
        Schema::dropIfExists('votes');
    }
};
