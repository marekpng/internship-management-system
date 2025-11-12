<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <title>Dohoda o odbornej praxi</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; line-height: 1.5; margin: 40px; }
        h1 { text-align: center; color: #007b3e; }
        .section { margin-bottom: 15px; }
        strong { color: #222; }
    </style>
</head>
<body>
    <h1>Dohoda o odbornej praxi</h1>

    <div class="section">
        <strong>Študent:</strong> {{ $student->first_name }} {{ $student->last_name }}<br>
        <strong>Email:</strong> {{ $student->email }}
    </div>

    <div class="section">
        <strong>Firma:</strong> {{ $company->company_name ?? 'Neznáma firma' }}<br>
        <strong>Kontakt:</strong> {{ $company->contact_person_name ?? '' }} ({{ $company->contact_person_email ?? '' }})
    </div>

    <div class="section">
        <strong>Garant:</strong> {{ $garant->first_name ?? '' }} {{ $garant->last_name ?? '' }}
    </div>

    <div class="section">
        <strong>Začiatok praxe:</strong> {{ $internship->start_date }}<br>
        <strong>Koniec praxe:</strong> {{ $internship->end_date }}
    </div>

    <p style="margin-top: 30px; text-align:center;">
        Dokument bol automaticky vygenerovaný systémom Internship Management System.
    </p>
</body>
</html>
