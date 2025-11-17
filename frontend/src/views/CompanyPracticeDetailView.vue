<template>
  <div class="container" v-if="internship">
    <div class="header-bar">
      <span class="header-title">Firemný portál • Praxe</span>
      <button class="header-back" @click="$router.push('/company/dashboard')">Domov</button>
    </div>
    <button class="back-btn" @click="$router.push('/company/dashboard')">← Domov</button>
    <h1>Detail praxe</h1>

    <div class="card">
      <h2>Študent</h2>
      <p><strong>Meno:</strong> {{ internship.student.first_name }} {{ internship.student.last_name }}</p>
      <p><strong>Email:</strong> {{ internship.student.email }}</p>

      <h2>Prax</h2>
      <template v-if="!editMode">
        <p><strong>Začiatok:</strong> {{ formatDate(internship.start_date) }}</p>
        <p><strong>Koniec:</strong> {{ formatDate(internship.end_date) }}</p>
        <p><strong>Semester:</strong> {{ internship.semester }}</p>
        <p><strong>Rok:</strong> {{ internship.year }}</p>
        <p><strong>Stav:</strong> {{ internship.status }}</p>
      </template>

      <template v-else>
        <p><strong>Aktuálny začiatok:</strong> {{ formatDate(internship.start_date) }}</p>
        <p><strong>Aktuálny koniec:</strong> {{ formatDate(internship.end_date) }}</p>
        <p><strong>Aktuálny semester:</strong> {{ internship.semester }}</p>
        <p><strong>Aktuálny rok:</strong> {{ internship.year }}</p>
        <p><strong>Aktuálny stav:</strong> {{ internship.status }}</p>

        <hr style="margin: 15px 0;">
        <h3>Upraviť údaje</h3>
        <div class="form-group">
          <label>Stav:</label>
          <select v-model="editForm.status">
            <option value="Vytvorená">Vytvorená</option>
            <option value="Potvrdená">Potvrdená</option>
            <option value="Zamietnutá">Zamietnutá</option>
          </select>
        </div>
      </template>
    </div>

    <div class="actions">
      <template v-if="internship.status === 'Vytvorená'">
        <button class="approve" @click="approve">Potvrdiť</button>
        <button class="reject" @click="reject">Zamietnuť</button>
      </template>

      <template v-if="!editMode && (internship.status === 'Potvrdená' || internship.status === 'Zamietnutá')">
        <button class="approve" style="background:#0b6b37" @click="editMode = true">Editovať prax</button>
      </template>

      <template v-if="editMode">
        <button class="approve" style="background:#0b6b37" @click="saveEdit">Uložiť zmeny</button>
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
  name: "CompanyPracticeDetailView",

  data() {
    return {
      internship: null,
      loading: true,
      editMode: false,
      editForm: {
        status: ''
      },
    };
  },

  methods: {
    async loadDetail() {
      try {
        const id = this.$route.params.id;
        const response = await axios.get(`http://localhost:8000/api/company/internships/${id}`);
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

    async approve() {
      try {
        const id = this.$route.params.id;
        await axios.post(`http://localhost:8000/api/company/internships/${id}/approve`);
        alert("Prax bola potvrdená.");
        this.$router.push("/company/practices");
      } catch (error) {
        console.error(error);
      }
    },

    async reject() {
      try {
        const id = this.$route.params.id;
        await axios.post(`http://localhost:8000/api/company/internships/${id}/reject`);
        alert("Prax bola zamietnutá.");
        this.$router.push("/company/practices");
      } catch (error) {
        console.error(error);
      }
    },

    async saveEdit() {
      try {
        const id = this.$route.params.id;
        await axios.put(`http://localhost:8000/api/company/internships/${id}/status`, {
          status: this.editForm.status
        });
        alert("Zmeny boli uložené.");
        this.editMode = false;
        this.loadDetail();
      } catch (error) {
        console.error(error);
      }
    },
    cancelEdit() {
      this.editMode = false;
    },

    edit() {
      const id = this.$route.params.id;
      this.$router.push(`/company/practices/${id}/edit`);
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
</style>
<style scoped>
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
<style scoped>
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