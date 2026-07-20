<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hvala što ste nas kontaktirali</title>
</head>
<body style="margin:0;padding:0;background-color:#f6f1e7;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="padding:32px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="560" cellpadding="0" cellspacing="0"
                    style="max-width:560px;width:100%;background:#ffffff;border-radius:12px;overflow:hidden;border:1px solid #e3d8c2;">
                    <tr>
                        <td style="background:#0c1b2a;padding:24px 32px;">
                            <p style="margin:0;color:#b08d57;font-size:12px;letter-spacing:2px;text-transform:uppercase;">
                                {{ config('app.name') }}
                            </p>
                            <h1 style="margin:8px 0 0;color:#ffffff;font-size:20px;font-weight:600;">
                                Hvala što ste nas kontaktirali
                            </h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:28px 32px;color:#33414f;font-size:14px;line-height:1.7;">
                            <p style="margin:0;">Poštovani/a {{ $contactMessage->name }},</p>
                            <p style="margin:16px 0 0;">
                                Vaša poruka je uspešno primljena. Odgovorićemo vam u najkraćem mogućem roku,
                                najkasnije u roku od 24 časa radnim danima.
                            </p>
                            <div style="margin-top:20px;padding:16px 20px;background:#fbf9f4;border-left:3px solid #b08d57;border-radius:6px;white-space:pre-line;">{{ $contactMessage->message }}</div>
                            <p style="margin:24px 0 0;">Srdačan pozdrav,<br>{{ config('app.name') }}</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
