<?php

namespace App\Http\Controllers;

use App\Models\Complaint;
use App\Models\User;
use App\Exports\ComplaintsExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ComplaintController extends Controller
{
    public function index(Request $request)
    {
        $query = Complaint::with(['user','assignedTo']);

        if ($request->filled('search')) {
            $q = $request->search;
            $query->where(fn($q2) => $q2->where('ticket_number','like',"%$q%")
                ->orWhere('complainant_name','like',"%$q%")
                ->orWhere('subject','like',"%$q%"));
        }
        if ($request->filled('status'))   $query->where('status', $request->status);
        if ($request->filled('priority')) $query->where('priority', $request->priority);
        if ($request->filled('category')) $query->where('category', $request->category);

        $complaints = $query->orderByDesc('created_at')->paginate(15)->withQueryString();
        $staff = User::role(['admin','supervisor'])->get();
        $stats = [
            'total'    => Complaint::count(),
            'open'     => Complaint::where('status','open')->count(),
            'urgent'   => Complaint::where('priority','urgent')->where('status','open')->count(),
            'resolved' => Complaint::where('status','resolved')->count(),
        ];

        return view('waste_management.complaints', compact('complaints','staff','stats'));
    }

    public function create()
    {
        // Public, unauthenticated complaint form — intentionally a standalone
        // view (not the admin "waste_management.complaints" dashboard, which
        // requires $complaints/$staff/$stats and the "view complaints" permission).
        return view('waste_management.complaint_public');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'complainant_name'  => 'required|string',
            'complainant_phone' => 'nullable|string',
            'complainant_email' => 'nullable|email',
            'category'          => 'required|in:missed_collection,damaged_container,illegal_dumping,odor,noise,hazardous_waste,other',
            'subject'           => 'required|string',
            'description'       => 'required|string',
            'latitude'          => 'nullable|numeric',
            'longitude'         => 'nullable|numeric',
            'address'           => 'nullable|string',
            // Priority is only editable from the internal admin form; the public
            // form doesn't expose it, so it's optional and defaults to "medium".
            'priority'          => 'nullable|in:low,medium,high,urgent',
        ]);
        $validated['priority'] = $validated['priority'] ?? 'medium';
        if (auth()->check()) $validated['user_id'] = auth()->id();
        Complaint::create($validated);

        // Authenticated staff submitting from the internal dashboard land back
        // on the complaints list; anonymous public submitters (who can't access
        // that protected route) land back on the public form with a success message.
        if (auth()->check()) {
            return redirect()->route('complaints.index')->with('success', __('Complaint submitted successfully'));
        }
        return redirect()->route('complaints.public')->with('success', __('Complaint submitted successfully'));
    }

    public function update(Request $request, Complaint $complaint)
    {
        $validated = $request->validate([
            'status'           => 'required|in:open,in_progress,resolved,closed,rejected',
            'priority'         => 'required|in:low,medium,high,urgent',
            'assigned_to'      => 'nullable|exists:users,id',
            'resolution_notes' => 'nullable|string',
        ]);
        if ($validated['status'] === 'resolved' && !$complaint->resolved_at) {
            $validated['resolved_at'] = now();
        }
        $complaint->update($validated);
        return redirect()->route('complaints.index')->with('success', __('Complaint updated successfully'));
    }

    public function destroy(Complaint $complaint)
    {
        $complaint->delete();
        return redirect()->route('complaints.index')->with('success', __('Complaint deleted'));
    }

    public function export()
    {
        return Excel::download(new ComplaintsExport, 'complaints-'.date('Y-m-d').'.xlsx');
    }
}