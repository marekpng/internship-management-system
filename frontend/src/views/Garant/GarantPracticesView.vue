<template>
  <CompanyNavBar>
    <template #filters>
      <div class="filter-bar">
        <!-- Filter tlačidlá: stav je v query parametri ?status=... -->
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

  <div class="container">
    <button
      class="back-btn"
      @click="$router.push('/garant/dashboard')"
    >
      ← Späť
    </button>
    <h1>Prax v stave: {{ title }}</h1>

    <div v-if="loading">Načítavam…</div>
    <div v-else-if="internships.length === 0">
      <p>Zatiaľ tu nie sú žiadne praxe v tomto stave.</p>
    </div>

    <ul v-else class="practice-list">
      <li
        v-for="internship in internships"
        :key="internship.id"
        class="practice-item"
      >
        <!-- Router link je bezpečnejší než @click (žiadne prekliky na zlý záznam) -->
        <router-link
          class="practice-link"
          :to="{ name: 'garantPracticeDetail', params: { id: internship.id }, query: { status: status || 'vytvorena' } }"
        >
          <strong>{{ internship.student?.first_name || "Neznámy študent" }} {{ internship.student?.last_name || "" }}</strong>
          <div>{{ internship.student?.email || "" }}</div>
          <div>{{ internship.status }} — vytvorená: {{ formatDate(internship.created_at) }}</div>
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
    }
  },

  methods: {
    async loadInternships() {
      try {
        this.status = this.$route.query.status || "vytvorena";
        const map = this.statusMap[this.status] || this.statusMap["vytvorena"];
        this.title = map.title;

        let url = "";

        // 🔥 Rozlíšenie medzi "všetky" a ostatnými stavmi
        if (this.status === "vsetky") {
          // Načítanie všetkých praxí cez všeobecné API
          url = "http://localhost:8000/api/internships/myNew";
        } else {
          // Filtrovanie podľa stavu cez garant API
          url = "http://localhost:8000/api/garant/internships/status/" + encodeURIComponent(map.api);
        }

        const response = await axios.get(url, {
          headers: {
            Authorization: `Bearer ${localStorage.getItem("access_token")}`
          }
        });

        console.log("API response:", response.data);
        this.internships = response.data;

      } catch (e) {
        console.error("Error loading internships:", e);
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
</style>
