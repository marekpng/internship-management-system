<template>
  <CompanyNavBar />
  <button class="back-button" @click="$router.back()">← Späť</button>
  <div class="settings-wrapper">
    <h1>Firemné nastavenia</h1>
    <div class="info-bar">
      <p>Niektoré údaje nie je možné meniť, pretože sú súčasťou firemného záznamu v systéme.</p>
    </div>

    <div class="notification-bar" v-if="notification">
      {{ notification }}
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
        <label>DIČ</label>
        <input type="text" :value="company.dic" disabled />
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
        <label>Notifikačný email</label>
        <input v-model="editable.notification_email" type="email" />
      </div>

      <div class="field">
        <label>Notifikačné telefónne číslo</label>
        <input v-model="editable.notification_phone" type="text" />
      </div>

      <div class="field">
        <label>Webstránka</label>
        <input v-model="editable.website" type="text" />
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
const notification = ref('')

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
}

async function saveChanges() {
  const token = localStorage.getItem('access_token')

  await axios.put(
    'http://localhost:8000/api/company/profile',
    {
      email: editable.value.email,
      phone: editable.value.phone,
      website: editable.value.website,
      notification_email: editable.value.notification_email,
      notification_phone: editable.value.notification_phone,
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

onMounted(loadCompany)
</script>

<style scoped>
.settings-wrapper {
  max-width: 800px;
  margin: 40px auto;
  padding: 20px;
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

.back-button {
  margin: 20px 0;
  background: none;
  border: none;
  font-size: 16px;
  cursor: pointer;
  color: #1d4d2d;
}

.notification-bar {
  background: #e8f7ee;
  border: 1px solid #1d4d2d;
  padding: 10px 16px;
  border-radius: 6px;
  color: #1d4d2d;
  margin-bottom: 20px;
}
</style>