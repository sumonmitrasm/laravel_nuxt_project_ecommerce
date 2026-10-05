<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StorefrontResetPasswordNotification extends Notification
{
    public function __construct(private readonly string $token) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $frontend = rtrim((string) config('app.frontend_url', 'http://localhost:3000'), '/');
        $url = $frontend.'/reset-password?'.http_build_query([
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], '', '&', PHP_QUERY_RFC3986);

        $setting = \App\Support\SiteSettings::get();
        $siteName = ($setting['side_name'] ?? null) ?: config('app.name', 'Store');
        $logoUrl = !empty($setting['image'])
            ? asset('admin/site_settings/'.basename($setting['image']))
            : null;

        return (new MailMessage)
            ->from(config('mail.from.address'), $siteName)
            ->subject($siteName.' password reset')
            ->view('emails.reset-password', [
                'customerName' => $notifiable->name,
                'resetUrl' => $url,
                'expiresInMinutes' => config('auth.passwords.users.expire'),
                'siteName' => $siteName,
                'logoUrl' => $logoUrl,
            ]);
    }
}
