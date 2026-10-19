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
        Schema::create('notes', function (Blueprint $table) {
            $table->string('id', 36)->primary();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('category_id', 36);
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
            $table->string('title')->nullable();
            $table->longText('content')->nullable();
            $table->boolean('is_pinned')->default(false);
            $table->string('date')->nullable();
            $table->string('image')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notes');
    }
};
