<template>
  <div class="landing">
    <HeaderComponent />

    <div class="login-body">
      <div class="login-container">
        <div class="login-box">
          <div class="login-logo">
            <img src="@/assets/logo-fpv.png" alt="UKF FPV logo" />
          </div>

          <h2>Admin Prihlásenie</h2>

          <form @submit.prevent="login">
            <label for="email">Email</label>
            <input
              type="email"
              id="email"
              v-model.trim="email"
              placeholder="Zadaj admin email"
              required
            />

            <label for="password">Heslo</label>
            <input
              type="password"
              id="password"
              v-model.trim="password"
              placeholder="Zadaj heslo"
              required
            />

            <div v-if="error" class="error">{{ error }}</div>

            <button type="submit" :disabled="loading">
              {{ loading ? 'Prihlasujem...' : 'Prihlásiť sa' }}
            </button>
          </form>

          <p class="register-link">
            Toto je len pre administrátorov
          </p>
        </div>
      </div>
    </div>


  </div>
</template>

<script>
import axios from "axios";
import { ref } from "vue";
import { useRouter } from "vue-router";
import HeaderComponent from "@/components/HeaderComponent.vue";
import FooterComponent from "@/components/FooterComponent.vue";

export default {
  components: {
    HeaderComponent,
    FooterComponent,
  },

  setup() {
    const email = ref("");
    const password = ref("");
    const error = ref("");
    const loading = ref(false);
    const router = useRouter();

    async function login() {
      error.value = "";
      loading.value = true;

      try {
        const res = await axios.post("http://127.0.0.1:8000/api/login", {
          email: email.value,
          password: password.value,
        });

        const user = res.data.user;

        // Musí mať rolu admin
        if (!user.roles.includes("admin")) {
          error.value = "Nemáte oprávnenie pre prístup do admin sekcie.";
          return;
        }

        // Uložíme token
        localStorage.setItem("access_token", res.data.access_token);
        localStorage.setItem("user", JSON.stringify(user));

        // presmeruj admina
        setTimeout(() => {
          router.push("/admin/dashboard");
        }, 200);
      } catch (err) {
        if (err.response) {
          error.value = err.response.data.message || "Nesprávny email alebo heslo.";
        } else {
          error.value = "Chyba pripojenia k serveru.";
        }
      } finally {
        loading.value = false;
      }
    }

    return { email, password, error, loading, login };
  },
};
</script>

<style src="../../assets/login.css"></style>
