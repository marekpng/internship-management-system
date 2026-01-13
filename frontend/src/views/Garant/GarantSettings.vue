<template>
  <CompanyNavBar />

  <div class="page-controls">
    <button class="back-button" type="button" @click="$router.back()">
      ← Späť
    </button>
  </div>

  <div class="settings-layout">
    <aside class="settings-sidebar">
      <h3>Nastavenia</h3>
      <ul>
        <li :class="{ active: activeTab === 'data' }" @click="activeTab = 'data'">Údaje garanta</li>
        <li :class="{ active: activeTab === 'notifications' }" @click="activeTab = 'notifications'">Notifikácie</li>
        <li :class="{ active: activeTab === 'password' }" @click="activeTab = 'password'">Zmena hesla</li>
      </ul>
    </aside>

    <div class="settings-wrapper">
      <div class="notification-bar" v-if="notification">
        {{ notification }}
      </div>

      <!-- TAB: Profil -->
      <div v-if="activeTab === 'data'">
        <h1>Nastavenia garanta</h1>

        <div class="info-bar">
          <p>Niektoré údaje nie je možné meniť, pretože sú súčasťou vášho používateľského profilu v systéme.</p>
        </div>

        <section class="block">
          <h2>Údaje profilu (nemožno meniť)</h2>

          <div class="field">
            <label>Meno</label>
            <input type="text" :value="garant?.first_name || ''" disabled />
          </div>

          <div class="field">
            <label>Priezvisko</label>
            <input type="text" :value="garant?.last_name || ''" disabled />
          </div>
        </section>

        <section class="block">
          <h2>Editovateľné údaje</h2>

          <div class="field">
            <label>Email (nemožno zmeniť)</label>
            <input :value="garant?.email || ''" type="email" disabled />
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
            <label><input type="checkbox" v-model="notifications.notify_new_request" /> Nová žiadosť o prax</label>
          </div>

          <div class="field">
            <label><input type="checkbox" v-model="notifications.notify_approved" /> Schválenie praxe</label>
          </div>

          <div class="field">
            <label><input type="checkbox" v-model="notifications.notify_rejected" /> Zamietnutie praxe</label>
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

      <!-- TAB: Zmena hesla -->
      <div v-if="activeTab === 'password'">
        <h1>Zmena hesla</h1>

        <section class="block">
          <h2>Zmena hesla</h2>

          <div class="field">
            <label>Aktuálne heslo</label>
            <input v-model="passwordForm.current_password" type="password" autocomplete="current-password" placeholder="aktuálne heslo" />
          </div>

          <div class="field">
            <label>Nové heslo</label>
            <input v-model="passwordForm.new_password" type="password" autocomplete="new-password" placeholder="nové heslo" />
          </div>

          <div class="field">
            <label>Potvrdenie nového hesla</label>
            <input v-model="passwordForm.new_password_confirmation" type="password" autocomplete="new-password" placeholder="zopakujte nové heslo" />
            <small class="hint">Heslo musí spĺňať požiadavky systému..</small>
          </div>

          <button class="btn-save" @click="passwordConfirmVisible = true">Zmeniť heslo</button>
        </section>

        <div v-if="passwordConfirmVisible" class="modal-backdrop">
          <div class="modal">
            <h3>Ste si istí, že chcete zmeniť heslo?</h3>
            <p>Po zmene hesla sa budete vedieť prihlásiť už len novým heslom.</p>

            <div class="modal-actions">
              <button class="btn-cancel" @click="passwordConfirmVisible = false">Zrušiť</button>
              <button class="btn-confirm" @click="confirmPasswordChange">Potvrdiť</button>
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
const token = localStorage.getItem('access_token')
const authHeaders = () => (token ? { Authorization: `Bearer ${token}` } : {})

const garant = ref({
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

const activeTab = ref('data')

const notifications = ref({
  notify_new_request: true,
  notify_approved: true,
  notify_rejected: true,
  notify_profile_change: true
})

const passwordConfirmVisible = ref(false)
const passwordForm = ref({
  current_password: '',
  new_password: '',
  new_password_confirmation: ''
})

async function loadGarant() {
  // DB je zdroj pravdy. Fallback = localStorage.
  const lsUser = JSON.parse(localStorage.getItem('user') || '{}')

  // Predvyplň z localStorage aby UI nebolo prázdne
  if (lsUser && lsUser.id) {
    garant.value = {
      ...garant.value,
      id: lsUser.id,
      email: lsUser.email ?? '',
      first_name: lsUser.first_name ?? '',
      last_name: lsUser.last_name ?? '',
      phone: lsUser.phone ?? '',
      alternative_email: lsUser.alternative_email ?? ''
    }

    editable.value.phone = garant.value.phone || ''
    editable.value.alternative_email = garant.value.alternative_email || ''

    if (typeof lsUser.notify_new_request !== 'undefined') notifications.value.notify_new_request = lsUser.notify_new_request == 1 || lsUser.notify_new_request === true
    if (typeof lsUser.notify_approved !== 'undefined') notifications.value.notify_approved = lsUser.notify_approved == 1 || lsUser.notify_approved === true
    if (typeof lsUser.notify_rejected !== 'undefined') notifications.value.notify_rejected = lsUser.notify_rejected == 1 || lsUser.notify_rejected === true
    if (typeof lsUser.notify_profile_change !== 'undefined') notifications.value.notify_profile_change = lsUser.notify_profile_change == 1 || lsUser.notify_profile_change === true
  }

  try {
    // Profil
    const res = await axios.get(`${API_BASE}/garant/profile`, { headers: authHeaders() })
    const p = res.data || {}

    garant.value = {
      ...garant.value,
      id: p.id ?? garant.value.id,
      email: p.email ?? garant.value.email,
      first_name: p.first_name ?? garant.value.first_name,
      last_name: p.last_name ?? garant.value.last_name,
      phone: p.phone ?? garant.value.phone,
      alternative_email: p.alternative_email ?? garant.value.alternative_email
    }

    editable.value.phone = garant.value.phone || ''
    editable.value.alternative_email = garant.value.alternative_email || ''

    // Notifikačné nastavenia
    const nres = await axios.get(`${API_BASE}/garant/notifications`, { headers: authHeaders() })
    const n = nres.data || {}

    if (typeof n.notify_new_request !== 'undefined') notifications.value.notify_new_request = !!n.notify_new_request
    if (typeof n.notify_approved !== 'undefined') notifications.value.notify_approved = !!n.notify_approved
    if (typeof n.notify_rejected !== 'undefined') notifications.value.notify_rejected = !!n.notify_rejected
    if (typeof n.notify_profile_change !== 'undefined') notifications.value.notify_profile_change = !!n.notify_profile_change

    // Sync do localStorage
    const updatedLs = {
      ...(lsUser || {}),
      id: garant.value.id,
      email: garant.value.email,
      first_name: garant.value.first_name,
      last_name: garant.value.last_name,
      phone: garant.value.phone,
      alternative_email: garant.value.alternative_email,
      notify_new_request: notifications.value.notify_new_request,
      notify_approved: notifications.value.notify_approved,
      notify_rejected: notifications.value.notify_rejected,
      notify_profile_change: notifications.value.notify_profile_change
    }
    localStorage.setItem('user', JSON.stringify(updatedLs))

    notification.value = ''
  } catch (e) {
    console.error('loadGarant failed:', e)
    notification.value = 'Nepodarilo sa načítať údaje z backendu. Používam localStorage.'
    setTimeout(() => (notification.value = ''), 3000)
  }
}

async function saveChanges() {
  try {
    const payload = {
      phone: editable.value.phone,
      alternative_email: editable.value.alternative_email
    }

    const res = await axios.put(`${API_BASE}/garant/profile`, payload, { headers: authHeaders() })
    const p = res.data?.user || res.data || {}

    const current = JSON.parse(localStorage.getItem('user') || '{}')
    const updated = {
      ...current,
      phone: p.phone ?? payload.phone,
      alternative_email: p.alternative_email ?? payload.alternative_email
    }
    localStorage.setItem('user', JSON.stringify(updated))

    confirmVisible.value = false
    notification.value = 'Zmeny boli uložené.'
    setTimeout(() => (notification.value = ''), 2500)

    await loadGarant()
  } catch (e) {
    console.error('saveChanges failed:', e)
    confirmVisible.value = false
    notification.value = 'Uloženie zlyhalo. Skontroluj backend endpoint /api/garant/profile.'
    setTimeout(() => (notification.value = ''), 3500)
  }
}

async function saveNotificationSettings() {
  try {
    const payload = {
      notify_new_request: !!notifications.value.notify_new_request,
      notify_approved: !!notifications.value.notify_approved,
      notify_rejected: !!notifications.value.notify_rejected,
      notify_profile_change: !!notifications.value.notify_profile_change
    }

    await axios.put(`${API_BASE}/garant/notifications`, payload, { headers: authHeaders() })

    const current = JSON.parse(localStorage.getItem('user') || '{}')
    localStorage.setItem('user', JSON.stringify({ ...current, ...payload }))

    notification.value = 'Notifikačné nastavenia boli uložené.'
    setTimeout(() => (notification.value = ''), 2500)

    await loadGarant()
  } catch (e) {
    console.error('saveNotificationSettings failed:', e)
    notification.value = 'Uloženie notifikácií zlyhalo. Skontroluj backend endpoint /api/garant/notifications.'
    setTimeout(() => (notification.value = ''), 3500)
  }
}

function confirmNotificationSave() {
  saveNotificationSettings().finally(() => {
    notifyConfirmVisible.value = false
  })
}

async function changePassword() {
  // Poznámka: endpoint uprav podľa backend route (napr. /api/change-password alebo /api/auth/change-password)
  // Tu používame najjednoduchšiu variantu.
  const payload = {
    current_password: passwordForm.value.current_password,
    new_password: passwordForm.value.new_password,
    new_password_confirmation: passwordForm.value.new_password_confirmation
  }

  try {
    await axios.post(`${API_BASE}/change-password`, payload, { headers: authHeaders() })

    notification.value = 'Heslo bolo úspešne zmenené.'
    setTimeout(() => (notification.value = ''), 2500)

    // Vyčistenie formulára
    passwordForm.value.current_password = ''
    passwordForm.value.new_password = ''
    passwordForm.value.new_password_confirmation = ''
  } catch (e) {
    console.error('changePassword failed:', e)

    // Ak backend vracia validačné chyby, ukážeme aspoň zmysluplnú hlášku
    const msg = e?.response?.data?.message
      || e?.response?.data?.error
      || 'Zmena hesla zlyhala. Skontroluj endpoint /api/change-password (alebo jeho názov v backende).'

    notification.value = msg
    setTimeout(() => (notification.value = ''), 4000)
  }
}

function confirmPasswordChange() {
  changePassword().finally(() => {
    passwordConfirmVisible.value = false
  })
}

onMounted(loadGarant)
</script>


<style scoped>
.page-controls {
  max-width: 1100px;
  margin: 24px auto 0;
  padding: 0 20px;
  display: flex;
  align-items: center;
}
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
  background: rgba(0,0,0,0.35);
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

.back-button {
  margin-bottom: 0;
  background: #ffffff;
  border: 1px solid #0b6b37;
  color: #0b6b37;
  padding: 8px 14px;
  border-radius: 6px;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-weight: 600;
}

.back-button:hover {
  background: #f0f6f2;
}

.back-button:active {
  transform: translateY(1px);
}

.notification-bar {
  background: #e8f7ee;
  border: 1px solid #1d4d2d;
  padding: 10px 16px;
  border-radius: 6px;
  color: #1d4d2d;
  margin-bottom: 20px;
}

.settings-layout {
  display: flex;
  gap: 30px;
  max-width: 1100px;
  margin: 20px auto;
}

.settings-sidebar {
  width: 220px;
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

.field input[type="checkbox"] {
  margin-right: 10px;
  transform: scale(1.2);
}
</style>