<?php

namespace App\Imports;

use App\Models\Container;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;

class ContainersImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnFailure
{
    use SkipsFailures;

    /**
     * Expected header row (case/spacing-insensitive, auto-slugged by Laravel Excel):
     * code, name, type, capacity, fill_level, zone, status, latitude, longitude, address
     */
    public function model(array $row)
    {
        return new Container([
            'code'       => $row['code'],
            'name'       => $row['name'],
            'type'       => $row['type'] ?? 'general',
            'capacity'   => $row['capacity'],
            'fill_level' => $row['fill_level'] ?? 0,
            'zone'       => $row['zone'] ?? null,
            'status'     => $row['status'] ?? 'active',
            'latitude'   => $row['latitude'],
            'longitude'  => $row['longitude'],
            'address'    => $row['address'] ?? null,
        ]);
    }

    public function rules(): array
    {
        return [
            'code'      => 'required|string|distinct|unique:containers,code',
            'name'      => 'required|string',
            'type'      => 'nullable|in:general,recyclable,organic,hazardous,electronic',
            'capacity'  => 'required|numeric|min:1',
            'latitude'  => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'status'    => 'nullable|in:active,inactive,maintenance,full',
        ];
    }
}
