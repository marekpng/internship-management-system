<template>
  <!-- Navbar (notifikácie + nastavenia + odhlásenie) -->
  <CompanyNavBar />

  <div class="practice-form">
    <button type="button" class="back-btn" @click="goBack">← Späť</button>
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
      <input id="year" v-model="year" type="number" readonly />

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
        @change="setYearFromStartDate"
      />

      <label for="end_date">Koniec praxe</label>
      <input id="end_date" v-model="end_date" type="date" required />

      <!-- Odoslanie -->
      <button type="submit" :disabled="submitting">
        {{ submitting ? "Ukladám..." : "Uložiť prax" }}
      </button>
    </form>

    <br />

    <!-- Firma nie je v zozname -->
    <div class="missing-company">
      <h3>Nenašli ste firmu v zozname?</h3>
      <br />
      <button type="button" class="register-company-btn" @click="goToCompanyRegister">
        Zaregistrovať firmu
      </button>
    </div>

    <!-- Správy o stave -->
    <p v-if="successMessage" class="success">{{ successMessage }}</p>
    <p v-if="errorMessage" class="error">{{ errorMessage }}</p>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { useRouter } from "vue-router";
import axios from "axios";
import CompanyNavBar from "@/components/icons/CompanyNavBar.vue";

const router = useRouter();
const goBack = () => router.back();

const COMPANY_LIST_URL = "http://localhost:8000/api/companies";

/**
 * ✅ Toto MUSÍ byť route, ktorá je v api.php v skupine:
 * Route::middleware(['auth:api', 'role:student'])->group(...)
 *
 * Príklad: Route::post('/student/internships', [InternshipController::class, 'storeForStudent']);
 */
const CREATE_INTERNSHIP_URL = "http://localhost:8000/api/student/internships";

const companySearch = ref("");
const companies = ref([]);
const filteredCompanies = ref([]);
const selectedCompany = ref(null);

const year = ref(new Date().getFullYear());
const semester = ref("");
const start_date = ref("");
const end_date = ref("");

const submitting = ref(false);
const successMessage = ref("");
const errorMessage = ref("");

const token = localStorage.getItem("access_token");

// Funkcia na nastavenie roku na základe začiatku praxe
const setYearFromStartDate = () => {
  if (start_date.value) {
    year.value = new Date(start_date.value).getFullYear();
  }
};

const goToCompanyRegister = () => {
  router.push("/student/company-register");
};

// Načítanie všetkých firiem
onMounted(async () => {
  try {
    const res = await axios.get(COMPANY_LIST_URL, {
      headers: token ? { Authorization: `Bearer ${token}` } : {},
    });

    const payload = Array.isArray(res.data) ? res.data : res.data?.data ?? [];
    companies.value = payload;
    filteredCompanies.value = payload;

    // ak je len jedna firma, rovno ju vyber
    if (companies.value.length === 1) {
      selectedCompany.value = companies.value[0];
      companySearch.value =
        companies.value[0].company_name || companies.value[0].name || "";
      filteredCompanies.value = [companies.value[0]];
    }
  } catch (error) {
    console.error("Chyba pri načítaní firiem:", error);
  }
});

// Filtrovanie podľa názvu
const filterCompanies = () => {
  const search = (companySearch.value || "").toLowerCase();
  filteredCompanies.value = companies.value.filter((c) => {
    const label = (c.company_name || c.name || "").toLowerCase();
    return label.includes(search);
  });
};

// Výber firmy zo zoznamu
const selectCompany = () => {
  const label = (companySearch.value || "").trim();
  const found = companies.value.find(
    (c) => (c.company_name || "").trim() === label || (c.name || "").trim() === label
  );
  selectedCompany.value = found || null;
};

// Odoslanie formulára
const submitForm = async () => {
  successMessage.value = "";
  errorMessage.value = "";

  // ✅ základné validácie
  if (!semester.value) {
    errorMessage.value = "Vyber semester.";
    return;
  }
  if (!start_date.value || !end_date.value) {
    errorMessage.value = "Vyplň dátum začiatku aj konca praxe.";
    return;
  }
  if (new Date(end_date.value) < new Date(start_date.value)) {
    errorMessage.value = "Koniec praxe nemôže byť pred začiatkom praxe.";
    return;
  }

  let selected = selectedCompany.value;
  if (!selected) {
    const label = (companySearch.value || "").trim();
    selected = companies.value.find(
      (c) => (c.company_name || "").trim() === label || (c.name || "").trim() === label
    );
  }

  if (!selected) {
    errorMessage.value = "Prosím, vyber firmu zo zoznamu.";
    return;
  }

  if (!token) {
    errorMessage.value = "Nie si prihlásený. Prihlás sa prosím znova.";
    return;
  }

  submitting.value = true;

  try {
    const user = JSON.parse(localStorage.getItem("user") || "null");
    await axios.post(
      CREATE_INTERNSHIP_URL,
      {
        company_id: selected.id,
        student_id: user?.id,
        status: "Vytvorená",
        year: year.value,
        semester: semester.value,
        start_date: start_date.value,
        end_date: end_date.value,
      },
      {
        headers: { Authorization: `Bearer ${token}` },
      }
    );

    successMessage.value = "Prax bola úspešne vytvorená!";
    errorMessage.value = "";

    // reset formulára
    companySearch.value = "";
    selectedCompany.value = null;
    semester.value = "";
    start_date.value = "";
    end_date.value = "";
    year.value = new Date().getFullYear();
  } catch (error) {
    successMessage.value = "";
    errorMessage.value =
      error.response?.data?.message ||
      (error.response?.data?.errors
        ? Object.values(error.response.data.errors).flat().join(" ")
        : "") ||
      "Nepodarilo sa vytvoriť prax.";

    console.error("Chyba pri vytváraní praxe:", error.response?.data || error);
  } finally {
    submitting.value = false;
  }
};
</script>

<style scoped>
.practice-form {
  max-width: 600px;
  margin: 0 auto;
  background: #fff;
  padding: 24px;
  padding-top: 94px; /* rezerva pre sticky navbar */
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.success {
  color: green;
}

.error {
  color: red;
}

.back-btn {
  margin-bottom: 12px;
  background: #ffffff;
  border: 1px solid #1b5e20;
  color: #1b5e20;
  padding: 8px 14px;
  border-radius: 6px;
  cursor: pointer;
  font-size: 14px;
  font-weight: 600;
  line-height: 1;
}

.back-btn:hover {
  background: #e8f5e9;
}

.register-company-btn {
  width: 100%;
  background-color: #1b5e20;
  color: #fff;
  border: none;
  padding: 12px;
  border-radius: 6px;
  font-size: 15px;
  font-weight: 500;
  cursor: pointer;
  transition: background-color 0.2s ease;
}

.register-company-btn:hover {
  background-color: #2e7d32;
}

button[type="submit"] {
  width: 100%;
  background-color: #1b5e20;
  color: #fff;
  border: none;
  padding: 12px;
  border-radius: 6px;
  font-size: 15px;
  font-weight: 600;
  cursor: pointer;
}

button[type="submit"]:disabled {
  opacity: 0.7;
  cursor: default;
}
</style>
