<?php

namespace App\Http\Controllers;

use App\Models\Internship;
use App\Models\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class GarantExportController extends Controller
{
    public function preview(Request $request)
    {
        $query = $this->filterQuery($request);
        $internships = $query->limit(200)->get();

        $data = $internships->map(fn($i) => [
            'student_name' => $this->fullName($i->student),
            'study_program' => $i->student?->study_field ?? '',
            'class' => '',
            'company' => $i->company?->company_name ?? '',
            'start_date' => $i->start_date,
            'end_date' => $i->end_date,
            'hours' => '',
            'status' => $i->status,
            'academic_year' => $i->year,
            'semester' => $i->semester,
            'supervisor' => $this->fullName($i->garant),
        ]);

        return response()->json(['data' => $data]);
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $query = $this->filterQuery($request);
        $internships = $query->get();

        $fileName = 'export_praxe_' . now()->format('Ymd_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$fileName\"",
        ];

        $columns = [
            'Meno a priezvisko študenta',
            'Študijný program',
            'Trieda / ročník',
            'Firma',
            'Dátum začiatku praxe',
            'Dátum konca praxe',
            'Počet odpracovaných hodín',
            'Stav praxe',
            'Akademický rok',
            'Semester',
            'Meno školiteľa'
        ];

        $callback = function () use ($internships, $columns) {
            $file = fopen('php://output', 'w');

            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($file, $columns, ';');

            foreach ($internships as $i) {
                fputcsv($file, [
                    $this->fullName($i->student),
                    $i->student?->study_field ?? '',
                    '',
                    $i->company?->company_name ?? '',
                    $i->start_date,
                    $i->end_date,
                    '',
                    $i->status,
                    $i->year,
                    $i->semester,
                    $this->fullName($i->garant),
                ], ';');
            }

            fclose($file);
        };

        return response()->streamDownload($callback, $fileName, $headers);
    }

    private function filterQuery(Request $request)
    {
        $query = Internship::query()
            ->with(['student', 'company', 'garant']);

        if ($request->filled('academic_year')) {
            $query->where('year', $request->academic_year);
        }

        if ($request->filled('semester')) {
            $query->where('semester', $request->semester);
        }

        if ($request->filled('status')) {
            $query->whereIn('status', $request->status);
        }

        return $query;
    }

    private function fullName(?User $u): string
    {
        if (!$u)
            return '';
        return trim(($u->first_name ?? '') . ' ' . ($u->last_name ?? ''));
    }
}
