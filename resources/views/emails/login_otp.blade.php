<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email OTP</title>
</head>
<body style="margin: 0; padding: 28px 14px; background: #f4f4f4; font-family: Arial, Helvetica, sans-serif; color: #333333;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse: collapse;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width: 520px; border-collapse: collapse; background: #ffffff; border-radius: 4px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.12);">
                    <tr>
                        <td style="padding: 34px 34px 24px;">
                            <h1 style="margin: 0; color: #ff5a00; font-size: 30px; line-height: 1.2; text-align: center; font-weight: 700;">
                                Email OTP
                            </h1>

                            <div style="height: 2px; background: #d8d8d8; margin: 22px 0 16px;"></div>

                            <p style="margin: 0 0 12px; font-size: 15px; line-height: 1.5;">
                                Halo {{ $name ?: 'Pengguna' }},
                            </p>

                            <p style="margin: 0 0 12px; font-size: 15px; line-height: 1.5;">
                                Kode One-Time Password (OTP) Anda adalah:
                            </p>

                            <div style="margin: 14px 0 18px; text-align: center; color: #ff5a00; font-size: 36px; line-height: 1; font-weight: 700; letter-spacing: 2px;">
                                {{ $otp }}
                            </div>

                            <p style="margin: 0 0 12px; font-size: 15px; line-height: 1.5;">
                                Gunakan kode OTP ini untuk menyelesaikan proses login Anda. Jangan bagikan kode ini kepada siapa pun.
                            </p>

                            <p style="margin: 0 0 24px; font-size: 15px; line-height: 1.5;">
                                Terima kasih telah menggunakan Email OTP InfraSPH.
                            </p>

                            <p style="margin: 0; color: #8a8a8a; font-size: 13px; line-height: 1.5; text-align: center;">
                                &copy; {{ date('Y') }} {{ $appUrl }}. Seluruh hak cipta dilindungi.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
