<template>
  <div class="company-settings-page">
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
        <li :class="{ active: activeTab === 'data' }" @click="activeTab = 'data'">Údaje firmy</li>
        <li :class="{ active: activeTab === 'notifications' }" @click="activeTab = 'notifications'">Notifikácie</li>
        <li :class="{ active: activeTab === 'password' }" @click="activeTab = 'password'">Zmena hesla</li>
      </ul>
    </aside>

    <div class="settings-wrapper">
      <div class="notification-bar" v-if="notification">
        {{ notification }}
      </div>

      <div v-if="activeTab === 'data'">
        <h1>Firemné nastavenia</h1>

        <div class="info-bar">
          <p>Niektoré údaje nie je možné meniť, pretože sú súčasťou firemného záznamu v systéme.</p>
        </div>

        <section class="block">
          <h2>Firemné údaje (nemožno meniť)</h2>

          <div class="field">
            <label>Názov firmy</label>
            <input type="text" :value="company.company_name" disabled />
          </div>

          <div class="field">
            <label>IČO</label>
            <input type="text" :value="company.ico" disabled />
          </div>


          <div class="field">
            <label>Meno a priezvisko</label>
            <input type="text" :value="company.contact_person_name" disabled />
          </div>

          <div class="field">
            <label>Adresa</label>
            <input type="text" :value="`${company.street} ${company.house_number}, ${company.postal_code} ${company.city}`" disabled />
          </div>
        </section>

        <section class="block">
          <h2>Editovateľné údaje</h2>

          <div class="field">
            <label>Email</label>
            <input v-model="editable.email" type="email" />
          </div>

          <div class="field">
            <label>Telefón</label>
            <input v-model="editable.phone" type="text" />
          </div>

          <div class="field">
            <label>Alternatívny email</label>
            <input v-model="editable.notification_email" type="email" />
          </div>

          <div class="field">
            <label>Alternatívne telefónne číslo</label>
            <input v-model="editable.notification_phone" type="text" />
          </div>

          <button class="btn-save" @click="confirmVisible = true">
            Uložiť zmeny
          </button>
        </section>

        <div v-if="confirmVisible" class="modal-backdrop">
          <div class="modal">
            <h3>Ste si istí, že chcete uložiť zmeny?</h3>
            <p>Tieto údaje sa aktualizujú vo vašom firemnom profile.</p>

            <div class="modal-actions">
              <button class="btn-cancel" @click="confirmVisible = false">Zrušiť</button>
              <button class="btn-confirm" @click="saveChanges">Potvrdiť</button>
            </div>
          </div>
        </div>
      </div>

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

      <div v-if="activeTab === 'password'">
        <h1>Zmena hesla</h1>

        <div class="info-bar">
          <p>Heslo slúži na prihlásenie do systému a môžete ho tu zmeniť.</p>
        </div>

        <section class="block">
          <div class="field">
            <label>Aktuálne heslo</label>
            <input type="password" v-model="passwordForm.current_password" />
          </div>

          <div class="field">
            <label>Nové heslo</label>
            <input type="password" v-model="passwordForm.new_password" />
          </div>

          <div class="field">
            <label>Potvrdenie nového hesla</label>
            <input type="password" v-model="passwordForm.new_password_confirmation" />
          </div>

          <button class="btn-save" @click="passwordConfirmVisible = true">
            Uložiť zmenu hesla
          </button>
        </section>

        <div v-if="passwordConfirmVisible" class="modal-backdrop">
          <div class="modal">
            <h3>Ste si istí, že chcete zmeniť heslo?</h3>
            <p>Po zmene hesla sa budete prihlasovať už len novým heslom.</p>

            <div class="modal-actions">
              <button class="btn-cancel" @click="passwordConfirmVisible = false">Zrušiť</button>
              <button class="btn-confirm" @click="changePassword">Potvrdiť</button>
            </div>
          </div>
        </div>
      </div>
    </div>
    </div>
  </div>
</template>

<script setup>
import CompanyNavBar from '@/components/icons/CompanyNavBar.vue'
import { ref, onMounted } from 'vue'
import axios from 'axios'

const company = ref({
  company_name: '',
  street: '',
  house_number: '',
  city: '',
  postal_code: '',
  email: '',
  phone: '',
  contact_person_name: '',
  contact_person_email: '',
  contact_person_phone: ''
})

const editable = ref({
  email: '',
  phone: '',
  website: '',
  notification_email: '',
  notification_phone: ''
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
const passwordForm = ref({ current_password: '', new_password: '', new_password_confirmation: '' })

async function loadCompany() {
  const token = localStorage.getItem('access_token')
  const response = await axios.get('http://localhost:8000/api/company/profile', {
    headers: token ? { Authorization: `Bearer ${token}` } : {}
  })

  company.value = response.data

  editable.value.email = response.data.email
  editable.value.phone = response.data.phone
  editable.value.website = response.data.website ?? ''
  editable.value.notification_email = response.data.contact_person_email ?? ''
  editable.value.notification_phone = response.data.contact_person_phone ?? ''

  notifications.value.notify_new_request = response.data.notify_new_request == 1;
  notifications.value.notify_approved = response.data.notify_approved == 1;
  notifications.value.notify_rejected = response.data.notify_rejected == 1;
  notifications.value.notify_profile_change = response.data.notify_profile_change == 1;
}

async function saveChanges() {
  const token = localStorage.getItem('access_token')

  await axios.put(
    'http://localhost:8000/api/company/profile',
    {
      email: editable.value.email,
      phone: editable.value.phone,
      website: editable.value.website,
      contact_person_email: editable.value.notification_email,
      contact_person_phone: editable.value.notification_phone,
    },
    {
      headers: token ? { Authorization: `Bearer ${token}` } : {}
    }
  )

  confirmVisible.value = false
  notification.value = 'Zmeny boli úspešne uložené.'
  setTimeout(() => notification.value = '', 3000)
  await loadCompany()
}

async function saveNotificationSettings() {
  const token = localStorage.getItem('access_token')

  await axios.put(
    'http://localhost:8000/api/company/notifications',
    {
      notify_new_request: notifications.value.notify_new_request ? 1 : 0,
      notify_approved: notifications.value.notify_approved ? 1 : 0,
      notify_rejected: notifications.value.notify_rejected ? 1 : 0,
      notify_profile_change: notifications.value.notify_profile_change ? 1 : 0,
    },
    { headers: { Authorization: `Bearer ${token}` } }
  );

  await loadCompany();

  notification.value = 'Notifikácie boli uložené.'
  setTimeout(() => notification.value = '', 3000)
}

async function confirmNotificationSave() {
  try {
    await saveNotificationSettings()
  } catch (error) {
    console.error('Chyba pri ukladaní notifikácií:', error)
    notification.value = error?.response?.data?.message || 'Nepodarilo sa uložiť notifikácie. Skúste to prosím znova.'
    setTimeout(() => (notification.value = ''), 3000)
  } finally {
    notifyConfirmVisible.value = false
  }
}

async function changePassword() {
  const token = localStorage.getItem('access_token')
  try {
    await axios.post(
      'http://localhost:8000/api/change-password',
      {
        current_password: passwordForm.value.current_password,
        password: passwordForm.value.new_password,
        password_confirmation: passwordForm.value.new_password_confirmation
      },
      {
        headers: token ? { Authorization: `Bearer ${token}` } : {}
      }
    )
    passwordConfirmVisible.value = false
    passwordForm.value.current_password = ''
    passwordForm.value.new_password = ''
    passwordForm.value.new_password_confirmation = ''
    notification.value = 'Heslo bolo úspešne zmenené.'
    setTimeout(() => notification.value = '', 3000)
  } catch (error) {
    passwordConfirmVisible.value = false
    notification.value = error?.response?.data?.message || 'Nepodarilo sa zmeniť heslo. Skúste to prosím znova.'
    setTimeout(() => notification.value = '', 3000)
  }
}

onMounted(loadCompany)
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

.page-controls {
  max-width: 1100px;
  margin: 32px auto 0;
  padding: 0 20px;
  display: flex;
  align-items: center;
}

.back-button {
  padding: 8px 14px;
  border-radius: 6px;
  border: 1px solid #e6e6e6;
  background: #ffffff;
  font-size: 14px;
  font-weight: 600;
  color: #334155;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  transition: background 0.15s ease, border-color 0.15s ease, transform 0.05s ease;
}

.back-button:hover {
  background: #f7f7f7;
  border-color: #d7d7d7;
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