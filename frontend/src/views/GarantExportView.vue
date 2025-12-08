<template>
  <div class="page">
    <h1 class="page-title">Export údajov o praxi</h1>

    <!-- KARTA S FILTRAMI -->
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
          <label>Študijný program</label>
          <select v-model="filters.study_program_id">
            <option value="">Všetky</option>
            <option
              v-for="program in studyPrograms"
              :key="program.id"
              :value="program.id"
            >
              {{ program.name }}
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
              {{ status.label }}
            </label>
          </div>
        </div>

        <div class="actions">
          <button type="submit">Zobraziť náhľad</button>

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

    <!-- KARTA S NÁHĽADOM -->
    <div class="card preview">
      <h2>Náhľad údajov</h2>

      <p v-if="loading">Načítavam údaje…</p>
      <p v-if="!loading && rows.length === 0">
        Žiadne záznamy pre zvolené filtre.
      </p>

      <table v-if="!loading && rows.length > 0">
        <thead>
          <tr>
            <th>Meno študenta</th>
            <th>Študijný program</th>
            <th>Trieda / ročník</th>
            <th>Firma</th>
            <th>Dátum začiatku</th>
            <th>Dátum konca</th>
            <th>Počet hodín</th>
            <th>Stav</th>
            <th>Akad. rok</th>
            <th>Semester</th>
            <th>Školiteľ</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(row, index) in rows" :key="index">
            <td>{{ row.student_name }}</td>
            <td>{{ row.study_program }}</td>
            <td>{{ row.class }}</td>
            <td>{{ row.company }}</td>
            <td>{{ row.start_date }}</td>
            <td>{{ row.end_date }}</td>
            <td>{{ row.hours }}</td>
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
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'

// FILTRE – stav
const filters = ref({
  academic_year: '',
  semester: '',
  study_program_id: '',
  status: [],
})

// DÁTA Z API
const rows = ref([])
const loading = ref(false)
const downloading = ref(false)

// Pomocné dáta
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

const studyPrograms = ref([])

async function loadStudyPrograms() {
  // ak nemáte API, nechaj prázdne / alebo dopln neskôr
  studyPrograms.value = []
}

async function loadPreview() {
  loading.value = true
  try {
    const res = await axios.get('/api/garant/export/preview', {
      params: filters.value,
    })
    rows.value = res.data.data || []
  } catch (err) {
    rows.value = []
  }
  loading.value = false
}

async function downloadCsv() {
  downloading.value = true
  try {
    const res = await axios.get('/api/garant/export/csv', {
      params: filters.value,
      responseType: 'blob',
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
  } catch (err) {
    console.error(err)
  }
  downloading.value = false
}

onMounted(async () => {
  await loadStudyPrograms()
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
