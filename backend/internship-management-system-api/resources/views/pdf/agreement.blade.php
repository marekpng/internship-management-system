<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <title>Dohoda o odbornej praxi študenta</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; line-height: 1.35; margin: 34px; color: #111; }
        .center { text-align: center; }
        .right { text-align: right; }
        h1 { font-size: 16px; margin: 0 0 8px; }
        h2 { font-size: 13px; margin: 14px 0 6px; }
        .muted { color: #333; }
        .block { margin: 10px 0; }
        .line { display:inline-block; border-bottom: 1px dotted #222; min-width: 220px; height: 14px; vertical-align: bottom; }
        .line-wide { min-width: 380px; }
        .line-xxwide { min-width: 520px; }
        .indent { margin-left: 18px; }
        .small { font-size: 11px; }
        .signature-row { margin-top: 22px; }
        .sig { display:inline-block; width: 48%; vertical-align: top; }
        .sig .place { margin-bottom: 22px; }
        .sig .signline { border-top: 1px solid #111; margin-top: 26px; padding-top: 4px; }
        .mt8 { margin-top: 8px; }
        .mt12 { margin-top: 12px; }
        .mt16 { margin-top: 16px; }
        ol { margin: 6px 0 6px 18px; padding: 0; }
        li { margin: 4px 0; }
    </style>
</head>
<body>
@php
    // Bezpečné skladanie údajov
    $studentName = trim(($student->first_name ?? '').' '.($student->last_name ?? ''));
    $studentEmail = $student->student_email ?? $student->email ?? '';
    $studentPhone = $student->phone ?? '';
    $studentAddress = trim(
        trim(($student->street ?? '').' '.($student->house_number ?? '')) .
        (empty($student->city) ? '' : ', '.$student->city) .
        (empty($student->postal_code) ? '' : ', '.$student->postal_code)
    );

    $companyName = $company->company_name ?? $company->name ?? '';
    $companyContactName = $company->contact_person_name ?? '';
    $companyContactEmail = $company->contact_person_email ?? ($company->email ?? '');

    $garantName = trim(($garant->first_name ?? '').' '.($garant->last_name ?? ''));

    $studyProgram = $student->study_field ?? '';

    $endDate = $internship->end_date ?? null;
    $endDateStr = $endDate ? \Carbon\Carbon::parse($endDate)->format('d.m.Y') : '';
    $todayStr = \Carbon\Carbon::now()->format('d.m.Y');
@endphp

<div class="center">
    <h1>Dohoda o odbornej praxi študenta</h1>
    <div class="muted small">uzatvorená v zmysle § 51 Občianskeho zákonníka a Zákona č. 131/2002 Z.z. o vysokých školách</div>
</div>

<div class="block mt16">
    <strong>Univerzita Konštantína Filozofa v Nitre</strong><br>
    Fakulta prírodných vied a informatiky<br>
    Trieda A. Hlinku 1, 949 01 Nitra<br>
    v zastúpení Dr. h. c. prof. RNDr. František Petrovič, PhD., MBA – dekan fakulty<br>
    fpetrovic@ukf.sk<br>
    tel. 037/6408555
</div>

<div class="block mt12">
    <strong>Poskytovateľ odbornej praxe (organizácia, resp. inštitúcia)</strong><br>
    Plný názov a adresa:
    @if(!empty($companyName))
        <span class="line line-xxwide">{{ $companyName }}</span>
    @else
        <span class="line line-xxwide"></span>
    @endif
    <br>
    v zastúpení <span class="line line-wide">{{ $companyContactName }}</span> (pozícia) <span class="line"></span><br>
    kontakt (email): <span class="line line-wide">{{ $companyContactEmail }}</span>
</div>

<div class="block mt12">
    <strong>Meno a priezvisko, adresa trvalého bydliska a kontakt študenta FPV UKF v Nitre</strong><br>
    <span class="line line-xxwide">{{ $studentName }}</span><br>
    <span class="line line-xxwide">{{ $studentAddress }}</span><br>
    kontakt (email/telefón): <span class="line line-wide">{{ trim($studentEmail . (empty($studentPhone) ? '' : ' / '.$studentPhone)) }}</span>
</div>

<div class="block mt12">
    <strong>Študijný program</strong>
    <span class="line line-xxwide">{{ $studyProgram }}</span>
</div>

<div class="block mt12">
    uzatvárajú túto dohodu o odbornej praxi študenta.
</div>

<h2 class="center mt16">I. Predmet dohody</h2>
<div class="block">
    Predmetom tejto dohody je vykonanie odbornej praxe študenta v rozsahu <strong>80 hodín</strong>,
    v termíne najneskôr do <span class="line">{{ $endDateStr }}</span> bezodplatne.
</div>

<h2 class="center mt16">II. Práva a povinnosti účastníkov dohody</h2>

<div class="block">
    <strong>1. Fakulta prírodných vied a informatiky Univerzity Konštantína Filozofa v Nitre:</strong><br><br>

    Poverí svojho zamestnanca:
    <span class="line line-wide">{{ $garantName }}</span><br>
    kontakt:
    <span class="line line-wide"></span><br>
    (ďalej garant odbornej praxe) garanciou odbornej praxe.
</div>

<div class="block indent">
    <strong>1.2 Prostredníctvom garanta odbornej praxe:</strong><br>
    a) poskytne študentovi:
    <div class="indent">
        - informácie o organizácii praxe, o podmienkach dojednania dohody o odbornej praxi, o obsahovom zameraní odbornej praxe a o požiadavkách na obsahovú náplň správy z odbornej praxe,<br>
        - návrh dohody o odbornej praxi študenta,
    </div>
    b) rozhodne o udelení hodnotenia „ABS“ (absolvoval) študentovi na základe „Potvrdenia o absolvovaní odbornej praxe“, vydaného poskytovateľom odbornej praxe a na základe študentom vypracovanej správy o odbornej praxe,<br>
    c) spravuje vyplnenú a účastníkmi podpísanú dohodu o odbornej praxe.
</div>

<div class="block mt12">
    <strong>2. Poskytovateľ odbornej praxe:</strong>
    <ol>
        <li>poverí svojho zamestnanca (tútor - zodpovedný za odbornú prax v organizácii)
            <span class="line line-wide"></span>, ktorý bude dohliadať na dodržiavanie dohody o odbornej praxe, plnenie obsahovej náplne odbornej praxe a bude nápomocný pri získavaní potrebných údajov pre vypracovanie správy z odbornej praxe,</li>
        <li>na začiatku praxe vykoná poučenie o bezpečnosti a ochrane zdravia pri práci v zmysle platných predpisov,</li>
        <li>vzniknuté organizačné problémy súvisiace s plnením dohody rieši spolu s garantom odbornej praxe,</li>
        <li>po ukončení odbornej praxe vydá študentovi „Potvrdenie o absolvovaní odbornej praxe“, ktoré obsahuje stručné hodnotenie študenta a je jedným z predpokladov na udelenie hodnotenia „absolvoval“,</li>
        <li>umožní garantovi odbornej praxe a garantovi študijného predmetu kontrolu študentom plnených úloh.</li>
    </ol>
</div>

<div class="block mt12">
    <strong>3. Študent FPVaI UKF v Nitre:</strong>
    <ol>
        <li>osobne zabezpečí podpísanie tejto dohody o odbornej praxe študenta,</li>
        <li>počas odbornej praxe spracováva písomnú informáciu o jej priebehu a zahrnie ju do: „Správy z odbornej praxe“, ktorú predloží garantovi odbornej praxe ako podklad pre udelenie hodnotenia,</li>
        <li>okamžite, bez zbytočného odkladu informuje garanta odbornej praxe o problémoch, ktoré bránia plneniu odbornej praxe.</li>
    </ol>
</div>

<h2 class="center mt16">III. Všeobecné a záverečné ustanovenia</h2>
<div class="block">
    Dohoda sa uzatvára na dobu určitú. Dohoda nadobúda platnosť a účinnosť dňom podpísania obidvomi zmluvnými stranami.
    Obsah dohody sa môže meniť písomne len po súhlase jej zmluvných strán.<br><br>
    Dohoda sa uzatvára v 3 vyhotoveniach, každá zmluvná strana obdrží jedno vyhotovenie dohody.
</div>

<div class="signature-row">
    <div class="sig">
        <div class="place">V Nitre, dňa <span class="line">{{ $todayStr }}</span></div>
        <div class="signline">
            Dr. h. c. prof. RNDr. František Petrovič, PhD., MBA<br>
            <span class="small">dekan FPVaI UKF v Nitre</span>
        </div>
    </div>

    <div class="sig" style="float:right;">
        <div class="place">V <span class="line"></span>, dňa <span class="line">{{ $todayStr }}</span></div>
        <div class="signline">
            <span class="line line-wide"></span><br>
            <span class="small">meno a priezvisko – štatutárny zástupca pracoviska odb. praxe</span>
        </div>

        <div class="signline" style="margin-top: 18px;">
            <span class="line line-wide">{{ $studentName }}</span><br>
            <span class="small">meno a priezvisko študenta</span>
        </div>
    </div>
    <div style="clear:both;"></div>
</div>

</body>
</html>
