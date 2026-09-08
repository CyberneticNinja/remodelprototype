<p>Hi {{ $client->first_name }},</p>

<p>
    {{ $client->createdByContractor->full_name }} of {{ $client->createdByContractor->company_name }}
    has added you to a project and invited you to create an account so you can view progress
    and sign off on work online, from anywhere — instead of only in person.
</p>

<p>
    <a href="{{ $url }}">Set up your account</a>
</p>

<p>If you'd rather not create an account, that's fine too — your contractor can still have you sign in person on their device.</p>

<p>This link expires in 7 days.</p>
