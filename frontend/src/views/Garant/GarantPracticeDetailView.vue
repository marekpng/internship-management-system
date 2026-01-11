<template>
  <CompanyNavBar>
    <template #filters>
      <div class="filter-bar">
        <!-- Rovnaké filtre ako v zozname (GarantPracticesView). Stav držíme v query ?status=... -->
        <router-link class="filter-btn" :class="{ active: status === 'vsetky' }" to="/garant/practices?status=vsetky">Všetky</router-link>
        <router-link class="filter-btn" :class="{ active: status === 'vytvorena' }" to="/garant/practices?status=vytvorena">Čakajúce</router-link>
        <router-link class="filter-btn" :class="{ active: status === 'potvrdena' }" to="/garant/practices?status=potvrdena">Potvrdené</router-link>
        <router-link class="filter-btn" :class="{ active: status === 'zamietnuta' }" to="/garant/practices?status=zamietnuta">Zamietnuté</router-link>
        <router-link class="filter-btn" :class="{ active: status === 'schvalena' }" to="/garant/practices?status=schvalena">Schválené</router-link>
        <router-link class="filter-btn" :class="{ active: status === 'neschvalena' }" to="/garant/practices?status=neschvalena">Neschválené</router-link>
        <router-link class="filter-btn" :class="{ active: status === 'obhajena' }" to="/garant/practices?status=obhajena">Obhájené</router-link>
        <router-link class="filter-btn" :class="{ active: status === 'neobhajena' }" to="/garant/practices?status=neobhajena">Neobhájené</router-link>
      </div>
    </template>
  </CompanyNavBar>

  <div class="container" v-if="internship">
    <button
      class="back-btn"
      @click="$router.push({ path: '/garant/practices', query: { status: status === 'zamietnute' ? 'zamietnuta' : status } })"
    >
      ← Späť
    </button>

    <h1>Detail praxe</h1>

    <div class="card">
      <h2>Študent</h2>
      <p><strong>Meno:</strong> {{ internship.student?.first_name || "Neznámy študent" }} {{ internship.student?.last_name || "" }}</p>
      <p><strong>Email:</strong> {{ internship.student?.email || "" }}</p>

      <h2>Firma</h2>
      <p><strong>Názov:</strong> {{ internship.company?.company_name || internship.company?.first_name || "Neznáma firma" }}</p>
      <p><strong>Email:</strong> {{ internship.company?.email || "" }}</p>

      <h2>Prax</h2>
      <div v-if="!editMode">
        <p><strong>Začiatok:</strong> {{ formatDate(internship.start_date) }}</p>
        <p><strong>Koniec:</strong> {{ formatDate(internship.end_date) }}</p>
        <p><strong>Semester:</strong> {{ internship.semester }}</p>
        <p><strong>Rok:</strong> {{ internship.year }}</p>
        <p><strong>Stav:</strong> {{ internship.status }}</p>
      </div>

      <template v-else>
        <hr style="margin: 15px 0;">
        <h3>Upraviť prax</h3>
        <div class="form-group">
          <label>Začiatok:</label>
          <input type="datetime-local" v-model="editForm.start_date">
        </div>
        <div class="form-group">
          <label>Koniec:</label>
          <input type="datetime-local" v-model="editForm.end_date">
        </div>
        <div class="form-group">
          <label>Semester:</label>
          <input type="text" v-model="editForm.semester">
        </div>
        <div class="form-group">
          <label>Rok:</label>
          <input type="number" v-model="editForm.year">
        </div>
        <div class="form-group">
          <label>Stav:</label>
          <select v-model="editForm.status">
            <option value="Vytvorená">Vytvorená</option>
            <option value="Potvrdená">Potvrdená</option>
            <option value="Schválená">Schválená</option>
            <option value="Neschválená">Neschválená</option>
            <option value="Zamietnutá">Zamietnutá</option>
            <option value="Obhájená">Obhájená</option>
            <option value="Neobhájená">Neobhájená</option>
          </select>
        </div>
      </template>
    </div>

    <div class="actions">

      <!-- Garant môže SCHVÁLIŤ iba ak je prax POTVRDENÁ -->
      <template v-if="internship.status === 'Potvrdená' && !editMode">
        <button class="approve" @click="approveByGarant">Schváliť prax</button>
        <button class="reject" @click="rejectByGarant">Neschváliť prax</button>
      </template>

      <!-- Garant môže editovať len SCHVÁLENÚ prax -->
      <template v-if="internship.status === 'Schválená' && !editMode">
        <button class="approve" style="background:#0b6b37" @click="editMode = true">Upraviť prax / nastaviť obhajobu</button>
      </template>

      <!-- Ukladanie editácie -->
      <template v-if="editMode">
        <button class="approve" style="background:#0b6b37" @click="saveEdit">Uložiť</button>
        <button class="reject" @click="cancelEdit">Zrušiť</button>
      </template>

      <!-- Ukladanie obhajoby priamo -->
      <template v-if="internship.status === 'Schválená' && !editMode">
        <button class="approve" @click="markDefended">Obhájiť</button>
        <button class="reject" @click="markNotDefended">Neobhájiť</button>
      </template>
    </div>
  </div>

  <div v-else class="loading">
    Načítavam detail…
  </div>
</template>

<script>
import axios from "axios";
import CompanyNavBar from '@/components/icons/CompanyNavBar.vue'

export default {
  name: "GarantPracticeDetailView",
  components: { CompanyNavBar },

  data() {
    return {
      internship: null,
      loading: true,
      status: 'vytvorena',
      editMode: false,
      editForm: {
        start_date: "",
        end_date: "",
        semester: "",
        year: null,
        status: "",
      },
    };
  },

  methods: {
    async loadDetail() {
      try {
        const id = this.$route.params.id;
        const response = await axios.get(`http://localhost:8000/api/garant/internships/${id}`, {
          headers: { Authorization: `Bearer ${localStorage.getItem("access_token")}` }
        });
        this.internship = response.data;

        // Predvyplnenie formulára
        this.editForm.start_date = this.internship.start_date ? this.internship.start_date.replace(" ", "T") : "";
        this.editForm.end_date = this.internship.end_date ? this.internship.end_date.replace(" ", "T") : "";
        this.editForm.semester = this.internship.semester || "";
        this.editForm.year = this.internship.year || new Date().getFullYear();
        this.editForm.status = this.internship.status || "Vytvorená";

      } catch (error) {
        console.error("Error loading detail:", error);
      } finally {
        this.loading = false;
      }
    },

    formatDate(date) {
      return date ? new Date(date).toLocaleDateString("sk-SK") : "";
    },

    async approveByGarant() {
      try {
        const id = this.$route.params.id;
        await axios.post(`http://localhost:8000/api/garant/internships/${id}/approve`, {}, {
          headers: { Authorization: `Bearer ${localStorage.getItem("access_token")}` }
        });
        alert("Prax bola schválená garantom.");
        this.loadDetail();
      } catch (error) {
        console.error(error);
      }
    },

    async rejectByGarant() {
      try {
        const id = this.$route.params.id;
        await axios.post(`http://localhost:8000/api/garant/internships/${id}/disapprove`, {}, {
          headers: { Authorization: `Bearer ${localStorage.getItem("access_token")}` }
        });
        alert("Prax bola neschválená garantom.");
        this.loadDetail();
      } catch (error) {
        console.error(error);
      }
    },

    async markDefended() {
      try {
        const id = this.$route.params.id;
        await axios.post(`http://localhost:8000/api/garant/internships/${id}/defended`, {}, {
          headers: { Authorization: `Bearer ${localStorage.getItem("access_token")}` }
        });
        alert("Prax bola označená ako obhájená.");
        this.loadDetail();
      } catch (error) {
        console.error(error);
      }
    },

    async markNotDefended() {
      try {
        const id = this.$route.params.id;
        await axios.post(`http://localhost:8000/api/garant/internships/${id}/not-defended`, {}, {
          headers: { Authorization: `Bearer ${localStorage.getItem("access_token")}` }
        });
        alert("Prax bola označená ako neobhájená.");
        this.loadDetail();
      } catch (error) {
        console.error(error);
      }
    },

    async saveEdit() {
      try {
        const id = this.$route.params.id;
        const payload = {
          start_date: this.editForm.start_date,
          end_date: this.editForm.end_date,
          semester: this.editForm.semester,
          year: this.editForm.year,
          status: this.editForm.status
        };
        await axios.put(`http://localhost:8000/api/garant/internships/${id}`, payload, {
          headers: { Authorization: `Bearer ${localStorage.getItem("access_token")}` }
        });
        alert("Prax bola úspešne aktualizovaná.");
        this.editMode = false;
        this.loadDetail();
      } catch (error) {
        console.error(error);
      }
    },

    cancelEdit() {
      this.editMode = false;
    }
  },

  mounted() {
    // status prichádza zo zoznamu cez query param (vďaka tomu sa vieme vrátiť späť do rovnakého filtra)
    this.status = this.$route.query.status || 'vytvorena'
    this.loadDetail();
  }
};
</script>

<style scoped>
.container {
  padding: 20px;
}

.card {
  border: 1px solid #ddd;
  padding: 20px;
  border-radius: 6px;
  margin-bottom: 20px;
  background: white;
}

.actions {
  display: flex;
  gap: 20px;
  margin-top: 15px;
}

.approve {
  background: #3aa76d;
  color: white;
  padding: 12px 20px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
}

.reject {
  background: #d9534f;
  color: white;
  padding: 12px 20px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
}

.loading {
  padding: 20px;
}

.form-group {
  margin-bottom: 12px;
}
.form-group label {
  display: block;
  margin-bottom: 6px;
  font-weight: 600;
}
.form-group input,
.form-group select {
  width: 100%;
  padding: 8px;
  border: 1px solid #ccc;
  border-radius: 4px;
}

/* Back */
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
</style>
