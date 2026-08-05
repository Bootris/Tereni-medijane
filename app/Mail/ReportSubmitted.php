<?php

namespace App\Mail;

use App\Models\Report;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/** Sent to a facility's stewards when a new report comes in. */
class ReportSubmitted extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Report $report) {}

    public function build()
    {
        $court = $this->report->court;

        return $this->subject('Nova prijava — '.$court->name.' ('.$this->report->category->label().')')
            ->view('emails.tereni.report_submitted');
    }
}
