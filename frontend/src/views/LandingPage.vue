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
              <li>Učiteľ schváli pridanie praxe</li>
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

        <div class="role-cards">
          <button class="role-card" :class="{ active: activeRole === 'student' }" @click="activeRole = 'student'">
            <h3>Pre žiakov</h3>
            <p>Rýchle zadanie praxe, denník etáp, hodnotenia.</p>
          </button>

          <button class="role-card" :class="{ active: activeRole === 'teacher' }" @click="activeRole = 'teacher'">
            <h3>Pre učiteľov</h3>
            <p>Schvaľovanie, dohľad nad priebehom, reporty.</p>
          </button>

          <button class="role-card" :class="{ active: activeRole === 'company' }" @click="activeRole = 'company'">
            <h3>Pre firmy</h3>
            <p>Prehľad študentov, exporty a auditovateľnosť.</p>
          </button>
        </div>

        <div class="role-detail">
          <div v-if="activeRole === 'student'" class="role-detail-grid">
            <div>
              <h3>Rozhranie pre študentov</h3>
              <ul>
                <li>Jednoduché zadanie miesta praxe</li>
                <li>Prehľad etáp a hodnotení</li>
                <li>Export záznamov do PDF</li>
              </ul>
            </div>
            <img src="@/assets/ziak.png" alt="Študentské rozhranie" />
          </div>

          <div v-if="activeRole === 'teacher'" class="role-detail-grid">
            <div>
              <h3>Rozhranie pre učiteľov (garantov)</h3>
              <ul>
                <li>Schvaľovanie praxí a etáp</li>
                <li>Dohľad nad priebehom praxe</li>
                <li>Reporty a hodnotenia</li>
              </ul>
            </div>
            <img src="@/assets/garant.png" alt="Rozhranie pre učiteľov" />
          </div>

          <div v-if="activeRole === 'company'" class="role-detail-grid">
            <div>
              <h3>Rozhranie pre firmy</h3>
              <ul>
                <li>Prehľad pridelených študentov</li>
                <li>Hodnotenie praxe</li>
                <li>Exporty a auditná stopa</li>
              </ul>
            </div>
            <img src="@/assets/firma.png" alt="Rozhranie pre firmy" />
          </div>
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
import { ref } from 'vue'

import HeaderComponent from '@/components/HeaderComponent.vue'
import FooterComponent from '@/components/FooterComponent.vue'

const activeRole = ref('student')
</script>

<style scoped>
/* Voliteľné drobnosti */
.role-cards {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 16px;
  margin-top: 24px;
}

.role-card {
  appearance: none;
  -webkit-appearance: none;
  background: #fff;
  border: 2px solid transparent;
  border-radius: 12px;
  padding: 16px;
  text-align: left;
  cursor: pointer;
  transition: all 0.2s ease;

  /* fix: remove default button text styling */
  font: inherit;
  color: #0f172a;

  /* fix: consistent layout */
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  justify-content: flex-start;
  gap: 6px;
  min-height: 92px;
}

.role-card h3 {
  margin: 0;
  font-size: 16px;
  line-height: 1.2;
  color: #0f172a;
}

.role-card p {
  margin: 0;
  font-size: 14px;
  line-height: 1.35;
  color: #475569;
}

.role-card:hover {
  border-color: #cfe3ff;
}

.role-card.active {
  border-color: #3b82f6;
  background: #f0f6ff;
}

.role-card.active h3 {
  color: #0f172a;
}

.role-card.active p {
  color: #334155;
}

.role-detail {
  margin-top: 32px;
}

.role-detail-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 32px;
  align-items: center;
}

.role-detail-grid img {
  width: 100%;
  border-radius: 12px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
}

@media (max-width: 768px) {
  .role-cards {
    grid-template-columns: 1fr;
  }

  .role-detail-grid {
    grid-template-columns: 1fr;
  }
}
</style>