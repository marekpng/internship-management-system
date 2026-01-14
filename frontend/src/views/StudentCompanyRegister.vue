<template>
  <div class="overlay">
    <div class="container">
      <!-- Navbar (rovnaký ako v ostatných views) -->
      <CompanyNavBar />

      <div class="welcome-bar">
        <span class="portal-badge">Študentský portál</span>
        <h1>Registrácia firmy</h1>
        <p>Zadaj údaje o firme, v ktorej budeš vykonávať odbornú prax.</p>
      </div>

      <div class="section">
        <div class="section-title">Údaje o firme</div>
        <div class="section-description">
          Vyplň názov firmy a IČO (8 číslic).
        </div>

        <form class="form" @submit.prevent="submit">
          <div class="field">
            <label for="company_name">Názov firmy</label>
            <input
              id="company_name"
              v-model.trim="form.company_name"
              type="text"
              placeholder="Napr. ABC Solutions s.r.o."
              required
            />
          </div>

          <div class="field">
            <label for="ico">IČO firmy</label>
            <input
              id="ico"
              v-model.trim="form.ico"
              type="text"
              inputmode="numeric"
              maxlength="8"
              placeholder="12345678"
              required
            />
            <small class="hint">IČO musí mať presne 8 číslic.</small>
          </div>

          <div v-if="error" class="alert error">
            {{ error }}
          </div>
          <div v-if="success" class="alert success">
            {{ success }}
          </div>

          <div class="actions">
            <button class="btn primary" type="submit" :disabled="loading">
              {{ loading ? 'Ukladám…' : 'Uložiť firmu' }}
            </button>

            <button class="btn" type="button" @click="goBack" :disabled="loading">
              Späť
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import CompanyNavBar from '@/components/icons/CompanyNavBar.vue'

const router = useRouter()

const loading = ref(false)
const error = ref('')
const success = ref('')

const form = reactive({
  company_name: '',
  ico: '',
})

// ✅ nastaviteľné cez Vite env (odporúčané)
const API_BASE_URL =
  import.meta.env.VITE_API_BASE_URL ||
  'http://localhost:8000' // fallback (uprav podľa toho kde ti beží backend)

// Helper: načítanie usera z localStorage
const getStoredUser = () => {
  const raw = localStorage.getItem('user')
  if (!raw) return null
  try {
    return JSON.parse(raw)
  } catch {
    return null
  }
}

onMounted(() => {
  const token = localStorage.getItem('access_token')
  const user = getStoredUser()

  if (!token || !user) {
    router.push({ name: 'login' })
    return
  }

  // ✅ ak už má študent uloženú firmu, predvyplň hodnoty
  if (user.company_name) form.company_name = user.company_name
  if (user.ico) form.ico = user.ico
})

const validate = () => {
  error.value = ''
  success.value = ''

  if (!form.company_name || form.company_name.length < 2) {
    error.value = 'Zadaj názov firmy.'
    return false
  }

  const icoDigits = form.ico.replace(/\D/g, '')
  if (icoDigits.length !== 8) {
    error.value = 'IČO musí obsahovať presne 8 číslic.'
    return false
  }

  form.ico = icoDigits
  return true
}

const submit = async () => {
  if (!validate()) return

  loading.value = true
  error.value = ''
  success.value = ''

  try {
    const token = localStorage.getItem('access_token')
    if (!token) {
      router.push({ name: 'login' })
      return
    }

    // ✅ endpoint z backendu: POST /api/student/company
    const response = await fetch(`${API_BASE_URL}/student/company`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        Authorization: `Bearer ${token}`,
        Accept: 'application/json',
      },
      body: JSON.stringify({
        company_name: form.company_name,
        ico: form.ico,
      }),
    })

    const data = await response.json().catch(() => ({}))

    if (!response.ok) {
      const msg =
        data?.message ||
        (data?.errors ? Object.values(data.errors).flat().join(' ') : null) ||
        'Nepodarilo sa uložiť firmu.'
      throw new Error(msg)
    }

    success.value = data?.message || 'Firma bola úspešne uložená.'

    // ✅ update localStorage.user aby sa údaje hneď prejavili v appke
    const user = getStoredUser() || {}
    const updated = {
      ...user,
      company_name: data?.company?.company_name ?? form.company_name,
      ico: data?.company?.ico ?? form.ico,
    }
    localStorage.setItem('user', JSON.stringify(updated))
  } catch (e) {
    error.value = e?.message || 'Nastala chyba pri ukladaní firmy.'
  } finally {
    loading.value = false
  }
}

const goBack = () => {
  // uprav podľa svojich route names
  if (router.hasRoute('studentPractice')) {
    router.push({ name: 'studentPractice' })
  } else {
    router.back()
  }
}
</script>


<style src="../assets/style.css"></style>

<style>
.portal-badge {
  display: inline-block;
  background: #dff2e7;
  color: #1d4d2d;
  font-size: 12px;
  font-weight: 600;
  padding: 4px 10px;
  border-radius: 999px;
  margin-bottom: 10px;
}

.welcome-bar {
  background: #e8f7ee;
  border: 1px solid #1d4d2d;
  padding: 18px 22px;
  border-radius: 10px;
  margin-bottom: 30px;
}

.welcome-bar h1 {
  margin: 0 0 6px 0;
  font-size: 26px;
  color: #1d4d2d;
}

.welcome-bar p {
  margin: 0;
  font-size: 15px;
  color: #355f44;
}

.form {
  margin-top: 14px;
}

.field {
  display: flex;
  flex-direction: column;
  gap: 8px;
  margin-bottom: 14px;
}

.field label {
  font-weight: 600;
  color: #1d4d2d;
}

.field input {
  padding: 10px 12px;
  border: 1px solid #1d4d2d;
  border-radius: 10px;
  background: #fff;
}

.field input:focus {
  box-shadow: 0 0 0 3px rgba(29, 77, 45, 0.15);
}

.hint {
  font-size: 12px;
  color: #355f44;
}

.actions {
  display: flex;
  gap: 10px;
  margin-top: 16px;
}

.btn {
  border: 1px solid #1d4d2d;
  background: #fff;
  color: #1d4d2d;
  padding: 10px 14px;
  border-radius: 10px;
  font-weight: 600;
  cursor: pointer;
}

.btn.primary {
  background: #1d4d2d;
  color: #fff;
}

.btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.alert {
  margin-top: 10px;
  padding: 10px 12px;
  border-radius: 10px;
  font-weight: 600;
}

.alert.error {
  background: #fde8e8;
  border: 1px solid #b42318;
  color: #7a271a;
}

.alert.success {
  background: #e7f6ee;
  border: 1px solid #1d4d2d;
  color: #1d4d2d;
}
</style>
