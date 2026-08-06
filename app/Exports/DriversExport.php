<?php

namespace App\Exports;
use App\Models\Driver;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class DriversExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return Driver::get([
            'employee_id', 'name', 'phone',
            'license_number', 'license_class', 'license_expiry',
            'hire_date', 'status', 'rating', 'created_at'
        ]);
    }

    public function headings(): array
    {
        return [
            'Employee ID', 'Name', 'Phone',
            'License Number', 'License Class', 'License Expiry',
            'Hire Date', 'Status', 'Rating', 'Created At'
        ];
    }
}