<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Login links back every sign-in in the app — there is no password.
     * The emailed URL carries a random plaintext token; only its hash is
     * stored here, so a leaked/backed-up database can't be replayed into a
     * login the way a stolen password hash could be. `purpose` is purely
     * informational (lets us tell a first-time client invite apart from an
     * everyday login when reading the table); it plays no role in
     * validation. Each link is single-use (`used_at`) and time-limited
     * (`expires_at`), and issuing a new one for a user invalidates any of
     * that user's earlier unused links (see LoginLink::issueFor()).
     */
    public function up(): void
    {
        Schema::create('login_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('token_hash')->unique();
            $table->string('purpose')->default('login');
            $table->timestamp('expires_at');
            $table->timestamp('used_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('login_links');
    }
};
