<template>
  <CompanyNavBar>
    <template #filters>
      <div class="filter-bar">
        <router-link class="filter-btn" :class="{ active: status === 'vsetky' }" :to="statusLink('vsetky')">Všetky</router-link>
        <router-link class="filter-btn" :class="{ active: status === 'vytvorena' }" :to="statusLink('vytvorena')">Čakajúce</router-link>
        <router-link class="filter-btn" :class="{ active: status === 'potvrdena' }" :to="statusLink('potvrdena')">Potvrdené</router-link>
        <router-link class="filter-btn" :class="{ active: status === 'zamietnuta' }" :to="statusLink('zamietnuta')">Zamietnuté</router-link>
        <router-link class="filter-btn" :class="{ active: status === 'schvalena' }" :to="statusLink('schvalena')">Schválené</router-link>
        <router-link class="filter-btn" :class="{ active: status === 'neschvalena' }" :to="statusLink('neschvalena')">Neschválené</router-link>
        <router-link class="filter-btn" :class="{ active: status === 'obhajena' }" :to="statusLink('obhajena')">Obhájené</router-link>
        <router-link class="filter-btn" :class="{ active: status === 'neobhajena' }" :to="statusLink('neobhajena')">Neobhájené</router-link>
      </div>

      <!-- ✅ FILTRE Z DÁT -->
      <div class="filter-row">
        <div class="field">
          <label>Rok</label>
          <select v-model="filters.year">
            <option value="">Všetky</option>
            <option v-for="y in availableYears" :key="y" :value="String(y)">{{ y }}</option>
          </select>
        </div>

        <div class="field">
          <label>Semester</label>
          <select v-model="filters.semester">
            <option value="">Všetky</option>
            <option v-for="s in availableSemesters" :key="s" :value="s">{{ s }}</option>
          </select>
        </div>

        <div class="field">
          <label>Študent</label>
          <select v-model="filters.studentKey">
            <option value="">Všetci</option>
            <option
              v-for="st in availableStudents"
              :key="st.key"
              :value="st.key"
            >
              {{ st.label }}
            </option>
          </select>
        </div>

        <div class="field">
          <label>Firma</label>
          <select v-model="filters.companyName">
            <option value="">Všetky</option>
            <option v-for="c in availableCompanies" :key="c" :value="c">{{ c }}</option>
          </select>
        </div>

        <div class="filter-actions">
          <button class="btn-clear" type="button" @click="clearFilters">
            Vymazať filtre
          </button>
        </div>
      </div>
    </template>
  </CompanyNavBar>

  <div class="container">
    <button class="back-btn" @click="$router.push('/garant/dashboard')">
      ← Späť
    </button>

    <h1>Prax v stave: {{ title }}</h1>

    <div v-if="loading">Načítavam…</div>

    <div v-else-if="filteredInternships.length === 0">
      <p>Zatiaľ tu nie sú žiadne praxe pre zvolené filtre.</p>
    </div>

    <ul v-else class="practice-list">
      <li
        v-for="internship in filteredInternships"
        :key="internship.id"
        class="practice-item"
      >
        <router-link
          class="practice-link"
          :to="{ name: 'garantPracticeDetail', params: { id: internship.id }, query: { status: status || 'vytvorena' } }"
        >
          <strong>
            {{ internship.student?.first_name || "Neznámy študent" }}
            {{ internship.student?.last_name || "" }}
          </strong>

          <div>{{ internship.student?.email || "" }}</div>

          <div>
            {{ internship.status }} — vytvorená: {{ formatDate(internship.created_at) }}
          </div>

          <!-- voliteľne: firma/rok/semester -->
          <div class="meta">
            <span v-if="internship.company?.company_name">Firma: {{ internship.company.company_name }}</span>
            <span v-if="internship.year"> • {{ internship.year }}</span>
            <span v-if="internship.semester"> • {{ internship.semester }}</span>
          </div>
        </router-link>
      </li>
    </ul>
  </div>
</template>

<script>
import axios from "axios";
import CompanyNavBar from '@/components/icons/CompanyNavBar.vue'

export default {
  name: "GarantPracticesView",
  components: { CompanyNavBar },

  data() {
    return {
      internships: [],
      loading: true,
      status: null,
      title: "",

      // ✅ vybrané filtre (dropdown)
      filters: {
        year: "",
        semester: "",
        studentKey: "",   // "First Last" normalizované
        companyName: "",
      },

      // ✅ dostupné možnosti (počítané z internships)
      availableYears: [],
      availableSemesters: [],
      availableStudents: [], // [{ key, label }]
      availableCompanies: [],
    };
  },

  computed: {
    statusMap() {
      return {
        vytvorena:   { title: "Vytvorené",    api: "Vytvorená" },
        potvrdena:   { title: "Potvrdené",    api: "Potvrdená" },
        zamietnuta:  { title: "Zamietnuté",   api: "Zamietnutá" },
        schvalena:   { title: "Schválené",    api: "Schválená" },
        neschvalena: { title: "Neschválené",  api: "Neschválená" },
        obhajena:    { title: "Obhájené",     api: "Obhájená" },
        neobhajena:  { title: "Neobhájené",   api: "Neobhájená" },
        vsetky:      { title: "Všetky",       api: "" },
      };
    },

    // ✅ výsledný list podľa filtrov
    filteredInternships() {
      return this.internships.filter((i) => {
        // year
        if (this.filters.year && String(i.year ?? "") !== String(this.filters.year)) return false;

        // semester
        if (this.filters.semester && String(i.semester ?? "") !== String(this.filters.semester)) return false;

        // student
        if (this.filters.studentKey) {
          const key = this.normalizeStudentKey(i);
          if (key !== this.filters.studentKey) return false;
        }

        // company
        if (this.filters.companyName) {
          const c = (i.company?.company_name || "").trim();
          if (c !== this.filters.companyName) return false;
        }

        return true;
      });
    }
  },

  methods: {
    // zachová filtre v url pri prepnutí statusu (aby si nestratil výber)
    statusLink(status) {
      return { path: "/garant/practices", query: { ...this.$route.query, status } };
    },

    normalizeStudentKey(internship) {
      const fn = (internship.student?.first_name || "").trim();
      const ln = (internship.student?.last_name || "").trim();
      const full = `${fn} ${ln}`.trim();
      return full.toLowerCase(); // kľúč
    },

    buildFilterOptionsFromData() {
      const years = new Set();
      const semesters = new Set();
      const studentsMap = new Map(); // key -> label
      const companies = new Set();

      for (const i of this.internships) {
        if (i.year !== null && i.year !== undefined && String(i.year).trim() !== "") years.add(String(i.year));
        if (i.semester) semesters.add(String(i.semester));

        const fn = (i.student?.first_name || "").trim();
        const ln = (i.student?.last_name || "").trim();
        const fullLabel = `${fn} ${ln}`.trim();
        if (fullLabel) {
          studentsMap.set(fullLabel.toLowerCase(), fullLabel);
        }

        const companyName = (i.company?.company_name || "").trim();
        if (companyName) companies.add(companyName);
      }

      // sort
      this.availableYears = Array.from(years).sort((a, b) => Number(b) - Number(a));
      this.availableSemesters = Array.from(semesters).sort((a, b) => a.localeCompare(b, "sk"));
      this.availableStudents = Array.from(studentsMap.entries())
        .map(([key, label]) => ({ key, label }))
        .sort((a, b) => a.label.localeCompare(b.label, "sk"));
      this.availableCompanies = Array.from(companies).sort((a, b) => a.localeCompare(b, "sk"));
    },

    clearFilters() {
      this.filters.year = "";
      this.filters.semester = "";
      this.filters.studentKey = "";
      this.filters.companyName = "";
    },

    async loadInternships() {
      try {
        this.loading = true;

        this.status = this.$route.query.status || "vytvorena";
        const map = this.statusMap[this.status] || this.statusMap["vytvorena"];
        this.title = map.title;

        let url = "";

        if (this.status === "vsetky") {
          url = "http://localhost:8000/api/internships/myNew";
        } else {
          url = "http://localhost:8000/api/garant/internships/status/" + encodeURIComponent(map.api);
        }

        const response = await axios.get(url, {
          headers: { Authorization: `Bearer ${localStorage.getItem("access_token")}` }
        });

        this.internships = response.data || [];

        // ✅ z načítaných dát vyrob možnosti filtrov
        this.buildFilterOptionsFromData();
      } catch (e) {
        console.error("Error loading internships:", e);
        this.internships = [];
        this.buildFilterOptionsFromData();
      } finally {
        this.loading = false;
      }
    },

    formatDate(date) {
      return new Date(date).toLocaleDateString("sk-SK");
    }
  },

  watch: {
    '$route.query.status'() {
      // pri zmene statusu reload (dáta sa menia)
      this.loadInternships();
    }
  },

  mounted() {
    this.loadInternships();
  }
};
</script>

<style scoped>
.container {
  padding: 20px;
}

.practice-list {
  list-style: none;
  padding: 0;
}

.practice-item {
  padding: 12px;
  margin-bottom: 10px;
  border: 1px solid #ccc;
  border-radius: 6px;
  cursor: pointer;
  transition: background 0.2s;
}

.practice-item:hover {
  background: #f5f5f5;
}

.practice-link {
  display: block;
  color: inherit;
  text-decoration: none;
}

.practice-link:visited {
  color: inherit;
}

.practice-link:hover {
  text-decoration: none;
}

.back-btn {
  margin-bottom: 15px;
  background: #ffffff;
  border: 1px solid #0b6b37;
  color: #0b6b37;
  padding: 8px 14px;
  border-radius: 6px;
  cursor: pointer;
}
.back-btn:hover {
  background: #f0f6f2;
}

.filter-bar {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  padding: 10px 0;
}

/* ✅ filter dropdown row */
.filter-row {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr)) auto;
  gap: 10px;
  align-items: end;
  padding: 10px 0 4px;
}

.field label {
  display: block;
  font-size: 12px;
  font-weight: 700;
  color: #0b6b37;
  margin-bottom: 6px;
}

.field input,
.field select {
  width: 100%;
  padding: 8px 10px;
  border: 1px solid #d0d7de;
  border-radius: 8px;
  background: #fff;
}

.filter-actions {
  display: flex;
  gap: 8px;
}

.btn-clear {
  padding: 8px 12px;
  border-radius: 8px;
  background: #fff;
  color: #0b6b37;
  border: 2px solid #0b6b37;
  cursor: pointer;
  font-weight: 700;
  height: 38px;
}

.btn-clear:hover {
  background: #f0f6f2;
}

.meta {
  margin-top: 4px;
  font-size: 12px;
  opacity: 0.8;
}

@media (max-width: 900px) {
  .filter-row {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
  .filter-actions {
    grid-column: 1 / -1;
  }
}
</style>
