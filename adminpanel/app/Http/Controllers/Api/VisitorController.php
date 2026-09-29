<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\VisitorLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class VisitorController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'visitor_id' => ['required', 'string', 'max:64'],
            'path' => ['required', 'string', 'max:500'],
            'page_title' => ['nullable', 'string', 'max:160'],
            'referrer' => ['nullable', 'string', 'max:500'],
        ]);

        // A visitor can be a signed-in customer or an anonymous browser.
        // No raw IP address is stored in this feature.
        $user = $request->user('sanctum');
        $userAgent = (string) $request->userAgent();

        $ipAddress = $request->ip();
        $location = $this->location($request, $ipAddress);

        VisitorLog::create([
            'user_id' => $user?->id,
            'visitor_id' => $data['visitor_id'],
            'ip_address' => $ipAddress,
            'path' => '/'.ltrim($data['path'], '/'),
            'page_title' => $data['page_title'] ?? null,
            'country' => $location['country'],
            'city' => $location['city'],
            'device' => $this->device($userAgent),
            'referrer' => $data['referrer'] ?? null,
        ]);

        return response()->json(['status' => true]);
    }

    private function location(Request $request, string $ipAddress): array
    {
        $country = $this->header($request, 'CF-IPCountry');
        $city = $this->header($request, 'CF-IPCity');

        if (in_array($ipAddress, ['127.0.0.1', '::1'], true)) {
            return ['country' => 'Local development', 'city' => null];
        }

        if ($country && $country !== 'XX') {
            return ['country' => $country, 'city' => $city];
        }

        return Cache::remember('visitor-location-'.sha1($ipAddress), now()->addDays(7), function () use ($ipAddress) {
            try {
                $response = Http::timeout(2)->get("https://ipapi.co/{$ipAddress}/json/");

                if ($response->successful()) {
                    return [
                        'country' => $response->json('country_name'),
                        'city' => $response->json('city'),
                    ];
                }
            } catch (\Throwable) {
                // Visitor logging must never slow down or break the storefront.
            }

            return ['country' => null, 'city' => null];
        });
    }

    private function header(Request $request, string $name): ?string
    {
        $value = trim((string) $request->header($name));

        return $value !== '' ? Str::limit($value, 100, '') : null;
    }

    private function device(string $userAgent): string
    {
        if (preg_match('/mobile|android|iphone|ipod/i', $userAgent)) {
            return 'Mobile';
        }

        if (preg_match('/ipad|tablet/i', $userAgent)) {
            return 'Tablet';
        }

        return 'Desktop';
    }
}
