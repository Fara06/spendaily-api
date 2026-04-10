<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('habits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // relasi ke users
            $table->enum('habit_type', ['good','bad']); // jenis habit
            $table->string('title'); // judul habit (opsional biar enak di UI)
            $table->text('description')->nullable(); // penjelasan habit
            $table->integer('score')->default(0); // skor habit (misal 1-5)
            $table->dateTime('detected_at'); // waktu terdeteksi
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('habits');
    }
};