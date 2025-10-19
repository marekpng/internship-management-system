<template>
  <div class="overlay">
    <div class="container">
      <!-- Vrchný panel -->
      <div class="top-bar">
        <div class="logo">
          <img src="@/assets/logo-fpv.png" alt="Logo FPV" />
          <div class="logo-text">Praxový systém</div>
        </div>
        <div class="user-actions">
          <div class="user-name">Matej Novák</div>
          <button class="logout-button">Odhlásiť sa</button>
        </div>
      </div>

      <!-- bunky na pridanie -->
      <div class="section">
        <div class="section-title">Informácie o praxi</div>
        <div class="section-description">
          Prehľadné informácie o priebehu, mieste a hodnotení tvojej praxe.
        </div>
      </div>

      <div class="section">
        <div class="section-title">Pridanie praxe</div>
        <div class="section-description">
          Tu budeš môcť pridať novú prax alebo upraviť existujúce záznamy.
        </div>
      </div>

      <div class="section">
        <div class="section-title">Moja prax</div>
        <div class="section-description">
          Zobrazenie tvojej doterajšej praxe a detailov o jednotlivých etapách.
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
// zatiaľ netreba žiadny JavaScript
</script>

<style src="../assets/style.css"></style>
