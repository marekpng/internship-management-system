<template>
  <div class="company-dashboard">

    <section class="hero-section">
      <div class="container hero-content">
        <div>
          <span class="chip">Firemný portál</span>
          <h1>Vitajte, {{ company?.company_name }}</h1>
          <p class="lead">
            Spravujte študentské praxe, schvaľujte žiadosti a majte prehľad na jednom mieste.
          </p>
          <div class="actions">
            <router-link to="/company/practices" class="btn-primary">
              Zobraziť čakajúce praxe
            </router-link>
                <button class="btn-secondary" @click="logout">
                  Odhlásiť sa
                </button>
          </div>
        </div>
      </div>
    </section>

    <section class="stats-section">
      <div class="container stats-grid">
        <div class="stat-card" @click="goToStatus('Vytvorená')" style="cursor: pointer;">
          <h3>Čakajúce</h3>
          <p class="stat-number">{{ pendingCount }}</p>
        </div>
        <div class="stat-card" @click="goToStatus('Potvrdená')" style="cursor: pointer;">
          <h3>Potvrdené</h3>
          <p class="stat-number">{{ approvedCount }}</p>
        </div>
        <div class="stat-card" @click="goToStatus('Zamietnutá')" style="cursor: pointer;">
          <h3>Zamietnuté</h3>
          <p class="stat-number">{{ rejectedCount }}</p>
        </div>
      </div>
    </section>

  </div>
</template>

<script>
import axios from 'axios'

export default {
  name: "CompanyDashboardView",

  data() {
    return {
      company: null,
      pendingCount: 0,
      approvedCount: 0,
      rejectedCount: 0,
    };
  },

  mounted() {
    axios.defaults.headers.common['Authorization'] = `Bearer ${localStorage.getItem('access_token')}`;
    axios
      .get("http://127.0.0.1:8000/api/company/dashboard")
      .then((res) => {
        this.company = res.data.company;
        if (res.data.stats) {
          this.pendingCount = res.data.stats.pending || 0;
          this.approvedCount = res.data.stats.approved || 0;
          this.rejectedCount = res.data.stats.rejected || 0;
        }
      })
      .catch((err) => {
        console.error("Nepodarilo sa načítať dáta firmy:", err);
      });

    axios
      .get("http://127.0.0.1:8000/api/company/internships/pending")
      .then((res) => {
        this.pendingCount = Array.isArray(res.data) ? res.data.length : 0;
      })
      .catch((err) => {
        console.error("Nepodarilo sa načítať čakajúce praxe:", err);
      });
  },

  methods: {
    logout() {
      localStorage.removeItem('access_token');
      localStorage.removeItem('user');
      this.$router.push('/login');
    },
    goToStatus(status) {
      this.$router.push(`/company/practices?status=${encodeURIComponent(status)}`);
    }
  },
};
</script>

<style scoped>
.company-dashboard {
  background: #f4f8f5;
  min-height: 100vh;
}

.hero-section {
  padding: 60px 0;
  background: #e9f5ee;
}

.hero-content h1 {
  font-size: 36px;
  margin-bottom: 10px;
}

.lead {
  font-size: 18px;
  margin-bottom: 20px;
  color: #333;
}

.btn-primary {
  display: inline-block;
  padding: 10px 20px;
  background: #0b6b37;
  color: white;
  border-radius: 6px;
  text-decoration: none;
  font-weight: 600;
}

.btn-primary:hover {
  background: #095a2f;
}

.chip {
  display: inline-block;
  background: #cce9dc;
  color: #0b6b37;
  padding: 6px 14px;
  border-radius: 20px;
  font-size: 14px;
  margin-bottom: 12px;
  font-weight: 600;
}

.stats-section {
  padding: 50px 0;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 24px;
}

.stat-card {
  background: white;
  padding: 24px;
  border-radius: 10px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.05);
  text-align: center;
}

.stat-card h3 {
  font-size: 20px;
  margin-bottom: 10px;
  color: #0b6b37;
}

.stat-number {
  font-size: 32px;
  font-weight: bold;
  color: #333;
}

.btn-secondary {
  display: inline-block;
  padding: 10px 20px;
  background: #ffffff;
  color: #0b6b37;
  border: 2px solid #0b6b37;
  border-radius: 6px;
  text-decoration: none;
  font-weight: 600;
  margin-left: 12px;
  cursor: pointer;
}

.btn-secondary:hover {
  background: #f0f6f2;
}
</style>
