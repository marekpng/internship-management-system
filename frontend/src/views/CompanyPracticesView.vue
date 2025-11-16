<template>
  <div class="container">
    <h1>Prax v stave „Vytvorená“</h1>

    <div v-if="loading">Načítavam…</div>
    <div v-else-if="internships.length === 0">
      <p>Zatiaľ tu nie sú žiadne nové praxe.</p>
    </div>

    <ul v-else class="practice-list">
      <li
        v-for="internship in internships"
        :key="internship.id"
        @click="goToDetail(internship.id)"
        class="practice-item"
      >
        <strong>{{ internship.student.first_name }} {{ internship.student.last_name }}</strong>
        <div>{{ internship.student.email }}</div>
        <div>Vytvorená: {{ formatDate(internship.created_at) }}</div>
      </li>
    </ul>
  </div>
</template>

<script>
import axios from "axios";

export default {
  name: "CompanyPracticesView",

  data() {
    return {
      internships: [],
      loading: true,
    };
  },

  methods: {
    async loadInternships() {
      try {
        const response = await axios.get("/company/internships/pending");
        this.internships = response.data;
      } catch (e) {
        console.error("Error loading internships:", e);
      } finally {
        this.loading = false;
      }
    },

    goToDetail(id) {
      this.$router.push(`/company/practices/${id}`);
    },

    formatDate(date) {
      return new Date(date).toLocaleDateString("sk-SK");
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
</style>
