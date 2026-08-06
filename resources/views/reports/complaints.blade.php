{{-- complaints.blade.php --}}
<!DOCTYPE html>
<html><head><meta charset="UTF-8">
<style>
body{font-family:'DejaVu Sans',sans-serif;font-size:10px;color:#2c3e50;margin:0;padding:20px;}
.header{background:#dc3545;color:#fff;padding:15px 20px;margin:-20px -20px 20px;}
.header h1{margin:0;font-size:16px;}
.stats{display:flex;gap:10px;margin-bottom:15px;}
.stat-box{flex:1;background:#fdf0f1;border-radius:6px;padding:10px;text-align:center;border-top:3px solid #dc3545;}
.stat-box .val{font-size:20px;font-weight:800;color:#dc3545;}
.stat-box .lbl{font-size:8px;color:#888;}
table{width:100%;border-collapse:collapse;}
th{background:#dc3545;color:#fff;padding:6px 8px;font-size:9px;text-align:left;}
td{padding:5px 8px;border-bottom:1px solid #eee;font-size:9px;}
tr:nth-child(even){background:#f9f9f9;}
.footer{margin-top:20px;padding-top:10px;border-top:1px solid #eee;font-size:8px;color:#aaa;text-align:center;}
</style></head><body>
<div class="header"><h1>⚠ {{ __('Complaints Report') }}</h1><small>{{ config('app.name') }} — {{ now()->format('Y-m-d H:i') }}</small></div>
<div class="stats">
    <div class="stat-box"><div class="val">{{ $stats['total'] }}</div><div class="lbl">{{ __('Total') }}</div></div>
    <div class="stat-box"><div class="val">{{ $stats['open'] }}</div><div class="lbl">{{ __('Open') }}</div></div>
    <div class="stat-box"><div class="val">{{ $stats['resolved'] }}</div><div class="lbl">{{ __('Resolved') }}</div></div>
    <div class="stat-box"><div class="val">{{ $stats['urgent'] }}</div><div class="lbl">{{ __('Urgent') }}</div></div>
    <div class="stat-box"><div class="val">{{ $stats['avg_resolve'] }} d</div><div class="lbl">{{ __('Avg Resolve') }}</div></div>
</div>
<table>
    <thead><tr>
        <th>{{ __('Ticket') }}</th><th>{{ __('Complainant') }}</th><th>{{ __('Category') }}</th>
        <th>{{ __('Subject') }}</th><th>{{ __('Priority') }}</th><th>{{ __('Status') }}</th>
        <th>{{ __('Assigned') }}</th><th>{{ __('Date') }}</th><th>{{ __('Resolved') }}</th>
    </tr></thead>
    <tbody>
        @foreach($complaints as $c)
        <tr>
            <td><strong>{{ $c->ticket_number }}</strong></td>
            <td>{{ $c->complainant_name }}</td>
            <td>{{ ucfirst(str_replace('_',' ',$c->category)) }}</td>
            <td>{{ \Str::limit($c->subject,35) }}</td>
            <td>{{ ucfirst($c->priority) }}</td>
            <td>{{ ucfirst(str_replace('_',' ',$c->status)) }}</td>
            <td>{{ $c->assignedTo?->name ?? '—' }}</td>
            <td>{{ $c->created_at->format('Y-m-d') }}</td>
            <td>{{ $c->resolved_at?->format('Y-m-d') ?? '—' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
<div class="footer">{{ config('app.name') }} — {{ __('Complaints Report') }} — {{ now()->format('Y-m-d') }}</div>
</body></html>
