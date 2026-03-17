<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // type: 'before' or 'after'
        Schema::create('room_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained('rooms')->cascadeOnDelete();
            $table->string('path');
            $table->enum('type', ['before', 'after']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('room_photos');
    }
};
