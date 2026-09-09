<?php

namespace App\Mail;

use App\Models\Order;
use App\Models\Setting;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderPlacedMail extends Mailable
{
    use SerializesModels;

    public string $siteName;
    public ?string $logoPath;

    public function __construct(public Order $order)
    {
        $this->order->loadMissing(['user', 'items', 'address', 'payments']);
        $setting = Setting::query()->where('status', true)->latest('id')->first(['side_name', 'image']);
        $this->siteName = $setting?->side_name ?: config('app.name', 'NovaCart');
        $logoPath = $setting?->image ? public_path('admin/site_settings/'.basename($setting->image)) : null;
        $this->logoPath = $logoPath && is_file($logoPath) ? $logoPath : null;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(config('mail.from.address'), $this->siteName),
            subject: $this->siteName.' order confirmed: '.$this->order->order_number,
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.order-placed');
    }

    public function attachments(): array
    {
        return [];
    }
}
