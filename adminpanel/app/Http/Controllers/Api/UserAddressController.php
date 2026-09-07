<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\District;
use App\Models\Upazila;
use App\Models\UserAddress;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class UserAddressController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $addresses = $request->user()->addresses()
            ->with(['locationDivision:id,name', 'locationDistrict:id,name', 'locationUpazila:id,name'])
            ->where('status', true)
            ->orderByDesc('is_default')
            ->latest('id')
            ->get();

        return response()->json(['status' => true, 'addresses' => $addresses]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validated($request);
        $user = $request->user();

        $address = DB::transaction(function () use ($user, $data) {
            $hasAddress = $user->addresses()->where('status', true)->exists();
            $makeDefault = ! $hasAddress || ($data['is_default'] ?? false);

            if ($makeDefault) {
                $user->addresses()->update(['is_default' => false]);
            }

            return $user->addresses()->create([
                ...$data,
                'is_default' => $makeDefault,
                'status' => true,
            ]);
        });

        return response()->json([
            'status' => true,
            'message' => 'Address saved successfully.',
            'address' => $address->load(['locationDivision:id,name', 'locationDistrict:id,name', 'locationUpazila:id,name']),
        ], 201);
    }

    public function update(Request $request, int $address): JsonResponse
    {
        $userAddress = $this->ownedAddress($request, $address);
        $data = $this->validated($request);

        DB::transaction(function () use ($request, $userAddress, $data) {
            if ($data['is_default'] ?? false) {
                $request->user()->addresses()->whereKeyNot($userAddress->id)->update(['is_default' => false]);
            }

            $userAddress->update($data);

            if (! $request->user()->addresses()->where('status', true)->where('is_default', true)->exists()) {
                $userAddress->update(['is_default' => true]);
            }
        });

        return response()->json([
            'status' => true,
            'message' => 'Address updated successfully.',
            'address' => $userAddress->fresh()->load(['locationDivision:id,name', 'locationDistrict:id,name', 'locationUpazila:id,name']),
        ]);
    }

    public function destroy(Request $request, int $address): JsonResponse
    {
        $userAddress = $this->ownedAddress($request, $address);

        DB::transaction(function () use ($request, $userAddress) {
            $wasDefault = $userAddress->is_default;
            $userAddress->delete();

            if ($wasDefault) {
                $request->user()->addresses()
                    ->where('status', true)
                    ->latest('id')
                    ->first()?->update(['is_default' => true]);
            }
        });

        return response()->json(['status' => true, 'message' => 'Address removed successfully.']);
    }

    public function makeDefault(Request $request, int $address): JsonResponse
    {
        $userAddress = $this->ownedAddress($request, $address);

        DB::transaction(function () use ($request, $userAddress) {
            $request->user()->addresses()->whereKeyNot($userAddress->id)->update(['is_default' => false]);
            $userAddress->update(['is_default' => true]);
        });

        return response()->json([
            'status' => true,
            'message' => 'Default address updated.',
            'address' => $userAddress->fresh()->load(['locationDivision:id,name', 'locationDistrict:id,name', 'locationUpazila:id,name']),
        ]);
    }

    private function ownedAddress(Request $request, int $address): UserAddress
    {
        return $request->user()->addresses()->where('status', true)->findOrFail($address);
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'label' => ['required', 'string', 'min:2', 'max:30', Rule::notIn(['default'])],
            'recipient_name' => ['required', 'string', 'min:2', 'max:100', "regex:/^[\\pL\\pM .'-]+$/u"],
            'phone' => ['required', 'string', 'max:20', 'regex:/^(?:\\+?88)?01[3-9]\\d{8}$/'],
            'alternative_phone' => ['nullable', 'string', 'max:20', 'different:phone', 'regex:/^(?:\\+?88)?01[3-9]\\d{8}$/'],
            'division' => ['required', 'integer', Rule::exists('divisions', 'id')],
            'district' => ['required', 'integer', Rule::exists('districts', 'id')],
            'upazila' => ['required', 'integer', Rule::exists('upazilas', 'id')],
            'area' => ['nullable', 'string', 'max:150'],
            'postal_code' => ['nullable', 'string', 'max:20', 'regex:/^[A-Za-z0-9 -]+$/'],
            'address_line' => ['required', 'string', 'min:5', 'max:500'],
            'is_default' => ['sometimes', 'boolean'],
        ], [
            'phone.regex' => 'Enter a valid Bangladeshi mobile number.',
            'alternative_phone.regex' => 'Enter a valid alternative Bangladeshi mobile number.',
            'recipient_name.regex' => 'Recipient name may contain letters, spaces, apostrophes, dots and hyphens only.',
        ]);

        $districtBelongsToDivision = District::query()
            ->whereKey($data['district'])
            ->where('division_id', $data['division'])
            ->exists();
        $upazilaBelongsToDistrict = Upazila::query()
            ->whereKey($data['upazila'])
            ->where('district_id', $data['district'])
            ->exists();
        $locationErrors = [];

        if (! $districtBelongsToDivision) {
            $locationErrors['district'] = ['The selected district does not belong to this division.'];
        }
        if (! $upazilaBelongsToDistrict) {
            $locationErrors['upazila'] = ['The selected upazila does not belong to this district.'];
        }
        if ($locationErrors !== []) {
            throw ValidationException::withMessages($locationErrors);
        }

        return $data;
    }
}