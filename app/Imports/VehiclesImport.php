<?php

namespace App\Imports;

use App\Models\Vehicle;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;

class VehiclesImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnFailure
{
    use SkipsFailures;

    /**
     * Expected header row:
     * plate_number, brand, model, year, type, capacity, status, fuel_type
     */
    public function model(array $row)
    {
        return new Vehicle([
            'plate_number' => $row['plate_number'],
            'brand'        => $row['brand'],
            'model'        => $row['model'],
            'year'         => $row['year'],
            'type'         => $row['type'] ?? 'truck',
            'capacity'     => $row['capacity'] ?? 0,
            'status'       => $row['status'] ?? 'active',
            'fuel_type'    => $row['fuel_type'] ?? 'diesel',
        ]);
    }

    public function rules(): array
    {
        return [
            'plate_number' => 'required|string|distinct|unique:vehicles,plate_number',
            'brand'        => 'required|string',
            'model'        => 'required|string',
            'year'         => 'required|integer|min:1990',
            'type'         => 'nullable|in:truck,mini_truck,compactor,tipper,suction',
            'status'       => 'nullable|in:active,inactive,maintenance,on_route',
        ];
    }
}
