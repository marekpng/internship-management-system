<template>
  <div class="landing">
    <HeaderComponent />

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
              <li>Učiteľ schváli pridanie praxe </li>
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
          Cieľom predmetu je rozvíjať praktické zručnosti študentov. Aplikácia zjednodušuje komunikáciu medzi učiteľom a žiakom.
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
          <div class="feature"><span class="badge">Krok 1</span><h3>Registrácia / Prihlásenie</h3><p>Trasy /register a /login</p></div>
          <div class="feature"><span class="badge">Krok 2</span><h3>Zadanie praxe</h3><p>Pridanie názov praxe</p></div>
          <div class="feature"><span class="badge">Krok 3</span><h3>Schválenie a hodnotenie</h3><p>Učiteľ potvrdí prax</p></div>
        </div>
      </div>
    </section>

    <section id="vyhody">
      <div class="container">
        <h2 class="section-title">Výhody</h2>
        <div class="benefits">
          <div class="benefit">Priehľadné rozhranie</div>
          <div class="benefit">Jednoduchá manipulácia</div>
          <div class="benefit">Všetko na jednom mieste</div>
          <div class="benefit">Ľahká komunikácia medzi učitelom a žiakom</div>
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
        <details><summary>Je možné pridať firmu bez jej registrácie?</summary><p>Nie</p></details>
        <details><summary>Viem si v tomto prostredí nájsť prax?</summary><p>Nie</p></details>
        <details><summary>Je možné exportovať PDF?</summary><p>Áno, v rámci sekcie „Moja prax“.</p></details>
      </div>
    </section>

    <FooterComponent />
  </div>

</template>

<script setup>
// import '@/assets/landing.css'
import HeaderComponent from '@/components/HeaderComponent.vue'
import FooterComponent from '@/components/FooterComponent.vue'

</script>

<style scoped>
/* Voliteľné drobnosti */
</style>