<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WelcomeUserMail extends Mailable
{
    use Queueable, SerializesModels;

    public array $data;

    public function __construct(User $user, ?string $plainPassword = null)
    {
        $this->data = [
            'name' => $user->name,
            'email' => $user->email,
            'etablissement_name' => $user->etablissement?->name ?? 'Votre établissement',
            'site_name' => config('app.name'),
            'login_url' => route('login'),
            'support_email' => config('mail.from.address', 'support@goexploriabusiness.com'),
        ];

        if ($plainPassword) {
            $this->data['password'] = $plainPassword;
        }
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Bienvenue sur ' . config('app.name'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.welcome',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
