<template>
  <div class="landing">
    <HeaderComponent />
  <div class="page">
    <h1>Registrácia študenta</h1>

    <form class="form" @submit.prevent="submit">
      <div class="grid">
        <label>
          Meno *
          <input v-model.trim="form.first_name" required />
        </label>
        <label>
          Priezvisko *
          <input v-model.trim="form.last_name" required />
        </label>
      </div>

      <label>
        Adresa (ulica) *
        <input v-model.trim="form.street" required />
      </label>

      <div class="grid">
        <label>
          Mesto *
          <input v-model.trim="form.city" required />
        </label>
        <label>
          Číslo domu *
          <input v-model.trim="form.house_number" required />
        </label>
      </div>

      <div class="grid">
        <label>
          PSČ *
          <input v-model.trim="form.postal_code" required />
        </label>
        <label>
          Študentský email *
          <input type="email" v-model.trim="form.student_email" required />
        </label>
      </div>

      <div class="grid">
        <label>
          Alternatívny email
          <input type="email" v-model.trim="form.alternative_email"/>
        </label>
        <label>
          Telefón *
          <input v-model.trim="form.phone" required />
        </label>
      </div>

      <label>
        Študijný odbor *
        <input v-model.trim="form.study_field" required />
      </label>

      <div v-if="error" class="error">{{ error }}</div>
      <div v-if="success" class="success">{{ success }}</div>

      <button class="btn" :disabled="loading" type="submit">
        {{ loading ? 'Posielam...' : 'Odoslať registráciu' }}
      </button>
    </form>
  </div>
</div>
</template>

<script setup>
import { reactive, ref } from 'vue'
import axios from 'axios'
import '@/assets/landing.css'
import HeaderComponent from '@/components/HeaderComponent.vue'

const form = reactive({
  first_name: '',
  last_name: '',
  city: '',
  street: '',
  house_number: '',
  postal_code: '',
  student_email: '',
  alternative_email: '',
  phone: '',
  study_field: ''
})

const loading = ref(false)
const error = ref('')
const success = ref('')

function validateEmail(email) {
  return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)
}

function validatePhone(phone) {
  return /^[0-9+\s()-]{6,}$/.test(phone)
}

async function submit() {
  error.value = ''
  success.value = ''

  if (
    !form.first_name ||
    !form.last_name ||
    !form.street ||
    !form.city ||
    !form.house_number ||
    !form.postal_code ||
    !form.student_email ||
    !form.phone ||
    !form.study_field
  ) {
    error.value = 'Vyplň všetky povinné polia.'
    return
  }

  if (!validateEmail(form.student_email)) {
    error.value = 'Zadaj platný email.'
    return
  }

  if (form.alternative_email && !validateEmail(form.alternative_email)) {
    error.value = 'Zadaj platný alternatívny email.'
    return
  }

  if (!validatePhone(form.phone)) {
    error.value = 'Zadaj platné telefónne číslo.'
    return
  }

  const payload = {
    first_name: form.first_name,
    last_name: form.last_name,
    student_email: form.student_email,
    city: form.city,
    street: form.street,
    house_number: form.house_number,
    postal_code: form.postal_code,
    alternative_email: form.alternative_email ? form.alternative_email : null,
    phone: form.phone,
    study_field: form.study_field
  }

  loading.value = true
  try {
    const res = await axios.post('http://127.0.0.1:8000/api/register/student', payload)
    success.value = res.data.message || 'Registrácia prebehla úspešne. Skontroluj email.'
    Object.keys(form).forEach(k => (form[k] = ''))
  } catch (err) {
    if (err.response) {
      if (err.response.status === 422 && err.response.data.errors) {
        const msgs = Object.values(err.response.data.errors).flat().join(' ')
        error.value = msgs
      } else if (err.response.data && err.response.data.message) {
        error.value = err.response.data.message
      } else {
        error.value = `Server error (${err.response.status}).`
      }
    } else {
      error.value = 'Nepodarilo sa pripojiť k serveru.'
    }
    console.error(err)
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.page {
  max-width: 760px;
  margin: 48px auto;
  padding: 24px;
  background: #fff;
  border-radius: 12px;
  box-shadow: 0 6px 20px rgba(0,0,0,.08);
}
h1 {
  margin: 0 0 16px;
  color:#0a7b3e;
}
.form {
  display: grid;
  gap: 16px;
}
.grid {
  display: grid;
  gap: 16px;
  grid-template-columns: repeat(2, minmax(0,1fr));
}
label {
  display: grid;
  gap: 6px;
  font-weight: 600;
}
input {
  padding: 10px 12px;
  border:1px solid #d0d7de;
  border-radius: 8px;
}
.btn {
  width: fit-content;
  padding: 10px 16px;
  border-radius: 8px;
  background:#0a7b3e;
  color:#fff;
  border:0;
  cursor:pointer;
  transition: background-color 0.2s ease, color 0.2s ease, transform 0.05s ease;
}
.btn:hover:not(:disabled) {
  background: #086735;
  color: #fff;
}
.btn:active:not(:disabled) {
  transform: translateY(1px);
}
.btn:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}
.error {
  color: #b00020;
  font-weight: 600;
}
.success {
  color: #0a7b3e;
  font-weight: 600;
}
@media (max-width: 600px){
  .grid {
    grid-template-columns: 1fr;
  }
}
</style>
