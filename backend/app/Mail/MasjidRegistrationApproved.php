<?php

namespace App\Mail;

use App\Models\MosqueSetting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MasjidRegistrationApproved extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public MosqueSetting $masjid) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: ucfirst($this->masjid->type).' registration approved');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.masjids.registration-approved');
    }
}
