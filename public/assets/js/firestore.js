let dataStore = window.dataStore || {};
if (Array.isArray(dataStore)) dataStore = {};
window.dataStore = dataStore;
let currentCollection = null;
let currentDoc = null;
let documentFields = [];
let fieldModalArrayElements = [];

const rowsPerPage = 5;

const resizers = document.querySelectorAll(".resizer");
let currentPanel = null,
    startX = 0,
    startWidth = 0;

function deleteApiKey(applicationId, apiKey) {
    if (!confirm(`Delete API key: ${apiKey}?`)) return;

    fetch(`/applications/${applicationId}/api-keys`, {
        method: "DELETE",
        headers: {
            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')
                .content,
            "Content-Type": "application/json",
            Accept: "application/json",
        },
        body: JSON.stringify({ key: apiKey }),
    })
        .then((res) => res.json())
        .then((data) => {
            if (data.success) {
                document.querySelector(`tr[data-key="${apiKey}"]`)?.remove();
            } else {
                alert(data.error || "Delete failed.");
            }
        })
        .catch(() => alert("Network error."));
}

function renderApiKeyPagination() {
    const rows = document.querySelectorAll("#apiKeysTableBody tr");
    const totalRows = rows.length;
    const totalPages = Math.ceil(totalRows / rowsPerPage);
    const pagination = document.getElementById("apiKeysPagination");

    pagination.innerHTML = "";

    if (totalPages <= 1) {
        rows.forEach((row) => (row.style.display = ""));
        return;
    }

    let currentPage = 1;

    function showPage(page) {
        currentPage = page;
        rows.forEach((row, index) => {
            row.style.display =
                index >= (page - 1) * rowsPerPage && index < page * rowsPerPage
                    ? ""
                    : "none";
        });

        pagination.innerHTML = "";
        for (let i = 1; i <= totalPages; i++) {
            const li = document.createElement("li");
            li.className = "page-item" + (i === page ? " active" : "");
            li.innerHTML = `<a class="page-link" href="#">${i}</a>`;
            li.addEventListener("click", (e) => {
                e.preventDefault();
                showPage(i);
            });
            pagination.appendChild(li);
        }
    }

    showPage(1);
}

function sanitizeDataStore(ds) {
    Object.keys(ds).forEach((col) => {
        if (Array.isArray(ds[col])) ds[col] = {};
    });
}

function downloadFirestoreJson() {
    sanitizeDataStore(dataStore);
    const json = JSON.stringify(dataStore, null, 2);
    const blob = new Blob([json], { type: "application/json" });
    const url = URL.createObjectURL(blob);
    const a = document.createElement("a");
    a.href = url;
    a.download = "firestore_data.json";
    document.body.appendChild(a);
    a.click();
    setTimeout(() => {
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    }, 100);
}

const startCollectionModal = new bootstrap.Modal(
    document.getElementById("startCollectionModal")
);
const addDocumentModal = new bootstrap.Modal(
    document.getElementById("addDocumentModal")
);
const addFieldModal = new bootstrap.Modal(
    document.getElementById("addFieldModal")
);

function initializeInterface() {
    renderCollections();
    const collections = Object.keys(dataStore);
    if (collections.length > 0) {
        selectCollection(collections[0]);
        renderDocuments();
        const docs = Object.keys(dataStore[currentCollection] || {});
        if (docs.length > 0) {
            selectDocument(docs[0]);
        } else {
            document.getElementById("currentDocumentTitle").textContent =
                "Select a document";
            document.getElementById("addFieldBtn").style.display = "none";
            document.getElementById("startCollectionBtn").style.display =
                "none";
            clearFields();
            updateBreadcrumb();
        }
    }
}
function renderCollections() {
    const container = document.getElementById("collectionsList");
    container.innerHTML = "";
    Object.keys(dataStore).forEach((name) => {
        const item = document.createElement("div");
        item.className = "collection-item";
        item.innerHTML = `<span class="collection-name">${name}</span><span class="collection-arrow">▶</span>`;
        item.onclick = () => selectCollection(name);
        if (name === currentCollection) item.classList.add("active");
        container.appendChild(item);
    });
}
function selectCollection(name) {
    currentCollection = name;
    currentDoc = null;
    document
        .querySelectorAll(".collection-item")
        .forEach((i) => i.classList.remove("active"));
    document.querySelectorAll(".collection-item").forEach((i) => {
        if (i.textContent.trim().startsWith(name)) i.classList.add("active");
    });
    document.getElementById("collectionTitle").textContent = name;
    document.getElementById("addDocumentBtn").style.display = "block";
    document.getElementById("currentDocumentTitle").textContent =
        "Select a document";
    document.getElementById("addFieldBtn").style.display = "none";
    document.getElementById("startCollectionBtn").style.display = "none";
    renderDocuments();
    clearFields();
    updateBreadcrumb();
}
function renderDocuments() {
    const container = document.getElementById("documentsList");
    container.innerHTML = "";
    const docs = dataStore[currentCollection] || {};
    Object.keys(docs).forEach((doc) => {
        const item = document.createElement("div");
        item.className = "document-item";
        item.innerHTML = `
                    <span class="document-icon" style="margin-right:6px;cursor:pointer;">📄</span>
                    <span class="document-name" style="cursor:pointer;">${doc}</span>
                    <span class="document-arrow" style="margin-left:4px;">▶</span>
                    <button class="firebase-btn-remove" style="margin-left:10px;float:right;background:transparent;color:#dc3545;border:none;font-size:16px;cursor:pointer;" title="Delete document" onclick="event.stopPropagation();deleteDocument('${doc}');">🗑️</button>
                `;
        item.onclick = () => selectDocument(doc);
        if (doc === currentDoc) item.classList.add("active");
        container.appendChild(item);
    });
    if (Object.keys(docs).length === 0) {
        container.innerHTML =
            '<div class="no-collection-selected"><p>No documents in this collection</p></div>';
    }
}
function deleteDocument(docId) {
    if (!currentCollection) return;
    if (!confirm(`Delete document '${docId}'?`)) return;
    delete dataStore[currentCollection][docId];
    if (currentDoc === docId) currentDoc = null;
    renderDocuments();
    clearFields();
    updateBreadcrumb();
}
function selectDocument(doc) {
    currentDoc = doc;
    document.getElementById("currentDocumentTitle").textContent = doc;
    document.getElementById("addFieldBtn").style.display = "inline-block";
    document.getElementById("startCollectionBtn").style.display =
        "inline-block";
    document
        .querySelectorAll(".document-item")
        .forEach((i) => i.classList.remove("active"));
    document.querySelectorAll(".document-item").forEach((i) => {
        if (i.textContent.trim().includes(doc)) i.classList.add("active");
    });
    renderFields();
    updateBreadcrumb();
}
function renderFields() {
    const container = document.getElementById("documentContent");
    if (!currentDoc) {
        container.innerHTML =
            '<div class="no-document-selected"><p>Select a document to view its fields</p></div>';
        return;
    }
    const fields = dataStore[currentCollection][currentDoc] || {};
    let html = "";
    Object.entries(fields).forEach(([k, v]) => {
        html += `
            <div class="field-row">
                <div class="field-name" style="display:flex;align-items:center;">
                    <span style="margin-right:6px;">🔑</span> ${k}:
                    <button class="firebase-btn-remove-small" style="margin-left:10px;background:transparent;color:#dc3545;border:none;font-size:14px;cursor:pointer;" title="Delete field" onclick="deleteField('${k}');event.stopPropagation();">🗑️</button>
                </div>
                <div class="field-value">
                    <input type="text" class="field-input" value="${String(v)}"
                        onchange="updateField('${k}', this.value)">
                </div>
            </div>`;
    });
    container.innerHTML =
        html || '<div class="no-document-selected"><p>No fields</p></div>';
}
function deleteField(fieldName) {
    if (!currentCollection || !currentDoc) return;
    if (!confirm(`Delete field '${fieldName}'?`)) return;
    delete dataStore[currentCollection][currentDoc][fieldName];
    renderFields();
}
function clearFields() {
    document.getElementById("documentContent").innerHTML =
        '<div class="no-document-selected"><p>Select a document to view its fields</p></div>';
}
function clearDocuments() {
    document.getElementById("documentsList").innerHTML =
        '<div class="no-collection-selected"><p>No documents in this collection</p></div>';
}
function MakeDefault() {
  clearFields();
  clearDocuments();
}

function getFieldType(v) {
    if (typeof v === "boolean") return "boolean";
    if (typeof v === "number") return "number";
    if (Array.isArray(v)) return "array";
    if (v instanceof Date) return "timestamp";
    if (typeof v === "object" && v !== null && v.latitude !== undefined)
        return "geopoint";
    return "string";
}
function updateField(name, val) {
    const type = getFieldType(dataStore[currentCollection][currentDoc][name]);
    let p = val;
    if (type === "number") p = Number(val);
    if (type === "boolean") p = val === "true";
    dataStore[currentCollection][currentDoc][name] = p;
}
function updateBreadcrumb() {
  const colCrumb = document.getElementById("collectionBreadcrumb");
  const docSep   = document.getElementById("docBreadcrumb");
  const docCrumb = document.getElementById("currentDocBreadcrumb");

  if (currentCollection) {
    colCrumb.textContent   = currentCollection;
    colCrumb.style.display = "inline";
    docSep.style.display   = currentDoc ? "inline" : "none";
  } else {
    colCrumb.style.display = "none";
    docSep.style.display   = "none";
  }

  if (currentDoc) {
    docCrumb.textContent   = currentDoc;
    docCrumb.style.display = "inline";
  } else {
    docCrumb.style.display = "none";
  }
}
function showStartCollectionModal() {
    document.getElementById("newCollectionId").value = "";
    startCollectionModal.show();
}
function proceedToAddDocument() {
    const id = document.getElementById("newCollectionId").value.trim();
    if (!id) return alert("Enter ID");
    if (dataStore[id]) return alert("Exists");
    dataStore[id] = {};
    currentCollection = id;
    startCollectionModal.hide();
    renderCollections();
    document.getElementById("collectionTitle").textContent = id;
    document.getElementById("addDocumentBtn").style.display = "block";
    renderDocuments();
    showAddDocumentModal();
    updateBreadcrumb();
}
function showAddDocumentModal() {
    if (!currentCollection) return alert("Select collection");
    document.getElementById("newDocumentId").value = "";
    document.getElementById(
        "documentParentPath"
    ).textContent = `/${currentCollection}`;
    documentFields = [];
    renderDocumentFields();
    addDocumentModal.show();
}
function saveDocument() {
    const docId = document.getElementById("newDocumentId").value.trim();
    if (!docId) return alert("Enter doc ID");
    if (
        !dataStore[currentCollection] ||
        typeof dataStore[currentCollection] !== "object" ||
        Array.isArray(dataStore[currentCollection])
    ) {
        dataStore[currentCollection] = {};
    }
    if (dataStore[currentCollection][docId]) return alert("Doc exists");
    const newDoc = {};
    documentFields.forEach((f) => {
        if (f.name.trim()) {
            if (f.type === "array") {
                newDoc[f.name] = f.arrayElements.map((e) =>
                    e.type === "number"
                        ? Number(e.value)
                        : e.type === "boolean"
                        ? e.value === true
                        : e.value
                );
            } else newDoc[f.name] = f.value;
        }
    });
    dataStore[currentCollection][docId] = newDoc;
    documentFields = [];
    addDocumentModal.hide();
    renderDocuments();
    currentDoc = docId;
    document.getElementById("currentDocumentTitle").textContent = docId;
    document.getElementById("addFieldBtn").style.display = "inline-block";
    document.getElementById("startCollectionBtn").style.display =
        "inline-block";
    renderFields();
    updateBreadcrumb();
}
function addDocumentField() {
    const id = "fld_" + Date.now();
    documentFields.push({
        id,
        name: "",
        type: "string",
        value: "",
        arrayElements: [],
    });
    renderDocumentFields();
}
function removeDocumentField(fid) {
    documentFields = documentFields.filter((f) => f.id !== fid);
    renderDocumentFields();
}
function updateDocumentField(fid, prop, val) {
  const f = documentFields.find(x => x.id === fid);
  if (!f) return;
  if (prop === "type") {
    f.type = val;
    f.value =
      val === "number"    ? 0    :
      val === "boolean"   ? true :
      val === "array"     ? []   :
      val === "timestamp" ? new Date() :
                            "";
    f.arrayElements = [];
    renderDocumentFields();
  } else {
    f[prop] = val;
  }
}

function addDocumentFieldArrayElement(fid) {
    const f = documentFields.find((x) => x.id === fid);
    if (!f) return;
    f.arrayElements.push({
        id: "el_" + Date.now(),
        type: "string",
        value: "",
    });
    renderDocumentFields();
}
function removeDocumentFieldArrayElement(fid, eid) {
    const f = documentFields.find((x) => x.id === fid);
    if (!f) return;
    f.arrayElements = f.arrayElements.filter((e) => e.id !== eid);
    renderDocumentFields();
}
function updateDocumentFieldArrayElement(fid, eid, prop, val) {
    const f = documentFields.find((x) => x.id === fid);
    if (!f) return;
    const e = f.arrayElements.find((x) => x.id === eid);
    if (!e) return;
    e[prop] = val;
    if (prop === "type")
        (e.value = prop === "number" ? 0 : prop === "boolean" ? false : ""),
            renderDocumentFields();
}
function getDefaultValueForType(t) {
    if (t === "boolean") return false;
    if (t === "number") return 0;
    if (t === "array") return [];
    if (t === "timestamp") return new Date();
    if (t === "geopoint")
        return {
            latitude: 0,
            longitude: 0,
        };
    return "";
}
function renderDocumentFields() {
    const c = document.getElementById("documentFieldsContainer");
    c.innerHTML = "";
    documentFields.forEach((f) => {
        const div = document.createElement("div");
        div.className = "document-field-item";
        div.innerHTML = `
            <div class="firebase-field-row">
              <div class="firebase-field-input">
                <label class="firebase-label-small">Field</label>
                <input type="text" class="firebase-input-medium" placeholder="Field name" value="${
                    f.name
                }"
                  onchange="updateDocumentField('${f.id}','name',this.value)">
              </div>
              <div class="firebase-field-type">
                <label class="firebase-label-small">Type</label>
                <select class="firebase-select" onchange="updateDocumentField('${
                    f.id
                }','type',this.value)">
                  <option value="string"${
                      f.type === "string" ? " selected" : ""
                  }>string</option>
                  <option value="number"${
                      f.type === "number" ? " selected" : ""
                  }>number</option>
                  <option value="boolean"${
                      f.type === "boolean" ? " selected" : ""
                  }>boolean</option>
                  <option value="array"${
                      f.type === "array" ? " selected" : ""
                  }>array</option>
                  <option value="timestamp"${
                      f.type === "timestamp" ? " selected" : ""
                  }>timestamp</option>
                  <option value="geopoint"${
                      f.type === "geopoint" ? " selected" : ""
                  }>geopoint</option>
                </select>
              </div>
              <button type="button" class="firebase-btn-remove" onclick="removeDocumentField('${
                  f.id
              }')">⊖</button>
            </div>
            <div class="firebase-field-value">${getDocumentFieldValueInput(
                f
            )}</div>
        `;
        c.appendChild(div);
    });
}
function getDocumentFieldValueInput(f) {
    const fid = f.id,
        t = f.type,
        v = f.value;
    if (t === "string") {
        return `<label class="firebase-label-small">String</label>
                <textarea class="firebase-textarea" rows="3"
                  onchange="updateDocumentField('${fid}','value',this.value)">${v}</textarea>`;
    }
    if (t === "number") {
        return `<label class="firebase-label-small">Number</label>
                <input type="number" class="firebase-input-medium" value="${v}"
                  onchange="updateDocumentField('${fid}','value',Number(this.value))">`;
    }
    if (t === "boolean") {
        return `<label class="firebase-label-small">Boolean</label>
                <select class="firebase-select" onchange="updateDocumentField('${fid}','value',this.value==='true')">
                  <option value="true"${
                      v === true ? " selected" : ""
                  }>true</option>
                  <option value="false"${
                      v === false ? " selected" : ""
                  }>false</option>
                </select>`;
    }
    if (t === "array") {
        let html = `<label class="firebase-label-small">Array</label><div class="array-container">`;
        f.arrayElements.forEach((e, i) => {
            html += `<div class="array-element"><div class="array-element-row">
                    <span class="array-index">${i}</span>
                    <select class="firebase-select-small"
                      onchange="updateDocumentFieldArrayElement('${fid}','${
                e.id
            }','type',this.value)">
                      <option value="string"${
                          e.type === "string" ? " selected" : ""
                      }>string</option>
                      <option value="number"${
                          e.type === "number" ? " selected" : ""
                      }>number</option>
                      <option value="boolean"${
                          e.type === "boolean" ? " selected" : ""
                      }>boolean</option>
                    </select>
                    <button type="button" class="firebase-btn-remove-small"
                      onclick="removeDocumentFieldArrayElement('${fid}','${
                e.id
            }')">⊖</button>
                  </div>${getArrayElementInput(fid, e)}</div>`;
        });
        html += `<button type="button" class="firebase-btn-add-field"
                  onclick="addDocumentFieldArrayElement('${fid}')">⊕ Add element</button></div>`;
        return html;
    }
    if (t === "timestamp") {
        const str =
            v instanceof Date
                ? v.toISOString().slice(0, 16)
                : new Date().toISOString().slice(0, 16);
        return `<label class="firebase-label-small">Timestamp</label>
                <input type="datetime-local" class="firebase-input-medium" value="${str}"
                  onchange="updateDocumentField('${fid}','value',new Date(this.value))">`;
    }
    if (t === "geopoint") {
        const lat = v.latitude || 0,
            lng = v.longitude || 0;
        return `<label class="firebase-label-small">Geopoint</label>
                <div class="geopoint-inputs">
                  <div class="geopoint-input">
                    <label class="firebase-label-small">Latitude</label>
                    <input type="number" step="any" class="firebase-input-medium" value="${lat}"
                      onchange="updateDocumentFieldGeopoint('${fid}','latitude',Number(this.value))">
                  </div>
                  <div class="geopoint-input">
                    <label class="firebase-label-small">Longitude</label>
                    <input type="number" step="any" class="firebase-input-medium" value="${lng}"
                      onchange="updateDocumentFieldGeopoint('${fid}','longitude',Number(this.value))">
                  </div>
                </div>`;
    }
    return "";
}
function getArrayElementInput(fid, e) {
    if (e.type === "number") {
        return `<input type="number" class="firebase-input-small" value="${e.value}"
                  onchange="updateDocumentFieldArrayElement('${fid}','${e.id}','value',Number(this.value))">`;
    }
    if (e.type === "boolean") {
        return `<select class="firebase-select-small"
                  onchange="updateDocumentFieldArrayElement('${fid}','${
            e.id
        }','value',this.value==='true')">
                  <option value="true"${
                      e.value === true ? " selected" : ""
                  }>true</option>
                  <option value="false"${
                      e.value === false ? " selected" : ""
                  }>false</option>
                </select>`;
    }
    return `<input type="text" class="firebase-input-small" value="${e.value}"
              onchange="updateDocumentFieldArrayElement('${fid}','${e.id}','value',this.value)">`;
}
function updateDocumentFieldGeopoint(fid, prop, val) {
    const f = documentFields.find((x) => x.id === fid);
    if (!f) return;
    if (typeof f.value !== "object")
        f.value = {
            latitude: 0,
            longitude: 0,
        };
    f.value[prop] = val;
}
function showAddFieldModal() {
    if (!currentDoc) return alert("Select a document");
    document.getElementById("fieldName").value = "";
    document.getElementById("fieldType").value = "string";
    fieldModalArrayElements = [];
    updateFieldModalValueType();
    addFieldModal.show();
}
function updateFieldModalValueType() {
    const type = document.getElementById("fieldType").value;
    const cont = document.getElementById("fieldModalValueContainer"),
        arr = document.getElementById("fieldModalArrayContainer");
    arr.style.display = "none";
    cont.style.display = "block";
    if (type === "boolean") {
        cont.innerHTML = `<label class="firebase-label-small">Boolean</label>
          <select id="fieldValue" class="firebase-textarea-bordered">
            <option value="true">true</option>
            <option value="false">false</option>
          </select>`;
    } else if (type === "number") {
        cont.innerHTML = `<label class="firebase-label-small">Number</label>
          <input type="number" id="fieldValue" class="firebase-textarea-bordered" style="height:auto;padding:12px;">`;
    } else if (type === "timestamp") {
        cont.innerHTML = `<label class="firebase-label-small">Timestamp</label>
          <input type="datetime-local" id="fieldValue" class="firebase-textarea-bordered" style="height:auto;padding:12px;">`;
    } else if (type === "geopoint") {
        cont.innerHTML = `<label class="firebase-label-small">Geopoint</label>
          <div style="display:flex;gap:12px;">
            <input type="number" step="any" id="fieldLat" class="firebase-textarea-bordered" placeholder="Latitude" style="height:auto;padding:12px;">
            <input type="number" step="any" id="fieldLng" class="firebase-textarea-bordered" placeholder="Longitude" style="height:auto;padding:12px;">
          </div>`;
    } else if (type === "array") {
        cont.style.display = "none";
        arr.style.display = "block";
        renderFieldModalArrayElements();
    } else {
        cont.innerHTML = `<label class="firebase-label-small">${
            type.charAt(0).toUpperCase() + type.slice(1)
        }</label>
          <textarea id="fieldValue" class="firebase-textarea-bordered" rows="4"></textarea>`;
    }
}
function addFieldModalArrayElement() {
    fieldModalArrayElements.push({
        id: "el_" + Date.now(),
        type: "string",
        value: "",
    });
    renderFieldModalArrayElements();
}
function removeFieldModalArrayElement(eid) {
    fieldModalArrayElements = fieldModalArrayElements.filter(
        (x) => x.id !== eid
    );
    renderFieldModalArrayElements();
}
function updateFieldModalArrayElement(eid, prop, val) {
    const e = fieldModalArrayElements.find((x) => x.id === eid);
    if (!e) return;
    e[prop] = val;
    if (prop === "type")
        (e.value = prop === "number" ? 0 : prop === "boolean" ? false : ""),
            renderFieldModalArrayElements();
}
function renderFieldModalArrayElements() {
    const c = document.getElementById("fieldModalArrayElements");
    c.innerHTML = "";
    fieldModalArrayElements.forEach((e, i) => {
        const d = document.createElement("div");
        d.className = "array-element";
        d.innerHTML = `
          <div class="array-element-row">
            <span class="array-index">${i}</span>
            <select class="firebase-select-small" onchange="updateFieldModalArrayElement('${
                e.id
            }','type',this.value)">
              <option value="string"${
                  e.type === "string" ? " selected" : ""
              }>string</option>
              <option value="number"${
                  e.type === "number" ? " selected" : ""
              }>number</option>
              <option value="boolean"${
                  e.type === "boolean" ? " selected" : ""
              }>boolean</option>
            </select>
            <button type="button" class="firebase-btn-remove-small" onclick="removeFieldModalArrayElement('${
                e.id
            }')">⊖</button>
          </div>${getFieldModalArrayElementInput(e)}`;
        c.appendChild(d);
    });
}
function getFieldModalArrayElementInput(e) {
    if (e.type === "number") {
        return `<input type="number" class="firebase-input-small" value="${e.value}"
                  onchange="updateFieldModalArrayElement('${e.id}','value',Number(this.value))">`;
    }
    if (e.type === "boolean") {
        return `<select class="firebase-select-small" onchange="updateFieldModalArrayElement('${
            e.id
        }','value',this.value==='true')">
                  <option value="true"${
                      e.value === true ? " selected" : ""
                  }>true</option>
                  <option value="false"${
                      e.value === false ? " selected" : ""
                  }>false</option>
                </select>`;
    }
    return `<input type="text" class="firebase-input-small" value="${e.value}"
              onchange="updateFieldModalArrayElement('${e.id}','value',this.value)">`;
}
function saveField() {
    const name = document.getElementById("fieldName").value.trim();
    if (!name) return alert("Enter field name");
    if (!currentCollection || !currentDoc)
        return alert("Select collection & doc");
    const type = document.getElementById("fieldType").value;
    let val;
    if (type === "array")
        val = fieldModalArrayElements.map((e) =>
            e.type === "number"
                ? Number(e.value)
                : e.type === "boolean"
                ? e.value === true
                : e.value
        );
    else if (type === "geopoint") {
        const lat = parseFloat(document.getElementById("fieldLat").value),
            lng = parseFloat(document.getElementById("fieldLng").value);
        if (isNaN(lat) || isNaN(lng)) return alert("Invalid geo");
        val = {
            latitude: lat,
            longitude: lng,
        };
    } else if (type === "timestamp") {
        const dv = document.getElementById("fieldValue").value;
        if (!dv) return alert("Invalid timestamp");
        val = new Date(dv);
    } else if (type === "boolean")
        val = document.getElementById("fieldValue").value === "true";
    else if (type === "number") {
        const nv = document.getElementById("fieldValue").value;
        if (nv === "" || isNaN(nv)) return alert("Invalid number");
        val = Number(nv);
    } else val = document.getElementById("fieldValue").value;
    dataStore[currentCollection][currentDoc][name] = val;
    addFieldModal.hide();
    renderFields();
}

document.addEventListener("DOMContentLoaded", () => {
    initializeInterface();
    window.firestoreData = dataStore;
    const f = document.getElementById("appForm");
    f.addEventListener("submit", function (e) {
        e.preventDefault();
        sanitizeDataStore(window.firestoreData);
        const fullJson = JSON.stringify(window.firestoreData, null, 2);
        document.getElementById("firestoreJson").value = fullJson;
        this.submit();
    });
    renderApiKeyPagination();
});

function startResize(e) {
    e.preventDefault();
    const touch = e.type === "touchstart";
    startX = touch ? e.touches[0].clientX : e.clientX;
    currentPanel = document.getElementById(this.previousElementSibling.id);
    startWidth = currentPanel.offsetWidth;
    document.addEventListener("mousemove", resizePanel);
    document.addEventListener("mouseup", stopResize);
    document.addEventListener("touchmove", resizePanel, {
        passive: false,
    });
    document.addEventListener("touchend", stopResize);
}

function resizePanel(e) {
    if (!currentPanel) return;
    const clientX = e.type.startsWith("touch")
        ? e.touches[0].clientX
        : e.clientX;
    const dx = clientX - startX;
    currentPanel.style.width = `${startWidth + dx}px`;
}

function stopResize() {
    document.removeEventListener("mousemove", resizePanel);
    document.removeEventListener("mouseup", stopResize);
    document.removeEventListener("touchmove", resizePanel);
    document.removeEventListener("touchend", stopResize);
    currentPanel = null;
}

resizers.forEach((r) => {
    r.addEventListener("mousedown", startResize);
    r.addEventListener("touchstart", startResize, {
        passive: false,
    });
});

function togglePanel(panelId) {
    const panel = document.getElementById(panelId);
    const resizer = document.querySelector(
        `.resizer[data-target="${panelId}"]`
    );
    if (!panel) return;
    const hidden = panel.style.display === "none";
    panel.style.display = hidden ? "block" : "none";
    if (resizer) resizer.style.display = hidden ? "block" : "none";
}
function generateFirestoreId() {
  const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
  let id = '';
  for (let i = 0; i < 20; i++) {
    id += chars.charAt(Math.floor(Math.random() * chars.length));
  }
  return id;
}
function autoId() {
  document.getElementById('newDocumentId').value = generateFirestoreId();
}
