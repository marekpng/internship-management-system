<template>
  <CompanyNavBar />

  <div class="company-wrapper">
    <!-- pôvodný obsah tvojho company -->
  </div>

  <div class="container" v-if="internship">
    <h1>Detail praxe</h1>

    <!-- ============================= -->
    <!-- ŠTUDENT + PRAX -->
    <!-- ============================= -->
    <div class="card">
      <h2>Študent</h2>
      <p><strong>Meno:</strong> {{ internship.student.first_name }} {{ internship.student.last_name }}</p>
      <p><strong>Email:</strong> {{ internship.student.email }}</p>

      <h2>Prax</h2>

      <template v-if="!editMode">
        <p><strong>Začiatok:</strong> {{ formatDate(internship.start_date) }}</p>
        <p><strong>Koniec:</strong> {{ formatDate(internship.end_date) }}</p>
        <p><strong>Semester:</strong> {{ internship.semester }}</p>
        <p><strong>Rok:</strong> {{ internship.year }}</p>
        <p><strong>Stav praxe:</strong> {{ internship.status }}</p>
      </template>

      <template v-else>
        <h3>Upraviť údaje</h3>
        <div class="form-group">
          <label>Stav praxe:</label>
          <select v-model="editForm.status">
            <option value="Vytvorená">Vytvorená</option>
            <option value="Potvrdená">Potvrdená</option>
            <option value="Zamietnutá">Zamietnutá</option>
          </select>
        </div>
      </template>
    </div>

    <!-- ============================= -->
    <!-- DOKUMENTY (firma len číta / sťahuje) -->
    <!-- ============================= -->
    <div class="card">
      <h2>Dokumenty</h2>

      <div v-if="documents.length" class="documents-list">
        <div v-for="doc in documents" :key="doc.document_id" class="doc-item">
          <!-- LEFT -->
          <div class="doc-info">
            <div class="doc-name">{{ doc.document_name }}</div>

            <div class="doc-meta">
              <span class="doc-badge">{{ translateDocType(doc.type) }}</span>
              <span
                v-if="doc.company_status"
                class="doc-status"
                :class="'status-' + doc.company_status"
              >
                {{ translateCompanyStatus(doc.company_status) }}
              </span>
            </div>
          </div>

          <!-- RIGHT -->
          <div class="doc-actions">
            <button class="btn-outline" @click="downloadDocument(doc.document_id)">
              📥 Stiahnuť
            </button>
          </div>
        </div>
      </div>

      <p v-else class="no-documents">Zatiaľ nie sú nahraté žiadne dokumenty.</p>


    </div>

    <!-- ============================= -->
    <!-- GLOBAL ACTIONS (schvaľovanie praxe – nechávam, lebo to nie sú dokumenty) -->
    <!-- ============================= -->
    <div class="actions">
      <template v-if="internship.status === 'Vytvorená'">
        <button class="approve" @click="approve">Potvrdiť</button>
        <button class="reject" @click="reject">Zamietnuť</button>
      </template>

      <template v-if="!editMode && (internship.status === 'Potvrdená' || internship.status === 'Zamietnutá')">
        <button class="approve" style="background:#0b6b37" @click="editMode = true">Editovať prax</button>
      </template>

      <template v-if="editMode">
        <button class="approve" style="background:#0b6b37" @click="saveEdit">Uložiť zmeny</button>
        <button class="reject" @click="cancelEdit">Zrušiť</button>
      </template>
    </div>
  </div>

  <div v-else class="loading">Načítavam detail…</div>
</template>

<script>
import CompanyNavBar from '@/components/icons/CompanyNavBar.vue'
import axios from "axios";

export default {
  name: "CompanyPracticeDetailView",
  components: { CompanyNavBar },

  data() {
    return {
      internship: null,
      loading: true,
      editMode: false,
      editForm: { status: "" },

      documents: []
    };
  },

  methods: {
    getToken() {
      return localStorage.getItem("access_token");
    },

    /* =============================
     *   LOAD DETAIL
     * ============================= */
    async loadDetail() {
      const id = this.$route.params.id;
      const token = this.getToken();

      try {
        const res = await axios.get(
          `http://localhost:8000/api/company/internships/${id}`,
          { headers: { Authorization: `Bearer ${token}` } }
        );

        this.internship = res.data;
        this.editForm.status = res.data.status;

        await this.loadDocuments();
      } finally {
        this.loading = false;
      }
    },

    /* =============================
     *   LOAD DOCUMENTS
     * ============================= */
    async loadDocuments() {
      const id = this.$route.params.id;
      const token = this.getToken();

      const res = await axios.get(
        `http://localhost:8000/api/internships/${id}/documents`,
        { headers: { Authorization: `Bearer ${token}` } }
      );

      this.documents = res.data || [];
    },

    /* =============================
     *   DOWNLOAD DOCUMENT
     * ============================= */
    async downloadDocument(documentId) {
      const token = this.getToken();

      try {
        const response = await axios.get(
          `http://localhost:8000/api/documents/${documentId}/download`,
          {
            headers: { Authorization: `Bearer ${token}` },
            responseType: "blob"
          }
        );

        const blob = new Blob([response.data], { type: response.headers["content-type"] });
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
      } catch {
        alert("Sťahovanie zlyhalo.");
      }
    },

    /* =============================
     *   TEXT HELPERS
     * ============================= */
    translateDocType(type) {
      return {
        report: "Správa z praxe",
        signed_agreement: "Podpísaná dohoda",
        review: "Hodnotenie",
        agreement_signed: "Dohoda podpísaná firmou"
      }[type] || type;
    },

    translateCompanyStatus(status) {
      return {
        pending: "Čaká na spracovanie",
        submitted: "Odoslané",
        approved: "Schválené",
        rejected: "Zamietnuté"
      }[status] || status;
    },

    formatDate(date) {
      return new Date(date).toLocaleDateString("sk-SK");
    },

    /* =============================
     *   APPROVE / REJECT PRACTICE
     * ============================= */
    async approve() {
      const confirmed = window.confirm(
        "Naozaj chcete potvrdiť túto prax?\n\n" +
        "Potvrdením beriete na vedomie, že študent môže pokračovať podľa plánovaného rozvrhu a " +
        "údaje budú považované za konečné. Tento krok je nezvratný."
      );

      if (!confirmed) return;

      const id = this.$route.params.id;
      const token = this.getToken();

      await axios.post(
        `http://localhost:8000/api/company/internships/${id}/approve`,
        {},
        { headers: { Authorization: `Bearer ${token}` } }
      );

      this.$router.push("/company/practices");
    },

    async reject() {
      const confirmed = window.confirm(
        "Naozaj chcete zamietnuť túto prax?\n\n" +
        "Zamietnutím bude študent informovaný a prax sa stane neplatnou. " +
        "Uistite sa, že máte na to dôvod, pretože tento krok je nezvratný."
      );

      if (!confirmed) return;

      const id = this.$route.params.id;
      const token = this.getToken();

      await axios.post(
        `http://localhost:8000/api/company/internships/${id}/reject`,
        {},
        { headers: { Authorization: `Bearer ${token}` } }
      );

      this.$router.push("/company/practices");
    },

    /* =============================
     *   EDIT PRACTICE
     * ============================= */
    async saveEdit() {
      const id = this.$route.params.id;
      const token = this.getToken();

      await axios.put(
        `http://localhost:8000/api/company/internships/${id}/status`,
        { status: this.editForm.status },
        { headers: { Authorization: `Bearer ${token}` } }
      );

      this.editMode = false;
      this.loadDetail();
    },

    cancelEdit() {
      this.editMode = false;
    }
  },

  mounted() {
    this.loadDetail();
  }
};
</script>

<style scoped>
.container { padding: 20px; }
.card {
  border: 1px solid #ddd;
  padding: 20px;
  border-radius: 10px;
  margin-bottom: 25px;
  background: white;
}
.documents-list {
  display: flex;
  flex-direction: column;
  gap: 14px;
  margin: 10px 0;
}
.doc-item {
  display: flex;
  justify-content: space-between;
  background: #f8faf7;
  border: 1px solid #e0e7e0;
  border-radius: 10px;
  padding: 12px 15px;
}
.doc-info { display: flex; flex-direction: column; }
.doc-name { font-weight: 600; font-size: 15px; }
.doc-meta { margin-top: 4px; display: flex; gap: 8px; flex-wrap: wrap; }
.doc-badge {
  background: #e1f2e5;
  padding: 4px 10px;
  border-radius: 8px;
  font-size: 12px;
  color: #0b6b37;
  font-weight: 600;
}
.doc-status {
  padding: 4px 10px;
  border-radius: 8px;
  font-size: 12px;
  font-weight: 600;
}
.status-pending { background: #fff2c2; color: #7a5b00; }
.status-submitted { background: #d7ecff; color: #004c82; }
.status-approved { background: #d7f7dd; color: #0b6b37; }
.status-rejected { background: #ffe0e0; color: #8e0000; }

.doc-actions { display: flex; align-items: center; gap: 8px; }
.btn-outline {
  background: white;
  border: 1px solid #0b6b37;
  color: #0b6b37;
  padding: 6px 10px;
  border-radius: 6px;
  cursor: pointer;
}

.actions { display: flex; gap: 15px; margin-bottom: 40px; }
.approve { background: #3aa76d; color: white; padding: 12px 18px; border-radius: 6px; }
.reject { background: #d9534f; color: white; padding: 12px 18px; border-radius: 6px; }

.note {
  margin-top: 12px;
  font-size: 13px;
  color: #555;
  font-style: italic;
}
</style>
