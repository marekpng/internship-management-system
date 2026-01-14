<template>
  <CompanyNavBar>
    <template #filters>
      <div class="filter-bar">
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

  <div class="container" v-if="internship">
    <button
      class="back-btn"
      @click="$router.push({ path: '/garant/practices', query: { status: status === 'zamietnute' ? 'zamietnuta' : status } })"
    >
      ← Späť
    </button>

    <h1>Detail praxe</h1>

    <!-- ============================= -->
    <!-- INFO KARTA -->
    <!-- ============================= -->
    <div class="card">
      <div class="card-header">
        <h2>Prehľad</h2>

        <!-- ✅ NOVÉ tlačidlo: edit vždy -->
        <button
          v-if="!editMode"
          class="btn-outline"
          type="button"
          @click="startFullEdit"
        >
          ✏️ Upraviť všetky údaje
        </button>
      </div>

      <div v-if="!editMode">
        <h3>Študent</h3>
        <p><strong>Meno:</strong> {{ internship.student?.first_name || "Neznámy študent" }} {{ internship.student?.last_name || "" }}</p>
        <p><strong>Email:</strong> {{ internship.student?.email || "" }}</p>

        <h3>Firma</h3>
        <p><strong>Názov:</strong> {{ internship.company?.company_name || internship.company?.first_name || "Neznáma firma" }}</p>
        <p><strong>Email:</strong> {{ internship.company?.email || "" }}</p>

        <h3>Prax</h3>
        <p><strong>Začiatok:</strong> {{ formatDate(internship.start_date) }}</p>
        <p><strong>Koniec:</strong> {{ formatDate(internship.end_date) }}</p>
        <p><strong>Semester:</strong> {{ internship.semester }}</p>
        <p><strong>Rok:</strong> {{ internship.year }}</p>
        <p><strong>Stav:</strong> {{ internship.status }}</p>
      </div>

      <!-- ============================= -->
      <!-- ✅ FULL EDIT MODE -->
      <!-- ============================= -->
      <template v-else>
        <hr style="margin: 15px 0;">
        <h3>Upraviť prax (všetky údaje)</h3>

        <!-- Firma / študent (voliteľné - ak chceš naozaj "všetky") -->
        <div class="grid">
          <div class="form-group">
            <label>Študent:</label>
            <select v-model.number="editForm.student_id">
              <option :value="null" disabled>Vyber študenta…</option>
              <option v-for="s in students" :key="s.id" :value="s.id">
                {{ s.first_name }} {{ s.last_name }} ({{ s.email }})
              </option>
            </select>
          </div>

          <div class="form-group">
            <label>Firma:</label>
            <select v-model.number="editForm.company_id">
              <option :value="null" disabled>Vyber firmu…</option>
              <option v-for="c in companies" :key="c.id" :value="c.id">
                {{ c.company_name || c.first_name || 'Firma #' + c.id }}
              </option>
            </select>
          </div>
        </div>

        <div class="grid">
          <div class="form-group">
            <label>Začiatok:</label>
            <input type="datetime-local" v-model="editForm.start_date" />
          </div>

          <div class="form-group">
            <label>Koniec:</label>
            <input type="datetime-local" v-model="editForm.end_date" />
          </div>
        </div>

        <div class="grid">
          <div class="form-group">
            <label>Semester:</label>
            <input type="text" v-model="editForm.semester" />
          </div>

          <div class="form-group">
            <label>Rok:</label>
            <input type="number" v-model.number="editForm.year" />
          </div>
        </div>

        <div class="form-group">
          <label>Stav:</label>
          <select v-model="editForm.status">
            <option value="Vytvorená">Vytvorená</option>
            <option value="Potvrdená">Potvrdená</option>
            <option value="Schválená">Schválená</option>
            <option value="Neschválená">Neschválená</option>
            <option value="Zamietnutá">Zamietnutá</option>
            <option value="Obhájená">Obhájená</option>
            <option value="Neobhájená">Neobhájená</option>
          </select>
        </div>

        <div class="actions">
          <button class="approve" type="button" @click="saveEdit" :disabled="saving">
            {{ saving ? 'Ukladám…' : 'Uložiť zmeny' }}
          </button>
          <button class="reject" type="button" @click="cancelEdit" :disabled="saving">
            Zrušiť
          </button>
        </div>

        <p v-if="editError" class="err">{{ editError }}</p>
      </template>
    </div>

    <!-- ============================= -->
    <!-- DOKUMENTY (GARANT) -->
    <!-- ============================= -->
    <div class="card">
      <h2>Dokumenty</h2>

      <div v-if="documents.length" class="documents-list">
        <div v-for="doc in documents" :key="doc.document_id" class="doc-item">
          <div class="doc-info">
            <div class="doc-name">{{ doc.document_name }}</div>

            <div class="doc-meta">
              <span class="doc-badge">{{ translateDocType(doc.type) }}</span>
              <span v-if="doc.company_status" class="doc-status" :class="'status-' + doc.company_status">
                {{ translateCompanyStatus(doc.company_status) }}
              </span>
            </div>
          </div>

          <div class="doc-actions">
            <button class="btn-outline" @click="downloadDocument(doc.document_id)">
              📥 Stiahnuť
            </button>

            <button
              v-if="doc.company_status !== 'approved'"
              class="btn-approve"
              @click="approveDocument(doc.document_id)"
            >
              ✔ Schváliť
            </button>

            <button
              v-if="doc.company_status !== 'rejected'"
              class="btn-reject"
              @click="rejectDocument(doc.document_id)"
            >
              ✖ Zamietnuť
            </button>
          </div>
        </div>
      </div>

      <p v-else class="no-documents">Zatiaľ nie sú nahraté žiadne dokumenty.</p>

      <hr style="margin: 18px 0;" />

      <h3>Pridať dokument (garant)</h3>
      <form class="upload-form" @submit.prevent="uploadDocument">
        <label>Typ dokumentu:</label>
        <select v-model="uploadForm.document_type" required>
          <option value="" disabled>Vyber typ...</option>
          <option value="review">Hodnotenie / posudok</option>
        </select>

        <label>Súbor:</label>
        <input type="file" accept=".pdf,.jpg,.jpeg,.png" @change="onFileChange" required />

        <p class="upload-error" v-if="uploadError">{{ uploadError }}</p>
        <p class="upload-success" v-if="uploadSuccess">{{ uploadSuccess }}</p>

        <button type="submit" class="upload-btn" :disabled="uploadLoading">
          {{ uploadLoading ? "Nahrávam..." : "Nahrať" }}
        </button>
      </form>
    </div>

    <!-- ============================= -->
    <!-- ACTIONS (Prax) -->
    <!-- ============================= -->
    <div class="actions" v-if="!editMode">
      <template v-if="internship.status === 'Potvrdená'">
        <button class="approve" @click="approveByGarant">Schváliť prax</button>
        <button class="reject" @click="rejectByGarant">Neschváliť prax</button>
      </template>

      <template v-if="internship.status === 'Schválená'">
        <button class="approve" @click="markDefended">Obhájiť</button>
        <button class="reject" @click="markNotDefended">Neobhájiť</button>
      </template>
    </div>
  </div>

  <div v-else class="loading">Načítavam detail…</div>
</template>

<script>
import axios from "axios";
import CompanyNavBar from '@/components/icons/CompanyNavBar.vue'

export default {
  name: "GarantPracticeDetailView",
  components: { CompanyNavBar },

  data() {
    return {
      internship: null,
      loading: true,
      saving: false,
      editError: "",
      status: 'vytvorena',
      editMode: false,

      // ✅ FULL EDIT FORM (všetky polia)
      editForm: {
        start_date: "",
        end_date: "",
        semester: "",
        year: null,
        status: "",
        student_id: null,
        company_id: null,
      },

      // options pre selecty
      students: [],
      companies: [],

      // dokumenty
      documents: [],
      uploadForm: { document_type: "", file: null },
      uploadError: "",
      uploadSuccess: "",
      uploadLoading: false,
    };
  },

  methods: {
    token() {
      return localStorage.getItem("access_token");
    },

    formatDate(date) {
      return date ? new Date(date).toLocaleDateString("sk-SK") : "";
    },

    toDateTimeLocal(val) {
    if (!val) return "";

    // ak je iba dátum YYYY-MM-DD -> doplň 00:00
    if (/^\d{4}-\d{2}-\d{2}$/.test(val)) {
      return `${val}T00:00`;
    }

    // ak je "YYYY-MM-DD HH:MM:SS" alebo "YYYY-MM-DD HH:MM"
    if (typeof val === "string") {
      const s = val.replace(" ", "T");
      // odrež sekundy (datetime-local ich nechce)
      return s.slice(0, 16);
    }

    // fallback: ak by prišiel Date objekt
    const d = new Date(val);
    const pad = (n) => String(n).padStart(2, "0");
    return `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`;
  },

    async loadStudentsAndCompanies() {
      // Študenti a firmy z users (ak nemáš endpointy, sprav jednoduché endpointy pre garanta)
      // Ak už máš admin endpointy, radšej sprav garant-only endpoint: /api/garant/users/students a /api/garant/users/companies
      try {
        const headers = { Authorization: `Bearer ${this.token()}` };

        // ✅ firmy - tento endpoint už máš verejný (/companies)
        const companiesRes = await axios.get(`http://localhost:8000/api/companies`, { headers });
        this.companies = companiesRes.data || [];

        // ✅ študenti - ak taký endpoint nemáš, treba ho doplniť.
        // dočasne tu nechávam URL, ktoré si vieš prispôsobiť
        const studentsRes = await axios.get(`http://localhost:8000/api/garant/students`, { headers });
this.students = studentsRes.data || [];

      } catch (e) {
        console.warn("Nepodarilo sa načítať zoznamy (students/companies).", e);
        // nech to neblokuje edit mód – len nebude dropdown
      }
    },

    startFullEdit() {
    this.editError = "";
    this.editMode = true;

    // ✅ tiež použi helper (pre istotu)
    this.editForm.start_date = this.toDateTimeLocal(this.internship.start_date);
    this.editForm.end_date   = this.toDateTimeLocal(this.internship.end_date);
    this.editForm.semester   = this.internship.semester || "";
    this.editForm.year       = this.internship.year || new Date().getFullYear();
    this.editForm.status     = this.internship.status || "Vytvorená";
    this.editForm.student_id = this.internship.student_id || this.internship.student?.id || null;
    this.editForm.company_id = this.internship.company_id || this.internship.company?.id || null;

    this.loadStudentsAndCompanies();
  },

    cancelEdit() {
      this.editMode = false;
      this.editError = "";
    },

    async loadDetail() {
    try {
      const id = this.$route.params.id;
      const res = await axios.get(`http://localhost:8000/api/garant/internships/${id}`, {
        headers: { Authorization: `Bearer ${this.token()}` }
      });

      this.internship = res.data;

      // ✅ automaticky naplň editForm podľa DB
      this.editForm.start_date = this.toDateTimeLocal(this.internship.start_date);
      this.editForm.end_date   = this.toDateTimeLocal(this.internship.end_date);
      this.editForm.semester   = this.internship.semester || "";
      this.editForm.year       = this.internship.year || new Date().getFullYear();
      this.editForm.status     = this.internship.status || "Vytvorená";
      this.editForm.student_id = this.internship.student_id || this.internship.student?.id || null;
      this.editForm.company_id = this.internship.company_id || this.internship.company?.id || null;

      await this.loadDocuments();
    } finally {
      this.loading = false;
    }

    
  },

  toDateTimeLocal(val) {
    if (!val) return "";

    // ak je iba dátum YYYY-MM-DD -> doplň 00:00
    if (/^\d{4}-\d{2}-\d{2}$/.test(val)) {
      return `${val}T00:00`;
    }

    // ak je "YYYY-MM-DD HH:MM:SS" alebo "YYYY-MM-DD HH:MM"
    if (typeof val === "string") {
      const s = val.replace(" ", "T");
      // odrež sekundy (datetime-local ich nechce)
      return s.slice(0, 16);
    }

    // fallback: ak by prišiel Date objekt
    const d = new Date(val);
    const pad = (n) => String(n).padStart(2, "0");
    return `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`;
  },
    /* =============================
     *   DOCUMENTS
     * ============================= */
    async loadDocuments() {
      const id = this.$route.params.id;
      const res = await axios.get(
        `http://localhost:8000/api/internships/${id}/documents`,
        { headers: { Authorization: `Bearer ${this.token()}` } }
      );
      this.documents = res.data || [];
    },

    onFileChange(e) {
      this.uploadForm.file = e.target.files?.[0] || null;
    },

    async uploadDocument() {
      this.uploadError = "";
      this.uploadSuccess = "";

      if (!this.uploadForm.document_type || !this.uploadForm.file) {
        this.uploadError = "Vyplň všetky polia.";
        return;
      }

      this.uploadLoading = true;

      try {
        const id = this.$route.params.id;

        const fd = new FormData();
        fd.append("document_type", this.uploadForm.document_type);
        fd.append("file", this.uploadForm.file);

        await axios.post(
          `http://localhost:8000/api/garant/internships/${id}/documents/upload`,
          fd,
          {
            headers: {
              Authorization: `Bearer ${this.token()}`,
              "Content-Type": "multipart/form-data",
            },
          }
        );

        this.uploadSuccess = "Dokument úspešne nahraný.";
        this.uploadForm.document_type = "";
        this.uploadForm.file = null;

        await this.loadDocuments();
      } catch (err) {
        this.uploadError = err.response?.data?.message || "Chyba pri nahrávaní.";
      } finally {
        this.uploadLoading = false;
      }
    },

    async downloadDocument(documentId) {
      try {
        const response = await axios.get(
          `http://localhost:8000/api/documents/${documentId}/download`,
          {
            headers: { Authorization: `Bearer ${this.token()}` },
            responseType: "blob",
          }
        );

        const blob = new Blob([response.data], {
          type: response.headers["content-type"],
        });
        const url = window.URL.createObjectURL(blob);

        let filename = "subor.pdf";
        const cd = response.headers["content-disposition"];
        if (cd) {
          const match = cd.match(/filename="?(.+)"?/);
          if (match) filename = match[1];
        }

        const a = document.createElement("a");
        a.href = url;
        a.download = filename;
        a.click();

        window.URL.revokeObjectURL(url);
      } catch (e) {
        console.error(e);
        alert("Sťahovanie zlyhalo.");
      }
    },

    async approveDocument(docId) {
      await axios.post(
        `http://localhost:8000/api/garant/documents/${docId}/approve`,
        {},
        { headers: { Authorization: `Bearer ${this.token()}` } }
      );
      await this.loadDocuments();
    },

    async rejectDocument(docId) {
      await axios.post(
        `http://localhost:8000/api/garant/documents/${docId}/reject`,
        {},
        { headers: { Authorization: `Bearer ${this.token()}` } }
      );
      await this.loadDocuments();
    },

    translateDocType(type) {
      return {
        report: "Správa z praxe",
        signed_agreement: "Podpísaná dohoda",
        review: "Hodnotenie / posudok",
        agreement_signed: "Dohoda podpísaná firmou",
      }[type] || type;
    },

    translateCompanyStatus(status) {
      return {
        pending: "Čaká na spracovanie",
        submitted: "Odoslané",
        approved: "Schválené",
        rejected: "Zamietnuté",
      }[status] || status;
    },

    /* =============================
     *   PRACTICE ACTIONS
     * ============================= */
    async approveByGarant() {
      const id = this.$route.params.id;
      await axios.post(
        `http://localhost:8000/api/garant/internships/${id}/approve`,
        {},
        { headers: { Authorization: `Bearer ${this.token()}` } }
      );
      alert("Prax bola schválená garantom.");
      this.goToFilter('schvalena');
    },

    async rejectByGarant() {
      const id = this.$route.params.id;
      await axios.post(
        `http://localhost:8000/api/garant/internships/${id}/disapprove`,
        {},
        { headers: { Authorization: `Bearer ${this.token()}` } }
      );
      alert("Prax bola neschválená garantom.");
      this.goToFilter('neschvalena');
    },

    async markDefended() {
      const id = this.$route.params.id;
      await axios.post(
        `http://localhost:8000/api/garant/internships/${id}/defended`,
        {},
        { headers: { Authorization: `Bearer ${this.token()}` } }
      );
      alert("Prax bola označená ako obhájená.");
      this.goToFilter('obhajena');
    },

    async markNotDefended() {
      const id = this.$route.params.id;
      await axios.post(
        `http://localhost:8000/api/garant/internships/${id}/not-defended`,
        {},
        { headers: { Authorization: `Bearer ${this.token()}` } }
      );
      alert("Prax bola označená ako neobhájená.");
      this.goToFilter('neobhajena');
    },

    goToFilter(filter) {
      this.$router.push({ path: '/garant/practices', query: { status: filter } })
    },

    /* =============================
     *   ✅ SAVE FULL EDIT
     * ============================= */
    async saveEdit() {
      this.saving = true;
      this.editError = "";

      try {
        const id = this.$route.params.id;

        const payload = {
          start_date: this.editForm.start_date,
          end_date: this.editForm.end_date,
          semester: this.editForm.semester,
          year: this.editForm.year,
          status: this.editForm.status,

          // ak nechceš meniť študenta/firma, vyhoď tieto 2 riadky
          student_id: this.editForm.student_id,
          company_id: this.editForm.company_id,
        };

        await axios.put(`http://localhost:8000/api/garant/internships/${id}/full`, payload,

          { headers: { Authorization: `Bearer ${this.token()}` } }
        );

        alert("Prax bola úspešne aktualizovaná.");
        this.editMode = false;
        await this.loadDetail();
      } catch (error) {
        console.error(error);
        this.editError =
          error.response?.data?.message ||
          (error.response?.data?.errors ? Object.values(error.response.data.errors).flat().join(" ") : "") ||
          "Nepodarilo sa uložiť zmeny.";
      } finally {
        this.saving = false;
      }
    },
  },

  mounted() {
    this.status = this.$route.query.status || 'vytvorena'
    this.loadDetail();
  },
};
</script>

<style scoped>
.container { padding: 20px; }

.card {
  border: 1px solid #ddd;
  padding: 20px;
  border-radius: 6px;
  margin-bottom: 20px;
  background: white;
}

.card-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
}

.grid {
  display: grid;
  gap: 12px;
  grid-template-columns: repeat(2, minmax(0, 1fr));
}
@media (max-width: 700px) {
  .grid { grid-template-columns: 1fr; }
}

.actions { display: flex; gap: 20px; margin-top: 15px; }

.approve {
  background: #3aa76d; color: white; padding: 12px 20px;
  border: none; border-radius: 6px; cursor: pointer;
}
.reject {
  background: #d9534f; color: white; padding: 12px 20px;
  border: none; border-radius: 6px; cursor: pointer;
}

.loading { padding: 20px; }

.form-group { margin-bottom: 12px; }
.form-group label { display: block; margin-bottom: 6px; font-weight: 600; }
.form-group input, .form-group select {
  width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;
}

/* Back */
.back-btn {
  margin-bottom: 15px;
  background: #ffffff;
  border: 1px solid #0b6b37;
  color: #0b6b37;
  padding: 8px 14px;
  border-radius: 6px;
  cursor: pointer;
}
.back-btn:hover { background: #f0f6f2; }

/* Dokumenty */
.documents-list { display: flex; flex-direction: column; gap: 14px; margin: 10px 0; }
.doc-item {
  display: flex; justify-content: space-between;
  background: #f8faf7; border: 1px solid #e0e7e0;
  border-radius: 10px; padding: 12px 15px;
}
.doc-info { display: flex; flex-direction: column; }
.doc-name { font-weight: 600; font-size: 15px; }
.doc-meta { margin-top: 4px; display: flex; gap: 8px; flex-wrap: wrap; }
.doc-badge {
  background: #e1f2e5; padding: 4px 10px; border-radius: 8px;
  font-size: 12px; color: #0b6b37; font-weight: 600;
}
.doc-status { padding: 4px 10px; border-radius: 8px; font-size: 12px; font-weight: 600; }
.status-pending { background: #fff2c2; color: #7a5b00; }
.status-submitted { background: #d7ecff; color: #004c82; }
.status-approved { background: #d7f7dd; color: #0b6b37; }
.status-rejected { background: #ffe0e0; color: #8e0000; }

.doc-actions { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
.btn-outline {
  background: white; border: 1px solid #0b6b37; color: #0b6b37;
  padding: 6px 10px; border-radius: 6px; cursor: pointer;
}
.btn-approve { background: #3aa76d; color: white; padding: 6px 10px; border-radius: 6px; border: none; cursor: pointer; }
.btn-reject { background: #d9534f; color: white; padding: 6px 10px; border-radius: 6px; border: none; cursor: pointer; }

.upload-form { display: flex; flex-direction: column; gap: 8px; }
.upload-btn {
  background: #0b6b37; color: white; padding: 10px;
  border-radius: 6px; margin-top: 6px; border: none; cursor: pointer;
}
.upload-error { color: #d9534f; font-weight: 600; }
.upload-success { color: #0b6b37; font-weight: 600; }
.no-documents { color: #666; }

.err {
  margin-top: 10px;
  color: #b00020;
  font-weight: 700;
}
</style>
