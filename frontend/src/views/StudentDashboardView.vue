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
    </div>
  </div>
  <div class="footer-only">
    <FooterComponent />
  </div>
</template>

<script setup>
import '@/assets/basic.css'
import FooterComponent from '@/components/FooterComponent.vue'
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

// 🔥 OPRAVENÁ FUNKCIA – už nespôsobuje chybu "invalid left-hand side"
const goToMyPractice = () => {
  // ak používaš ROUTE NAME → dáš meno route:
  if (router.hasRoute('studentMyPractice')) {
    router.push({ name: 'studentMyPractice' })
  } else {
    // fallback podľa tvojej existujúcej štruktúry v routeri
    router.push('/student/my-practice')
  }
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
</style>
