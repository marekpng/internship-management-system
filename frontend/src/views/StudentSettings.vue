<template>
  <CompanyNavBar />

  <div class="page-controls">
    <button class="back-btn" type="button" @click="$router.back()">← Späť</button>
  </div>

  <div class="settings-layout">
    <div class="left-col">
      <aside class="settings-sidebar">
        <h3>Nastavenia</h3>
        <ul>
          <li :class="{ active: activeTab === 'data' }" @click="activeTab = 'data'">Údaje študenta</li>
          <li :class="{ active: activeTab === 'notifications' }" @click="activeTab = 'notifications'">Notifikácie</li>
          <li :class="{ active: activeTab === 'password' }" @click="activeTab = 'password'">Zmena hesla</li>
        </ul>
      </aside>
    </div>

    <div class="settings-wrapper">
      <div class="notification-bar" v-if="notification" :class="notificationType">
        <span>{{ notification }}</span>
        <button class="notification-close" @click="clearNotification">✕</button>
      </div>
      <!-- TAB: Zmena hesla -->
      <div v-if="activeTab === 'password'">
        <h1>Zmena hesla</h1>

        <section class="block">
          <h2>Bezpečnosť účtu</h2>

          <div class="field">
            <label>Aktuálne heslo</label>
            <input v-model="passwordForm.current_password" type="password" autocomplete="current-password" />
          </div>

          <div class="field">
            <label>Nové heslo</label>
            <input v-model="passwordForm.password" type="password" autocomplete="new-password" />
          </div>

          <div class="field">
            <label>Nové heslo (znova)</label>
            <input v-model="passwordForm.password_confirmation" type="password" autocomplete="new-password" />
          </div>

          <button class="btn-save" @click="passwordConfirmVisible = true">Zmeniť heslo</button>
        </section>

        <div v-if="passwordConfirmVisible" class="modal-backdrop">
          <div class="modal">
            <h3>Ste si istí, že chcete zmeniť heslo?</h3>
            <p>Po uložení sa budete prihlasovať novým heslom.</p>

            <div class="modal-actions">
              <button class="btn-cancel" @click="passwordConfirmVisible = false">Zrušiť</button>
              <button class="btn-confirm" @click="changePassword">Potvrdiť</button>
            </div>
          </div>
        </div>
      </div>

      <!-- TAB: Profil -->
      <div v-if="activeTab === 'data'">
        <h1>Nastavenia študenta</h1>

        <div class="info-bar">
          <p>Niektoré údaje nie je možné meniť, pretože sú súčasťou vášho používateľského profilu v systéme.</p>
        </div>

        <section class="block">
          <h2>Údaje profilu (nemožno meniť)</h2>

          <div class="field">
            <label>Meno</label>
            <input type="text" :value="student?.first_name || ''" disabled />
          </div>

          <div class="field">
            <label>Priezvisko</label>
            <input type="text" :value="student?.last_name || ''" disabled />
          </div>
        </section>

        <section class="block">
          <h2>Editovateľné údaje</h2>

          <div class="field">
            <label>Email (nemožno zmeniť)</label>
            <input :value="student?.email || ''" type="email" disabled />
            <small class="hint">Email je viazaný na prihlásenie. Ak ho potrebujete zmeniť, kontaktujte administrátora.</small>
          </div>

          <div class="field">
            <label>Telefón</label>
            <input v-model="editable.phone" type="text" placeholder="telefón" />
          </div>

          <div class="field">
            <label>Alternatívny email</label>
            <input v-model="editable.alternative_email" type="email" placeholder="alternatívny email" />
          </div>

          <button class="btn-save" @click="confirmVisible = true">Uložiť zmeny</button>
        </section>

        <div v-if="confirmVisible" class="modal-backdrop">
          <div class="modal">
            <h3>Ste si istí, že chcete uložiť zmeny?</h3>
            <p>Tieto údaje sa aktualizujú vo vašom profile.</p>

            <div class="modal-actions">
              <button class="btn-cancel" @click="confirmVisible = false">Zrušiť</button>
              <button class="btn-confirm" @click="saveChanges">Potvrdiť</button>
            </div>
          </div>
        </div>
      </div>

      <!-- TAB: Notifikácie -->
      <div v-if="activeTab === 'notifications'">
        <h1>Nastavenia notifikácií</h1>

        <section class="block">
          <h2>Emailové notifikácie</h2>

          <div class="field">
            <label>
              <input type="checkbox" v-model="notifications.notify_approved" disabled />
              Schválenie praxe
            </label>
          </div>

          <div class="field">
            <label>
              <input type="checkbox" v-model="notifications.notify_rejected" disabled />
              Zamietnutie praxe
            </label>
          </div>

          <div class="field">
            <label><input type="checkbox" checked disabled /> Schválená/Zamietnutá prax</label>
          </div>

          <div class="field">
            <label><input type="checkbox" checked disabled /> Obhájená/Neobhájená prax</label>
          </div>

          <div class="field">
            <label><input type="checkbox" v-model="notifications.notify_profile_change" /> Zmena údajov profilu</label>
          </div>

          <button class="btn-save" @click="notifyConfirmVisible = true">Uložiť notifikácie</button>
        </section>

        <div v-if="notifyConfirmVisible" class="modal-backdrop">
          <div class="modal">
            <h3>Ste si istí, že chcete uložiť zmeny notifikácií?</h3>
            <p>Toto ovplyvní spôsob, akým budete dostávať emailové upozornenia.</p>

            <div class="modal-actions">
              <button class="btn-cancel" @click="notifyConfirmVisible = false">Zrušiť</button>
              <button class="btn-confirm" @click="confirmNotificationSave">Potvrdiť</button>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import CompanyNavBar from '@/components/icons/CompanyNavBar.vue'

const API_BASE = 'http://localhost:8000/api'

// Headers vždy berieme z localStorage (aby to fungovalo aj po re-login)
const authHeaders = () => {
  const token = localStorage.getItem('access_token')
  return token ? { Authorization: `Bearer ${token}` } : {}
}

const student = ref({
  id: null,
  first_name: '',
  last_name: '',
  email: '',
  phone: '',
  alternative_email: ''
})

const editable = ref({
  phone: '',
  alternative_email: ''
})

const confirmVisible = ref(false)
const notifyConfirmVisible = ref(false)
const notification = ref('')
const notificationType = ref('info') // info | success | error

function setNotification(message, type = 'info') {
  notification.value = message
  notificationType.value = type
}

function clearNotification() {
  notification.value = ''
  notificationType.value = 'info'
}

const activeTab = ref('data')

const passwordConfirmVisible = ref(false)
const passwordForm = ref({
  current_password: '',
  password: '',
  password_confirmation: ''
})

const notifications = ref({
  notify_new_request: true,
  notify_approved: true,
  notify_rejected: true,
  notify_profile_change: true
})

async function loadStudent() {
  // 1) Fallback z localStorage, aby UI nebolo prázdne hneď po načítaní
  const lsUser = JSON.parse(localStorage.getItem('user') || '{}')

  if (lsUser && lsUser.id) {
    student.value = {
      ...student.value,
      id: lsUser.id,
      email: lsUser.email ?? '',
      first_name: lsUser.first_name ?? '',
      last_name: lsUser.last_name ?? '',
      phone: lsUser.phone ?? '',
      alternative_email: lsUser.alternative_email ?? ''
    }

    editable.value.phone = student.value.phone || ''
    editable.value.alternative_email = student.value.alternative_email || ''

    // Študent mení iba notify_profile_change, ostatné sú vždy zapnuté
    notifications.value.notify_new_request = true
    notifications.value.notify_approved = true
    notifications.value.notify_rejected = true
    if (typeof lsUser.notify_profile_change !== 'undefined') {
      notifications.value.notify_profile_change = lsUser.notify_profile_change == 1 || lsUser.notify_profile_change === true
    }
  }

  // 2) Ak existuje backend endpoint, je to zdroj pravdy
  try {
    const res = await axios.get(`${API_BASE}/student/profile`, { headers: authHeaders() })
    const p = res.data || {}

    student.value = {
      ...student.value,
      id: p.id ?? student.value.id,
      email: p.email ?? student.value.email,
      first_name: p.first_name ?? student.value.first_name,
      last_name: p.last_name ?? student.value.last_name,
      phone: p.phone ?? student.value.phone,
      alternative_email: p.alternative_email ?? student.value.alternative_email
    }

    editable.value.phone = student.value.phone || ''
    editable.value.alternative_email = student.value.alternative_email || ''

    const nres = await axios.get(`${API_BASE}/student/notifications`, { headers: authHeaders() })
    const n = nres.data || {}

    // Študent mení iba notify_profile_change, ostatné sú vždy zapnuté
    notifications.value.notify_new_request = true
    notifications.value.notify_approved = true
    notifications.value.notify_rejected = true
    if (typeof n.notify_profile_change !== 'undefined') notifications.value.notify_profile_change = !!n.notify_profile_change

    // 3) Sync do localStorage, aby navbar a iné view mali rovnaké dáta
    const updatedLs = {
      ...(lsUser || {}),
      id: student.value.id,
      email: student.value.email,
      first_name: student.value.first_name,
      last_name: student.value.last_name,
      phone: student.value.phone,
      alternative_email: student.value.alternative_email,
      notify_new_request: notifications.value.notify_new_request,
      notify_approved: notifications.value.notify_approved,
      notify_rejected: notifications.value.notify_rejected,
      notify_profile_change: notifications.value.notify_profile_change
    }
    localStorage.setItem('user', JSON.stringify(updatedLs))

    clearNotification()
  } catch (e) {
    console.error('loadStudent failed:', e)
    setNotification('Nepodarilo sa načítať údaje z backendu. Používam localStorage.', 'error')
  }
}

async function saveChanges() {
  try {
    const payload = {
      phone: editable.value.phone,
      alternative_email: editable.value.alternative_email
    }

    const res = await axios.put(`${API_BASE}/student/profile`, payload, { headers: authHeaders() })
    const p = res.data?.user || res.data || {}

    const current = JSON.parse(localStorage.getItem('user') || '{}')
    const updated = {
      ...current,
      phone: p.phone ?? payload.phone,
      alternative_email: p.alternative_email ?? payload.alternative_email
    }
    localStorage.setItem('user', JSON.stringify(updated))

    // Update UI immediately
    student.value.phone = updated.phone
    student.value.alternative_email = updated.alternative_email
    editable.value.phone = student.value.phone || ''
    editable.value.alternative_email = student.value.alternative_email || ''

    confirmVisible.value = false
    setNotification('Údaje boli úspešne zmenené.', 'success')

    await loadStudent()
  } catch (e) {
    console.error('saveChanges failed:', e)
    confirmVisible.value = false
    setNotification('Uloženie zlyhalo. Skontroluj backend endpoint /api/student/profile.', 'error')
  }
}

async function saveNotificationSettings() {
  try {
    const payload = {
      notify_new_request: true,
      notify_approved: true,
      notify_rejected: true,
      notify_profile_change: !!notifications.value.notify_profile_change
    }

    await axios.put(`${API_BASE}/student/notifications`, payload, { headers: authHeaders() })

    const current = JSON.parse(localStorage.getItem('user') || '{}')
    localStorage.setItem('user', JSON.stringify({ ...current, ...payload }))

    setNotification('Notifikačné nastavenia boli uložené.', 'success')

    await loadStudent()
  } catch (e) {
    console.error('saveNotificationSettings failed:', e)
    setNotification('Uloženie notifikácií zlyhalo. Skontroluj backend endpoint /api/student/notifications.', 'error')
  }
}
async function changePassword() {
  try {
    // Jednoduchá validácia na FE, aby sme hneď chytili preklepy.
    if (!passwordForm.value.current_password || !passwordForm.value.password) {
      setNotification('Vyplň aktuálne aj nové heslo.', 'error')
      passwordConfirmVisible.value = false
      return
    }
    if (passwordForm.value.password !== passwordForm.value.password_confirmation) {
      setNotification('Nové heslá sa nezhodujú.', 'error')
      passwordConfirmVisible.value = false
      return
    }

    // Predpoklad: máš API endpoint na zmenu hesla.
    // Ak používaš iný endpoint, zmeň URL na správnu.
    await axios.post(`${API_BASE}/change-password`, {
      current_password: passwordForm.value.current_password,
      password: passwordForm.value.password,
      password_confirmation: passwordForm.value.password_confirmation
    }, { headers: authHeaders() })

    passwordForm.value.current_password = ''
    passwordForm.value.password = ''
    passwordForm.value.password_confirmation = ''

    setNotification('Heslo bolo úspešne zmenené.', 'success')
  } catch (e) {
    console.error('changePassword failed:', e)
    // Ak backend vracia validácie, ukáž prvú zmysluplnú.
    const msg = e?.response?.data?.message || 'Zmena hesla zlyhala. Skontroluj backend endpoint /api/change-password.'
    setNotification(msg, 'error')
  } finally {
    passwordConfirmVisible.value = false
  }
}

function confirmNotificationSave() {
  saveNotificationSettings().finally(() => {
    notifyConfirmVisible.value = false
  })
}

onMounted(loadStudent)
</script>

<style scoped>
.settings-wrapper {
  flex: 1;
  max-width: 800px;
  margin: 0;
  padding: 10px 20px;
}

h1 {
  font-size: 28px;
  margin-bottom: 30px;
}

.block {
  background: white;
  border: 1px solid #e6e6e6;
  padding: 20px;
  border-radius: 8px;
  margin-bottom: 30px;
}

.field {
  display: flex;
  flex-direction: column;
  margin-bottom: 15px;
}

label {
  font-weight: 600;
  margin-bottom: 6px;
}

input {
  padding: 10px;
  border: 1px solid #cfcfcf;
  border-radius: 6px;
}

input:disabled {
  background: #f1f1f1;
  color: #666;
}

.hint {
  margin-top: 6px;
  font-size: 12px;
  color: #666;
}

.btn-save {
  background: #1d4d2d;
  color: white;
  padding: 10px 16px;
  border-radius: 6px;
  border: none;
  cursor: pointer;
}

.btn-save:hover {
  background: #164022;
}

.modal-backdrop {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.35);
  display: flex;
  justify-content: center;
  align-items: center;
}

.modal {
  background: white;
  padding: 25px;
  border-radius: 8px;
  width: 400px;
}

.modal-actions {
  margin-top: 20px;
  display: flex;
  justify-content: flex-end;
  gap: 15px;
}

.btn-cancel {
  background: #d4d4d4;
  border: none;
  padding: 8px 14px;
  border-radius: 6px;
}

.btn-confirm {
  background: #1d4d2d;
  color: white;
  border: none;
  padding: 8px 14px;
  border-radius: 6px;
}

.info-bar {
  background: #f7f7f7;
  border: 1px solid #e5e5e5;
  padding: 12px 16px;
  border-radius: 6px;
  margin-bottom: 20px;
  font-size: 14px;
  color: #444;
}


.back-btn {
  margin: 20px 0;
  background: #ffffff;
  border: 1px solid #1d4d2d;
  color: #1d4d2d;
  padding: 8px 14px;
  border-radius: 6px;
  cursor: pointer;
  font-size: 14px;
  font-weight: 600;
  line-height: 1;
}

.back-btn:hover {
  background: #e8f5e9;
}


.left-col {
  width: 220px;
  display: flex;
  flex-direction: column;
  align-items: flex-start;
}


.page-controls {
  max-width: 1100px;
  margin: 24px auto 0; /* viac priestoru pod navbarom */
  padding: 0 20px;
  display: flex;
  align-items: center;
}

.notification-bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 12px;
  padding: 10px 16px;
  border-radius: 6px;
  margin-bottom: 20px;
}

.notification-bar.info {
  background: #eef5ff;
  border: 1px solid #1d4d2d;
  color: #1d4d2d;
}

.notification-bar.success {
  background: #e8f7ee;
  border: 1px solid #1d4d2d;
  color: #1d4d2d;
}

.notification-bar.error {
  background: #ffecec;
  border: 1px solid #b42318;
  color: #b42318;
}

.notification-close {
  background: transparent;
  border: none;
  cursor: pointer;
  font-size: 14px;
  padding: 6px 10px;
  border-radius: 6px;
}

.notification-close:hover {
  background: rgba(0,0,0,0.06);
}

.settings-layout {
  display: flex;
  gap: 30px;
  max-width: 1100px;
  margin: 20px auto; /* odsadenie pod back buttonom */
}

.settings-sidebar {
  width: 100%;
  background: white;
  padding: 20px;
  border: 1px solid #e6e6e6;
  border-radius: 8px;
  height: fit-content;
}

.settings-sidebar h3 {
  margin-bottom: 15px;
  font-size: 18px;
  font-weight: 700;
  color: #1d4d2d;
}

.settings-sidebar ul {
  list-style: none;
  padding: 0;
  margin: 0;
}

.settings-sidebar li {
  padding: 10px 0;
  cursor: pointer;
  font-size: 15px;
  color: #333;
  border-bottom: 1px solid #eee;
}

.settings-sidebar li:last-child {
  border-bottom: none;
}

.settings-sidebar li.active {
  font-weight: 700;
  color: #1d4d2d;
}

.field input[type='checkbox'] {
  margin-right: 10px;
  transform: scale(1.2);
}
</style>
