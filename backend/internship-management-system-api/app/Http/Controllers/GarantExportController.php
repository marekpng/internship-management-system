<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Internship;

class GarantExportController extends Controller
{
    /**
     * Spoločné filtrovanie pre preview aj CSV.
     */
    private function getFilteredInternships(Request $request)
    {
        $query = Internship::with(['student', 'company', 'garant']);

        // Akademický rok z frontendu: napr. "2024/2025"
        if ($request->filled('academic_year')) {
            $parts = explode('/', $request->academic_year);
            // predpoklad: v DB je uložený koncový rok (2025, 2026, ...)
            $year = (int) trim(end($parts));
            if ($year) {
                $query->where('year', $year);
            }
        }

        // Semester – vo frontende posielaš "Zimný" / "Letný"
        if ($request->filled('semester')) {
            $query->where('semester', $request->semester);
        }

        // Študijný program – ak ho máte naviazaný na študenta (users.study_program_id)
        if ($request->filled('study_program_id')) {
            $query->whereHas('student', function ($q) use ($request) {
                $q->where('study_program_id', $request->study_program_id);
            });
        }

        // Stav praxe – checkboxy, posielané ako pole status[]
        if ($request->filled('status')) {
            $statuses = (array) $request->status;
            $query->whereIn('status', $statuses);
        }

        return $query->get();
    }

    public function preview(Request $request)
    {
        $internships = $this->getFilteredInternships($request);

        $data = $internships->map(function ($i) {
            return [
                'student_name' => optional($i->student)->first_name . ' ' . optional($i->student)->last_name,
                'study_program' => '—', // doplníš podľa vašej DB
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
        $internships = $this->getFilteredInternships($request);

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
                    '—', // študijný program – doplníš neskôr
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
