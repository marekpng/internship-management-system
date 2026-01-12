<template>
  <header class="company-topbar">
    <div class="container row">
      <!-- Link vľavo musí smerovať podľa roly, inak garanta/študenta presmeruje na firmu -->
      <router-link :to="homePath" class="brand">
        <div class="brand-badge">FPV</div>
        Praxový systém
      </router-link>

      <nav class="actions">
        <!-- Notifikácie: endpoint sa vyberie podľa roly (company/garant/student) -->
        <div v-if="isAuthed" class="notification-wrapper" @click="toggleNotifications">
          <i class="fa-regular fa-bell"></i>
          <span v-if="unreadCount > 0" class="badge">{{ unreadCount }}</span>

          <!--
            Dôležité: Kliky v dropdown paneli musia zastaviť bublanie (stop),
            inak sa panel pri kliknutí na obsah zavrie/otvorí a vyzerá to, že notifikácie "nezobrazí".
          -->
          <div v-if="showNotifications" class="notifications-panel dropdown" @click.stop>
            <div class="notif-header">
              Notifikácie
              <button class="close-btn" @click.stop="toggleNotifications">✖</button>
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
                @click.stop="markAsRead(notif.id)"
              >
                ✔
              </button>
            </div>
          </div>
        </div>

        <!-- Nastavenia: každá rola má svoj cieľ (firma/garant) alebo profil (študent) -->
        <router-link v-if="role === 'company'" to="/company/settings" class="nav-btn">
          <i class="fa-solid fa-gear"></i> Nastavenia
        </router-link>
        <router-link v-else-if="role === 'garant'" to="/garant/settings" class="nav-btn">
          <i class="fa-solid fa-gear"></i> Nastavenia
        </router-link>
        <router-link v-else-if="role === 'student'" to="/student/settings" class="nav-btn">
          <i class="fa-solid fa-gear"></i> Nastavenia
        </router-link>
        <router-link v-else to="/profile" class="nav-btn">
          <i class="fa-solid fa-gear"></i> Nastavenia
        </router-link>

        <button class="nav-btn logout" @click="logout">
          <i class="fa-solid fa-arrow-right-from-bracket"></i> Odhlásiť sa
        </button>
      </nav>
    </div>

    <!-- Slot pre filtre (napr. Company/Garant stránky). Keď nie je použitý, nič sa nezobrazí. -->
    <div class="filter-container">
      <slot name="filters"></slot>
    </div>
  </header>
</template>

<script setup>
import { useRouter } from 'vue-router'
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import axios from 'axios'

const router = useRouter()

// Domovská stránka podľa roly (roles môže byť ["company"] alebo [{ name: "company" }])
const homePath = computed(() => {
  const user = JSON.parse(localStorage.getItem('user') || '{}')
  const rawRole = user?.roles?.[0]
  const role = typeof rawRole === 'string' ? rawRole : rawRole?.name

  if (role === 'student') return '/student/dashboard'
  if (role === 'garant') return '/garant/dashboard'
  if (role === 'company') return '/company/dashboard'
  return '/'
})

// Rola používateľa (kvôli tomu, aby sa company-only prvky nezobrazovali garantovi/študentovi)
const role = computed(() => {
  const user = JSON.parse(localStorage.getItem('user') || '{}')
  const rawRole = user?.roles?.[0]
  return typeof rawRole === 'string' ? rawRole : rawRole?.name
})

// Jednoduchá kontrola, či je používateľ prihlásený
const isAuthed = computed(() => {
  return !!localStorage.getItem('access_token')
})

// URL pre notifikácie podľa roly
const notificationsListUrl = computed(() => {
  if (role.value === 'company') return 'http://127.0.0.1:8000/api/company/user-notifications'
  if (role.value === 'garant') return 'http://127.0.0.1:8000/api/garant/user-notifications'
  if (role.value === 'student') return 'http://127.0.0.1:8000/api/student/user-notifications'
  return null
})

const notificationsReadUrlPrefix = computed(() => {
  if (role.value === 'company') return 'http://127.0.0.1:8000/api/company/notifications/read/'
  if (role.value === 'garant') return 'http://127.0.0.1:8000/api/garant/notifications/read/'
  if (role.value === 'student') return 'http://127.0.0.1:8000/api/student/notifications/read/'
  return null
})

const showNotifications = ref(false)
const notifications = ref([])

// Interval pre periodické načítanie notifikácií (napr. aby sa badge aktualizoval aj bez otvorenia panelu)
let notificationsTimer = null

async function fetchNotifications() {
  const url = notificationsListUrl.value
  if (!url) {
    notifications.value = []
    return
  }

  try {
    const res = await axios.get(url, {
      headers: {
        Authorization: `Bearer ${localStorage.getItem('access_token')}`
      }
    })

    // Očakávame pole notifikácií
    notifications.value = Array.isArray(res.data) ? res.data : []
  } catch (err) {
    // Ak backend pre danú rolu ešte nie je hotový, nech UI nepadá
    const status = err?.response?.status
    if (status === 404) {
      notifications.value = []
      return
    }

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
  const prefix = notificationsReadUrlPrefix.value
  if (!prefix) return

  try {
    await axios.post(
      `${prefix}${id}`,
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
  // Načítame notifikácie iba ak je používateľ prihlásený
  if (isAuthed.value) {
    fetchNotifications()

    // Každých 25s obnovíme notifikácie (ľahké polling riešenie s nízkou záťažou)
    notificationsTimer = setInterval(() => {
      // Neotvárame panel, len obnovujeme dáta
      fetchNotifications()
    }, 25000)
  }
})

onBeforeUnmount(() => {
  if (notificationsTimer) {
    clearInterval(notificationsTimer)
    notificationsTimer = null
  }
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