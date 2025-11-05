<template>
  <div class="landing">
    <HeaderComponent />

    <div class="login-body">
      <div class="login-container">
        <div class="login-box">
          <div class="login-logo">
            <img src="@/assets/logo-fpv.png" alt="UKF FPV logo" />
          </div>

          <h2>Úprava profilu</h2>
          <p class="info-text">Vyberte si, čo chcete zmeniť:</p>

          <!-- 🔘 Prepínač -->
          <div class="switch-buttons">
            <button
              :class="{ active: activeForm === 'email' }"
              @click="activeForm = 'email'"
            >
              ✉️ Zmeniť alternatívny email
            </button>
            <button
              :class="{ active: activeForm === 'password' }"
              @click="activeForm = 'password'"
            >
              🔒 Zmeniť heslo
            </button>
          </div>

          <hr class="divider" />

          <!-- 🔹 Animovaný prechod medzi formulármi -->
          <transition name="fade" mode="out-in">
            <!-- 🔹 Zmena alternatívneho emailu -->
            <form
              v-if="activeForm === 'email'"
              key="emailForm"
              @submit.prevent="updateProfile"
              class="form-section"
            >
              <label for="alternative_email">Alternatívny email</label>
              <input
                v-model.trim="form.alternative_email"
                type="email"
                id="alternative_email"
                placeholder="Zadaj nový alternatívny email"
              />

              <button type="submit" :disabled="loadingProfile">
                {{ loadingProfile ? 'Ukladám...' : 'Uložiť email' }}
              </button>
            </form>

            <!-- 🔹 Zmena hesla -->
            <form
              v-else-if="activeForm === 'password'"
              key="passwordForm"
              @submit.prevent="updatePassword"
              class="form-section"
            >
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

              <button type="submit" :disabled="loadingPassword">
                {{ loadingPassword ? 'Mením heslo...' : 'Zmeniť heslo' }}
              </button>
            </form>
          </transition>

          <p v-if="success" class="success">{{ success }}</p>
          <p v-if="error" class="error">{{ error }}</p>

          <p class="register-link">
            <router-link to="/">← Späť na hlavnú stránku</router-link>
          </p>
        </div>
      </div>
    </div>

    <FooterComponent />
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue'
import axios from 'axios'
import HeaderComponent from '@/components/HeaderComponent.vue'
import FooterComponent from '@/components/FooterComponent.vue'
import '@/assets/login.css'

const form = reactive({
  alternative_email: '',
  current_password: '',
  new_password: '',
  new_password_confirmation: ''
})

const activeForm = ref('')
const loadingProfile = ref(false)
const loadingPassword = ref(false)
const success = ref('')
const error = ref('')

// 🔹 Zmena alternatívneho emailu
async function updateProfile() {
  error.value = ''
  success.value = ''
  loadingProfile.value = true

  try {
    const token = localStorage.getItem('access_token')
    const res = await axios.post(
      'http://127.0.0.1:8000/api/update-profile',
      { alternative_email: form.alternative_email },
      { headers: { Authorization: `Bearer ${token}` } }
    )
    success.value = res.data.message || 'Email bol aktualizovaný.'
  } catch (err) {
    error.value = err.response?.data?.message || 'Chyba pri ukladaní emailu.'
  } finally {
    loadingProfile.value = false
  }
}

// 🔹 Zmena hesla
async function updatePassword() {
  error.value = ''
  success.value = ''

  if (form.new_password !== form.new_password_confirmation) {
    error.value = 'Nové heslá sa nezhodujú.'
    return
  }

  loadingPassword.value = true
  try {
    const token = localStorage.getItem('access_token')
    const res = await axios.post(
      'http://127.0.0.1:8000/api/change-password',
      {
        current_password: form.current_password,
        new_password: form.new_password,
        new_password_confirmation: form.new_password_confirmation
      },
      { headers: { Authorization: `Bearer ${token}` } }
    )

    success.value = res.data.message || 'Heslo bolo zmenené.'
    Object.assign(form, {
      current_password: '',
      new_password: '',
      new_password_confirmation: ''
    })
  } catch (err) {
    error.value =
      err.response?.data?.message || 'Nepodarilo sa zmeniť heslo.'
  } finally {
    loadingPassword.value = false
  }
}
</script>

<style scoped>
.info-text {
  color: #333;
  font-size: 15px;
  font-weight: 500;
  text-align: center;
  margin-bottom: 16px;
}

/* 🔹 Lepší layout prepínačov */
.switch-buttons {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 20px;
  flex-wrap: wrap;
  margin-bottom: 20px;
}

.switch-buttons button {
  flex: 1 1 180px;
  max-width: 220px;
  background-color: white;
  border: 2px solid #007b3e;
  color: #007b3e;
  font-weight: 600;
  padding: 12px 0;
  border-radius: 10px;
  cursor: pointer;
  text-align: center;
  transition: all 0.25s ease;
  font-size: 15px;
}

.switch-buttons button:hover {
  background-color: #e8f8ee;
  transform: translateY(-2px);
  box-shadow: 0 3px 8px rgba(0, 128, 0, 0.15);
}

.switch-buttons .active {
  background-color: #007b3e;
  color: white;
  border-color: #00662f;
  box-shadow: 0 3px 10px rgba(0, 128, 0, 0.3);
}

.switch-buttons .active:hover {
  background-color: #00994a;
}

.divider {
  margin: 24px 0;
  border: 0;
  border-top: 1px solid #ddd;
}

.form-section {
  display: flex;
  flex-direction: column;
  gap: 10px;
  animation: fadeIn 0.4s ease;
}

/* 🔹 Fade efekt */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

.success {
  color: #0a7b3e;
  font-weight: 600;
  margin-top: 16px;
}

.error {
  color: #b00020;
  font-weight: 600;
  margin-top: 16px;
}
</style>
