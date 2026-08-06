<?php

namespace App\Exports;

use App\Models\Vehicle;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class VehiclesExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return Vehicle::get([
            'plate_number', 'brand', 'model', 'year',
            'type', 'status', 'fuel_type',
            'last_maintenance', 'next_maintenance',
            'insurance_expiry', 'created_at'
        ]);
    }

    public function headings(): array
    {
        return [
            'Plate Number', 'Brand', 'Model', 'Year',
            'Type', 'Status', 'Fuel Type',
            'Last Maintenance', 'Next Maintenance',
            'Insurance Expiry', 'Created At'
        ];
    }
}