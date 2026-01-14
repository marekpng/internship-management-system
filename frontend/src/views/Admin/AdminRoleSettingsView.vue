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

      <div class="table-wrapper">
        <table class="role-table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Meno</th>
              <th>Email</th>
              <th>Role</th>
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
.table-wrapper {
  width: 100%;
  overflow-x: auto;
}

.role-table {
  min-width: 720px;
  width: 100%;
  border-collapse: collapse;
}
/* ---------- RESPONSIVE ---------- */

/* TABLET */
@media (max-width: 1024px) {
  .page-title {
    font-size: 26px;
  }
}

/* MOBILE */
@media (max-width: 768px) {
  .nav-container {
    flex-direction: column;
    align-items: stretch;
    text-align: center;
  }

  .nav-btn {
    width: 100%;
  }

  .page-title {
    font-size: 22px;
  }

  th, td {
    padding: 10px;
    font-size: 14px;
  }

  .badge {
    display: inline-block;
    margin-bottom: 4px;
  }

  .modal-actions {
    flex-direction: column;
    gap: 10px;
  }

  .modal-actions button {
    width: 100%;
  }
}

</style>
