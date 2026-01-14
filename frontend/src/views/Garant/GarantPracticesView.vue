<template>
  <CompanyNavBar>
    <template #filters>
      <div class="filter-bar">
        <!-- Status filter buttons -->
        <router-link class="filter-btn" :class="{ active: status === 'vsetky' }" :to="statusLink('vsetky')">Všetky</router-link>
        <router-link class="filter-btn" :class="{ active: status === 'vytvorena' }" :to="statusLink('vytvorena')">Čakajúce</router-link>
        <router-link class="filter-btn" :class="{ active: status === 'potvrdena' }" :to="statusLink('potvrdena')">Potvrdené</router-link>
        <router-link class="filter-btn" :class="{ active: status === 'zamietnuta' }" :to="statusLink('zamietnuta')">Zamietnuté</router-link>
        <router-link class="filter-btn" :class="{ active: status === 'schvalena' }" :to="statusLink('schvalena')">Schválené</router-link>
        <router-link class="filter-btn" :class="{ active: status === 'neschvalena' }" :to="statusLink('neschvalena')">Neschválené</router-link>
        <router-link class="filter-btn" :class="{ active: status === 'obhajena' }" :to="statusLink('obhajena')">Obhájené</router-link>
        <router-link class="filter-btn" :class="{ active: status === 'neobhajena' }" :to="statusLink('neobhajena')">Neobhájené</router-link>
      </div>

      <!-- Filters from loaded data -->
      <div class="filter-row">
        <!-- ✅ NEW: Len moje praxe -->
        <div class="field field-checkbox">
          <label>Len moje praxe</label>
          <label class="checkbox">
            <input type="checkbox" v-model="filters.onlyMine" />
            <span>Zobraziť iba praxe, kde som garant</span>
          </label>
        </div>

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
            <option v-for="st in availableStudents" :key="st.key" :value="st.key">
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
      <li v-for="internship in filteredInternships" :key="internship.id" class="practice-item">
        <router-link
          class="practice-link"
          :to="{ name: 'garantPracticeDetail', params: { id: internship.id }, query: { status: status || 'vytvorena' } }"
        >
          <!-- ✅ STATUS BADGE -->
          <div class="status-box" :class="statusClass(internship.status)">
            {{ internship.status }}
          </div>

          <!-- ✅ DELETE BUTTON (X) under status -->
          <button
            class="delete-x"
            type="button"
            title="Vymazať prax"
            @click.prevent.stop="deleteInternship(internship.id)"
          >
            ✕
          </button>

          <strong>
            {{ internship.student?.first_name || "Neznámy študent" }}
            {{ internship.student?.last_name || "" }}
          </strong>

          <div>{{ internship.student?.email || "" }}</div>

          <div>vytvorená: {{ formatDate(internship.created_at) }}</div>

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
import CompanyNavBar from "@/components/icons/CompanyNavBar.vue";

export default {
  name: "GarantPracticesView",
  components: { CompanyNavBar },

  data() {
    return {
      internships: [],
      loading: true,
      status: null,
      title: "",

      // ✅ current logged garant id (from localStorage.user.id)
      currentGarantId: null,

      filters: {
        year: "",
        semester: "",
        studentKey: "",
        companyName: "",
        onlyMine: false,
      },

      availableYears: [],
      availableSemesters: [],
      availableStudents: [], // [{ key, label }]
      availableCompanies: [],
    };
  },

  computed: {
    statusMap() {
      return {
        vytvorena: { title: "Vytvorené", api: "Vytvorená" },
        potvrdena: { title: "Potvrdené", api: "Potvrdená" },
        zamietnuta: { title: "Zamietnuté", api: "Zamietnutá" },
        schvalena: { title: "Schválené", api: "Schválená" },
        neschvalena: { title: "Neschválené", api: "Neschválená" },
        obhajena: { title: "Obhájené", api: "Obhájená" },
        neobhajena: { title: "Neobhájené", api: "Neobhájená" },
        vsetky: { title: "Všetky", api: "" },
      };
    },

    filteredInternships() {
      return this.internships.filter((i) => {
        // ✅ only mine filter
        if (this.filters.onlyMine) {
          const gid = i.garant_id ?? i.garant?.id ?? null;
          if (!this.currentGarantId || Number(gid) !== Number(this.currentGarantId)) return false;
        }

        if (this.filters.year && String(i.year ?? "") !== String(this.filters.year)) return false;
        if (this.filters.semester && String(i.semester ?? "") !== String(this.filters.semester)) return false;

        if (this.filters.studentKey) {
          const key = this.normalizeStudentKey(i);
          if (key !== this.filters.studentKey) return false;
        }

        if (this.filters.companyName) {
          const c = (i.company?.company_name || "").trim();
          if (c !== this.filters.companyName) return false;
        }

        return true;
      });
    },
  },

  methods: {
    token() {
      return localStorage.getItem("access_token");
    },

    statusLink(status) {
      return { path: "/garant/practices", query: { ...this.$route.query, status } };
    },

    normalizeStudentKey(internship) {
      const fn = (internship.student?.first_name || "").trim();
      const ln = (internship.student?.last_name || "").trim();
      const full = `${fn} ${ln}`.trim();
      return full.toLowerCase();
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
        if (fullLabel) studentsMap.set(fullLabel.toLowerCase(), fullLabel);

        const companyName = (i.company?.company_name || "").trim();
        if (companyName) companies.add(companyName);
      }

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
      this.filters.onlyMine = false;
    },

    statusClass(status) {
      const s = (status || "").trim();

      if (["Zamietnutá", "Neschválená", "Neobhájená"].includes(s)) return "status--danger";
      if (["Vytvorená", "Potvrdená"].includes(s)) return "status--warning";
      if (["Schválená", "Obhájená"].includes(s)) return "status--success";

      return "status--neutral";
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
          headers: { Authorization: `Bearer ${this.token()}` },
        });

        this.internships = response.data || [];
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
    },

    // ✅ DELETE internship (garant)
    async deleteInternship(id) {
      const ok = confirm("Naozaj chceš vymazať túto prax? Táto akcia je nevratná.");
      if (!ok) return;

      try {
        // ⚠️ uprav URL podľa tvojich secure routes:
        // odporúčané: DELETE /api/garant/internships/{id}
        await axios.delete(`http://localhost:8000/api/garant/internships/${id}`, {
          headers: { Authorization: `Bearer ${this.token()}` },
        });

        // refresh list
        await this.loadInternships();
        alert("Prax bola vymazaná.");
      } catch (e) {
        console.error("Delete error:", e);
        alert(e.response?.data?.message || "Nepodarilo sa vymazať prax.");
      }
    },
  },

  watch: {
    "$route.query.status"() {
      this.loadInternships();
    },
  },

  mounted() {
    const u = JSON.parse(localStorage.getItem("user") || "{}");
    this.currentGarantId = u?.id ?? null;

    this.loadInternships();
  },
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
  position: relative;
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

/* =========================
   FILTER BAR (STATUS)
========================= */
.filter-bar {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  padding: 10px 0;
}

/* =========================
   FILTER ROW (SELECTS + CHECKBOX)
========================= */
.filter-row {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr)) auto;
  gap: 10px;
  align-items: end;
  padding: 10px 0 4px;
}

/* klasické polia (select) */
.field label {
  display: block;
  font-size: 12px;
  font-weight: 700;
  color: #0b6b37;
  margin-bottom: 6px;
}

.field select {
  width: 95%;
  height: 38px;
  padding: 8px 10px;
  border: 1px solid #d0d7de;
  border-radius: 8px;
  background: #fff;
  box-sizing: border-box;
}

/* =========================
   CHECKBOX – LEN MOJE PRAXE
========================= */
.field-checkbox {
  display: flex;
  flex-direction: column;
  justify-content: flex-end;
}

.field-checkbox label {
  display: block;
  font-size: 12px;
  font-weight: 700;
  color: #0b6b37;
  margin-bottom: 6px;
}

.field-checkbox .checkbox {
  height: 38px;
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 8px 10px;
  border: 1px solid #d0d7de;
  border-radius: 8px;
  background: #fff;
  box-sizing: border-box;
  cursor: pointer;
}

.field-checkbox .checkbox span {
  white-space: nowrap;
}

/* =========================
   FILTER ACTIONS
========================= */
.filter-actions {
  display: flex;
  gap: 8px;
}

.btn-clear {
  height: 38px;
  padding: 0 12px;
  border-radius: 8px;
  background: #fff;
  color: #0b6b37;
  border: 2px solid #0b6b37;
  cursor: pointer;
  font-weight: 700;
}
.btn-clear:hover {
  background: #f0f6f2;
}

/* =========================
   META INFO
========================= */
.meta {
  margin-top: 4px;
  font-size: 12px;
  opacity: 0.8;
}

/* =========================
   STATUS BADGE
========================= */
.status-box {
  position: absolute;
  top: 10px;
  right: 10px;
  padding: 6px 14px;
  border-radius: 8px;
  font-weight: bold;
  font-size: 14px;
  border: 1px solid transparent;
  z-index: 2;
  pointer-events: none;
}

/* ✅ Delete X pod status badge */
.delete-x {
  position: absolute;
  top: 46px; /* pod status-box */
  right: 10px;
  width: 28px;
  height: 28px;
  border-radius: 8px;
  border: 1px solid #fecaca;
  background: #fef2f2;
  color: #991b1b;
  font-weight: 900;
  cursor: pointer;
  z-index: 3;
  display: flex;
  align-items: center;
  justify-content: center;
}

.delete-x:hover {
  background: #fee2e2;
}

.status--warning {
  background: #fff7ed;
  color: #9a3412;
  border-color: #fed7aa;
}

.status--danger {
  background: #fef2f2;
  color: #991b1b;
  border-color: #fecaca;
}

.status--success {
  background: #ecfdf5;
  color: #065f46;
  border-color: #a7f3d0;
}

.status--neutral {
  background: #f1f5f9;
  color: #334155;
  border-color: #e2e8f0;
}

/* =========================
   RESPONSIVE
========================= */
@media (max-width: 900px) {
  .filter-row {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }

  .filter-actions {
    grid-column: 1 / -1;
  }
}
</style>
