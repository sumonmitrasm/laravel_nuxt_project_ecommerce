<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Support\SiteSettings;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function info(): JsonResponse
    {
        $setting = SiteSettings::get();

        return response()->json([
            'site_name' => $setting['side_name'] ?? null,
            'phone' => ($setting['phone'] ?? null) ?: ($setting['perronal_phone'] ?? null),
            'email' => $setting['email'] ?? null,
            'address' => $setting['address'] ?? null,
            'map_url' => $setting['map_url'] ?? null,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'first_name' => ['required', 'string', 'min:2', 'max:50', 'regex:/^[\pL\s.-]+$/u'],
            'last_name' => ['required', 'string', 'min:2', 'max:50', 'regex:/^[\pL\s.-]+$/u'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'regex:/^[0-9+\-\s()]+$/', 'max:20'],
            'topic' => ['required', 'in:Order and delivery,Returns and refunds,Product information,Payment issue,Other'],
            'message' => ['required', 'string', 'min:10', 'max:2000', 'regex:/^[\pL\pN\s.,!?()\x27"\-\r\n]+$/u'],
        ], [
            'first_name.regex' => 'First name contains invalid characters.',
            'last_name.regex' => 'Last name contains invalid characters.',
            'phone.regex' => 'Please enter a valid phone number.',
            'message.regex' => 'Message cannot contain @, # or other special characters.',
        ]);

        ContactMessage::create($data);

        return response()->json(['message' => 'Message received! We will contact you soon.'], 201);
    }
}

