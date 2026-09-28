<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light only">
    <title>Reset your password</title>
</head>
<body style="margin:0;padding:0;background:#f4f6f5;font-family:Arial,Helvetica,sans-serif;color:#17211c;">
<div style="display:none;max-height:0;overflow:hidden;opacity:0;color:transparent;">Reset your {{ $siteName }} password securely.</div>
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="width:100%;background:#f4f6f5;">
    <tr>
        <td align="center" style="padding:36px 16px;">
            <table role="presentation" width="600" cellspacing="0" cellpadding="0" border="0" style="width:100%;max-width:600px;">
                <tr>
                    <td align="center" style="padding:0 16px 22px;">
                        @if($logoUrl)
                            <img src="{{ $logoUrl }}" alt="{{ $siteName }}" style="display:block;width:auto;max-width:180px;height:auto;max-height:58px;border:0;">
                        @else
                            <span style="font-size:24px;font-weight:800;letter-spacing:-.6px;color:#172a21;">{{ $siteName }}</span>
                        @endif
                    </td>
                </tr>
                <tr>
                    <td style="background:#173126;border-radius:14px 14px 0 0;padding:34px 42px;">
                        <div style="font-size:12px;font-weight:700;letter-spacing:1.4px;text-transform:uppercase;color:#ff9b89;">Account security</div>
                        <div style="padding-top:8px;font-size:28px;line-height:35px;font-weight:800;color:#ffffff;">Reset your password</div>
                        <div style="padding-top:10px;font-size:14px;line-height:22px;color:#d7e2dc;">Use the secure link below to choose a new password.</div>
                    </td>
                </tr>
                <tr>
                    <td style="background:#ffffff;padding:38px 42px 34px;border-radius:0 0 14px 14px;box-shadow:0 14px 40px rgba(23,42,33,.08);">
                        <p style="margin:0 0 18px;font-size:16px;line-height:25px;color:#26332d;">Hello <strong>{{ $customerName }}</strong>,</p>
                        <p style="margin:0 0 28px;font-size:15px;line-height:25px;color:#64706a;">We received a request to reset the password for your {{ $siteName }} account. Click the button below to continue.</p>

                        <table role="presentation" cellspacing="0" cellpadding="0" border="0" align="center" style="margin:0 auto 28px;">
                            <tr>
                                <td align="center" bgcolor="#ff5a45" style="border-radius:7px;">
                                    <a href="{{ $resetUrl }}" target="_blank" style="display:inline-block;padding:15px 30px;border:1px solid #ff5a45;border-radius:7px;color:#ffffff;font-size:14px;font-weight:700;text-decoration:none;">Reset password &nbsp;&rarr;</a>
                                </td>
                            </tr>
                        </table>

                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-bottom:28px;background:#fff6f3;border-left:3px solid #ff5a45;">
                            <tr>
                                <td style="padding:13px 15px;font-size:12px;line-height:19px;color:#72564e;"><strong>For your security:</strong> This link expires in {{ $expiresInMinutes }} minutes and can only be used once.</td>
                            </tr>
                        </table>

                        <p style="margin:0 0 8px;font-size:12px;line-height:19px;color:#8a948f;">If the button does not work, copy and paste this link into your browser:</p>
                        <p style="margin:0;padding:12px 14px;background:#f6f8f7;border:1px solid #e6ebe8;border-radius:6px;font-size:11px;line-height:17px;word-break:break-all;color:#52615a;"><a href="{{ $resetUrl }}" style="color:#52615a;text-decoration:none;">{{ $resetUrl }}</a></p>

                        <div style="height:1px;background:#e9edea;margin:30px 0 22px;"></div>
                        <p style="margin:0;font-size:12px;line-height:20px;color:#8a948f;">If you did not request a password reset, you can safely ignore this email. Your password will not change.</p>
                    </td>
                </tr>
                <tr>
                    <td align="center" style="padding:24px 24px 0;font-size:11px;line-height:18px;color:#98a19d;">&copy; {{ date('Y') }} {{ $siteName }}. All rights reserved.<br>This is an automated security email. Please do not reply.</td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
