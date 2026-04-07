@extends('layout.default')

@push('css')
<link href="/assets/css/firestore.css" rel="stylesheet" />
@endpush

@section('content')
<div class="container mt-3">
    <div class="row gx-4 gy-4">
        {{-- Left column: Application form --}}
        <div class="col-12 col-lg-6">
            <div class="card shadow-lg border-0 rounded-4 h-100">
                <div class="card-header bg-theme text-white text-center py-3">
                    <h3 class="mb-0">
                        {{ isset($application) ? 'Edit Application' : 'Create Application' }}
                    </h3>
                </div>
                <div class="card-body p-4">
                    <form
                        id="appForm"
                        action="{{ isset($application) ? route('applications.update', $application->id) : route('applications.store') }}"
                        method="POST"
                        class="needs-validation"
                        novalidate>
                        @csrf
                        <input type="hidden" name="firestore_json" id="firestoreJson">
                        @isset($application)
                        @method('PUT')
                        @endisset

                        <div class="mb-4">
                            <label class="form-label">Application Name:</label>
                            <input
                                type="text"
                                name="application_name"
                                value="{{ old('application_name', $application->application_name ?? '') }}"
                                class="form-control"
                                required>
                            @error('application_name')
                            <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Package Name:</label>
                            <input
                                type="text"
                                name="package_name"
                                value="{{ old('package_name', $application->package_name ?? '') }}"
                                class="form-control"
                                required>
                            @error('package_name')
                            <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <button type="button" class="btn btn-secondary" onclick="history.back()">
                                Cancel
                            </button>
                            @if(isset($application))
                            <button type="submit" class="btn btn-theme">
                                Save Application &amp; Ads
                            </button>
                            @else
                            <button type="submit" class="btn btn-primary">
                                Create Application
                            </button>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Right column: API Keys --}}
        @isset($application)
        @if(!empty($application->api_keys))
        @php
        $apiKeys = array_filter(explode(',', $application->api_keys));
        @endphp
        <div class="col-12 col-lg-6">
            <div class="card shadow-sm border-0 rounded-4 h-100">
                <div class="card-header bg-light border-bottom">
                    <h5 class="mb-0">API Keys</h5>
                </div>
                <div class="card-body p-3">
                    <div class="table-responsive">
                        <table
                            class="table table-sm table-bordered table-hover mb-0 text-sm align-middle"
                            id="apiKeysTable">
                            <thead class="table-light">
                                <tr>
                                    <th>Key</th>
                                    <th style="width: 100px;">Action</th>
                                </tr>
                            </thead>
                            <tbody id="apiKeysTableBody">
                                @foreach($apiKeys as $index => $key)
                                <tr data-key="{{ trim($key) }}" data-index="{{ $index }}">
                                    <td class="text-break">{{ trim($key) }}</td>
                                    <td>
                                        <button
                                            class="btn btn-outline-danger btn-sm"
                                            onclick="deleteApiKey('{{ $application->id }}', '{{ trim($key) }}', this)">
                                            Delete
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <nav class="mt-3">
                        <ul class="pagination pagination-sm justify-content-end" id="apiKeysPagination"></ul>
                    </nav>
                </div>
            </div>
        </div>
        @endif
        @endisset
    </div>

    {{-- Full-width Firestore UI below --}}
    @isset($application)
    <div class="firebase-container mt-4">
        <div class="firebase-breadcrumb d-flex justify-content-between align-items-center flex-wrap">
            <div class="breadcrumb-items">
                <span class="breadcrumb-item">🏠</span>
                <span class="breadcrumb-separator"><i class="fa fa-chevron-right" aria-hidden="true"></i></span>
                <span class="breadcrumb-item" id="collectionBreadcrumb" style="display:none;"></span>
                <span class="breadcrumb-separator" id="docBreadcrumb" style="display: inline;">
                    <i class="fa fa-chevron-right" aria-hidden="true"></i>
                </span>
                <span class="breadcrumb-item" id="currentDocBreadcrumb" style="display:none;"></span>
            </div>
            <div class="breadcrumb-actions d-flex align-items-center gap-2">
                <button
                    type="button"
                    class="btn btn-success btn-sm"
                    onclick="downloadFirestoreJson()">
                    ⬇️ Download JSON
                </button>
                <div class="mobile-panel-toggle d-flex gap-1">
                    <button onclick="togglePanel('leftPanel')">📂</button>
                    <button onclick="togglePanel('middlePanel')">📄</button>
                    <button onclick="togglePanel('rightPanel')">📋</button>
                </div>
            </div>
        </div>

        <div class="firebase-interface" id="firebaseInterface">
            <!-- Left Panel -->
            <div class="firebase-left-panel" id="leftPanel">
                <div class="firebase-panel-header">
                    <span class="panel-title" role="button" tabindex="0" style="cursor: pointer;" onclick="MakeDefault()">📡 (default)</span>
                    <button class="firebase-btn-icon" onclick="togglePanel('leftPanel')">❌</button>
                </div>
                <div class="collection-section">
                    <div class="start-collection-btn" onclick="showStartCollectionModal()">➕ Start collection</div>
                    <div class="collections-list" id="collectionsList"></div>
                </div>
            </div>
            <div class="resizer" data-direction="horizontal" data-target="leftPanel"></div>

            <!-- Middle Panel -->
            <div class="firebase-middle-panel" id="middlePanel">
                <div class="firebase-panel-header">
                    <span class="panel-icon">📦</span>
                    <span class="panel-title" id="collectionTitle">Select a collection</span>
                    <button class="firebase-btn-icon" onclick="togglePanel('middlePanel')">❌</button>
                </div>
                <div class="documents-section">
                    <div class="add-document-btn" id="addDocumentBtn" onclick="showAddDocumentModal()" style="display:none;">
                        ➕ Add document
                    </div>
                    <div class="documents-list" id="documentsList">
                        <div class="no-collection-selected">
                            <p>Select a collection to view documents</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="resizer" data-direction="horizontal" data-target="middlePanel"></div>

            <!-- Right Panel -->
            <div class="firebase-right-panel" id="rightPanel">
                <div class="firebase-panel-header">
                    <span class="panel-icon">📄</span>
                    <span class="panel-title" id="currentDocumentTitle">Select a document</span>
                    <div class="panel-actions">
                        <button class="firebase-btn-icon" id="startCollectionBtn" onclick="showStartCollectionModal()" style="display:none;">➕ Start collection</button>
                        <button class="firebase-btn-icon" id="addFieldBtn" onclick="showAddFieldModal()" style="display:none;">➕ Add field</button>
                        <button class="firebase-btn-icon" onclick="togglePanel('rightPanel')">❌</button>
                    </div>
                </div>
                <div class="document-content" id="documentContent">
                    <div class="no-document-selected">
                        <p>Select a document to view its fields</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="startCollectionModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content firebase-modal-large">
                <div class="modal-body p-0">
                    <div class="firebase-modal-content">
                        <h2 class="firebase-modal-title">Start a collection</h2>
                        <div class="firebase-steps">
                            <div class="step-item active">
                                <div class="step-number">1</div>
                                <div class="step-text">Give the collection an ID</div>
                            </div>
                            <div class="step-separator"></div>
                            <div class="step-item">
                                <div class="step-number">2</div>
                                <div class="step-text">Add its first document</div>
                            </div>
                        </div>
                        <div class="firebase-form-section">
                            <div class="form-group">
                                <label class="firebase-label">Parent path</label>
                                <div class="firebase-path">/</div>
                            </div>
                            <div class="form-group">
                                <label class="firebase-label">Collection ID <span class="firebase-info-icon">ⓘ</span></label>
                                <input type="text" id="newCollectionId" class="firebase-input-large" placeholder="Enter collection ID">
                            </div>
                        </div>
                        <div class="firebase-modal-actions">
                            <button type="button" class="firebase-btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="firebase-btn-primary" onclick="proceedToAddDocument()">Next</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addDocumentModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content firebase-modal-large">
                <div class="modal-body p-0">
                    <div class="firebase-modal-content">
                        <h2 class="firebase-modal-title">Add a document</h2>
                        <div class="firebase-form-section">
                            <div class="form-group">
                                <label class="firebase-label">Parent path</label>
                                <div class="firebase-path" id="documentParentPath">/</div>
                            </div>
                            <div class="form-group">
                                <label class="firebase-label">Document ID <span class="firebase-info-icon">ⓘ</span></label>
                                <div class="firebase-input-group">
                                    <input type="text" id="newDocumentId" class="firebase-input-large" placeholder="Enter document ID">
                                    <button type="button" class="firebase-btn-link" onclick="autoId()">Auto-ID</button>
                                </div>
                                <div class="firebase-required">⚠ Required</div>
                            </div>
                            <div class="firebase-field-section">
                                <div id="documentFieldsContainer"></div>
                                <button type="button" class="firebase-btn-add-field" onclick="addDocumentField()">⊕ Add field</button>
                            </div>
                        </div>
                        <div class="firebase-modal-actions">
                            <button type="button" class="firebase-btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="firebase-btn-primary" onclick="saveDocument()">Save</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addFieldModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content firebase-modal-large">
                <div class="modal-body p-0">
                    <div class="firebase-modal-content firebase-field-modal">
                        <div class="firebase-field-form">
                            <div class="firebase-field-row">
                                <div class="firebase-field-input">
                                    <label class="firebase-label-small">Field</label>
                                    <input type="text" id="fieldName" class="firebase-input-bordered" placeholder="Field name">
                                </div>
                                <div class="firebase-field-type">
                                    <label class="firebase-label-small">Type</label>
                                    <select id="fieldType" class="firebase-select-bordered" onchange="updateFieldModalValueType()">
                                        <option value="string">string</option>
                                        <option value="number">number</option>
                                        <option value="boolean">boolean</option>
                                        <option value="array">array</option>
                                        <option value="timestamp">timestamp</option>
                                        <option value="geopoint">geopoint</option>
                                    </select>
                                </div>
                            </div>
                            <div class="firebase-field-value" id="fieldModalValueContainer">
                                <label class="firebase-label-small">String</label>
                                <textarea id="fieldValue" class="firebase-textarea-bordered" rows="4"></textarea>
                            </div>
                            <div id="fieldModalArrayContainer" style="display: none;">
                                <div class="array-elements" id="fieldModalArrayElements"></div>
                                <button type="button" class="firebase-btn-add-field" onclick="addFieldModalArrayElement()">⊕ Add element</button>
                            </div>
                        </div>
                        <div class="firebase-modal-actions">
                            <button type="button" class="firebase-btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="firebase-btn-primary" onclick="saveField()">Add</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    window.dataStore = {!! json_encode(!empty($ads) ? $ads : [$application->package_name => new \stdClass]) !!};
    </script>
    <script src="/assets/js/firestore.js?ts={{ time() }}"></script>

    @endisset
</div>
@endsection