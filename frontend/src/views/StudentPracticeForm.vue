<template>
  <div class="practice-form">
    <div class="back-button" @click="goBack">← Späť</div>
    <h2>Vytvorenie nového záznamu praxe</h2>

    <form @submit.prevent="submitForm">
      <!-- Výber firmy -->
      <label for="company">Firma</label>
      <input
        id="company"
        v-model="companySearch"
        type="text"
        placeholder="Vyhľadaj firmu podľa názvu..."
        @input="filterCompanies"
        @change="selectCompany"
        list="companyList"
        required
      />
      <datalist id="companyList">
        <option
          v-for="company in filteredCompanies"
          :key="company.id"
          :value="company.company_name || company.name"
        />
      </datalist>

      <!-- Rok a semester -->
      <label for="year">Rok</label>
      <input id="year" v-model="year" type="number" required />

      <label for="semester">Semester</label>
      <select id="semester" v-model="semester" required>
        <option value="">Vyber semester</option>
        <option value="zimný">Zimný</option>
        <option value="letný">Letný</option>
      </select>

      <!-- Dátumy praxe -->
      <label for="start_date">Začiatok praxe</label>
      <input
        id="start_date"
        v-model="start_date"
        type="date"
        required
      />

      <label for="end_date">Koniec praxe</label>
      <input
        id="end_date"
        v-model="end_date"
        type="date"
        required
      />

      <!-- Odoslanie -->
      <button type="submit">Uložiť prax</button>
    </form>

    <!-- Správy o stave -->
    <p v-if="successMessage" class="success">{{ successMessage }}</p>
    <p v-if="errorMessage" class="error">{{ errorMessage }}</p>

    <!-- 🔽 Nové tlačidlo na stiahnutie PDF dohody -->
    <div v-if="pdfDownloadLink" class="pdf-download">
      <button class="download-btn" @click="downloadPdf">
        📄 Stiahnuť dohodu o praxi
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'

const router = useRouter()
const goBack = () => router.back()

const companySearch = ref('')
const companies = ref([])
const filteredCompanies = ref([])
const selectedCompany = ref(null)
const year = ref(new Date().getFullYear())
const semester = ref('')
const successMessage = ref('')
const errorMessage = ref('')

const start_date = ref('')
const end_date = ref('')
const pdfDownloadLink = ref('') // 🔽 pridane pre uloženie URL PDF

const token = localStorage.getItem('access_token')
const user = JSON.parse(localStorage.getItem('user'))

// Načítanie všetkých firiem
onMounted(async () => {
  try {
    const res = await axios.get('http://localhost:8000/api/companies', {
      headers: token ? { Authorization: `Bearer ${token}` } : {}
    })
    const payload = Array.isArray(res.data) ? res.data : (res.data?.data ?? [])
    companies.value = payload
    filteredCompanies.value = payload

    if (companies.value.length === 1) {
      selectedCompany.value = companies.value[0]
      companySearch.value = companies.value[0].company_name || companies.value[0].name
      filteredCompanies.value = [companies.value[0]]
    }
  } catch (error) {
    console.error('Chyba pri načítaní firiem:', error)
  }
})

// Filtrovanie podľa názvu
const filterCompanies = () => {
  const search = (companySearch.value || '').toLowerCase()
  filteredCompanies.value = companies.value.filter(c => {
    const label = (c.company_name || c.name || '').toLowerCase()
    return label.includes(search)
  })
}

// Výber firmy zo zoznamu
const selectCompany = () => {
  const label = companySearch.value
  const found = companies.value.find(c => (c.company_name === label) || (c.name === label))
  if (found) selectedCompany.value = found
}

// Odoslanie formulára
const submitForm = async () => {
  let selected = selectedCompany.value
  if (!selected) {
    selected = companies.value.find(
      c => c.company_name === companySearch.value || c.name === companySearch.value
    )
  }
  if (!selected) {
    errorMessage.value = 'Prosím, vyber firmu zo zoznamu.'
    successMessage.value = ''
    return
  }

  try {
    const response = await axios.post(
      'http://localhost:8000/api/internships',
      {
        company_id: selected.id,
        student_id: user?.id || 1,
        status: 'Vytvorená',
        year: year.value,
        semester: semester.value,
        start_date: start_date.value,
        end_date: end_date.value
      },
      {
        headers: { Authorization: `Bearer ${token}` }
      }
    )

    successMessage.value = 'Prax bola úspešne vytvorená!'
    errorMessage.value = ''
    
    // 🔽 Po vytvorení si uložíme link na PDF dohodu (napr. ID novej praxe)
    const internshipId = response.data?.internship?.id
    if (internshipId) {
      pdfDownloadLink.value = `http://localhost:8000/api/internships/${internshipId}/agreement/download`
    }

  } catch (error) {
    successMessage.value = ''
    errorMessage.value = 'Nepodarilo sa vytvoriť prax.'
    if (error.response && error.response.data) {
      console.error('Backend validation errors:', error.response.data)
    } else {
      console.error('Chyba:', error)
    }
  }
}

// 🔽 Funkcia pre stiahnutie PDF
const downloadPdf = () => {
  if (!pdfDownloadLink.value) return
  window.open(pdfDownloadLink.value, '_blank')
}
</script>

<style scoped>
.practice-form {
  max-width: 600px;
  margin: 0 auto;
  background: #fff;
  padding: 24px;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}
.success { color: green; }
.error { color: red; }

.back-button {
  cursor: pointer;
  font-size: 18px;
  font-weight: 500;
  color: #1b5e20;
  margin-bottom: 12px;
  display: inline-block;
  transition: color 0.2s ease;
}

.back-button:hover {
  color: #2e7d32;
}

.pdf-download {
  margin-top: 24px;
  text-align: center;
}

.download-btn {
  background-color: #1b5e20;
  color: white;
  border: none;
  border-radius: 8px;
  padding: 10px 16px;
  font-size: 15px;
  cursor: pointer;
  transition: background-color 0.2s ease;
}

.download-btn:hover {
  background-color: #2e7d32;
}
</style>
