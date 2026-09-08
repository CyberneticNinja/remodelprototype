<p>Hi {{ $user->first_name }},</p>

<p>Click below to sign in — no password needed.</p>

<p>
    <a href="{{ $url }}">Sign in</a>
</p>

<p>This link works once and expires in 15 minutes. If you didn't request it, you can ignore this email.</p>
