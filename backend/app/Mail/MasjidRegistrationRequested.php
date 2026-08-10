<?php

namespace App\Mail;

use App\Models\MosqueSetting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MasjidRegistrationRequested extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public MosqueSetting $masjid) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'New '.ucfirst($this->masjid->type).' registration request');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.masjids.registration-requested');
    }
}
