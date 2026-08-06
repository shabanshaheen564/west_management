<?php

namespace App\Exports;

use App\Models\Complaint;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ComplaintsExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return Complaint::get([
            'ticket_number', 'complainant_name', 'complainant_phone',
            'complainant_email', 'category', 'subject',
            'priority', 'status', 'address',
            'assigned_to', 'resolved_at', 'created_at'
        ]);
    }

    public function headings(): array
    {
        return [
            'Ticket', 'Name', 'Phone',
            'Email', 'Category', 'Subject',
            'Priority', 'Status', 'Address',
            'Assigned To', 'Resolved At', 'Created At'
        ];
    }
}