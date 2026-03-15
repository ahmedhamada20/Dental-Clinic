<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $report['title'] ?? ucfirst(str_replace('_', ' ', $reportType)) . ' Report' }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1f2937; }
        h1 { font-size: 20px; margin-bottom: 6px; }
        h2 { font-size: 14px; margin: 18px 0 8px; }
        .meta { margin-bottom: 14px; color: #4b5563; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        th, td { border: 1px solid #d1d5db; padding: 6px 8px; text-align: left; vertical-align: top; }
        th { background: #f3f4f6; }
        .muted { color: #6b7280; }
    </style>
</head>
<body>
    <h1>{{ $report['title'] ?? ucfirst(str_replace('_', ' ', $reportType)) . ' Report' }}</h1>
    <div class="meta">
        Generated: {{ now()->format('Y-m-d H:i:s') }}
    </div>

    <h2>Filters</h2>
    <table>
        <thead>
            <tr>
                <th>Filter</th>
                <th>Value</th>
            </tr>
        </thead>
        <tbody>
            @forelse(($report['filters'] ?? []) as $key => $value)
                <tr>
                    <td>{{ str($key)->replace('_', ' ')->title() }}</td>
                    <td>{{ is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : ($value === null || $value === '' ? '—' : $value) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="2" class="muted">No filters applied.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <h2>Summary</h2>
    <table>
        <thead>
            <tr>
                <th>Metric</th>
                <th>Value</th>
            </tr>
        </thead>
        <tbody>
            @forelse(($report['summary'] ?? []) as $key => $value)
                <tr>
                    <td>{{ str($key)->replace('_', ' ')->title() }}</td>
                    <td>{{ is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : $value }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="2" class="muted">No summary data available.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <h2>Rows</h2>
    @php($rows = $report['rows'] ?? [])
    @php($headings = array_keys($rows[0] ?? []))
    <table>
        <thead>
            <tr>
                @forelse($headings as $heading)
                    <th>{{ str($heading)->replace('_', ' ')->title() }}</th>
                @empty
                    <th>Message</th>
                @endforelse
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
                <tr>
                    @foreach($headings as $heading)
                        <td>{{ is_array($row[$heading] ?? null) ? json_encode($row[$heading], JSON_UNESCAPED_UNICODE) : ($row[$heading] ?? '—') }}</td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td class="muted">No rows available.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if(!empty($report['analytics']))
        <h2>Analytics</h2>
        <table>
            <thead>
                <tr>
                    <th>Key</th>
                    <th>Value</th>
                </tr>
            </thead>
            <tbody>
                @foreach($report['analytics'] as $key => $value)
                    <tr>
                        <td>{{ str($key)->replace('_', ' ')->title() }}</td>
                        <td>{{ is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : $value }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</body>
</html>

