<template>
  <div class="my-practice-view">
    <div class="back-button" @click="goBack">← Späť</div>
    <h2>Moja prax</h2>

    <div v-if="loading" class="loading">Načítavam údaje o praxi...</div>
    <div v-else-if="practices.length" class="practice-list">
      <div v-for="p in practices" :key="p.id" class="practice-details">
        <template v-if="editingPracticeId !== p.id">
          <p><strong>Firma:</strong> {{  p.company_name }}</p>
          <p><strong>Rok:</strong> {{ p.year }}</p>
          <p><strong>Semester:</strong> {{ p.semester }}</p>
          <p><strong>Začiatok praxe:</strong> {{ formatDate(p.start_date) }}</p>
          <p><strong>Koniec praxe:</strong> {{ formatDate(p.end_date) }}</p>
          <button @click="startEditing(p)">Upraviť prax</button>
        </template>
        <template v-else>
          <form @submit.prevent="submitEdit(p.id)">
            <p><strong>Firma:</strong> {{  p.company_name }}</p>
            <p>
              <label for="year">Rok:</label>
              <input id="year" type="number" v-model.number="editForm.year" required disabled />
            </p>
            <p>
              <label for="semester">Semester:</label>
              <input id="semester" type="text" v-model="editForm.semester" required disabled />
            </p>
            <p>
              <label for="start_date">Začiatok praxe:</label>
              <input id="start_date" type="date" v-model="editForm.start_date" required />
            </p>
            <p>
              <label for="end_date">Koniec praxe:</label>
              <input id="end_date" type="date" v-model="editForm.end_date" required />
            </p>
            <button type="submit">Uložiť</button>
            <button type="button" @click="cancelEditing">Zrušiť</button>
          </form>
        </template>
      </div>
    </div>
    <div v-else class="no-practice">
      Žiadna prax zatiaľ nebola vytvorená.
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'

const router = useRouter()
const practices = ref([])
const loading = ref(true)
const editingPracticeId = ref(null)
const editForm = ref({
  year: null,
  semester: '',
  start_date: '',
  end_date: ''
})

const goBack = () => router.back()

const formatDate = (date) => {
  if (!date) return '—'
  return new Date(date).toLocaleDateString('sk-SK')
}

const loadPractices = async () => {
  loading.value = true
  try {
    const token = localStorage.getItem('access_token')
    const response = await axios.get('http://localhost:8000/api/internships/my', {
      headers: { Authorization: `Bearer ${token}` },
    })
    practices.value = response.data
  } catch (error) {
    console.error('Chyba pri načítaní praxe:', error)
  } finally {
    loading.value = false
  }
}

const toInputDate = (dateString) => {
  if (!dateString) return ''
  const date = new Date(dateString)
  const year = date.getFullYear()
  const month = String(date.getMonth() + 1).padStart(2, '0')
  const day = String(date.getDate()).padStart(2, '0')
  return `${year}-${month}-${day}`
}



const startEditing = (practice) => {
  editingPracticeId.value = practice.id
  editForm.value.year = practice.year
  editForm.value.semester = practice.semester
  // správne formátovanie dátumov bez posunu
  editForm.value.start_date = toInputDate(practice.start_date)
  editForm.value.end_date = toInputDate(practice.end_date)
}


const cancelEditing = () => {
  editingPracticeId.value = null
  editForm.value = { year: null, semester: '', start_date: '', end_date: '' }
}

const submitEdit = async (id) => {
  try {
    const token = localStorage.getItem('access_token')
    await axios.put(`http://localhost:8000/api/internships/${id}`, {
      start_date: editForm.value.start_date,
      end_date: editForm.value.end_date
    }, {
      headers: { Authorization: `Bearer ${token}` },
    })
    alert('Prax bola úspešne aktualizovaná.')
    cancelEditing()
    await loadPractices()
  } catch (error) {
    console.error('Chyba pri aktualizácii praxe:', error)
    alert('Nepodarilo sa aktualizovať prax.')
  }
}

onMounted(loadPractices)
</script>

<style scoped>
.my-practice-view {
  max-width: 600px;
  margin: 50px auto;
  background: white;
  padding: 25px 35px;
  border-radius: 10px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

h2 {
  text-align: center;
  margin-bottom: 20px;
  color: #1b5e20;
}

.loading, .no-practice {
  text-align: center;
  font-style: italic;
  color: #555;
}

.practice-details {
  border-top: 1px solid #ddd;
  padding-top: 15px;
  margin-top: 15px;
}

.practice-details:first-of-type {
  border-top: none;
  margin-top: 0;
  padding-top: 0;
}

.practice-details p {
  font-size: 16px;
  margin: 8px 0;
}

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

button {
  margin-top: 10px;
  margin-right: 10px;
  padding: 6px 12px;
  border: none;
  background-color: #1b5e20;
  color: white;
  border-radius: 4px;
  cursor: pointer;
  font-size: 14px;
}

button:hover {
  background-color: #2e7d32;
}

input[type="number"],
input[type="text"],
input[type="date"] {
  padding: 5px;
  font-size: 14px;
  border: 1px solid #ccc;
  border-radius: 4px;
  width: 150px;
}

label {
  margin-right: 8px;
  font-weight: 500;
}
</style>
