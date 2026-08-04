<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('forms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->json('schema');
            $table->string('slug')->unique();
            $table->string('status')->default('draft');
            $table->boolean('is_public')->default(true);
            $table->json('settings')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index('slug');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('forms');
    }
};
