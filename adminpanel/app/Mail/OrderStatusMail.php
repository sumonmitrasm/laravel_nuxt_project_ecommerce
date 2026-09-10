<?php

namespace App\Mail;

use App\Models\Order;
use App\Models\Setting;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderStatusMail extends Mailable
{
    use SerializesModels;

    public string $siteName;
    public ?string $logoPath;

    public function __construct(public Order $order, public string $status)
    {
        $this->order->loadMissing(['user', 'items', 'address']);
        $setting = Setting::query()->where('status', true)->latest('id')->first(['side_name', 'image']);
        $this->siteName = $setting?->side_name ?: config('app.name', 'NovaCart');
        $path = $setting?->image ? public_path('admin/site_settings/'.basename($setting->image)) : null;
        $this->logoPath = $path && is_file($path) ? $path : null;
    }

    public function envelope(): Envelope
    {
        $label = $this->status === 'delivered' ? 'delivered' : 'cancelled';

        return new Envelope(
            from: new Address(config('mail.from.address'), $this->siteName),
            subject: $this->siteName.' order '.$label.': '.$this->order->order_number,
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.order-status');
    }

    public function attachments(): array
    {
        return [];
    }
}
