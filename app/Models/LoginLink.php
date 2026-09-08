<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class LoginLink extends Model
{
    protected $fillable = ['user_id', 'token_hash', 'purpose', 'expires_at', 'used_at'];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'used_at'    => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Issue a fresh login link for a user, invalidating any of their
     * earlier unused links first (only the most recently sent link ever
     * works). Returns [LoginLink, plaintext token, full URL] — the
     * plaintext token exists only here and in the emailed URL; the
     * database only ever stores its hash.
     */
    public static function issueFor(User $user, string $purpose = 'login', ?\DateTimeInterface $expiresAt = null): array
    {
        static::where('user_id', $user->id)->whereNull('used_at')->delete();

        $token = Str::random(64);

        $link = static::create([
            'user_id'    => $user->id,
            'token_hash' => hash('sha256', $token),
            'purpose'    => $purpose,
            'expires_at' => $expiresAt ?? ($purpose === 'invite' ? now()->addDays(7) : now()->addMinutes(15)),
        ]);

        return [$link, $token, URL::route('login.confirm', $token)];
    }

    /**
     * Redeem a plaintext token from an emailed link. Marks it used and
     * returns the User, or null if the token is unknown, expired, or
     * already used. A client using their very first link activates their
     * online account as a side effect.
     */
    public static function consume(string $token): ?User
    {
        $link = static::where('token_hash', hash('sha256', $token))
            ->whereNull('used_at')
            ->where('expires_at', '>=', now())
            ->first();

        if (!$link) {
            return null;
        }

        $link->update(['used_at' => now()]);

        $user = $link->user;

        if ($user->isClient() && is_null($user->activated_at)) {
            $user->update(['activated_at' => now()]);
        }

        return $user;
    }

    /**
     * Look up the (still valid, unused) user for a token without consuming
     * it — used to render the "click to finish signing in" confirm page.
     */
    public static function peek(string $token): ?User
    {
        $link = static::where('token_hash', hash('sha256', $token))
            ->whereNull('used_at')
            ->where('expires_at', '>=', now())
            ->first();

        return $link?->user;
    }
}
