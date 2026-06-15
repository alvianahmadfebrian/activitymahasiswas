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
    Schema::create('tasks', function (Blueprint $table) {
        $table->id();

        $table->unsignedBigInteger('user_id')->nullable();

        $table->string('title');
        $table->longText('description')->nullable();

        $table->dateTime('deadline')->nullable();

        $table->string('status')->default('pending');

        $table->text('file_url')->nullable();
        $table->text('file_path')->nullable();
        $table->string('file_name')->nullable();

        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
