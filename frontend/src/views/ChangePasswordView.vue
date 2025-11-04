<template>
  <div class="login-body">
    <div class="login-container">
      <div class="login-box">
        <div class="login-logo">
          <img src="@/assets/logo-fpv.png" alt="UKF FPV logo" />
        </div>

        <h2>Zmena hesla</h2>
        <p class="info-text">Pred pokračovaním si musíte zmeniť svoje heslo.</p>

        <form @submit.prevent="changePassword">
          <label for="current_password">Aktuálne heslo</label>
          <input
            v-model.trim="form.current_password"
            type="password"
            id="current_password"
            placeholder="Zadaj aktuálne heslo"
            required
          />

          <label for="new_password">Nové heslo</label>
          <input
            v-model.trim="form.new_password"
            type="password"
            id="new_password"
            placeholder="Zadaj nové heslo"
            required
          />

          <label for="new_password_confirmation">Potvrdenie hesla</label>
          <input
            v-model.trim="form.new_password_confirmation"
            type="password"
            id="new_password_confirmation"
            placeholder="Zadaj nové heslo znova"
            required
          />

          <button type="submit" :disabled="loading">
            {{ loading ? 'Mením heslo...' : 'Zmeniť heslo' }}
          </button>
        </form>

        <p v-if="success" class="success">{{ success }}</p>
        <p v-if="error" class="error">{{ error }}</p>

        <p class="register-link">
          <router-link to="/login">Späť na prihlásenie</router-link>
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import axios from 'axios'
import { reactive, ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const form = reactive({
  email: '', // pridane
  current_password: '',
  new_password: '',
  new_password_confirmation: ''
})

const loading = ref(false)
const success = ref('')
const error = ref('')

// 🔹 Načítaj email z localStorage po načítaní komponentu
onMounted(() => {
  form.email = localStorage.getItem('user_email') || ''
})

async function changePassword() {
  error.value = ''
  success.value = ''
  loading.value = true

  try {
    const response = await axios.post('http://127.0.0.1:8000/api/change-password', {
      email: form.email,
      current_password: form.current_password,
      new_password: form.new_password,
      new_password_confirmation: form.new_password_confirmation
    })

    success.value = response.data.message || 'Heslo bolo úspešne zmenené.'
    setTimeout(() => router.push('/login'), 1500)
  } catch (err) {
    if (err.response?.data?.message) {
      error.value = err.response.data.message
    } else {
      error.value = 'Nepodarilo sa zmeniť heslo.'
    }
    console.error(err)
  } finally {
    loading.value = false
  }
}
</script>

<style src="../assets/login.css"></style>

<style scoped>
.info-text {
  color: #333;
  font-size: 14px;
  margin-bottom: 12px;
}
.success {
  color: #0a7b3e;
  font-weight: 600;
  margin-top: 12px;
}
.error {
  color: #b00020;
  font-weight: 600;
  margin-top: 12px;
}
.register-link {
  margin-top: 16px;
}
</style>
