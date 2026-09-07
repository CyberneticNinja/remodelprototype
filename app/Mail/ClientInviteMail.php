<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ClientInviteMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $client,
        public string $activationUrl,
    ) {}

    public function build()
    {
        return $this->subject("You've been invited to {$this->client->createdByContractor->company_name}")
            ->view('emails.client-invite');
    }
}
