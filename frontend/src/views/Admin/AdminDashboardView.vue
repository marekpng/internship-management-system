
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
/* ADMIN NAVBAR */
.admin-nav {
  background: #008736; 
  position: sticky;
  top: 0;
  z-index: 100;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
}

.nav-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 14px 20px;

  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  color: white;
}

/* TITLE */
.nav-title {
  font-size: 20px;
  font-weight: 600;
  letter-spacing: 0.3px;
  white-space: nowrap;
}

/* BUTTONS */
.nav-btn {
  background: rgba(255, 255, 255, 0.15);
  border: none;
  color: white;
  padding: 8px 16px;
  border-radius: 10px;
  cursor: pointer;
  font-size: 14px;
  transition: all 0.2s ease;
}

.nav-btn:hover {
  background: rgba(255, 255, 255, 0.25);
}

/* LOGOUT */
.nav-btn.logout {
  background: #d9534f;
}

.nav-btn.logout:hover {
  background: #c9302c;
}

/* ===== RESPONSIVE ===== */
@media (max-width: 768px) {
  .nav-container {
    flex-wrap: wrap;
    justify-content: center;
  }

  .nav-title {
    order: -1;
    width: 100%;
    text-align: center;
    margin-bottom: 10px;
  }
}

</style>
