<script setup>
import { RouterView } from 'vue-router'

import '@/assets/basic.css'
import FooterComponent from '@/components/FooterComponent.vue'
</script>

<template>
  <!-- Tu sa bude zobrazovať obsah podľa routeru -->
  <div class="page-wrapper">
    <div class="content">
      <RouterView />
    </div>
  </div>
  <div class="footer-only">
    <FooterComponent />
  </div>
</template>

<style>
body {
  margin: 0;
  font-family: 'Inter', sans-serif;
}
</style>
