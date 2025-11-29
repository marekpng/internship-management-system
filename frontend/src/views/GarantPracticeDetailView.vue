<template>
  <div class="container" v-if="internship">
    <div class="header-bar">
      <span class="header-title">Garant • Praxe</span>
      <button class="header-back" @click="$router.push('/garant/dashboard')">Domov</button>
    </div>

    <button class="back-btn" @click="$router.push('/garant/practices')">← Späť</button>

    <h1>Detail praxe</h1>

    <div class="card">
      <h2>Študent</h2>
      <p><strong>Meno:</strong> {{ internship.student?.first_name || "Neznámy študent" }} {{ internship.student?.last_name || "" }}</p>
      <p><strong>Email:</strong> {{ internship.student?.email || "" }}</p>

      <h2>Prax</h2>
      <p><strong>Začiatok:</strong> {{ formatDate(internship.start_date) }}</p>
      <p><strong>Koniec:</strong> {{ formatDate(internship.end_date) }}</p>
      <p><strong>Semester:</strong> {{ internship.semester }}</p>
      <p><strong>Rok:</strong> {{ internship.year }}</p>
      <p><strong>Stav:</strong> {{ internship.status }}</p>

      <template v-if="editMode">
        <hr style="margin: 15px 0;">
        <h3>Zmeniť stav</h3>

        <div class="form-group">
          <label>Nový stav:</label>
          <select v-model="editForm.status">
            <option value="Obhájená">Obhájené</option>
            <option value="Neobhájená">Neobhájené</option>
          </select>
        </div>
      </template>
    </div>

    <div class="actions">

      <!-- Garant môže SCHVÁLIŤ iba ak je prax POTVRDENÁ -->
      <template v-if="internship.status === 'Potvrdená'">
        <button class="approve" @click="approveByGarant">Schváliť prax</button>
        <button class="reject" @click="rejectByGarant">Neschváliť prax</button>
      </template>

      <!-- Garant môže editovať len SCHVÁLENÚ prax -->
      <template v-if="internship.status === 'Schválená' && !editMode">
        <button class="approve" style="background:#0b6b37" @click="editMode = true">Nastaviť obhajobu</button>
      </template>

      <!-- Ukladanie obhajoby -->
      <template v-if="editMode">
        <button class="approve" style="background:#0b6b37" @click="saveEdit">Uložiť</button>
        <button class="reject" @click="cancelEdit">Zrušiť</button>
      </template>

    </div>
  </div>

  <div v-else class="loading">
    Načítavam detail…
  </div>
</template>

<script>
import axios from "axios";

export default {
  name: "GarantPracticeDetailView",

  data() {
    return {
      internship: null,
      loading: true,
      editMode: false,
      editForm: {
        status: ""
      },
    };
  },

  methods: {
    async loadDetail() {
      try {
        const id = this.$route.params.id;
        const response = await axios.get(
          `http://localhost:8000/api/garant/internships/${id}`,
          { headers: { Authorization: `Bearer ${localStorage.getItem("access_token")}` } }
        );
        this.internship = response.data;
        this.editForm.status = response.data.status;
      } catch (error) {
        console.error("Error loading detail:", error);
      } finally {
        this.loading = false;
      }
    },

    formatDate(date) {
      return new Date(date).toLocaleDateString("sk-SK");
    },

    async approveByGarant() {
      try {
        const id = this.$route.params.id;
        await axios.post(
          `http://localhost:8000/api/garant/internships/${id}/approve`,
          {},
          { headers: { Authorization: `Bearer ${localStorage.getItem("access_token")}` } }
        );
        alert("Prax bola schválená garantom.");
        this.loadDetail();
      } catch (error) {
        console.error(error);
      }
    },

    async rejectByGarant() {
      try {
        const id = this.$route.params.id;
        await axios.post(
          `http://localhost:8000/api/garant/internships/${id}/disapprove`,
          {},
          { headers: { Authorization: `Bearer ${localStorage.getItem("access_token")}` } }
        );
        alert("Prax bola neschválená garantom.");
        this.loadDetail();
      } catch (error) {
        console.error(error);
      }
    },

    async saveEdit() {
      try {
        const id = this.$route.params.id;
        if (this.editForm.status === 'Obhájená') {
          await axios.post(
            `http://localhost:8000/api/garant/internships/${id}/defended`,
            {},
            { headers: { Authorization: `Bearer ${localStorage.getItem("access_token")}` } }
          );
        } else if (this.editForm.status === 'Neobhájená') {
          await axios.post(
            `http://localhost:8000/api/garant/internships/${id}/not-defended`,
            {},
            { headers: { Authorization: `Bearer ${localStorage.getItem("access_token")}` } }
          );
        }
        alert("Stav bol aktualizovaný.");
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
.form-group select {
  width: 100%;
  padding: 8px;
  border: 1px solid #ccc;
  border-radius: 4px;
}

/* Header + back */
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

.header-bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: #0b6b37;
  padding: 12px 20px;
  color: white;
  margin-bottom: 15px;
}
.header-title {
  font-size: 16px;
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
</style>
