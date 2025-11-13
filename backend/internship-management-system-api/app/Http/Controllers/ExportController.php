<?php

namespace App\Http\Controllers;

use App\Http\Requests\ExportInternshipsRequest;
use Illuminate\Support\Facades\DB;

class ExportController extends Controller
{
    public function exportCsv(ExportInternshipsRequest $request)
    {
        $q = DB::table('internships as i')
            ->join('users as s', 's.id', '=', 'i.student_id')
            ->join('users as c', 'c.id', '=', 'i.company_id')
            ->leftJoin('users as g', 'g.id', '=', 'i.garant_id')
            ->selectRaw("
                CONCAT(COALESCE(s.first_name,''),' ',COALESCE(s.last_name,''))            as student_name,
                NULL                                                                          as study_program,   -- TODO doplniť podľa tvojej DB
                NULL                                                                          as class_name,      -- TODO doplniť podľa tvojej DB
                COALESCE(c.company_name, CONCAT(COALESCE(c.first_name,''),' ',COALESCE(c.last_name,''))) as company_name,
                i.start_date, i.end_date,
                NULL                                                                          as worked_hours,    -- TODO doplniť podľa tvojej DB
                i.status,
                i.year                                                                        as academic_year,
                i.semester,
                CONCAT(COALESCE(g.first_name,''),' ',COALESCE(g.last_name,''))               as supervisor_name
            ");

        // filtre
        if ($v = $request->input('company')) {
            $q->where(function ($w) use ($v) {
                $w->where('c.company_name', 'like', "%{$v}%")
                    ->orWhere(DB::raw("CONCAT(c.first_name,' ',c.last_name)"), 'like', "%{$v}%");
            });
        }
        if ($v = $request->input('status'))
            $q->where('i.status', $v);
        if ($v = $request->input('year'))
            $q->where('i.year', $v);
        if ($v = $request->input('semester'))
            $q->where('i.semester', $v);
        if ($v = $request->input('student'))
            $q->where(DB::raw("CONCAT(s.first_name,' ',s.last_name)"), 'like', "%{$v}%");
        if ($v = $request->input('supervisor'))
            $q->where(DB::raw("CONCAT(g.first_name,' ',g.last_name)"), 'like', "%{$v}%");
        if ($v = $request->input('date_from'))
            $q->whereDate('i.start_date', '>=', $v);
        if ($v = $request->input('date_to'))
            $q->whereDate('i.end_date', '<=', $v);

        $filename = 'export_praxe_' . date('Ymd_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control' => 'no-store, no-cache',
        ];

        $columns = [
            'Meno a priezvisko študenta',
            'Študijný program',
            'Trieda / ročník',
            'Názov firmy',
            'Dátum začiatku praxe',
            'Dátum konca praxe',
            'Počet odpracovaných hodín',
            'Stav praxe',
            'Akademický rok',
            'Semester',
            'Meno školiteľa',
        ];

        $separator = ';'; // kvôli Excelu na SK

        return response()->stream(function () use ($q, $columns, $separator) {
            $out = fopen('php://output', 'w');

            // UTF-8 BOM pre Excel
            echo "\xEF\xBB\xBF";

            // hlavička
            fputcsv($out, $columns, $separator);

            // dáta (pamäťovo bezpečné)
            $q->orderBy('s.last_name')->chunk(1000, function ($rows) use ($out, $separator) {
                foreach ($rows as $r) {
                    fputcsv($out, [
                        $r->student_name,
                        $r->study_program,
                        $r->class_name,
                        $r->company_name,
                        $r->start_date,
                        $r->end_date,
                        $r->worked_hours,
                        $r->status,
                        $r->academic_year,
                        $r->semester,
                        $r->supervisor_name,
                    ], $separator);
                }
            });

            fclose($out);
        }, 200, $headers);
    }
}
