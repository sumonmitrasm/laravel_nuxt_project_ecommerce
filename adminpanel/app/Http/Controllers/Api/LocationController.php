<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\District;
use App\Models\Division;
use Illuminate\Http\JsonResponse;
use App\Support\ContentCache;

class LocationController extends Controller
{
    public function divisions(): JsonResponse
    {
        $divisions = ContentCache::remember('locations',
            'api.locations.divisions.v2',
            fn () => Division::query()->orderBy('name')->get(['id', 'name', 'bn_name'])->toArray(),
        );

        return response()->json(['status' => true, 'locations' => $divisions]);
    }

    public function districts(Division $division): JsonResponse
    {
        $districts = ContentCache::remember('locations',
            "api.locations.division.{$division->id}.districts.v2",
            fn () => $division->districts()->orderBy('name')->get(['id', 'name', 'bn_name'])->toArray(),
        );

        return response()->json(['status' => true, 'locations' => $districts]);
    }

    public function upazilas(District $district): JsonResponse
    {
        $upazilas = ContentCache::remember('locations',
            "api.locations.district.{$district->id}.upazilas.v2",
            fn () => $district->upazilas()->orderBy('name')->get(['id', 'name', 'bn_name'])->toArray(),
        );

        return response()->json(['status' => true, 'locations' => $upazilas]);
    }
}
