{{-- routes report --}}
<!DOCTYPE html>
<html><head><meta charset="UTF-8">
<style>
body{font-family:'DejaVu Sans',sans-serif;font-size:10px;color:#2c3e50;margin:0;padding:20px;}
.header{background:#7b3fa0;color:#fff;padding:15px 20px;margin:-20px -20px 20px;}
.header h1{margin:0;font-size:16px;}
.stats{display:flex;gap:10px;margin-bottom:15px;}
.stat-box{flex:1;background:#f5edfb;border-radius:6px;padding:10px;text-align:center;border-top:3px solid #7b3fa0;}
.stat-box .val{font-size:20px;font-weight:800;color:#7b3fa0;}
.stat-box .lbl{font-size:8px;color:#888;}
table{width:100%;border-collapse:collapse;}
th{background:#7b3fa0;color:#fff;padding:6px 8px;font-size:9px;text-align:left;}
td{padding:5px 8px;border-bottom:1px solid #eee;font-size:9px;}
tr:nth-child(even){background:#f9f9f9;}
.footer{margin-top:20px;padding-top:10px;border-top:1px solid #eee;font-size:8px;color:#aaa;text-align:center;}
</style></head><body>
<div class="header"><h1>🗺 {{ __('Routes Report') }}</h1><small>{{ config('app.name') }} — {{ now()->format('Y-m-d H:i') }}</small></div>
<div class="stats">
    <div class="stat-box"><div class="val">{{ $stats['total'] }}</div><div class="lbl">{{ __('Total') }}</div></div>
    <div class="stat-box"><div class="val">{{ $stats['completed'] }}</div><div class="lbl">{{ __('Completed') }}</div></div>
    <div class="stat-box"><div class="val">{{ $stats['total_distance'] }} km</div><div class="lbl">{{ __('Total Distance') }}</div></div>
    <div class="stat-box"><div class="val">{{ $stats['avg_duration'] }} min</div><div class="lbl">{{ __('Avg Duration') }}</div></div>
</div>
<table>
    <thead><tr>
        <th>#</th><th>{{ __('Code') }}</th><th>{{ __('Name') }}</th><th>{{ __('Vehicle') }}</th>
        <th>{{ __('Driver') }}</th><th>{{ __('Status') }}</th><th>{{ __('Distance') }}</th>
        <th>{{ __('Scheduled') }}</th><th>{{ __('Completed') }}</th>
    </tr></thead>
    <tbody>
        @foreach($routes as $i => $r)
        <tr>
            <td>{{ $i+1 }}</td>
            <td>{{ $r->code }}</td>
            <td>{{ $r->name }}</td>
            <td>{{ $r->vehicle?->plate_number ?? '—' }}</td>
            <td>{{ $r->driver?->name ?? '—' }}</td>
            <td>{{ ucfirst($r->status) }}</td>
            <td>{{ $r->actual_distance ? $r->actual_distance.' km' : ($r->total_distance ? $r->total_distance.' km' : '—') }}</td>
            <td>{{ $r->scheduled_at?->format('Y-m-d') ?? '—' }}</td>
            <td>{{ $r->completed_at?->format('Y-m-d H:i') ?? '—' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
<div class="footer">{{ config('app.name') }} — {{ __('Routes Report') }} — {{ now()->format('Y-m-d') }}</div>
</body></html>
