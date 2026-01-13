<template>
  <div class="landing">
    <HeaderComponent />

    <!-- ADMIN NAVBAR -->
    <nav class="admin-nav">
      <div class="nav-container">
        <button class="nav-btn" @click="goBack">Admin Dashboard</button>
        <span class="nav-title">Nastavenia rolí</span>
        <button class="nav-btn logout" @click="logout">Odhlásiť sa</button>
      </div>
    </nav>

    <div class="container">
      <h1 class="page-title">Nastavenia rolí</h1>

      <div class="card">
        <table class="role-table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Meno</th>
              <th>Email</th>
              <th>Roly</th>
              <th>Akcia</th>
            </tr>
          </thead>

          <tbody>
            <tr v-for="user in users" :key="user.id">
              <td>{{ user.id }}</td>
              <td>{{ user.first_name }} {{ user.last_name }}</td>
              <td>{{ user.email }}</td>

              <td>
                <span v-for="role in user.roles" :key="role" class="badge">
                  {{ role.name }}
                </span>
              </td>

              <td>
                <button class="btn-primary" @click="editUser(user)">Upraviť</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- MODAL -->
    <div v-if="selectedUser" class="modal">
      <div class="modal-content">
        <h3>Zmeniť roly pre {{ selectedUser.email }}</h3>

        <label>Vyber roly:</label>
        <select v-model="selectedRoles" multiple>
          <option v-for="role in roles" :key="role" :value="role">
            {{ role }}
          </option>
        </select>

        <div class="modal-actions">
          <button class="btn-primary" @click="updateRole">Uložiť</button>
          <button class="btn-secondary" @click="closeModal">Zrušiť</button>
        </div>
      </div>
    </div>

    <FooterComponent />
  </div>
</template>

<script>
import { useRouter } from "vue-router";
import axios from "axios";
import { ref, onMounted } from "vue";
import HeaderComponent from "@/components/HeaderComponent.vue";
import FooterComponent from "@/components/FooterComponent.vue";

export default {
  components: {
    HeaderComponent,
    FooterComponent,
  },

  setup() {
    const router = useRouter();

    const users = ref([]);
    const roles = ref(["student", "company", "garant", "admin", "external"]);

    const selectedUser = ref(null);
    const selectedRoles = ref([]);

    const token = localStorage.getItem("access_token");

    function goBack() {
      router.push("/admin/dashboard");
    }

    function logout() {
      localStorage.removeItem("access_token");
      router.push("/login");
    }

    async function loadUsers() {
      const res = await axios.get("http://127.0.0.1:8000/api/admin/users", {
        headers: { Authorization: `Bearer ${token}` },
      });

      users.value = res.data;
    }

    function editUser(user) {
      selectedUser.value = user;
      selectedRoles.value = [...user.roles];
    }

    function closeModal() {
      selectedUser.value = null;
      selectedRoles.value = [];
    }

    async function updateRole() {
      await axios.put(
        `http://127.0.0.1:8000/api/admin/users/${selectedUser.value.id}`,
        { roles: selectedRoles.value },
        {
          headers: {
            Authorization: `Bearer ${token}`,
          },
        }
      );

      selectedUser.value.roles = [...selectedRoles.value];
       await loadUsers();
      closeModal();
    }

    onMounted(loadUsers);

    return {
      goBack,
      logout,
      users,
      roles,
      selectedUser,
      selectedRoles,
      editUser,
      closeModal,
      updateRole,
    };
  },
};
</script>

<style scoped>
/* ADMIN NAVBAR */
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

/* MAIN LAYOUT */
.container {
  width: 90%;
  margin: auto;
}

.page-title {
  text-align: center;
  margin-bottom: 25px;
  color: #003f86;
  font-size: 32px;
}

.card {
  background: white;
  padding: 24px;
  border-radius: 14px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.08);
  margin-bottom: 40px;
}

/* TABLE */
.role-table {
  width: 100%;
  border-collapse: collapse;
}

th {
  background: #008736;
  color: white;
  padding: 14px;
}

td {
  padding: 14px;
  border-bottom: 1px solid #ddd;
}

.badge {
  background: #008736;
  padding: 4px 10px;
  border-radius: 6px;
  color: white;
  font-size: 12px;
  margin-right: 6px;
}

/* BUTTONS */
.btn-primary {
  background: #008736;
  padding: 8px 16px;
  color: white;
  border-radius: 6px;
  border: none;
  cursor: pointer;
}

.btn-primary:hover {
  background: #008736;
}

.btn-secondary {
  background: #ccc;
  padding: 8px 16px;
  border-radius: 6px;
  border: none;
  cursor: pointer;
}

/* MODAL */
.modal {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0,0,0,0.6);
  display: flex;
  justify-content: center;
  align-items: center;
}

.modal-content {
  background: white;
  width: 400px;
  padding: 30px;
  border-radius: 12px;
}

.modal-actions {
  margin-top: 20px;
  display: flex;
  justify-content: space-between;
}

select {
  width: 100%;
  padding: 10px;
  margin-top: 8px;
}
</style>
