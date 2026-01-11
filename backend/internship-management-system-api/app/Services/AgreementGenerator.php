<?php

namespace App\Services;

use App\Models\Internship;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\TemplateProcessor;
use Symfony\Component\Process\Process;

class AgreementGenerator
{
    // presné bodky z originálu (použiť LEN keď chýba CELÝ riadok)
    private const DOTS_COMPANY_NAME_ADDRESS = '............................................................................';
    private const DOTS_COMPANY_REP_NAME     = '...........................................................................................';
    private const DOTS_STUDENT_LINE_1       = '.............................................................................................................................................................';
    private const DOTS_STUDENT_LINE_2       = '.............................................................................................................................................................';
    private const DOTS_STUDY_FIELD          = '................................................................................................';
    private const DOTS_PRACTICE_DEADLINE    = '......................................................';

    private const DOTS_GARANT_NAME          = '......................................................................';
    private const DOTS_GARANT_EMAIL         = '......................................................................';

    public function generatePdf(Internship $internship): string
    {
        $internship->load(['student', 'company', 'garant']);

        $student = $internship->student;
        $company = $internship->company;
        $garant  = $internship->garant;

        if (!$student || !$company || !$garant) {
            throw new \RuntimeException('Internship relations missing (student/company/garant).');
        }

        $templatePath = storage_path('app/templates/Dohoda_o_odbornej_praxi_template.docx');
        if (!file_exists($templatePath)) {
            throw new \RuntimeException("Template not found: {$templatePath}");
        }

        $processor = new TemplateProcessor($templatePath);

        // ---------- helpers ----------
        $s = static function ($v): string {
            return trim((string)($v ?? ''));
        };

        $fullName = static function ($u) use ($s): string {
            return trim($s($u->first_name ?? '') . ' ' . $s($u->last_name ?? ''));
        };

        $buildAddress = static function ($u) use ($s): string {
            $street = $s($u->street ?? '');
            $hn     = $s($u->house_number ?? '');
            $city   = $s($u->city ?? '');
            $psc    = $s($u->postal_code ?? '');

            $line1 = trim($street . ($hn !== '' ? ' ' . $hn : ''));
            $line2 = trim(($psc !== '' ? $psc . ' ' : '') . $city);

            if ($line1 !== '' && $line2 !== '') return $line1 . ', ' . $line2;
            if ($line1 !== '') return $line1;
            return $line2;
        };

        // ---------- STUDENT ----------
        $studentFullName = $s($fullName($student));
        $studentAddress  = $s($buildAddress($student));

        $studentEmail = $s($student->student_email ?? '') !== ''
            ? $s($student->student_email)
            : $s($student->email ?? '');

        $studentPhone = $s($student->phone ?? '');

        $studyField = $s($student->study_field ?? '');

        // --- riadok 1: meno + adresa ---
        // bodky len keď chýba OBOJE
        if ($studentFullName === '' && $studentAddress === '') {
            $processor->setValue('student_full_name', self::DOTS_STUDENT_LINE_1);
            $processor->setValue('student_address', '');
        } else {
            // ak adresa existuje, pridaj prefix ", " (len vtedy)
            $addrWithPrefix = $studentAddress !== '' ? ', ' . $studentAddress : '';
            $processor->setValue('student_full_name', $studentFullName);
            $processor->setValue('student_address', $addrWithPrefix);
        }

        // --- riadok 2: email + phone ---
        // bodky len keď chýba OBOJE
        if ($studentEmail === '' && $studentPhone === '') {
            $processor->setValue('student_email', self::DOTS_STUDENT_LINE_2);
            $processor->setValue('student_phone', '');
        } else {
            // ak phone existuje, pridaj prefix " , " (len vtedy)
            $phoneWithPrefix = $studentPhone !== '' ? ' , ' . $studentPhone : '';
            $processor->setValue('student_email', $studentEmail);
            $processor->setValue('student_phone', $phoneWithPrefix);
        }

        // study field: v template nechaj úvodzovky, do premennej dávaj len text alebo bodky
        $processor->setValue('study_field', $studyField !== '' ? $studyField : self::DOTS_STUDY_FIELD);

        // ---------- COMPANY ----------
        $companyName = $s($company->company_name ?? $company->name ?? '');
        $companyAddr = $s($buildAddress($company));

        $companyNameAddress = trim($companyName);
        if ($companyAddr !== '') {
            $companyNameAddress = trim($companyNameAddress . ', ' . $companyAddr);
        }

        $companyRepName = $s($company->contact_person_name ?? '');

        $processor->setValue(
            'company_name_address',
            $companyNameAddress !== '' ? $companyNameAddress : self::DOTS_COMPANY_NAME_ADDRESS
        );

        $processor->setValue(
            'company_rep_name',
            $companyRepName !== '' ? $companyRepName : self::DOTS_COMPANY_REP_NAME
        );

        // ---------- GARANT ----------
        $garantFullName = $s($fullName($garant));
        $garantEmail    = $s($garant->email ?? '');

        $processor->setValue('garant_full_name', $garantFullName !== '' ? $garantFullName : self::DOTS_GARANT_NAME);
        $processor->setValue('garant_email',     $garantEmail    !== '' ? $garantEmail    : self::DOTS_GARANT_EMAIL);

        // ---------- PRACTICE DEADLINE ----------
        $deadline = $internship->end_date?->format('d.m.Y') ?? '';
        $deadline = $s($deadline);

        $processor->setValue('practice_deadline', $deadline !== '' ? $deadline : self::DOTS_PRACTICE_DEADLINE);

        // ---------- TMP + LibreOffice convert ----------
        $tmpDir     = storage_path('app/tmp');
        $profileDir = $tmpDir . '/lo-profile';

        if (!is_dir($tmpDir)) {
            @mkdir($tmpDir, 0775, true);
        }
        if (!is_dir($profileDir)) {
            @mkdir($profileDir, 0700, true);
        }

        $docxAbs = $tmpDir . "/dohoda_{$internship->id}.docx";
        $processor->saveAs($docxAbs);

        if (!file_exists($docxAbs)) {
            throw new \RuntimeException("DOCX was not created: {$docxAbs}");
        }

        $process = new Process([
            'soffice',
            '--headless',
            '--nologo',
            '--nofirststartwizard',
            '-env:UserInstallation=file://' . $profileDir,
            '--convert-to', 'pdf',
            '--outdir', $tmpDir,
            $docxAbs,
        ]);

        $process->setTimeout(60);
        $process->setEnv([
            'HOME'   => $tmpDir,
            'TMPDIR' => $tmpDir,
        ]);

        $process->run();

        if (!$process->isSuccessful()) {
            throw new \RuntimeException(
                "LibreOffice convert failed (exit={$process->getExitCode()}):\n" .
                $process->getErrorOutput() . "\n" . $process->getOutput()
            );
        }

        $pdfAbsTmp = $tmpDir . "/dohoda_{$internship->id}.pdf";
        if (!file_exists($pdfAbsTmp)) {
            throw new \RuntimeException("PDF not found after conversion: {$pdfAbsTmp}");
        }

        $relativePath = "agreements/dohoda_{$internship->id}.pdf";
        Storage::disk('public')->put($relativePath, file_get_contents($pdfAbsTmp));

        @unlink($docxAbs);
        @unlink($pdfAbsTmp);

        return $relativePath;
    }
}
