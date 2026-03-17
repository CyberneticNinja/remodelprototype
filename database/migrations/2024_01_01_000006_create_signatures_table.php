<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // stage:  'work_agreed' or 'completed'
        // role:   'contractor'  or 'client'
        // A stage is complete only when BOTH roles have signed for that stage
        Schema::create('signatures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained('rooms')->cascadeOnDelete();
            $table->enum('stage', ['work_agreed', 'completed']);
            $table->enum('role', ['contractor', 'client']);
            $table->text('signature_data'); // base64 canvas image
            $table->timestamp('signed_at');
            $table->timestamps();

            // One signature per role per stage per room
            $table->unique(['room_id', 'stage', 'role']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('signatures');
    }
};
