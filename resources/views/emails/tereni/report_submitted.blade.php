<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nova prijava</title>
</head>
<body style="margin:0;padding:0;background-color:#f1f5f2;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="padding:32px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="560" cellpadding="0" cellspacing="0"
                    style="max-width:560px;width:100%;background:#ffffff;border-radius:12px;overflow:hidden;border:1px solid #d1e0d5;">
                    <tr>
                        <td style="background:#14532d;padding:24px 32px;">
                            <p style="margin:0;color:#86efac;font-size:12px;letter-spacing:2px;text-transform:uppercase;">
                                Tereni Medijana
                            </p>
                            <h1 style="margin:8px 0 0;color:#ffffff;font-size:20px;font-weight:600;">
                                Nova prijava — {{ $report->court->name }}
                            </h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:28px 32px;color:#33414f;font-size:14px;line-height:1.7;">
                            <p style="margin:0 0 4px;"><strong>Teren:</strong>
                                {{ $report->court->name }}@if ($report->court->facility) — {{ $report->court->facility->name }}@endif
                            </p>
                            <p style="margin:0 0 4px;"><strong>Kategorija:</strong> {{ $report->category->label() }}</p>
                            <p style="margin:0 0 4px;"><strong>Prijavio:</strong>
                                {{ $report->reporter_name ?: 'Anoniman' }}@if ($report->reporter_contact) ({{ $report->reporter_contact }})@endif
                            </p>
                            @if ($report->description)
                                <div style="margin-top:16px;padding:16px 20px;background:#f6faf7;border-left:3px solid #15803d;border-radius:6px;white-space:pre-line;">{{ $report->description }}</div>
                            @endif
                            <p style="margin:24px 0 0;font-size:13px;">
                                Prijava čeka moderaciju pre javnog prikaza. Otvorite administraciju da je pregledate,
                                objavite i promenite status.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
