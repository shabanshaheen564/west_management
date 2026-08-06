<?php

namespace App\Exports;

use App\Models\Container;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ContainersExport implements FromCollection, WithHeadings
{
    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = Container::query();

        if (!empty($this->filters['zone']))
            $query->where('zone', $this->filters['zone']);

        if (!empty($this->filters['status']))
            $query->where('status', $this->filters['status']);

        if (!empty($this->filters['type']))
            $query->where('type', $this->filters['type']);

        return $query->get([
            'code', 'name', 'type', 'capacity',
            'fill_level', 'zone', 'status',
            'latitude', 'longitude', 'address',
            'last_emptied_at', 'created_at'
        ]);
    }

    public function headings(): array
    {
        return [
            'Code', 'Name', 'Type', 'Capacity',
            'Fill Level', 'Zone', 'Status',
            'Latitude', 'Longitude', 'Address',
            'Last Emptied', 'Created At'
        ];
    }
}