<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Internship;

class GarantExportController extends Controller
{
    public function preview(Request $request)
    {
        $internships = Internship::with(['student', 'company', 'garant'])->get();

        $data = $internships->map(function ($i) {
            return [
                'student_name' => optional($i->student)->first_name . ' ' . optional($i->student)->last_name,
                'study_program' => '—',
                'class' => '—',
                'company' => optional($i->company)->company_name,
                'start_date' => $i->start_date,
                'end_date' => $i->end_date,
                'hours' => '—',
                'status' => $i->status,
                'academic_year' => $i->year,
                'semester' => $i->semester,
                'supervisor' => optional($i->garant)->first_name . ' ' . optional($i->garant)->last_name,
            ];
        });

        return response()->json([
            'data' => $data,
        ]);
    }

    public function exportCsv(Request $request)
    {
        $internships = Internship::with(['student', 'company', 'garant'])->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=export_praxe.csv',
        ];

        $callback = function () use ($internships) {
            $file = fopen('php://output', 'w');

            // HLAVIČKA
            fputcsv($file, [
                'Meno študenta',
                'Študijný program',
                'Trieda / ročník',
                'Firma',
                'Dátum začiatku',
                'Dátum konca',
                'Počet hodín',
                'Stav',
                'Akademický rok',
                'Semester',
                'Školiteľ',
            ], ';');

            foreach ($internships as $i) {
                fputcsv($file, [
                    optional($i->student)->first_name . ' ' . optional($i->student)->last_name,
                    '—',
                    '—',
                    optional($i->company)->company_name,
                    $i->start_date,
                    $i->end_date,
                    '—',
                    $i->status,
                    $i->year,
                    $i->semester,
                    optional($i->garant)->first_name . ' ' . optional($i->garant)->last_name,
                ], ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
