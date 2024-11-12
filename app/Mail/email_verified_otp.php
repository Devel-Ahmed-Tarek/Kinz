<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class email_verified_otp extends Mailable
{
    use Queueable, SerializesModels;
    public $randomNumbers;
    /**
     * Create a new message instance.
     */
    public function __construct($randomNumbers)
    {
        $this->randomNumbers = $randomNumbers;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Email Verified Otp',
        );
    }

    /**
     * Get the message content definition.
     */
    public function build()
    {
        return $this->view('email_verified_otp')->with('randomNumbers', $this->randomNumbers);

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
