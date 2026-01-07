<template>
  <div class="container" v-if="internship">
    <div class="header-bar">
      <span class="header-title">Garant • Praxe</span>
      <button class="header-back" @click="$router.push('/garant/dashboard')">Domov</button>
    </div>

    <button class="back-btn" @click="$router.push('/garant/practices')">← Späť</button>

    <h1>Detail praxe</h1>

    <!-- ============================= -->
    <!-- INFO KARTA -->
    <!-- ============================= -->
    <div class="card">
      <h2>Študent</h2>
      <p><strong>Meno:</strong> {{ internship.student?.first_name || "Neznámy študent" }} {{ internship.student?.last_name || "" }}</p>
      <p><strong>Email:</strong> {{ internship.student?.email || "" }}</p>

      <h2>Firma</h2>
      <p><strong>Názov:</strong> {{ internship.company?.company_name || internship.company?.first_name || "Neznáma firma" }}</p>
      <p><strong>Email:</strong> {{ internship.company?.email || "" }}</p>

      <h2>Prax</h2>
      <div v-if="!editMode">
        <p><strong>Začiatok:</strong> {{ formatDate(internship.start_date) }}</p>
        <p><strong>Koniec:</strong> {{ formatDate(internship.end_date) }}</p>
        <p><strong>Semester:</strong> {{ internship.semester }}</p>
        <p><strong>Rok:</strong> {{ internship.year }}</p>
        <p><strong>Stav:</strong> {{ internship.status }}</p>
      </div>

      <template v-else>
        <hr style="margin: 15px 0;">
        <h3>Upraviť prax</h3>
        <div class="form-group">
          <label>Začiatok:</label>
          <input type="datetime-local" v-model="editForm.start_date">
        </div>
        <div class="form-group">
          <label>Koniec:</label>
          <input type="datetime-local" v-model="editForm.end_date">
        </div>
        <div class="form-group">
          <label>Semester:</label>
          <input type="text" v-model="editForm.semester">
        </div>
        <div class="form-group">
          <label>Rok:</label>
          <input type="number" v-model="editForm.year">
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

      <!-- Upload: garant môže nahrať hodnotenie -->
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
    <div class="actions">
      <template v-if="internship.status === 'Potvrdená' && !editMode">
        <button class="approve" @click="approveByGarant">Schváliť prax</button>
        <button class="reject" @click="rejectByGarant">Neschváliť prax</button>
      </template>

      <template v-if="internship.status === 'Schválená' && !editMode">
        <button class="approve" style="background:#0b6b37" @click="editMode = true">
          Upraviť prax / nastaviť obhajobu
        </button>
      </template>

      <template v-if="editMode">
        <button class="approve" style="background:#0b6b37" @click="saveEdit">Uložiť</button>
        <button class="reject" @click="cancelEdit">Zrušiť</button>
      </template>

      <template v-if="internship.status === 'Schválená' && !editMode">
        <button class="approve" @click="markDefended">Obhájiť</button>
        <button class="reject" @click="markNotDefended">Neobhájiť</button>
      </template>
    </div>
  </div>

  <div v-else class="loading">Načítavam detail…</div>
</template>

<script>
import axios from "axios";

export default {
  name: "GarantPracticeDetailView",

  data() {
    return {
      internship: null,
      loading: true,
      editMode: false,
      editForm: {
        start_date: "",
        end_date: "",
        semester: "",
        year: null,
        status: "",
      },

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

    async loadDetail() {
      try {
        const id = this.$route.params.id;

        const response = await axios.get(
          `http://localhost:8000/api/garant/internships/${id}`,
          { headers: { Authorization: `Bearer ${this.token()}` } }
        );

        this.internship = response.data;

        this.editForm.start_date = this.internship.start_date
          ? this.internship.start_date.replace(" ", "T")
          : "";
        this.editForm.end_date = this.internship.end_date
          ? this.internship.end_date.replace(" ", "T")
          : "";
        this.editForm.semester = this.internship.semester || "";
        this.editForm.year = this.internship.year || new Date().getFullYear();
        this.editForm.status = this.internship.status || "Vytvorená";

        await this.loadDocuments();
      } catch (error) {
        console.error("Error loading detail:", error);
      } finally {
        this.loading = false;
      }
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

        // ✅ garant upload
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
      try {
        const id = this.$route.params.id;
        await axios.post(
          `http://localhost:8000/api/garant/internships/${id}/approve`,
          {},
          { headers: { Authorization: `Bearer ${this.token()}` } }
        );
        alert("Prax bola schválená garantom.");
        this.loadDetail();
      } catch (error) {
        console.error(error);
      }
    },

    async rejectByGarant() {
      try {
        const id = this.$route.params.id;
        await axios.post(
          `http://localhost:8000/api/garant/internships/${id}/disapprove`,
          {},
          { headers: { Authorization: `Bearer ${this.token()}` } }
        );
        alert("Prax bola neschválená garantom.");
        this.loadDetail();
      } catch (error) {
        console.error(error);
      }
    },

    async markDefended() {
      try {
        const id = this.$route.params.id;
        await axios.post(
          `http://localhost:8000/api/garant/internships/${id}/defended`,
          {},
          { headers: { Authorization: `Bearer ${this.token()}` } }
        );
        alert("Prax bola označená ako obhájená.");
        this.loadDetail();
      } catch (error) {
        console.error(error);
      }
    },

    async markNotDefended() {
      try {
        const id = this.$route.params.id;
        await axios.post(
          `http://localhost:8000/api/garant/internships/${id}/not-defended`,
          {},
          { headers: { Authorization: `Bearer ${this.token()}` } }
        );
        alert("Prax bola označená ako neobhájená.");
        this.loadDetail();
      } catch (error) {
        console.error(error);
      }
    },

    async saveEdit() {
      try {
        const id = this.$route.params.id;
        const payload = {
          start_date: this.editForm.start_date,
          end_date: this.editForm.end_date,
          semester: this.editForm.semester,
          year: this.editForm.year,
          status: this.editForm.status,
        };

        await axios.put(
          `http://localhost:8000/api/garant/internships/${id}`,
          payload,
          { headers: { Authorization: `Bearer ${this.token()}` } }
        );

        alert("Prax bola úspešne aktualizovaná.");
        this.editMode = false;
        this.loadDetail();
      } catch (error) {
        console.error(error);
      }
    },

    cancelEdit() {
      this.editMode = false;
    },
  },

  mounted() {
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

/* Header + back */
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

.header-bar {
  display: flex; justify-content: space-between; align-items: center;
  background: #0b6b37; padding: 12px 20px; color: white; margin-bottom: 15px;
}
.header-title { font-size: 16px; font-weight: 600; }
.header-back {
  background: white; color: #0b6b37; padding: 6px 12px;
  border-radius: 6px; cursor: pointer; border: none; font-weight: 600;
}
.header-back:hover { background: #f0f6f2; }

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
</style>
