<?php

namespace App\Services;

use App\Mail\MasjidRegistrationApproved;
use App\Mail\MasjidRegistrationRequested;
use App\Models\MosqueSetting;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class MasjidEmailNotificationService
{
    public function registrationRequested(MosqueSetting $masjid): void
    {
        $adminEmail = config('admin.email');

        if (blank($adminEmail)) {
            Log::warning('Registration notification skipped because ADMIN_EMAIL is not configured.', [
                'masjid_id' => $masjid->id,
            ]);

            return;
        }

        $this->send($adminEmail, new MasjidRegistrationRequested($masjid), $masjid, 'registration requested');
    }

    public function registrationApproved(MosqueSetting $masjid): void
    {
        if (blank($masjid->contact_email)) {
            return;
        }

        $this->send($masjid->contact_email, new MasjidRegistrationApproved($masjid), $masjid, 'registration approved');
    }

    private function send(string $recipient, Mailable $mail, MosqueSetting $masjid, string $event): void
    {
        try {
            Mail::to($recipient)->send($mail);
        } catch (\Throwable $exception) {
            report($exception);
            Log::error("Masjid {$event} email could not be sent.", [
                'masjid_id' => $masjid->id,
                'recipient' => $recipient,
            ]);
        }
    }
}
