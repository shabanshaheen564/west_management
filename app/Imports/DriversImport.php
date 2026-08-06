<?php

namespace App\Imports;

use App\Models\Driver;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;

class DriversImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnFailure
{
    use SkipsFailures;

    /**
     * Expected header row:
     * employee_id, name, phone, license_number, license_class, license_expiry, hire_date, status
     */
    public function model(array $row)
    {
        return new Driver([
            'employee_id'     => $row['employee_id'],
            'name'            => $row['name'],
            'phone'           => $row['phone'],
            'license_number'  => $row['license_number'],
            'license_class'   => $row['license_class'] ?? 'C',
            'license_expiry'  => $row['license_expiry'],
            'hire_date'       => $row['hire_date'],
            'status'          => $row['status'] ?? 'active',
        ]);
    }

    public function rules(): array
    {
        return [
            'employee_id'    => 'required|string|distinct|unique:drivers,employee_id',
            'name'           => 'required|string',
            'phone'          => 'required|string',
            'license_number' => 'required|string|distinct|unique:drivers,license_number',
            'license_expiry' => 'required|date',
            'hire_date'      => 'required|date',
            'status'         => 'nullable|in:active,inactive,on_leave,suspended',
        ];
    }
}
