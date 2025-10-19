<template>
  <div class="login-body">
    <div class="login-container">
      <div class="login-box">
        <div class="login-logo">
          <!-- použij obrázok z public/ (rovnako ako pri HomeView) -->
          <img src="@/assets/logo-fpv.png" alt="UKF FPV logo" />
        </div>

        <h2>Prihlásenie</h2>

        <form>
          <label for="email">Email</label>
          <input type="email" id="email" placeholder="Zadaj email" required />

          <label for="password">Heslo</label>
          <input type="password" id="password" placeholder="Zadaj heslo" required />

          <button type="submit">Prihlásiť sa</button>
        </form>

        <p class="register-link">
  Nemáš účet? <router-link to="/register">Zaregistruj sa</router-link>
</p>
      </div>
    </div>
  </div>
</template>

<script setup>
// Zatiaľ netreba žiadny JS
</script>

<style src="../assets/login.css"></style>
