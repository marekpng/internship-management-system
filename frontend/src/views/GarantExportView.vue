<template>
  <div class="page">
    <h1 class="page-title">Export údajov o praxi</h1>

    <!-- FILTER -->
    <div class="card">
      <form class="filters" @submit.prevent="loadPreview">
        <div class="field">
          <label>Akademický rok</label>
          <select v-model="filters.academic_year">
            <option value="">Všetky</option>
            <option v-for="year in academicYears" :key="year" :value="year">
              {{ year }}
            </option>
          </select>
        </div>

        <div class="field">
          <label>Semester</label>
          <select v-model="filters.semester">
            <option value="">Všetky</option>
            <option v-for="sem in semesters" :key="sem.value" :value="sem.value">
              {{ sem.label }}
            </option>
          </select>
        </div>

        <div class="field">
          <label>Študijný odbor</label>
          <select v-model="filters.study_field">
            <option value="">Všetky</option>
            <option v-for="field in studyFields" :key="field" :value="field">
              {{ field }}
            </option>
          </select>
        </div>

        <div class="field field-status">
          <label>Stav praxe</label>
          <div class="checkboxes">
            <label v-for="status in statuses" :key="status.value">
              <input
                type="checkbox"
                :value="status.value"
                v-model="filters.status"
              />
              <span>{{ status.label }}</span>
            </label>
          </div>
        </div>

        <div class="actions">
          <button type="submit" :disabled="loading">
            {{ loading ? 'Načítavam…' : 'Zobraziť náhľad' }}
          </button>

          <button
            type="button"
            class="btn-export"
            @click="downloadCsv"
            :disabled="downloading"
          >
            {{ downloading ? 'Exportujem…' : 'Exportovať do CSV' }}
          </button>
        </div>
      </form>
    </div>

    <!-- PREVIEW -->
    <div class="card preview">
      <h2>Náhľad údajov</h2>

      <p v-if="loading">Načítavam údaje…</p>
      <p v-else-if="rows.length === 0">
        Žiadne záznamy pre zvolené filtre.
      </p>

      <table v-else>
        <thead>
          <tr>
            <th>Meno študenta</th>
            <th>Študijný odbor</th>
            <th>Firma</th>
            <th>Dátum začiatku</th>
            <th>Dátum konca</th>
            <th>Stav</th>
            <th>Akad. rok</th>
            <th>Semester</th>
            <th>Školiteľ</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(row, index) in rows" :key="index">
            <td>{{ row.student_name }}</td>
            <td>{{ row.study_field || '—' }}</td>
            <td>{{ row.company }}</td>
            <td>{{ row.start_date }}</td>
            <td>{{ row.end_date }}</td>
            <td>{{ row.status }}</td>
            <td>{{ row.academic_year }}</td>
            <td>{{ row.semester }}</td>
            <td>{{ row.supervisor }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const filters = ref({
  academic_year: '',
  semester: '',
  study_field: '',
  status: [],
})

const rows = ref([])
const loading = ref(false)
const downloading = ref(false)

const academicYears = ref(['2023/2024', '2024/2025'])

const semesters = ref([
  { value: 'Zimný', label: 'Zimný semester' },
  { value: 'Letný', label: 'Letný semester' },
])

const statuses = ref([
  { value: 'Vytvorená', label: 'Vytvorená' },
  { value: 'Schválená', label: 'Schválená' },
  { value: 'Zamietnutá', label: 'Zamietnutá' },
])

const studyFields = ref([])

function authHeaders() {
  const token = localStorage.getItem('access_token')
  return token ? { Authorization: `Bearer ${token}` } : {}
}

async function loadStudyFields() {
  const res = await axios.get('/api/garant/export/study-fields', {
    headers: authHeaders(),
  })
  studyFields.value = res.data?.data || []
}

async function loadPreview() {
  loading.value = true
  try {
    const res = await axios.get('/api/garant/export/preview', {
      params: filters.value,
      headers: authHeaders(),
    })
    rows.value = res.data?.data || []
  } finally {
    loading.value = false
  }
}

async function downloadCsv() {
  downloading.value = true
  try {
    const res = await axios.get('/api/garant/export/csv', {
      params: filters.value,
      responseType: 'blob',
      headers: authHeaders(),
    })

    const blob = new Blob([res.data], { type: 'text/csv;charset=utf-8;' })
    const url = window.URL.createObjectURL(blob)

    const link = document.createElement('a')
    link.href = url
    link.setAttribute('download', 'export_praxe.csv')

    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
    window.URL.revokeObjectURL(url)
  } finally {
    downloading.value = false
  }
}

onMounted(async () => {
  await loadStudyFields()
  await loadPreview()
})
</script>

<style scoped>
.page {
  min-height: 100vh;
  background: #f1f5f9;
  padding: 2rem 1.5rem;
}

.page-title {
  max-width: 1200px;
  margin: 0 auto 1.5rem;
  font-size: 2rem;
  font-weight: 700;
}

.card {
  max-width: 1200px;
  margin: 0 auto 1rem;
  background: #ffffff;
  border-radius: 12px;
  padding: 1.5rem;
  box-shadow: 0 8px 20px rgba(15, 23, 42, 0.08);
}

.filters {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 1rem 1.5rem;
}

.field {
  display: flex;
  flex-direction: column;
  gap: 0.35rem;
}

.field-status {
  grid-column: 1 / span 3;
}

.checkboxes {
  display: flex;
  gap: 2.5rem;
  margin-top: 0.5rem;
}

.checkboxes label {
  display: flex;
  align-items: center;
  gap: 0.4rem;
  font-weight: 500;
}

.actions {
  grid-column: 1 / span 3;
  display: flex;
  gap: 1rem;
  margin-top: 1rem;
}

button,
.btn-export {
  padding: 0.55rem 1.4rem;
  border-radius: 8px;
  border: none;
  font-weight: 600;
  font-size: 0.95rem;
  cursor: pointer;
}

button:disabled,
.btn-export:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

button {
  background: #2563eb;
  color: white;
}

.btn-export {
  background: #16a34a;
  color: white;
}

table {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.9rem;
}

th,
td {
  border: 1px solid #e5e7eb;
  padding: 0.4rem 0.5rem;
}

th {
  background: #f3f4f6;
  text-align: left;
}
</style>
