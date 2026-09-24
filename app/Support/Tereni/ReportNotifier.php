<?php

namespace App\Support\Tereni;

use App\Mail\ReportStatusChanged;
use App\Mail\ReportSubmitted;
use App\Models\Report;
use App\Support\Sms\SmsSender;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

/**
 * One place that fans a report event out to the responsible people (email + SMS)
 * and back to the reporter. Never lets a delivery failure break the request -
 * the report is already saved; notifications are best-effort.
 */
class ReportNotifier
{
    public function __construct(private SmsSender $sms) {}

    /** New report → notify every opted-in steward of the facility. */
    public function newReport(Report $report): void
    {
        $report->loadMissing('court.facility.stewards');
        $court = $report->court;
        $stewards = $court?->facility?->stewards ?? collect();

        $adminUrl = $this->adminReportUrl($report);
        $smsText = sprintf(
            'Nova prijava (%s) - %s. Detalji: %s',
            $report->category->label(),
            $court?->name,
            $adminUrl,
        );

        foreach ($stewards as $steward) {
            if (! $steward->notify) {
                continue;
            }

            try {
                if ($steward->email) {
                    Mail::to($steward->email)->send(new ReportSubmitted($report));
                }
                if ($steward->phone) {
                    $this->sms->send($steward->phone, $smsText);
                }
            } catch (\Throwable $e) {
                report($e);
            }
        }
    }

    /** Status change → tell the reporter, if they left an email/phone. */
    public function statusChanged(Report $report): void
    {
        $contact = trim((string) $report->reporter_contact);

        if ($contact === '') {
            return;
        }

        try {
            if (filter_var($contact, FILTER_VALIDATE_EMAIL)) {
                Mail::to($contact)->send(new ReportStatusChanged($report));
            } elseif (preg_match('/\d/', $contact)) {
                $this->sms->send($contact, sprintf(
                    'Status vaše prijave za %s je sada: %s.',
                    $report->court?->name,
                    $report->status->label(),
                ));
            }
        } catch (\Throwable $e) {
            report($e);
        }
    }

    private function adminReportUrl(Report $report): string
    {
        try {
            return URL::to('/'.config('site.admin_path').'/reports/'.$report->getKey().'/edit');
        } catch (\Throwable) {
            return URL::to('/');
        }
    }
}
