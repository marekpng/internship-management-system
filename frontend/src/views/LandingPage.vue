<template>
 <div class="landing">
  <header class="topbar">
    <div class="container nav-row">
      <div class="brand">
        <div class="brand-badge">FPV</div>
        Praxový systém
      </div>

      <nav class="nav-links">
        <a href="#o-predmete">O predmete</a>
        <a href="#ako-funguje">Ako to funguje</a>
        <a href="#vyhody">Výhody</a>
        <a href="#faq">FAQ</a>
      </nav>

      <div class="nav-cta">
        <a class="btn" href="/login">Prihlásiť sa</a>
        <a class="btn btn-primary" href="/register">Registrovať sa</a>
      </div>
    </div>
  </header>

  <section class="hero">
    <div class="container hero-grid">
      <div>
        <span class="chip">Predmet: Odborná prax</span>
        <h1>Jednoduché vedenie praxí pre učiteľov a žiakov</h1>
        <p class="lead">Zadávaj prax, schvaľuj etapy a maj prehľad o hodnoteniach — všetko na jednom mieste.</p>
        <div class="hero-actions">
          <a class="btn btn-primary" href="/register">Začať – Registrácia</a>
          <a class="btn" href="/login">Mám účet – Prihlásiť sa</a>
        </div>
        <p class="subnote">Rýchla registrácia • Bezplatné pre študentov</p>
      </div>

      <div class="preview-grid">
        <div class="card">
          <div style="display:flex;justify-content:space-between;align-items:center;gap:8px">
            <h3>Informačný prehľad praxe</h3>
            <span class="badge">Dashboard</span>
          </div>
          <p>Priebežný stav, miesto praxe a hodnotenia na prvý pohľad.</p>
        </div>
        <div class="card">
          <h3>Pridanie novej praxe</h3>
          <ul>
            <li>Žiak vyplní miesto a termín</li>
            <li>Učiteľ schváli a priradí mentora</li>
            <li>Automatické generovanie záznamov etáp</li>
          </ul>
        </div>
        <div class="card">
          <h3>Moja prax</h3>
          <p>Detailné etapy s hodnotením a exportom PDF.</p>
        </div>
      </div>
    </div>
  </section>

  <section id="o-predmete">
    <div class="container">
      <h2 class="section-title">O predmete</h2>
      <p class="section-text">
        Cieľom predmetu je rozvíjať praktické zručnosti študentov. Aplikácia zjednodušuje komunikáciu medzi učiteľom a žiakom a ukladá dáta do SQL cez Laravel.
      </p>
      <div class="grid-3">
        <div class="feature"><h3>Pre žiakov</h3><p>Rýchle zadanie praxe, denník etáp, hodnotenia.</p></div>
        <div class="feature"><h3>Pre učiteľov</h3><p>Schvaľovanie, dohľad nad priebehom, reporty.</p></div>
        <div class="feature"><h3>Pre školu</h3><p>Exporty a auditovateľnosť.</p></div>
      </div>
    </div>
  </section>

  <section id="ako-funguje" class="section-alt">
    <div class="container">
      <h2 class="section-title">Ako to funguje</h2>
      <div class="grid-3">
        <div class="feature"><span class="badge">Krok 1</span><h3>Registrácia / Prihlásenie</h3><p>Trasy /register a /login (alebo Laravel).</p></div>
        <div class="feature"><span class="badge">Krok 2</span><h3>Zadanie praxe</h3><p>Ukladanie do SQL (practices, stages, evaluations).</p></div>
        <div class="feature"><span class="badge">Krok 3</span><h3>Schválenie a hodnotenie</h3><p>Učiteľ potvrdí prax a priradí mentora.</p></div>
      </div>
    </div>
  </section>

  <section id="vyhody">
    <div class="container">
      <h2 class="section-title">Výhody</h2>
      <div class="benefits">
        <div class="benefit">Jednoduché UI bez rušivých prvkov.</div>
        <div class="benefit">Jasné CTA: Registrácia a Prihlásenie.</div>
        <div class="benefit">Responzívny dizajn.</div>
        <div class="benefit">Pripravené pre roly učiteľ/žiak.</div>
      </div>
      <div class="hero-actions" style="margin-top:24px">
        <a class="btn btn-primary" href="/register">Registruj sa</a>
        <a class="btn" href="/login">Prihlás sa</a>
      </div>
    </div>
  </section>

  <section id="faq" class="section-alt">
    <div class="container">
      <h2 class="section-title">Časté otázky</h2>
      <details><summary>Je stránka napojená na Laravel autentifikáciu?</summary><p>Áno, tlačidlá vedú na /login a /register.</p></details>
      <details><summary>Ako sú dáta uložené?</summary><p>Do SQL (MySQL/PostgreSQL) cez Laravel Eloquent.</p></details>
      <details><summary>Je možné exportovať PDF?</summary><p>Áno, v rámci sekcie „Moja prax“.</p></details>
    </div>
  </section>

  <footer>
    <div class="footer-row">
      <p>© 2025 FPV Praxový systém</p>
      <div class="footer-links">
        <a href="#o-predmete">O predmete</a>
        <a href="#vyhody">Výhody</a>
        <a href="#faq">FAQ</a>
      </div>
    </div>
  </footer>
</div>

</template>

<script setup>
import '@/assets/landing.css'

</script>

<style scoped>
/* Voliteľné drobnosti */
</style>