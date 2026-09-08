<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * A single users table for both contractors and clients, distinguished
     * by `type`. There are no passwords anywhere — everyone (contractors and
     * clients who choose to sign online) logs in via an emailed magic link,
     * see the `login_links` table/migration. Clients created by a contractor
     * either activate an account via their first emailed link (to sign
     * online) or never activate one at all and just sign in person on the
     * contractor's device.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['contractor', 'client']);

            // Shared identity fields
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->string('phone')->nullable();

            // Contractor-only fields
            $table->string('company_name')->nullable();
            $table->string('company_address')->nullable();
            $table->string('company_phone')->nullable();

            // Client-only fields
            $table->string('address')->nullable();
            $table->foreignId('created_by_contractor_id')->nullable()
                ->constrained('users')->nullOnDelete();

            // Client account activation (online signing)
            $table->timestamp('invited_at')->nullable();
            $table->timestamp('activated_at')->nullable();

            // Marks the single public-demo contractor account (see
            // User::demoContractor() and the demo:reset command). Never
            // mass-assignable — only ever set by that seeding code.
            $table->boolean('is_demo')->default(false);

            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('users');
    }
};
