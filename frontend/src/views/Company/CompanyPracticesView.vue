<template>
  <CompanyNavBar>
    <template #filters>
      <div class="filter-bar">
        <router-link
          to="/company/practices?status=Vytvorená"
          class="filter-btn"
          :class="{ active: $route.query.status === 'Vytvorená' }"
        >
          Čakajúce
        </router-link>

        <router-link
          to="/company/practices?status=Potvrdená"
          class="filter-btn"
          :class="{ active: $route.query.status === 'Potvrdená' }"
        >
          Potvrdené
        </router-link>

        <router-link
          to="/company/practices?status=Zamietnutá"
          class="filter-btn"
          :class="{ active: $route.query.status === 'Zamietnutá' }"
        >
          Zamietnuté
        </router-link>
      </div>

      <div class="filter-secondary">
        <button class="back-button secondary-back" type="button" @click="$router.back()">
          ← Späť
        </button>
        <div class="filter-group">
          <label for="year">Rok</label>
          <select id="year" v-model="year" @change="reloadWithFilters">
            <option value="">Všetky</option>
            <option v-for="y in availableYears" :key="y" :value="y">{{ y }}</option>
          </select>
        </div>

        <div class="filter-group">
          <label for="semester">Semester</label>
          <select id="semester" v-model="semester" @change="reloadWithFilters">
            <option value="">Všetky</option>
            <option value="zimný">Zimný</option>
            <option value="letný">Letný</option>
          </select>
        </div>

        <button class="filter-reset" type="button" @click="resetFilters">Reset</button>
      </div>
    </template>
  </CompanyNavBar>

  <div class="container">


    <div v-if="loading">Načítavam…</div>

    <div v-else-if="internships.length === 0">
      <p>Zatiaľ tu nie sú žiadne praxe v tomto stave.</p>
    </div>

    <ul v-else class="practice-list">
      <li
        v-for="internship in internships"
        :key="internship.id"
        @click="goToDetail(internship.id)"
        class="practice-item"
      >
        <strong>{{ internship.student?.first_name || '' }} {{ internship.student?.last_name || '' }}</strong>

        <div>{{ internship.student?.email || '' }}</div>
        <div>{{ internship.status }}: {{ formatDate(internship.created_at) }}</div>

        <!-- 🔥 INFO O DOKUMENTOCH -->
        <div class="doc-row">
          <span
            class="doc-badge"
            :class="internship.hasDocuments ? 'badge-green' : 'badge-red'"
          >
            Dokumenty: {{ internship.hasDocuments ? "Áno" : "Nie" }}
          </span>
        </div>
      </li>
    </ul>
  </div>
</template>

<script>
import CompanyNavBar from '@/components/icons/CompanyNavBar.vue';
import axios from "axios";

export default {
  name: "CompanyPracticesView",
  components: { CompanyNavBar },

  data() {
    return {
      internships: [],
      loading: true,
      status: null,
      title: "Vytvorená",
      year: "",
      semester: "",
      availableYears: [],
    };
  },

  methods: {
    normalizeSemester(value) {
      if (!value) return '';
      const v = String(value).trim().toLowerCase();
      // accept SK + EN variants that may exist in DB
      if (v === 'zimný' || v === 'zimny' || v === 'winter') return 'zimný';
      if (v === 'letný' || v === 'letny' || v === 'summer') return 'letný';
      return v;
    },

    getInternshipYear(i) {
      // Prefer explicit DB column `year`, fallback to start_date, then created_at
      if (i && (i.year || i.year === 0)) return String(i.year);
      const src = i?.start_date || i?.created_at;
      if (!src) return '';
      const d = new Date(src);
      if (Number.isNaN(d.getTime())) return '';
      return String(d.getFullYear());
    },
    async loadInternships() {
      try {
        this.status = this.$route.query.status || "Vytvorená";

        // auth header (API je za auth:api)
        const token = localStorage.getItem('access_token');
        const headers = token ? { Authorization: `Bearer ${token}` } : {};

        // Mapovanie status -> endpoint
        let url = "http://localhost:8000/api/company/internships/pending";

        if (this.status === "Potvrdená") {
          url = "http://localhost:8000/api/company/internships/approved";
          this.title = "Potvrdená";
        } else if (this.status === "Zamietnutá") {
          url = "http://localhost:8000/api/company/internships/rejected";
          this.title = "Zamietnutá";
        } else {
          this.title = "Vytvorená";
        }

        const response = await axios.get(url, {
          headers
        });

        const items = Array.isArray(response.data) ? response.data : [];

        // doplnenie dokumentov pre každú prax
        for (let internship of items) {
          try {
            const res = await axios.get(`http://localhost:8000/api/internships/${internship.id}/documents`, { headers });
            internship.hasDocuments = Array.isArray(res.data) && res.data.length > 0;
          } catch {
            internship.hasDocuments = false;
          }
        }

        // Klientsky filter: rok + semester (preferuj DB stĺpce year/semester)
        const filtered = items.filter((i) => {
          const y = this.getInternshipYear(i);
          if (this.year && y && y !== String(this.year)) return false;

          if (this.semester) {
            const semDb = this.normalizeSemester(i?.semester);
            // Fallback: ak DB nemá semester, odhadneme zo start_date
            let sem = semDb;
            if (!sem) {
              const src = i?.start_date || i?.created_at;
              if (src) {
                const d = new Date(src);
                if (!Number.isNaN(d.getTime())) {
                  const m = d.getMonth() + 1; // 1-12
                  sem = (m >= 9 || m <= 2) ? 'zimný' : 'letný';
                }
              }
            }

            if (sem && sem !== this.semester) return false;
          }

          return true;
        });

        this.internships = filtered;

        // dostupné roky z dát (aby select nebol ručný)
        const years = new Set();
        items.forEach((i) => {
          const y = this.getInternshipYear(i);
          if (y) years.add(String(y));
        });
        this.availableYears = Array.from(years).sort((a, b) => Number(b) - Number(a));

      } catch (e) {
        console.error("Error loading internships:", e);
        this.internships = [];
      } finally {
        this.loading = false;
      }
    },

    reloadWithFilters() {
      this.loading = true;
      this.loadInternships();
    },

    resetFilters() {
      this.year = "";
      this.semester = "";
      this.reloadWithFilters();
    },

    goToDetail(id) {
      this.$router.push(`/company/practices/${id}`);
    },

    formatDate(date) {
      return new Date(date).toLocaleDateString("sk-SK");
    }
  },

  watch: {
    '$route.query.status'() {
      this.loading = true;
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
  max-width: 1250px;
  margin: 0 auto;
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
  background: white;
}

.practice-item:hover {
  background: #f5f5f5;
}

.doc-row {
  margin-top: 8px;
}

.doc-badge {
  padding: 5px 10px;
  border-radius: 8px;
  font-size: 13px;
  font-weight: 600;
}

.badge-green {
  background: #e1f2e5;
  color: #0b6b37;
}

.badge-red {
  background: #ffe4e4;
  color: #a11b1b;
}

/* ------ pôvodný dizajn zostáva nezmenený ------ */

.navbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: #ffffff;
  padding: 14px 20px;
  border-radius: 10px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.07);
  margin-bottom: 25px;
}

.nav-title {
  font-size: 20px;
  font-weight: 700;
  color: #0b6b37;
}

.nav-right {
  display: flex;
  gap: 12px;
}

.nav-btn {
  background: #0b6b37;
  color: white;
  padding: 8px 16px;
  border-radius: 6px;
  border: none;
  cursor: pointer;
  transition: background 0.2s ease;
  font-weight: 600;
}

.nav-btn:hover {
  background: #095a2f;
}

.header-bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: #0b6b37;
  padding: 12px 20px;
  color: white;
  margin-bottom: 20px;
  border-radius: 8px;
}

.header-title {
  font-size: 18px;
  font-weight: 600;
}

.header-back {
  background: white;
  color: #0b6b37;
  padding: 6px 12px;
  border-radius: 6px;
  cursor: pointer;
  border: none;
  font-weight: 600;
}

.header-back:hover {
  background: #f0f6f2;
}

.filter-secondary {
  display: flex;
  align-items: flex-end;
  gap: 12px;
  margin-top: 10px;
  flex-wrap: wrap;

  justify-content: flex-start; /* LEFT aligned */
  width: 100%;
}

.filter-group {
  display: flex;
  flex-direction: column;
  gap: 6px;
  min-width: 140px;
  margin-right: 6px;
}

.filter-group label {
  font-size: 12px;
  font-weight: 700;
  color: #0b6b37;
}

.filter-group select {
  padding: 8px 10px;
  border: 1px solid #d9d9d9;
  border-radius: 8px;
  background: white;
  min-width: 140px;
}

.filter-reset {
  padding: 8px 12px;
  border-radius: 8px;
  border: 1px solid #d9d9d9;
  background: #ffffff;
  cursor: pointer;
  font-weight: 700;
  color: #0b6b37;
}

.filter-reset:hover {
  background: #f5f5f5;
}

/* --- Back button inline with filters --- */
.inline-back {
  margin-right: 16px;
  height: 36px;
  padding: 0 14px;
  border-radius: 999px;
  border: 1px solid #d9d9d9;
  background: #ffffff;
  font-size: 13px;
  font-weight: 700;
  color: #0b6b37;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  transition: background 0.15s ease, border-color 0.15s ease;
}

.inline-back:hover {
  background: #f5f7f5;
  border-color: #cfe2d5;
}

/* ensure filter bar aligns nicely */
.filter-bar {
  display: flex;
  align-items: center;
  gap: 12px;
  justify-content: center;
}

/* --- Back button in secondary filter row --- */
.secondary-back {
  height: 36px;
  padding: 0 14px;
  border-radius: 6px;
  border: 1px solid #0b6b37;
  background: #ffffff;
  font-size: 13px;
  font-weight: 600;
  color: #0b6b37;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  transition: background 0.15s ease, transform 0.05s ease;
}

.secondary-back:hover {
  background: #f0f6f2;
}

.secondary-back:active {
  transform: translateY(1px);
}
</style>
