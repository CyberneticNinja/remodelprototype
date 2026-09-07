<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('name')->default('Bedroom');
            $table->text('notes')->nullable();

            // The contractor's proposal for this room: what will be done,
            // roughly what it will cost, and how long it will take. Shown
            // to the client before they sign off on "work agreed".
            $table->text('scope_description')->nullable();
            $table->decimal('estimated_cost', 10, 2)->nullable();
            $table->unsignedInteger('estimated_duration_days')->nullable();

            // Once set, the estimate is frozen and can no longer be edited —
            // set the moment the first work_agreed signature (either role)
            // is collected for this room.
            $table->timestamp('estimate_locked_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
