<template>
  <div class="container">
    <div class="header-bar">
      <span class="header-title">Garant • Praxe</span>
      <button class="header-back" @click="$router.push('/garant/dashboard')">Domov</button>
    </div>

    <nav class="navbar">
      <div class="nav-left">
        <span class="nav-title">Študentské praxe</span>
      </div>
      <div class="nav-right">
        <button class="nav-btn" @click="$router.push('/garant/practices?status=vsetky')">Všetky</button>
        <button class="nav-btn" @click="$router.push('/garant/practices?status=vytvorena')">Vytvorené</button>
        <button class="nav-btn" @click="$router.push('/garant/practices?status=potvrdena')">Potvrdené</button>
        <button class="nav-btn" @click="$router.push('/garant/practices?status=zamietnuta')">Zamietnuté</button>
        <button class="nav-btn" @click="$router.push('/garant/practices?status=schvalena')">Schválené</button>
        <button class="nav-btn" @click="$router.push('/garant/practices?status=neschvalena')">Neschválené</button>
        <button class="nav-btn" @click="$router.push('/garant/practices?status=obhajena')">Obhájené</button>
        <button class="nav-btn" @click="$router.push('/garant/practices?status=neobhajena')">Neobhájené</button>
      </div>
    </nav>

    <h1>Prax v stave: {{ title }}</h1>

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
        <strong>{{ internship.student?.first_name || "Neznámy študent" }} {{ internship.student?.last_name || "" }}</strong>
        <div>{{ internship.student?.email || "" }}</div>
        <div>{{ internship.status }} — vytvorená: {{ formatDate(internship.created_at) }}</div>
      </li>
    </ul>
  </div>
</template>

<script>
import axios from "axios";

export default {
  name: "GarantPracticesView",

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

        let url = "http://localhost:8000/api/garant/internships";
if (this.status !== "vsetky") {
  url += `/status/${encodeURIComponent(map.api)}`;
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

    goToDetail(id) {
      this.$router.push(`/garant/practices/${id}`);
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
</style>
