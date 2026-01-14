<template>
  <div class="landing">
    <HeaderComponent />
    <div class="login-body">
      <div class="login-container">
        <div class="login-box">
          <div class="login-logo">
            <img src="@/assets/logo-fpv.png" alt="UKF FPV logo" />
          </div>

          <h2>Prihlásenie</h2>

          <form @submit.prevent="login">
            <label for="email">Email</label>
            <input
              type="email"
              id="email"
              v-model.trim="email"
              placeholder="Zadaj email"
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
              {{ loading ? "Prihlasujem..." : "Prihlásiť sa" }}
            </button>
          </form>

          <p class="register-link">
            Nemáš účet?
            <router-link to="/register">Zaregistruj sa</router-link>
          </p>
        </div>
      </div>
    </div>
    <FooterComponent />
  </div>
</template>

<script setup>
import { ref } from "vue";
import axios from "axios";
import { useRouter } from "vue-router";

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

    // Skontrolujeme, či backend nevyžaduje zmenu hesla
    if (res.data.status === "FORCE_PASSWORD_CHANGE") {
      localStorage.setItem("user_email", res.data.user.email);
      router.push("/change-password");
      return;
    }

    // Uložíme token a používateľa
    localStorage.setItem("access_token", res.data.access_token);
    localStorage.setItem("user", JSON.stringify(res.data.user));

// Presmerovanie podľa roly
const role = res.data.user.roles?.[0] || null

    setTimeout(() => {
      if (role === "student") {
        router.push("/student/dashboard");
      } else if (role === "company") {
        router.push("/company/dashboard");
      } else if (role === "garant") {
        router.push("/garant/dashboard");
      } else {
        router.push("/");
      }
    }, 200);
  } catch (err) {
    if (err.response) {
      if (err.response.data.status === "FORCE_PASSWORD_CHANGE") {
        localStorage.setItem("user_email", err.response.data.user.email);
        router.push("/change-password");
        return;
      }
      error.value = err.response.data.message || "Nesprávny email alebo heslo.";
    } else {
      error.value = "Chyba pripojenia k serveru.";
    }
  } finally {
    loading.value = false;
  }
}

import "@/assets/landing.css";
import HeaderComponent from "@/components/HeaderComponent.vue";
import FooterComponent from "@/components/FooterComponent.vue";
</script>

<style src="../assets/login.css"></style>
