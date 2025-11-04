<template>
  <header class="topbar">
    <div class="container nav-row">
      <router-link to="/" class="brand">
  <div class="brand-badge">FPV</div>
  Praxový systém
</router-link>


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
</template>

<script setup>
</script>

<style scoped>
.brand {
  text-decoration: none;
  color: inherit;
  display: flex;
  align-items: center;
  gap: 10px;
  font-weight: 600;
  letter-spacing: -.01em;
}
</style>
