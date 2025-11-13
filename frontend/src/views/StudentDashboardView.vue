<template>
  <div class="overlay">
    <div class="container">
      <!-- Vrchný panel -->
      <div class="top-bar">
        <div class="logo">
          <img src="@/assets/logo-fpv.png" alt="Logo FPV" />
          <div class="logo-text">Praxový systém</div>
        </div>
        <div class="user-actions">
          <div class="user-name">{{ userName }}</div>
          <button class="logout-button" @click="logout">Odhlásiť sa</button>
        </div>
      </div>

      <!-- sekcie dashboardu -->
      <div class="section">
        <div class="section-title">Informácie o praxi</div>
        <div class="section-description">
          Prehľadné informácie o priebehu, mieste a hodnotení tvojej praxe.
        </div>
      </div>

      <div class="section clickable" @click="goToPracticeForm">
        <div class="section-title">Pridanie praxe</div>
        <div class="section-description">
          Tu budeš môcť pridať novú prax.
        </div>
      </div>

      <div class="section clickable" @click="goToMyPractice">
        <div class="section-title">Moja prax</div>
        <div class="section-description">
          Zobrazenie tvojej doterajšej praxe a detailov o jednotlivých etapách a úprave praxe.
        </div>
      </div>
      <button class="green-button" @click="goToExport">
    Export údajov
  </button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const userName = ref('')

// keď sa načíta dashboard, načítaj meno prihláseného používateľa
onMounted(() => {
  const userData = localStorage.getItem('user')
  if (userData) {
    try {
      const user = JSON.parse(userData)
      // backend môže posielať meno v rôznych formátoch
      if (user.first_name && user.last_name) {
        userName.value = `${user.first_name} ${user.last_name}`
      } else if (user.name) {
        userName.value = user.name
      } else if (user.student_email) {
        userName.value = user.student_email
      } else {
        userName.value = 'Študent'
      }
    } catch (error) {
      console.error('Chyba pri načítaní používateľa:', error)
      userName.value = 'Študent'
    }
  } else {
    // ak nie je prihlásený, presmeruj ho na login
    router.push({ name: 'login' })
  }
})

// odhlásenie
const logout = () => {
  localStorage.removeItem('token')
  localStorage.removeItem('user')
  router.push({ name: 'login' })
}

const goToPracticeForm = () => {
  router.push({ name: 'studentPracticeForm' })
}

const goToMyPractice = () => {
  router.push('/student/my-practice')
}
const goToExport = () => {
  router.push({ path: '/exporty' })
}

</script>

<style src="../assets/style.css"></style>
<style>
.clickable {
  cursor: pointer;
  transition: background-color 0.2s ease;
}
.clickable:hover {
  background-color: #f9f9f9;
}
.green-button {
  margin-top: 12px;
  background-color: #16a34a; 
  color: white;
  border: none;
  padding: 10px 18px;
  border-radius: 10px;
  font-size: 15px;
  font-weight: 500;
  cursor: pointer;
  transition: background-color 0.2s ease, transform 0.1s ease;
}

.green-button:hover {
  background-color: #15803d; 
  transform: translateY(-1px);
}

.green-button:active {
  transform: translateY(1px);
}


</style>