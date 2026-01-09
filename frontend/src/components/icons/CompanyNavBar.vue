<template>
  <header class="company-topbar">
    <div class="container row">
      <router-link to="/company/dashboard" class="brand">
        <div class="brand-badge">FPV</div>
        Praxový systém
      </router-link>

      <nav class="actions">
        <div class="notification-wrapper" @click="toggleNotifications">
          <i class="fa-solid fa-bell"></i>
          <span v-if="unreadCount > 0" class="badge">{{ unreadCount }}</span>

          <div v-if="showNotifications" class="notifications-panel dropdown">
            <div class="notif-header">
              Notifikácie
              <button class="close-btn" @click="toggleNotifications">✖</button>
            </div>

            <div v-if="notifications.length === 0" class="notif-empty">
              Nemáte žiadne notifikácie
            </div>

            <div
              v-for="notif in notifications"
              :key="notif.id"
              class="notif-item"
              :class="{ 'notif-read': notif.read }"
            >
              <p>{{ notif.message }}</p>
              <button
                v-if="!notif.read"
                class="confirm-btn"
                @click="markAsRead(notif.id)"
              >
                ✔
              </button>
            </div>
          </div>
        </div>
        
        <router-link to="/company/settings" class="nav-btn">
          <i class="fas fa-cog"></i> Nastavenia
        </router-link>


        <button class="nav-btn logout" @click="logout">
          <i class="fa-solid fa-arrow-right-from-bracket"></i> Odhlásiť sa 
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
import { ref, computed } from 'vue'
import axios from 'axios'
import { onMounted } from 'vue'

const router = useRouter()

const showNotifications = ref(false)
const notifications = ref([])

async function fetchNotifications() {
  try {
    const res = await axios.get('http://127.0.0.1:8000/api/company/user-notifications', {
      headers: {
        Authorization: `Bearer ${localStorage.getItem('access_token')}`
      }
    })
    notifications.value = res.data
  } catch (err) {
    console.error('Nepodarilo sa načítať notifikácie:', err)
  }
}

const unreadCount = computed(() =>
  notifications.value.filter(n => !n.read).length
)

function toggleNotifications() {
  showNotifications.value = !showNotifications.value

  // Keď sa panel otvorí, načítame nové notifikácie
  if (showNotifications.value) {
    fetchNotifications()
  }
}

async function markAsRead(id) {
  try {
    await axios.post(
      `http://127.0.0.1:8000/api/company/notifications/read/${id}`,
      {},
      {
        headers: {
          Authorization: `Bearer ${localStorage.getItem('access_token')}`
        }
      }
    )
    const notif = notifications.value.find(n => n.id === id)
    if (notif) notif.read = true
  } catch (err) {
    console.error('Nepodarilo sa označiť notifikáciu ako prečítanú:', err)
  }
}

function logout() {
  localStorage.removeItem('access_token')
  localStorage.removeItem('user')
  router.push('/login')
}

onMounted(() => {
  fetchNotifications()
})
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

  .notification-wrapper {
    position: relative;
    cursor: pointer;
    font-size: 20px;
    padding: 6px 10px;
    border-radius: 6px;
  }
  .notification-wrapper:hover {
    background: #f3f3f3;
  }

.badge {
  background: red;
  color: white;
  padding: 2px 6px;
  border-radius: 50%;
  font-size: 12px;
  position: absolute;
  top: -5px;
  right: -10px;
}

  .notifications-panel {
    position: absolute;
    top: 36px;
    right: 0;
    width: 280px;
    background: white;
    border: 1px solid #e6e6e6;
    box-shadow: 0 4px 14px rgba(0,0,0,0.12);
    border-radius: 10px;
    padding: 12px;
    z-index: 9999;
    max-height: 320px;
    overflow-y: auto;
  }
  .notification-wrapper {
    position: relative;
  }

.notif-header {
  display: flex;
  justify-content: space-between;
  font-weight: 600;
  margin-bottom: 10px;
}

.close-btn {
  background: transparent;
  border: none;
  cursor: pointer;
  font-size: 16px;
}

  .notif-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 6px;
    border-bottom: 1px solid #f2f2f2;
  }

.confirm-btn {
  background: #1d4d2d;
  color: white;
  border: none;
  padding: 4px 8px;
  border-radius: 4px;
  cursor: pointer;
}

.notif-empty {
  text-align: center;
  padding: 20px 0;
  color: #888;
}

.notif-read {
  opacity: 0.5;
}
</style>