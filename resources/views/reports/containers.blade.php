{{-- resources/views/reports/containers.blade.php --}}
<!DOCTYPE html>
<html dir="{{ app()->getLocale()==='ar'?'rtl':'ltr' }}" lang="{{ app()->getLocale() }}">
<head>
<meta charset="UTF-8">
<style>
    body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #2c3e50; margin: 0; padding: 20px; }
    .header { background: #2d8a4e; color: #fff; padding: 15px 20px; margin: -20px -20px 20px; display: flex; justify-content: space-between; align-items: center; }
    .header h1 { margin: 0; font-size: 16px; }
    .header .meta { font-size: 9px; opacity: .8; }
    .stats { display: flex; gap: 10px; margin-bottom: 15px; }
    .stat-box { flex: 1; background: #f0f7f4; border-radius: 6px; padding: 10px; text-align: center; border-top: 3px solid #2d8a4e; }
    .stat-box .val { font-size: 20px; font-weight: 800; color: #2d8a4e; }
    .stat-box .lbl { font-size: 8px; color: #888; }
    table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    th { background: #2d8a4e; color: #fff; padding: 6px 8px; font-size: 9px; text-align: left; }
    td { padding: 5px 8px; border-bottom: 1px solid #eee; font-size: 9px; }
    tr:nth-child(even) { background: #f9f9f9; }
    .badge { padding: 2px 6px; border-radius: 4px; font-size: 8px; font-weight: 700; }
    .badge-success { background: #d4edda; color: #155724; }
    .badge-danger  { background: #f8d7da; color: #721c24; }
    .badge-warning { background: #fff3cd; color: #856404; }
    .footer { margin-top: 20px; padding-top: 10px; border-top: 1px solid #eee; font-size: 8px; color: #aaa; text-align: center; }
    .fill-bar-wrap { width: 60px; height: 6px; background: #eee; border-radius: 3px; display: inline-block; vertical-align: middle; }
    .fill-bar { height: 100%; border-radius: 3px; }
</style>
</head>
<body>
<div class="header">
    <div><h1>🗑 {{ __('Containers Report') }}</h1><div class="meta">{{ config('app.name') }}</div></div>
    <div class="meta" style="text-align:right;">{{ __('Generated') }}: {{ now()->format('Y-m-d H:i') }}</div>
</div>
<div class="stats">
    <div class="stat-box"><div class="val">{{ $stats['total'] }}</div><div class="lbl">{{ __('Total') }}</div></div>
    <div class="stat-box"><div class="val">{{ $stats['active'] }}</div><div class="lbl">{{ __('Active') }}</div></div>
    <div class="stat-box"><div class="val">{{ $stats['needs_emptying'] }}</div><div class="lbl">{{ __('Needs Emptying') }}</div></div>
    <div class="stat-box"><div class="val">{{ $stats['avg_fill'] }}%</div><div class="lbl">{{ __('Avg Fill') }}</div></div>
</div>
<table>
    <thead><tr>
        <th>#</th><th>{{ __('Code') }}</th><th>{{ __('Name') }}</th><th>{{ __('Type') }}</th>
        <th>{{ __('Zone') }}</th><th>{{ __('Capacity') }}</th><th>{{ __('Fill Level') }}</th>
        <th>{{ __('Status') }}</th><th>{{ __('Last Emptied') }}</th>
    </tr></thead>
    <tbody>
        @foreach($containers as $i => $c)
        <tr>
            <td>{{ $i+1 }}</td>
            <td>{{ $c->code }}</td>
            <td>{{ $c->name }}</td>
            <td>{{ ucfirst($c->type) }}</td>
            <td>{{ $c->zone ?? '—' }}</td>
            <td>{{ number_format($c->capacity) }}L</td>
            <td>
                <div class="fill-bar-wrap"><div class="fill-bar" style="width:{{ $c->fill_level }}%;background:{{ $c->fill_level>=90?'#dc3545':($c->fill_level>=70?'#ffc107':'#2d8a4e') }};"></div></div>
                {{ $c->fill_level }}%
            </td>
            <td><span class="badge badge-{{ $c->status==='active'?'success':($c->status==='full'?'danger':'warning') }}">{{ ucfirst($c->status) }}</span></td>
            <td>{{ $c->last_emptied_at?->format('Y-m-d') ?? '—' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
<div class="footer">{{ config('app.name') }} — {{ __('Containers Report') }} — {{ now()->format('Y-m-d') }} — {{ __('Page') }} 1</div>
</body></html>
