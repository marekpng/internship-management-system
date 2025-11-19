
<template>
  <div class="landing">
    <HeaderComponent />

            <div class="admin-dashboard">
                <h1>Admin Panel</h1>

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

    return { goToRoleSettings };
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
  background-color: #0057b8;
  color: white;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-size: 18px;
}

.role-btn:hover {
  background-color: #003f86;
}
</style>
