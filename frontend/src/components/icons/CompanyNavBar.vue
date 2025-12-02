<template>
  <header class="company-topbar">
    <div class="container row">
      <router-link to="/company/dashboard" class="brand">
        <div class="brand-badge">FPV</div>
        Praxový systém
      </router-link>

      <nav class="actions">
        <router-link to="/company/settings" class="nav-btn">
          ⚙️ Nastavenia
        </router-link>

        <button class="nav-btn logout" @click="logout">
          ⏻ Odhlásiť sa
        </button>
      </nav>
    </div>
  
    <div class="filter-container">
      <slot name="filters"></slot>
    </div>

  </header>
</template>

<script setup>
import { useRouter } from 'vue-router'
const router = useRouter()

function logout() {
  localStorage.removeItem('access_token')
  localStorage.removeItem('user')
  router.push('/login')
}
</script>

<style scoped>
.company-topbar {
  width: 100%;
  background: white;
  border-bottom: 1px solid #e6e6e6;
  padding: 14px 0;
  position: sticky;
  top: 0;
  z-index: 50;
}

.container {
  max-width: 1250px;
  margin: 0 auto;
  padding: 0 20px;
}

.row {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.brand {
  text-decoration: none;
  color: #1b1b1b;
  font-size: 20px;
  font-weight: 700;
  display: flex;
  align-items: center;
  gap: 10px;
}

.brand-badge {
  background: #1d4d2d;
  color: white;
  padding: 6px 10px;
  border-radius: 6px;
  font-size: 14px;
}

.actions {
  display: flex;
  gap: 22px;
  align-items: center;
}

.nav-btn {
  text-decoration: none;
  font-size: 15px;
  font-weight: 500;
  color: #1d1d1d;
}

.nav-btn:hover {
  color: #14542b;
}

.logout {
  background: #1d4d2d;
  color: white !important;
  padding: 8px 16px;
  border-radius: 6px;
  border: 1px solid #1d4d2d;
}
.logout:hover {
  background: #164022;
  color: white !important;
}

/* Slot filter area */
::v-deep .filter-container {
  width: 100%;
  background: #ffffff;
  border-bottom: 1px solid #e6e6e6;
}

::v-deep .filter-bar {
  max-width: 1250px;
  margin: 0 auto;
  padding: 10px 20px;
  display: flex;
  justify-content: center;
  gap: 14px;
}

::v-deep .filter-btn {
  background: #e8f3ec;
  padding: 6px 14px;
  border-radius: 6px;
  font-size: 14px;
  text-decoration: none;
  color: #1d4d2d;
  border: 1px solid #cfe5d8;
}

::v-deep .filter-btn:hover {
  background: #d4e9dc;
}

::v-deep .filter-btn.active {
  background: #1d4d2d;
  color: white !important;
  border-color: #1d4d2d;
}
</style>