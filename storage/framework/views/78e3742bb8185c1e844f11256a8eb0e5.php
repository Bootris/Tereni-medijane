<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nova poruka sa sajta</title>
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
                                <?php echo e(config('app.name')); ?>

                            </p>
                            <h1 style="margin:8px 0 0;color:#ffffff;font-size:20px;font-weight:600;">
                                Nova poruka sa sajta
                            </h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:28px 32px;color:#33414f;font-size:14px;line-height:1.7;">
                            <p style="margin:0 0 4px;"><strong>Ime:</strong> <?php echo e($contactMessage->name); ?></p>
                            <p style="margin:0 0 4px;"><strong>Email:</strong>
                                <a href="mailto:<?php echo e($contactMessage->email); ?>" style="color:#9a7a48;"><?php echo e($contactMessage->email); ?></a>
                            </p>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($contactMessage->phone): ?>
                                <p style="margin:0 0 4px;"><strong>Telefon:</strong> <?php echo e($contactMessage->phone); ?></p>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($contactMessage->subject): ?>
                                <p style="margin:0 0 4px;"><strong>Tema:</strong> <?php echo e($contactMessage->subject); ?></p>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <div style="margin-top:20px;padding:16px 20px;background:#fbf9f4;border-left:3px solid #b08d57;border-radius:6px;white-space:pre-line;"><?php echo e($contactMessage->message); ?></div>
                            <p style="margin:24px 0 0;font-size:12px;color:#7c8894;">
                                Poruka je sačuvana i u administraciji sajta (Inbox).
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
<?php /**PATH /var/www/html/site-core/resources/views/emails/contact.blade.php ENDPATH**/ ?>