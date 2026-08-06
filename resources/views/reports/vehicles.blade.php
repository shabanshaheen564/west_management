{{-- resources/views/reports/vehicles.blade.php --}}
<!DOCTYPE html>
<html><head><meta charset="UTF-8">
<style>
body{font-family:'DejaVu Sans',sans-serif;font-size:10px;color:#2c3e50;margin:0;padding:20px;}
.header{background:#1a6b9a;color:#fff;padding:15px 20px;margin:-20px -20px 20px;}
.header h1{margin:0;font-size:16px;}
.stats{display:flex;gap:10px;margin-bottom:15px;}
.stat-box{flex:1;background:#e8f4fd;border-radius:6px;padding:10px;text-align:center;border-top:3px solid #1a6b9a;}
.stat-box .val{font-size:20px;font-weight:800;color:#1a6b9a;}
.stat-box .lbl{font-size:8px;color:#888;}
table{width:100%;border-collapse:collapse;}
th{background:#1a6b9a;color:#fff;padding:6px 8px;font-size:9px;text-align:left;}
td{padding:5px 8px;border-bottom:1px solid #eee;font-size:9px;}
tr:nth-child(even){background:#f9f9f9;}
.badge{padding:2px 6px;border-radius:4px;font-size:8px;font-weight:700;}
.footer{margin-top:20px;padding-top:10px;border-top:1px solid #eee;font-size:8px;color:#aaa;text-align:center;}
</style></head><body>
<div class="header"><h1>🚛 {{ __('Vehicles Report') }}</h1><small>{{ config('app.name') }} — {{ now()->format('Y-m-d H:i') }}</small></div>
<div class="stats">
    <div class="stat-box"><div class="val">{{ $stats['total'] }}</div><div class="lbl">{{ __('Total') }}</div></div>
    <div class="stat-box"><div class="val">{{ $stats['active'] }}</div><div class="lbl">{{ __('Active') }}</div></div>
    <div class="stat-box"><div class="val">{{ $stats['on_route'] }}</div><div class="lbl">{{ __('On Route') }}</div></div>
    <div class="stat-box"><div class="val">{{ $stats['maintenance'] }}</div><div class="lbl">{{ __('Maintenance') }}</div></div>
</div>
<table>
    <thead><tr>
        <th>#</th><th>{{ __('Plate') }}</th><th>{{ __('Brand/Model') }}</th><th>{{ __('Type') }}</th>
        <th>{{ __('Capacity') }}</th><th>{{ __('Driver') }}</th><th>{{ __('Status') }}</th>
        <th>{{ __('Next Maintenance') }}</th><th>{{ __('Insurance Expiry') }}</th>
    </tr></thead>
    <tbody>
        @foreach($vehicles as $i => $v)
        <tr>
            <td>{{ $i+1 }}</td>
            <td><strong>{{ $v->plate_number }}</strong></td>
            <td>{{ $v->brand }} {{ $v->model }} ({{ $v->year }})</td>
            <td>{{ ucfirst($v->type) }}</td>
            <td>{{ $v->capacity }} t</td>
            <td>{{ $v->driver?->name ?? '—' }}</td>
            <td><span class="badge" style="background:{{ $v->status==='active'?'#d4edda':($v->status==='maintenance'?'#fff3cd':'#f8d7da') }};color:#333;">{{ ucfirst($v->status) }}</span></td>
            <td>{{ $v->next_maintenance?->format('Y-m-d') ?? '—' }}</td>
            <td>{{ $v->insurance_expiry?->format('Y-m-d') ?? '—' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
<div class="footer">{{ config('app.name') }} — {{ __('Vehicles Report') }} — {{ now()->format('Y-m-d') }}</div>
</body></html>
