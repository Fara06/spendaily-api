<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('missions', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->enum('type', ['time', 'amount', 'frequency']);
            $table->integer('target_value');
            $table->integer('duration');
            $table->integer('reward_points');
            $table->string('color')->nullable();
            $table->string('icon')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_flash')->default(false);
            $table->decimal('estimated_saving', 15, 2)->nullable();
            $table->integer('participants_count')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('missions');
    }
};