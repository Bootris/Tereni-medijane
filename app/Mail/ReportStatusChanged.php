<?php

namespace App\Mail;

use App\Models\Report;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Sent to the reporter (when they left a contact) after a status change, so the
 * loop closes even for people who never come back to the field page.
 */
class ReportStatusChanged extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Report $report) {}

    public function build()
    {
        $court = $this->report->court;

        return $this->subject('Status prijave — '.$court->name.': '.$this->report->status->label())
            ->view('emails.tereni.report_status_changed');
    }
}
