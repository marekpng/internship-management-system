<template>
  <div class="page">
    <h1>Registrácia firmy</h1>

    <form class="form" @submit.prevent="submit">
      <label>
        Názov firmy *
        <input v-model.trim="form.company_name" required />
      </label>

      <label>
        Adresa firmy
        <input v-model.trim="form.company_address" />
      </label>

      <div class="grid">
        <label>
          Kontaktná osoba *
          <input v-model.trim="form.contact_person_name" required />
        </label>
        <label>
          Kontaktný email *
          <input type="email" v-model.trim="form.contact_person_email" required />
        </label>
      </div>

      <label>
        Telefón
        <input v-model.trim="form.contact_person_phone" />
      </label>

      <div class="grid">
        <label>
          Heslo *
          <input type="password" v-model.trim="form.password" required />
        </label>
        <label>
          Potvrdenie hesla *
          <input type="password" v-model.trim="form.password_confirmation" required />
        </label>
      </div>

      <div v-if="error" class="error">{{ error }}</div>
      <div v-if="success" class="success">{{ success }}</div>

      <button class="btn" :disabled="loading" type="submit">
        {{ loading ? 'Posielam...' : 'Odoslať registráciu' }}
      </button>
    </form>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue'
import axios from 'axios'

const form = reactive({
  company_name: '',
  company_address: '',
  contact_person_name: '',
  contact_person_email: '',
  contact_person_phone: '',
  password: '',
  password_confirmation: ''
})

const loading = ref(false)
const error = ref('')
const success = ref('')

function validateEmail(email) {
  return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)
}

async function submit() {
  error.value = ''
  success.value = ''

  if (!form.company_name) {
    error.value = 'Názov firmy je povinný.'
    return
  }
  if (!form.contact_person_name) {
    error.value = 'Kontaktná osoba je povinná.'
    return
  }
  if (!validateEmail(form.contact_person_email)) {
    error.value = 'Zadaj platný kontaktný email.'
    return
  }
  if (!form.password || form.password.length < 8) {
    error.value = 'Heslo musí mať aspoň 8 znakov.'
    return
  }
  if (form.password !== form.password_confirmation) {
    error.value = 'Heslá sa nezhodujú.'
    return
  }

  loading.value = true

  try {
    const payload = { ...form }
    const res = await axios.post('http://127.0.0.1:8000/api/register/company', payload)
    success.value = res.data.message || 'Registrácia prebehla úspešne. Skontrolujte email (aktivačný link).'
    Object.keys(form).forEach(k => form[k] = '')
  } catch (err) {
    if (err.response?.status === 422 && err.response.data.errors) {
      const msgs = Object.values(err.response.data.errors).flat().join(' ')
      error.value = msgs
    } else if (err.response?.data?.message) {
      error.value = err.response.data.message
    } else {
      error.value = 'Chyba servera alebo žiadna odpoveď.'
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
