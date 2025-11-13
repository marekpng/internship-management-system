<template>
  <div class="overlay">
    <div class="container">
      <!-- Vrchný panel (rovnaký ako v dashboarde) -->
      <div class="top-bar">
        <div class="logo">
          <img src="@/assets/logo-fpv.png" alt="Logo FPV" />
          <div class="logo-text">Praxový systém</div>
        </div>
        <div class="user-actions">
          <div class="user-name">{{ userName }}</div>
          <button class="logout-button" @click="logout">Odhlásiť sa</button>
        </div>
      </div>

      <!-- Obsah stránky -->
      <div class="section">
        <div class="section-title">Export údajov</div>
        <div class="section-description">
          Zvoľ filtre a stiahni CSV. (UTF-8 s BOM, oddeľovač ; )
        </div>

        <form @submit.prevent="exportCsv" class="export-grid">
          <input v-model="filters.student" placeholder="Meno študenta" />
          <input v-model="filters.company" placeholder="Názov firmy" />
          <input v-model="filters.supervisor" placeholder="Meno školiteľa" />
          <input v-model="filters.year" type="number" placeholder="Akademický rok (YYYY)" />

          <select v-model="filters.semester">
            <option value="">-- Semester --</option>
            <option value="ZS">ZS</option>
            <option value="LS">LS</option>
          </select>

          <select v-model="filters.status">
            <option value="">-- Stav praxe --</option>
            <option value="pending">Pending</option>
            <option value="approved">Approved</option>
            <option value="finished">Finished</option>
            <option value="rejected">Rejected</option>
          </select>

          <label>Dátum od <input v-model="filters.date_from" type="date" /></label>
          <label>Dátum do <input v-model="filters.date_to" type="date" /></label>

          <div class="actions">
            <button :disabled="loading">{{ loading ? 'Exportujem…' : 'Exportovať do CSV' }}</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const filters = ref({
  student: '', company: '', supervisor: '',
  year: '', semester: '', status: '',
  date_from: '', date_to: '',
})

const loading = ref(false)
const userName = ref('')

onMounted(() => {
  const raw = localStorage.getItem('user')
  if (raw) {
    try {
      const user = JSON.parse(raw)
      if (user.first_name && user.last_name) userName.value = `${user.first_name} ${user.last_name}`
      else if (user.name) userName.value = user.name
      else if (user.student_email) userName.value = user.student_email
      else userName.value = 'Prihlásený používateľ'
    } catch {
      userName.value = 'Prihlásený používateľ'
    }
  }
})

function logout () {
  localStorage.removeItem('token')
  localStorage.removeItem('user')
  // ak máš routu menom 'login', použi router push; inak jednoduché presmerovanie:
  window.location.href = '/login'
}

async function exportCsv () {
  loading.value = true
  try {
    const token = localStorage.getItem('token') // zjednotené s dashboardom
    const params = new URLSearchParams(filters.value).toString()

    // ak máš Vite proxy na /api -> stačí relatívna cesta
    const url = `/api/exports/internships?${params}`

    const res = await axios.get(url, {
      responseType: 'blob',
      headers: token ? { Authorization: `Bearer ${token}` } : {}
    })

    const blob = new Blob([res.data], { type: 'text/csv;charset=utf-8;' })
    const a = document.createElement('a')
    const href = URL.createObjectURL(blob)
    a.href = href

    const fallback = `export_praxe_${new Date().toISOString().replace(/[-:TZ]/g,'').slice(0,14)}.csv`
    const cd = res.headers['content-disposition']
    let filename = fallback
    if (cd) {
      const m = /filename="?([^"]+)"?/i.exec(cd)
      if (m && m[1]) filename = m[1]
    }

    a.setAttribute('download', filename)
    document.body.appendChild(a)
    a.click()
    document.body.removeChild(a)
    URL.revokeObjectURL(href)
  } finally {
    loading.value = false
  }
}
</script>

<!-- dôležité: použijeme rovnaký globálny štýl ako dashboard -->
<style src="../assets/style.css"></style>

<!-- len jemné doplnky na grid, držíme sa „zelenej“ témy -->
<style scoped>
.export-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 12px;
  padding: 16px;
  border: 1px solid rgba(0,0,0,.06);
  border-radius: 12px;
  background: #fff;
}
.export-grid input,
.export-grid select {
  padding: .55rem .7rem;
  border-radius: 10px;
  border: 1px solid #e5e7eb;
  background: #fff;
  color: #111827;
}
.export-grid label {
  display: flex;
  align-items: center;
  gap: 8px;
}
.actions {
  grid-column: 1 / -1;
}
.actions button {
  background: #16a34a; /* zelené tlačidlo aby ladilo */
  color: #fff;
  border: 0;
  padding: .6rem 1rem;
  border-radius: 10px;
  cursor: pointer;
}
.actions button:hover { background: #15803d; }

@media (max-width: 720px) {
  .export-grid { grid-template-columns: 1fr; }
}
</style>
