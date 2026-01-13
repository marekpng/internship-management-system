
<template>
  <div class="landing">
    <HeaderComponent />
<nav class="admin-nav">
      <div class="nav-container">
        <button class="nav-btn" @click="goBack">Admin Dashboard</button>
        <span class="nav-title">Admin Panel</span>
        <button class="nav-btn logout" @click="logout">Odhlásiť sa</button>
      </div>
    </nav>
            <div class="admin-dashboard">
             

                <button class="role-btn" @click="goToRoleSettings">
                Nastavenia rolí
                </button>
            </div>
   

    <FooterComponent />
  </div>
</template>
<script>
import { useRouter } from "vue-router";
import axios from "axios";
import { ref } from "vue";
import HeaderComponent from "@/components/HeaderComponent.vue";
import FooterComponent from "@/components/FooterComponent.vue";

export default {
    components: {
    HeaderComponent,
    FooterComponent,
  },
  setup() {
    const router = useRouter();

    function goToRoleSettings() {
      router.push("/admin/role-settings");
    }
    function logout() {
      localStorage.removeItem("access_token");
      router.push("/login");
    }
    return { goToRoleSettings, logout };
  },
};
</script>

<style>
.admin-dashboard {
  padding: 40px;
  text-align: center;
}

.role-btn {
  padding: 12px 24px;
  background-color: #008736;
  color: white;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-size: 18px;
}

.role-btn:hover {
  background-color: #003f86;
}
.admin-nav {
  background: #008736;
  padding: 14px 0;
  margin-bottom: 30px;
}

.nav-container {
  width: 90%;
  margin: auto;
  display: flex;
  justify-content: space-between;
  align-items: center;
  color: white;
}

.nav-title {
  font-size: 25px;
  font-weight: bold;
}

.nav-btn {
  background: #016d2c;
  color: white;
  border: none;
  padding: 10px 18px;
  border-radius: 6px;
  cursor: pointer;
}

.nav-btn:hover {
  background: #008736;
}

.logout {
  background: #d9534f !important;
}
</style>
