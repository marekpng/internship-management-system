<template>
  <!-- Navbar (notifikácie + nastavenia + odhlásenie) -->
  <CompanyNavBar />

  <div class="overlay">
    <div class="practice-container">
      <!-- HEADER -->
      <div class="header-row">
        <button type="button" class="back-btn" @click="goBack">← Späť</button>
        <h2>Moja prax</h2>
      </div>

      <!-- LOADING -->
      <div v-if="loading" class="loading">
        Načítavam údaje o praxi...
      </div>

      <!-- CONTENT -->
      <div v-else-if="practices.length">
        <div v-for="p in practices" :key="p.id" class="practice-card">
          <!-- ==================================== -->
          <!-- =========== EDIT MODE =============== -->
          <!-- ==================================== -->
          <template v-if="editingPracticeId === p.id">
            <form @submit.prevent="submitEdit(p.id)" class="edit-form">
              <p><strong>Firma:</strong> {{ p.company_name }}</p>

              <label>Rok</label>
              <input type="number" v-model.number="editForm.year" disabled />

              <label>Semester</label>
              <input type="text" v-model="editForm.semester" disabled />

              <label>Začiatok praxe</label>
              <input type="date" v-model="editForm.start_date" required />

              <label>Koniec praxe</label>
              <input type="date" v-model="editForm.end_date" required />

              <div class="button-row">
                <button type="submit">Uložiť</button>
                <button type="button" class="btn-outline" @click="cancelEditing">
                  Zrušiť
                </button>
              </div>
            </form>
          </template>

          <!-- ==================================== -->
          <!-- =========== VIEW MODE ============== -->
          <!-- ==================================== -->
          <template v-else>
            <!-- STATUS -->
            <div class="status-box" :class="statusClass(p.status)">
              {{ p.status }}
            </div>

            <!-- BASIC INFO -->
            <div class="info-section">
              <p><strong>Firma:</strong> {{ p.company_name }}</p>
              <p><strong>Rok:</strong> {{ p.year }}</p>
              <p><strong>Semester:</strong> {{ p.semester }}</p>
              <p><strong>Začiatok praxe:</strong> {{ formatDate(p.start_date) }}</p>
              <p><strong>Koniec praxe:</strong> {{ formatDate(p.end_date) }}</p>
              <p>
                <strong>Študent:</strong>
                {{ p.student_first_name }} {{ p.student_last_name }}
              </p>
              <p>
                <strong>Garant:</strong>
                {{ p.garant_first_name }} {{ p.garant_last_name }}
              </p>
            </div>

            <!-- ========================= -->
            <!-- ====== DOKUMENTY ========= -->
            <!-- ========================= -->
            <div class="documents-section">
              <h3>Dokumenty</h3>

              <div class="documents-list">
                <!-- ŠPECIÁLNY DOKUMENT – DOHODA -->
                <div class="doc-item special-doc">
                  <div class="doc-main">
                    <div class="doc-name">Dohoda o vykonávaní odbornej praxe</div>
                    <div class="doc-meta">
                      <span class="doc-badge special-badge">Systémový dokument</span>
                    </div>
                  </div>

                  <button
                    type="button"
                    class="btn-outline"
                    @click="downloadAgreement(p.id)"
                  >
                    📥 Stiahnuť
                  </button>
                </div>

                <!-- POUŽÍVATEĽSKÉ DOKUMENTY -->
                <template v-if="documents[p.id]?.length">
                  <div
                    v-for="doc in documents[p.id]"
                    :key="doc.document_id"
                    class="doc-item"
                  >
                    <div class="doc-main">
                      <div class="doc-name">{{ doc.document_name }}</div>
                      <div class="doc-meta">
                        <span class="doc-badge">
                          {{ translateDocType(doc.type) }}
                        </span>
                        <span
                          v-if="doc.company_status"
                          class="doc-badge status-badge"
                        >
                          {{ translateCompanyStatus(doc.company_status) }}
                        </span>
                      </div>
                    </div>

                    <button
                      class="btn-outline"
                      type="button"
                      @click="downloadDocument(doc.document_id)"
                    >
                      📥 Stiahnuť
                    </button>
                  </div>
                </template>

                <p v-else class="no-documents no-documents-inline">
                  Zatiaľ neboli nahraté žiadne dokumenty.
                </p>
              </div>

              <!-- UPLOAD FORM -->
              <form class="doc-upload-form" @submit.prevent="uploadDocument(p.id)">
                <div class="upload-row">
                  <div class="upload-field">
                    <label>Typ dokumentu</label>
                    <select v-model="uploadForms[p.id].document_type" required>
                      <option disabled value="">Vyber typ dokumentu</option>
                      <option value="report">Správa z praxe</option>
                      <option value="signed_agreement">Podpísaná dohoda</option>
                    </select>
                  </div>

                  <div class="upload-field">
                    <label>Súbor</label>
                    <input
                      type="file"
                      accept=".pdf,.jpg,.jpeg,.png"
                      @change="onFileChange($event, p.id)"
                      required
                    />
                  </div>
                </div>

                <p class="doc-error" v-if="uploadErrors[p.id]">
                  {{ uploadErrors[p.id] }}
                </p>
                <p class="doc-success" v-if="uploadSuccess[p.id]">
                  {{ uploadSuccess[p.id] }}
                </p>

                <button
                  class="doc-upload-btn"
                  type="submit"
                  :disabled="uploadLoading[p.id]"
                >
                  {{ uploadLoading[p.id] ? "Nahrávam..." : "Nahrať dokument" }}
                </button>
              </form>
            </div>

            <!-- ACTION BUTTONS -->
            <div class="button-row">
              <button type="button" @click="startEditing(p)" class="btn-outline">
                Upraviť prax
              </button>
            </div>
          </template>
        </div>
      </div>

      <div v-else class="no-practice">
        Žiadna prax zatiaľ nebola vytvorená.
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { useRouter } from "vue-router";
import axios from "axios";

import CompanyNavBar from "@/components/icons/CompanyNavBar.vue";

const router = useRouter();

const practices = ref([]);
const documents = ref({});

const uploadForms = ref({});
const uploadErrors = ref({});
const uploadSuccess = ref({});
const uploadLoading = ref({});

const loading = ref(true);
const editingPracticeId = ref(null);

const editForm = ref({
  year: null,
  semester: "",
  start_date: "",
  end_date: "",
});

const goBack = () => router.back();

const formatDate = (d) => {
  if (!d) return "—";
  return new Date(d).toLocaleDateString("sk-SK");
};

const statusClass = (status) => {
  const s = (status || '').trim();

  if (['Zamietnutá', 'Neschválená', 'Neobhájená'].includes(s)) {
    return 'status--danger';
  }

  if (['Vytvorená', 'Potvrdená'].includes(s)) {
    return 'status--warning';
  }

  if (['Schválená', 'Obhájená'].includes(s)) {
    return 'status--success';
  }

  return 'status--neutral';
};

const initUpload = (id) => {
  uploadForms.value[id] = uploadForms.value[id] || {
    document_type: "",
    file: null,
  };
  uploadErrors.value[id] = "";
  uploadSuccess.value[id] = "";
  uploadLoading.value[id] = false;
};

const loadDocuments = async (id) => {
  try {
    const token = localStorage.getItem("access_token");
    const res = await axios.get(
      `http://localhost:8000/api/internships/${id}/documents`,
      { headers: { Authorization: `Bearer ${token}` } }
    );
    documents.value[id] = res.data;
  } catch (e) {
    console.error("Chyba pri načítaní dokumentov", e);
    documents.value[id] = [];
  }
};

const loadPractices = async () => {
  loading.value = true;

  try {
    const token = localStorage.getItem("access_token");
    const res = await axios.get("http://localhost:8000/api/internships/my", {
      headers: { Authorization: `Bearer ${token}` },
    });

    practices.value = res.data;

    for (const p of practices.value) {
      initUpload(p.id);
      await loadDocuments(p.id);
    }
  } catch (e) {
    console.error("Chyba pri načítaní praxí", e);
  }

  loading.value = false;
};

const startEditing = (p) => {
  editingPracticeId.value = p.id;

  editForm.value = {
    year: p.year,
    semester: p.semester,
    start_date: p.start_date.slice(0, 10),
    end_date: p.end_date.slice(0, 10),
  };
};

const cancelEditing = () => {
  editingPracticeId.value = null;
};

const submitEdit = async (id) => {
  try {
    const token = localStorage.getItem("access_token");

    await axios.put(
      `http://localhost:8000/api/internships/${id}`,
      {
        start_date: editForm.value.start_date,
        end_date: editForm.value.end_date,
      },
      { headers: { Authorization: `Bearer ${token}` } }
    );

    cancelEditing();
    await loadPractices();
  } catch (e) {
    console.error("Chyba pri aktualizácii praxe", e);
    alert("Nepodarilo sa aktualizovať prax.");
  }
};

// ------------------- DOWNLOAD HELPERS -------------------

const extensionFromContentType = (ct) => {
  if (!ct) return null;
  const type = ct.split(";")[0].trim().toLowerCase();

  if (type === "application/pdf") return ".pdf";
  if (type === "image/jpeg") return ".jpg";
  if (type === "image/png") return ".png";
  if (type === "application/vnd.openxmlformats-officedocument.wordprocessingml.document")
    return ".docx";

  return null;
};

// RFC5987 + fallback
const getFileNameFromDisposition = (cd) => {
  if (!cd) return null;

  // filename*=UTF-8''...
  const utf8 = cd.match(/filename\*\s*=\s*UTF-8''([^;]+)/i);
  if (utf8?.[1]) {
    try {
      return decodeURIComponent(utf8[1]);
    } catch {
      return utf8[1];
    }
  }

  // filename="..."
  const ascii = cd.match(/filename\s*=\s*"?([^";]+)"?/i);
  if (ascii?.[1]) return ascii[1];

  return null;
};

const ensureExtension = (name, contentType) => {
  if (!name) return name;
  const ext = extensionFromContentType(contentType);
  if (!ext) return name;

  // ak už má príponu, nechaj
  if (/\.[a-z0-9]{2,5}$/i.test(name)) return name;

  return name + ext;
};

/**
 * Spoločná funkcia na stiahnutie súboru cez autorizovanú request.
 * forcedFileName = keď nechceš riešiť CORS/headers (napr. dohoda).
 */
const downloadWithAuth = async (url, forcedFileName = null) => {
  try {
    const token = localStorage.getItem("access_token");

    const response = await axios.get(url, {
      headers: { Authorization: `Bearer ${token}` },
      responseType: "blob",
    });

    const cd = response.headers["content-disposition"];
    const ct = response.headers["content-type"];

    const fromHeader = getFileNameFromDisposition(cd);

    // prioritne: forced -> header -> fallback
    let fileName =
      forcedFileName ||
      fromHeader ||
      (url.includes("/agreement/") ? "Dohoda_praxe.pdf" : "dokument");

    // doplň príponu podľa Content-Type ak treba
    fileName = ensureExtension(fileName, ct);

    const blob = new Blob([response.data], {
      type: ct || "application/octet-stream",
    });

    const blobUrl = window.URL.createObjectURL(blob);

    const a = document.createElement("a");
    a.href = blobUrl;
    a.download = fileName;
    document.body.appendChild(a);
    a.click();
    a.remove();

    window.URL.revokeObjectURL(blobUrl);
  } catch (e) {
    console.error("Chyba pri sťahovaní súboru", e);
    alert("Nepodarilo sa stiahnuť dokument.");
  }
};

// dohoda: vynútiť názov podľa ID
const downloadAgreement = (id) =>
  downloadWithAuth(
    `http://localhost:8000/api/internships/${id}/agreement/download`,
    `Dohoda_praxe_${id}.pdf`
  );

const downloadDocument = (docId) =>
  downloadWithAuth(`http://localhost:8000/api/documents/${docId}/download`);

// ------------------- UPLOAD -------------------

const onFileChange = (e, id) => {
  if (!uploadForms.value[id]) initUpload(id);
  uploadForms.value[id].file = e.target.files[0] || null;
};

const uploadDocument = async (id) => {
  initUpload(id);

  const form = uploadForms.value[id];
  uploadErrors.value[id] = "";
  uploadSuccess.value[id] = "";

  if (!form.document_type) {
    uploadErrors.value[id] = "Vyber typ dokumentu.";
    return;
  }
  if (!form.file) {
    uploadErrors.value[id] = "Vyber súbor.";
    return;
  }

  uploadLoading.value[id] = true;

  try {
    const token = localStorage.getItem("access_token");
    const fd = new FormData();
    fd.append("document_type", form.document_type);
    fd.append("file", form.file);

    await axios.post(
      `http://localhost:8000/api/internships/${id}/documents/upload`,
      fd,
      { headers: { Authorization: `Bearer ${token}` } }
    );

    uploadSuccess.value[id] = "Dokument bol úspešne nahratý.";
    await loadDocuments(id);

    uploadForms.value[id] = { document_type: "", file: null };
  } catch (error) {
    console.error("Chyba pri nahrávaní dokumentu", error);
    uploadErrors.value[id] =
      error.response?.data?.message || "Nepodarilo sa nahrať dokument.";
  }

  uploadLoading.value[id] = false;
};

// ------------------- TRANSLATIONS -------------------

const translateDocType = (t) =>
  ({
    report: "Správa z praxe",
    signed_agreement: "Podpísaná dohoda",
    agreement_signed: "Dohoda podpísaná firmou",
    review: "Hodnotenie / posudok",
  }[t] || t);

const translateCompanyStatus = (s) =>
  ({
    pending: "Čaká na spracovanie",
    submitted: "Odoslané",
    approved: "Schválené",
    rejected: "Zamietnuté",
  }[s] || s);

onMounted(loadPractices);
</script>

<style scoped>
.practice-container {
  max-width: 650px;
  margin: 40px auto;
  background: white;
  padding: 25px 30px;
  border-radius: 12px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
}

/* HEADER */
.header-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 10px;
  position: relative;
  margin-bottom: 10px;
}

/* BACK BUTTON – jednotný štýl */
.back-btn {
  background: #ffffff;
  border: 1px solid #1b5e20;
  color: #1b5e20;
  padding: 8px 14px;
  border-radius: 6px;
  cursor: pointer;
  font-size: 14px;
  font-weight: 600;
  line-height: 1;
}

.back-btn:hover {
  background: #e8f5e9;
}

h2 {
  flex-grow: 1;
  text-align: center;
  color: #1b5e20;
  font-size: 24px;
}

/* LOADING TEXT */
.loading {
  text-align: center;
  margin: 20px 0 10px;
  font-style: italic;
  color: #444;
}

/* STATUS + CARD */
.practice-card {
  margin-top: 18px;
  position: relative;
}

.status-box {
  position: absolute;
  top: -10px;
  right: 0;
  padding: 6px 14px;
  border-radius: 8px;
  font-weight: bold;
  font-size: 14px;
  border: 1px solid transparent;
}

/* Čaká (oranžová) */
.status--warning {
  background: #fff7ed;
  color: #9a3412;
  border-color: #fed7aa;
}

/* Zamietnutá / Neschválená / Neobhájená (červená) */
.status--danger {
  background: #fef2f2;
  color: #991b1b;
  border-color: #fecaca;
}

/* Schválená / Obhájená (zelená) */
.status--success {
  background: #ecfdf5;
  color: #065f46;
  border-color: #a7f3d0;
}

/* Fallback */
.status--neutral {
  background: #f1f5f9;
  color: #334155;
  border-color: #e2e8f0;
}

/* INFO */
.info-section {
  margin-top: 15px;
}

.info-section p {
  margin: 6px 0;
  font-size: 15px;
}

/* DOCUMENTS */
.documents-section {
  margin-top: 20px;
  padding: 16px;
  background: #f7faf7;
  border-radius: 10px;
  border: 1px solid #dfe9df;
}

.documents-section h3 {
  margin-bottom: 12px;
  font-size: 18px;
  color: #1b5e20;
}

.no-documents {
  font-size: 14px;
  color: #666;
  margin-top: 4px;
}

.no-documents-inline {
  margin-left: 2px;
}

.documents-list {
  display: flex;
  flex-direction: column;
  gap: 10px;
  margin-bottom: 10px;
}

.doc-item {
  display: flex;
  justify-content: space-between;
  padding: 10px 14px;
  border-radius: 8px;
  background: white;
  border: 1px solid #e0e7e0;
}

/* špeciálna karta – dohoda */
.special-doc {
  border-left: 4px solid #1b5e20;
}

.special-badge {
  background: #dceefc;
  color: #004c78;
}

.doc-main {
  max-width: 70%;
}

.doc-name {
  font-weight: 600;
  font-size: 15px;
}

.doc-meta {
  margin-top: 5px;
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
}

.doc-badge {
  background: #e8f5e9;
  padding: 3px 9px;
  border-radius: 14px;
  color: #1b5e20;
  font-size: 12px;
  font-weight: 600;
}

.status-badge {
  background: #fff1c2;
  color: #7a5b00;
}

/* UPLOAD */
.doc-upload-form {
  margin-top: 14px;
  border-top: 1px solid #dfe5df;
  padding-top: 14px;
}

.upload-row {
  display: flex;
  gap: 15px;
  flex-wrap: wrap;
}

.upload-field {
  flex: 1 1 0;
  min-width: 180px;
}

.upload-field label {
  display: block;
  font-size: 14px;
  margin-bottom: 4px;
}

.upload-field select,
.upload-field input {
  width: 100%;
  padding: 7px 9px;
  border-radius: 6px;
  border: 1px solid #c9d6c9;
  font-size: 14px;
}

.doc-success {
  margin-top: 10px;
  background: #e6f6e6;
  padding: 8px 10px;
  border-radius: 6px;
  color: #1b5e20;
  font-weight: 500;
  font-size: 14px;
}

.doc-error {
  margin-top: 10px;
  background: #fde2e2;
  padding: 8px 10px;
  border-radius: 6px;
  color: #a10000;
  font-weight: 500;
  font-size: 14px;
}

.doc-upload-btn {
  width: 100%;
  background: #1b5e20;
  color: white;
  padding: 10px;
  border-radius: 6px;
  margin-top: 10px;
}

/* BUTTONS */
button {
  background: #1b5e20;
  color: white;
  border-radius: 6px;
  padding: 8px 14px;
  border: none;
  cursor: pointer;
  font-size: 14px;
}

button:disabled {
  opacity: 0.7;
  cursor: default;
}

.btn-outline {
  background: white !important;
  color: #1b5e20 !important;
  border: 1px solid #1b5e20 !important;
}

.btn-outline:hover {
  background: #e8f5e9 !important;
}

.button-row {
  display: flex;
  gap: 10px;
  margin-top: 20px;
}

.no-practice {
  text-align: center;
  margin-top: 20px;
  color: #555;
  font-style: italic;
}

.overlay {
  padding-top: 70px;
}
</style>
