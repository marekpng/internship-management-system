<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Internship;
use App\Models\User;
use Carbon\Carbon;

class GarantExportController extends Controller
{

    private function getFilteredInternships(Request $request)
    {
        $query = Internship::with(['student', 'company', 'garant']);


        if ($request->filled('academic_year')) {
            $parts = explode('/', $request->academic_year);

            $year = (int) trim(end($parts));
            if ($year) {
                $query->where('year', $year);
            }
        }


        if ($request->filled('semester')) {
            $query->where('semester', $request->semester);
        }


        if ($request->filled('study_field')) {
            $query->whereHas('student', function ($q) use ($request) {
                $q->where('study_field', $request->study_field);
            });
        }


        if ($request->filled('status')) {
            $statuses = (array) $request->status;
            $query->whereIn('status', $statuses);
        }

        return $query->get();
    }


    public function studyFields(Request $request)
    {
        $fields = User::query()
            ->whereNotNull('study_field')
            ->where('study_field', '!=', '')
            ->distinct()
            ->orderBy('study_field')
            ->pluck('study_field')
            ->values();

        return response()->json([
            'data' => $fields,
        ]);
    }

    public function preview(Request $request)
    {
        $internships = $this->getFilteredInternships($request);

        $data = $internships->map(function ($i) {
            return [
                'student_name' => trim((optional($i->student)->first_name ?? '') . ' ' . (optional($i->student)->last_name ?? '')),
                'study_field' => optional($i->student)->study_field,
                'company' => optional($i->company)->company_name,

                'start_date' => $i->start_date ? Carbon::parse($i->start_date)->format('Y-m-d') : '',
                'end_date' => $i->end_date ? Carbon::parse($i->end_date)->format('Y-m-d') : '',

                'status' => $i->status,
                'academic_year' => $i->year,
                'semester' => $i->semester,
                'supervisor' => trim((optional($i->garant)->first_name ?? '') . ' ' . (optional($i->garant)->last_name ?? '')),
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
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename=export_praxe.csv',
        ];

        $callback = function () use ($internships) {
            $file = fopen('php://output', 'w');


            fwrite($file, "\xEF\xBB\xBF");


            fputcsv($file, [
                'Meno študenta',
                'Študijný odbor',
                'Firma',
                'Dátum začiatku',
                'Dátum konca',
                'Stav',
                'Akademický rok',
                'Semester',
                'Školiteľ',
            ], ';');

            foreach ($internships as $i) {
                $start = $i->start_date ? Carbon::parse($i->start_date)->format('Y-m-d') : '';
                $end = $i->end_date ? Carbon::parse($i->end_date)->format('Y-m-d') : '';

                fputcsv($file, [
                    trim((optional($i->student)->first_name ?? '') . ' ' . (optional($i->student)->last_name ?? '')),
                    optional($i->student)->study_field,
                    optional($i->company)->company_name,
                    $start,
                    $end,
                    $i->status,
                    $i->year,
                    $i->semester,
                    trim((optional($i->garant)->first_name ?? '') . ' ' . (optional($i->garant)->last_name ?? '')),
                ], ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
