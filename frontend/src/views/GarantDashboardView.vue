<template>
  <div class="garant-dashboard">

    <section class="hero-section">
      <div class="container hero-content">
        <div>
          <span class="chip">Garant portál</span>
          <h1>Vitajte, {{ garant?.first_name, garant?.last_name }}</h1>
          <p class="lead">
            Spravujte a kontrolujte všetky študentské praxe na vašej fakulte.
          </p>
          <div class="actions">
            <router-link to="/garant/practices" class="btn-primary">
              Zobraziť všetky praxe
            </router-link>
            <button class="btn-secondary" @click="logout">
              Odhlásiť sa
            </button>
          </div>
        </div>
      </div>
    </section>
    <RouterLink to="/garant/export" class="menu-item">
  Export údajov
</RouterLink>


    <section class="stats-section">
      <div class="container stats-grid">
        <div class="stat-card" @click="goToStatus('vsetky')" style="cursor: pointer;">
          <h3>Všetky</h3>
          <p class="stat-number">{{ allCount }}</p>
        </div>
        <div class="stat-card" @click="goToStatus('vytvorena')" style="cursor: pointer;">
          <h3>Čakajúce</h3>
          <p class="stat-number">{{ pendingCount }}</p>
        </div>
        <div class="stat-card" @click="goToStatus('potvrdena')" style="cursor: pointer;">
          <h3>Potvrdené</h3>
          <p class="stat-number">{{ approvedCount }}</p>
        </div>
        <div class="stat-card" @click="goToStatus('zamietnute')" style="cursor: pointer;">
          <h3>Zamietnute</h3>
          <p class="stat-number">{{ rejectedCount }}</p>
        </div>
        <div class="stat-card" @click="goToStatus('schvalena')" style="cursor: pointer;">
          <h3>Schválené</h3>
          <p class="stat-number">{{ approvedCountGarant }}</p>
        </div>
        <div class="stat-card" @click="goToStatus('neschvalena')" style="cursor: pointer;">
          <h3>Neschválené</h3>
          <p class="stat-number">{{ rejectedCountGarant }}</p>
        </div>
        <div class="stat-card" @click="goToStatus('obhajena')" style="cursor: pointer;">
          <h3>Obhájené</h3>
          <p class="stat-number">{{ defendedCount }}</p>
        </div>
        <div class="stat-card" @click="goToStatus('neobhajena')" style="cursor: pointer;">
          <h3>Neobhájené</h3>
          <p class="stat-number">{{ notDefendedCount }}</p>
        </div>
      </div>
    </section>

  </div>
</template>

<script>
import axios from 'axios'

export default {
  name: "GarantDashboardView",

  data() {
    return {
      garant: null,
      allCount: 0,
      pendingCount: 0,
      approvedCount: 0,
      rejectedCount: 0,
      defendedCount: 0,
      rejectedCountGarant: 0,
      approvedCountGarant: 0,
      notDefendedCount: 0,
    };
  },

  mounted() {
    axios.defaults.headers.common['Authorization'] = `Bearer ${localStorage.getItem('access_token')}`;

    axios.get("http://127.0.0.1:8000/api/garant/internships/count/Vytvorená")
      .then((res) => { 
        this.pendingCount = res.data.count;
        this.updateAllCount();
      });

    axios.get("http://127.0.0.1:8000/api/garant/internships/count/Schválená")
      .then((res) => { 
        this.approvedCountGarant = res.data.count;
        this.updateAllCount();
      });

    axios.get("http://127.0.0.1:8000/api/garant/internships/count/Neschválená")
      .then((res) => { 
        this.rejectedCountGarant = res.data.count;
        this.updateAllCount();
      });

    axios.get("http://127.0.0.1:8000/api/garant/internships/count/Potvrdená")
      .then((res) => { 
        this.approvedCount = res.data.count;
        this.updateAllCount();
      });

    axios.get("http://127.0.0.1:8000/api/garant/internships/count/Zamietnutá")
      .then((res) => { 
        this.rejectedCount = res.data.count;
        this.updateAllCount();
      });

    axios.get("http://127.0.0.1:8000/api/garant/internships/count/Obhájená")
      .then((res) => { 
        this.defendedCount = res.data.count;
        this.updateAllCount();
      });

    axios.get("http://127.0.0.1:8000/api/garant/internships/count/Neobhájená")
      .then((res) => { 
        this.notDefendedCount = res.data.count;
        this.updateAllCount();
      });
  },

  methods: {
    logout() {
      localStorage.removeItem('access_token');
      localStorage.removeItem('user');
      this.$router.push('/login');
    },
    goToStatus(status) {
      this.$router.push(`/garant/practices?status=${encodeURIComponent(status)}`);
    },
    updateAllCount() {
      this.allCount =
        this.pendingCount +
        this.approvedCount +
        this.rejectedCount +
        this.approvedCountGarant +
        this.rejectedCountGarant +
        this.defendedCount +
        this.notDefendedCount;
    }
  },
};
</script>


<style scoped>
.garant-dashboard {
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
