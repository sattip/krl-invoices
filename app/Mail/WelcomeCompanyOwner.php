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

class WelcomeCompanyOwner extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public User $user;
    public Company $company;
    public Plan $plan;
    public string $password;
    public string $gracePeriodEnd;

    /**
     * Create a new message instance.
     */
    public function __construct(
        User $user,
        Company $company,
        Plan $plan,
        string $password,
        string $gracePeriodEnd
    ) {
        $this->user = $user;
        $this->company = $company;
        $this->plan = $plan;
        $this->password = $password;
        $this->gracePeriodEnd = $gracePeriodEnd;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Welcome to InvoiceAI - Your Account is Ready',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.welcome-company-owner',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
