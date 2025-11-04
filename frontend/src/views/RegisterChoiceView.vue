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
  <FooterComponent />
</div>
</template>

<script setup>
import '@/assets/landing.css'
import HeaderComponent from '@/components/HeaderComponent.vue'
import FooterComponent from '@/components/FooterComponent.vue'
</script>

<style scoped>
.page { max-width: 680px; margin: 48px auto; padding: 24px; background: #fff; border-radius: 12px; box-shadow: 0 6px 20px rgba(0,0,0,.08);}
h1 { margin: 0 0 12px; color:#0a7b3e; }
.actions { display: flex; gap: 12px; margin-top: 16px; }
.btn { padding: 10px 16px; border-radius: 8px; text-decoration: none; background:#0a7b3e; color:#fff; }
.btn.secondary { background:#1f7a8c; }

</style>