<?php

namespace App\Mail;

use App\Models\Company;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WelcomeSubscriber extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public User $user;
    public Company $company;
    public ?Plan $plan;

    public function __construct(User $user, Company $company, ?Plan $plan = null)
    {
        $this->user = $user;
        $this->company = $company;
        $this->plan = $plan;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Welcome to InvoiceAI!',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.auth.welcome-subscriber',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
