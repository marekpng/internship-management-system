<template>
  <div class="landing">
  <HeaderComponent />
  <div class="page">
    <h1>Registrácia</h1>
    <p>Vyberte, čo chcete registrovať:</p>

    <div class="actions">
      <RouterLink class="btn" to="/register/student">Registrovať študenta</RouterLink>
      <RouterLink class="btn secondary" to="/register/company">Registrovať firmu</RouterLink>
    </div>
  </div>
</div>
</template>

<script setup>
import '@/assets/landing.css'
import HeaderComponent from '@/components/HeaderComponent.vue'
</script>

<style scoped>
.page { max-width: 680px; margin: 48px auto; padding: 24px; background: #fff; border-radius: 12px; box-shadow: 0 6px 20px rgba(0,0,0,.08);}
h1 { margin: 0 0 12px; color:#0a7b3e; }
.actions { display: flex; gap: 12px; margin-top: 16px; flex-wrap: wrap; }

.btn {
  padding: 10px 16px;
  border-radius: 8px;
  text-decoration: none;
  background:#0a7b3e;
  color:#fff;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border: 2px solid transparent;
  transition: background-color .2s ease, color .2s ease, border-color .2s ease, box-shadow .2s ease, transform .12s ease;
}

.btn:hover {
  background: #086531;
  color: #fff;
  box-shadow: 0 6px 16px rgba(10,123,62,.18);
  transform: translateY(-1px);
}

.btn:active {
  transform: translateY(0);
  box-shadow: none;
}

.btn:focus-visible {
  outline: none;
  box-shadow: 0 0 0 4px rgba(10,123,62,.25);
}

.btn.secondary { background:#1f7a8c; }

.btn.secondary:hover {
  background: #186070;
  color: #fff;
  box-shadow: 0 6px 16px rgba(31,122,140,.18);
}

.btn.secondary:focus-visible {
  box-shadow: 0 0 0 4px rgba(31,122,140,.25);
}
</style>
