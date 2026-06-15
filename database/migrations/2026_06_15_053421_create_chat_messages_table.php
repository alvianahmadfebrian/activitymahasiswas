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
    Schema::create('chat_messages', function (Blueprint $table) {
        $table->id();

        $table->unsignedBigInteger('user_id')->nullable();

        $table->unsignedBigInteger('chat_session_id')->nullable();

        $table->string('role');

        $table->longText('message');

        $table->text('file_url')->nullable();
        $table->text('file_path')->nullable();
        $table->string('file_type')->nullable();
        $table->string('file_name')->nullable();

        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_messages');
    }
};
