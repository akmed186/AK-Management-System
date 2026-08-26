<?php

namespace App\Support;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

class Exporter
{
    /**
     * Stream the given rows as a downloadable CSV file (opens directly in Excel).
     */
    public static function csv(string $filename, array $headers, iterable $rows): StreamedResponse
    {
        return response()->streamDownload(function () use ($headers, $rows) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $headers);

            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    /**
     * Render the given rows as a downloadable PDF table.
     */
    public static function pdf(string $filename, string $title, array $headers, iterable $rows)
    {
        $rows = $rows instanceof Collection ? $rows : collect($rows);

        $pdf = Pdf::loadView('exports.pdf', [
            'title' => $title,
            'headers' => $headers,
            'rows' => $rows,
        ])->setPaper('a4', 'landscape');

        return $pdf->download($filename);
    }
}
