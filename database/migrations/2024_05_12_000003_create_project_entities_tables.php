<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->string('status', 50)->default('idle')->index();
            $table->unsignedBigInteger('user_id')->index();
            $table->timestamps();
        });

        Schema::create('project_notes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id')->index();
            $table->string('title', 255);
            $table->text('note');
            $table->string('status', 50)->default('pending')->index();
            $table->timestamps();
        });

        Schema::create('project_times', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id')->index();
            $table->unsignedBigInteger('user_id')->default(0)->index();
            $table->timestamp('counted_at')->nullable();
            $table->unsignedBigInteger('seconds_counted')->default(0);
            $table->unsignedTinyInteger('is_counting')->default(0)->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_times');
        Schema::dropIfExists('project_notes');
        Schema::dropIfExists('projects');
    }
};
