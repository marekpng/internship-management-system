<template>
  <div class="page-wrapper">
    <div class="content">
      <CompanyNavBar>
        <template #filters>
          <div class="filter-bar">
            <router-link to="/company/practices?status=Vytvorená" class="filter-btn"
              :class="{ active: $route.query.status === 'Vytvorená' }">
              Čakajúce
            </router-link>

            <router-link to="/company/practices?status=Potvrdená" class="filter-btn"
              :class="{ active: $route.query.status === 'Potvrdená' }">
              Potvrdené
            </router-link>

            <router-link to="/company/practices?status=Zamietnutá" class="filter-btn"
              :class="{ active: $route.query.status === 'Zamietnutá' }">
              Zamietnuté
            </router-link>
          </div>
        </template>
      </CompanyNavBar>

      <div class="container">


        <div v-if="loading">Načítavam…</div>

        <div v-else-if="internships.length === 0">
          <p>Zatiaľ tu nie sú žiadne praxe v tomto stave.</p>
        </div>

        <ul v-else class="practice-list">
          <li v-for="internship in internships" :key="internship.id" @click="goToDetail(internship.id)"
            class="practice-item">
            <strong>{{ internship.student.first_name }} {{ internship.student.last_name }}</strong>

            <div>{{ internship.student.email }}</div>
            <div>{{ internship.status }}: {{ formatDate(internship.created_at) }}</div>

            <!-- 🔥 INFO O DOKUMENTOCH -->
            <div class="doc-row">
              <span class="doc-badge" :class="internship.hasDocuments ? 'badge-green' : 'badge-red'">
                Dokumenty: {{ internship.hasDocuments ? "Áno" : "Nie" }}
              </span>
            </div>
          </li>
        </ul>
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
</script>

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
    };
  },

  methods: {
    async loadInternships() {
      try {
        this.status = this.$route.query.status || "Vytvorená";

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

        const response = await axios.get(url);
        console.log("API response:", response.data);
        this.internships = response.data;

        // 🔥 doplnenie dokumentov pre každú prax
        for (let internship of this.internships) {
          try {
            const res = await axios.get(`http://localhost:8000/api/internships/${internship.id}/documents`);
            internship.hasDocuments = res.data.length > 0;
          } catch {
            internship.hasDocuments = false;
          }
        }
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
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.07);
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
</style>
