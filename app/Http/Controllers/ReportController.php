<?php

namespace App\Http\Controllers;

use App\Services\Reports\PdfReportService;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function __construct(private PdfReportService $pdfService) {}

    public function index()
    {
        return view('waste_management.reports');
    }

    public function generate(Request $request)
    {
        $type    = $request->get('type');
        $filters = $request->except(['type','format','_token']);

        $pdf = match($type) {
            'containers' => $this->pdfService->generateContainersReport($filters),
            'vehicles'   => $this->pdfService->generateVehiclesReport($filters),
            'routes'     => $this->pdfService->generateRoutesReport($filters),
            'complaints' => $this->pdfService->generateComplaintsReport($filters),
            'dashboard'  => $this->pdfService->generateDashboardReport(),
            default      => abort(400, 'Invalid report type'),
        };

        return $pdf->download($type.'-report-'.date('Y-m-d').'.pdf');
    }
}