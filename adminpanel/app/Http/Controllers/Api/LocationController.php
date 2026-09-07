<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\District;
use App\Models\Division;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class LocationController extends Controller
{
    public function divisions(): JsonResponse
    {
        $divisions = Cache::remember(
            'api.locations.divisions.v2',
            now()->addDay(),
            fn () => Division::query()->orderBy('name')->get(['id', 'name', 'bn_name'])->toArray(),
        );

        return response()->json(['status' => true, 'locations' => $divisions]);
    }

    public function districts(Division $division): JsonResponse
    {
        $districts = Cache::remember(
            "api.locations.division.{$division->id}.districts.v2",
            now()->addDay(),
            fn () => $division->districts()->orderBy('name')->get(['id', 'name', 'bn_name'])->toArray(),
        );

        return response()->json(['status' => true, 'locations' => $districts]);
    }

    public function upazilas(District $district): JsonResponse
    {
        $upazilas = Cache::remember(
            "api.locations.district.{$district->id}.upazilas.v2",
            now()->addDay(),
            fn () => $district->upazilas()->orderBy('name')->get(['id', 'name', 'bn_name'])->toArray(),
        );

        return response()->json(['status' => true, 'locations' => $upazilas]);
    }
}
