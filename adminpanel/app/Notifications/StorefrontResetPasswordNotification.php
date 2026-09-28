<?php

namespace App\Notifications;

use App\Models\Setting;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Cache;

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

        $setting = Cache::remember(
            'general_setting.v2',
            now()->addHours(6),
            fn () => Setting::query()->where('status', true)->latest('id')->first(['side_name', 'image']),
        );
        $siteName = $setting?->side_name ?: config('app.name', 'Store');
        $logoUrl = $setting?->image
            ? asset('admin/site_settings/'.basename($setting->image))
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
