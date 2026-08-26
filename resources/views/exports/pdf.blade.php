<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
            color: #1f2937;
        }
        h1 {
            font-size: 16px;
            margin: 0 0 2px;
        }
        .meta {
            color: #6b7280;
            font-size: 9px;
            margin-bottom: 14px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #e5e7eb;
            padding: 5px 7px;
            text-align: left;
            vertical-align: top;
        }
        thead th {
            background: #f3f4f6;
            font-size: 9px;
            text-transform: uppercase;
            color: #6b7280;
        }
        tbody tr:nth-child(even) {
            background: #fafafa;
        }
    </style>
</head>
<body>
    <h1>{{ $title }}</h1>
    <div class="meta">Exported {{ now()->format('M j, Y g:ia') }} &middot; {{ count($rows) }} {{ Str::plural('record', count($rows)) }}</div>

    <table>
        <thead>
            <tr>
                @foreach ($headers as $header)
                    <th>{{ $header }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr>
                    @foreach ($row as $cell)
                        <td>{{ $cell }}</td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($headers) }}">No records.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
