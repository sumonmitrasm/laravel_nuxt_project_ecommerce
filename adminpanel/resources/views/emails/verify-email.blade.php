<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light only">
    <title>Verify your email</title>
</head>
<body style="margin:0;padding:0;background:#f4f6f5;font-family:Arial,Helvetica,sans-serif;color:#17211c;">
<div style="display:none;max-height:0;overflow:hidden;opacity:0;color:transparent;">Verify your email to activate your NovaCart account.</div>
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="width:100%;background:#f4f6f5;">
    <tr>
        <td align="center" style="padding:36px 16px;">
            <table role="presentation" width="600" cellspacing="0" cellpadding="0" border="0" style="width:100%;max-width:600px;">
                <tr>
                    <td style="padding:0 4px 22px;">
                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                            <tr>
                                <td style="font-size:24px;font-weight:900;letter-spacing:-1px;color:#111815;">NOVA<span style="color:#ff5a45;">CART</span></td>
                                <td align="right" style="font-size:12px;color:#7c8781;">Secure account verification</td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td style="background:#172a21;border-radius:14px 14px 0 0;padding:34px 42px;">
                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                            <tr>
                                <td width="58" valign="top">
                                    <div style="width:48px;height:48px;line-height:48px;text-align:center;border-radius:50%;background:#ff5a45;color:#fff;font-size:22px;">✓</div>
                                </td>
                                <td style="padding-left:14px;">
                                    <div style="font-size:12px;font-weight:700;letter-spacing:1.4px;text-transform:uppercase;color:#ff9b89;">Welcome to NovaCart</div>
                                    <div style="padding-top:7px;font-size:27px;line-height:34px;font-weight:800;color:#fff;">Verify your email address</div>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td style="background:#fff;padding:38px 42px 34px;border-radius:0 0 14px 14px;box-shadow:0 14px 40px rgba(23,42,33,.08);">
                        <p style="margin:0 0 18px;font-size:16px;line-height:25px;color:#26332d;">Hello <strong>{{ $customerName }}</strong>,</p>
                        <p style="margin:0 0 28px;font-size:15px;line-height:25px;color:#64706a;">Thanks for creating your NovaCart account. Confirm this email address to activate your account and securely access checkout, order tracking and member benefits.</p>

                        <table role="presentation" cellspacing="0" cellpadding="0" border="0" align="center" style="margin:0 auto 28px;">
                            <tr>
                                <td align="center" bgcolor="#ff5a45" style="border-radius:7px;">
                                    <a href="{{ $verificationUrl }}" target="_blank" style="display:inline-block;padding:15px 30px;border:1px solid #ff5a45;border-radius:7px;color:#fff;font-size:14px;font-weight:700;text-decoration:none;">Verify email address &nbsp;→</a>
                                </td>
                            </tr>
                        </table>

                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-bottom:28px;background:#fff6f3;border-left:3px solid #ff5a45;">
                            <tr>
                                <td style="padding:13px 15px;font-size:12px;line-height:19px;color:#72564e;"><strong>For your security:</strong> This link expires in {{ $expiresInMinutes }} minutes and can only be used for this account.</td>
                            </tr>
                        </table>

                        <p style="margin:0 0 8px;font-size:12px;line-height:19px;color:#8a948f;">If the button does not work, copy and paste this link into your browser:</p>
                        <p style="margin:0;padding:12px 14px;background:#f6f8f7;border:1px solid #e6ebe8;border-radius:6px;font-size:11px;line-height:17px;word-break:break-all;color:#52615a;"><a href="{{ $verificationUrl }}" style="color:#52615a;text-decoration:none;">{{ $verificationUrl }}</a></p>

                        <div style="height:1px;background:#e9edea;margin:30px 0 22px;"></div>
                        <p style="margin:0;font-size:12px;line-height:20px;color:#8a948f;">If you did not create a NovaCart account, you can safely ignore this email. No account will be activated without verification.</p>
                    </td>
                </tr>
                <tr>
                    <td align="center" style="padding:24px 24px 0;font-size:11px;line-height:18px;color:#98a19d;">
                        <strong style="color:#68736e;">NovaCart</strong> · Your shopping, simplified<br>
                        This is an automated security email. Please do not reply.<br>
                        © {{ date('Y') }} NovaCart. All rights reserved.
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
