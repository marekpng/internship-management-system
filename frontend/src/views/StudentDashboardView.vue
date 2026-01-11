<template>
  <div class="overlay">
    <div class="container">
      <!-- Navbar (spoločný pre roly: notifikácie + nastavenia + odhlásenie) -->
      <CompanyNavBar />
      <div class="welcome-bar">
        <span class="portal-badge">Študentský portál</span>
        <h1>Vitajte, {{ userName }}</h1>
        <p>Spravujte a sledujte priebeh svojej odbornej praxe.</p>
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
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import CompanyNavBar from '@/components/icons/CompanyNavBar.vue'

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
  localStorage.removeItem('access_token')
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

.clickable {
  cursor: pointer;
  transition: background-color 0.2s ease;
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
</style>
