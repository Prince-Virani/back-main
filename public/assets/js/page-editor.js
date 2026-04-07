(function(window, document) {
    'use strict';
    
    if (window.PageEditor) {
        return;
    }

    class PageEditor {
        constructor() {
            this.form = null;
            this.editor = null;
            this.hasUnsavedChanges = false;
            this.autoSaveTimer = null;
            this.wordCount = 0;
            this.pageKey = 'pageDraft_' + window.location.pathname;
            this.isInitialized = false;
            this.debouncedSaveDraft = null;
            this.debouncedWordCount = null;
            this.retryCount = 0;
            this.maxRetries = 3;
            
            this.handleFormSubmit = this.handleFormSubmit.bind(this);
            this.handleFormChange = this.handleFormChange.bind(this);
            this.onEditorChange = this.onEditorChange.bind(this);
            this.updateWordCount = this.updateWordCount.bind(this);
            this.handleBeforeUnload = this.handleBeforeUnload.bind(this);
            
            this.init();
        }

        init() {
            if (this.isInitialized || (document.body && document.body.hasAttribute('data-page-editor-initialized'))) {
                return;
            }
            
            if (document.body) {
                document.body.setAttribute('data-page-editor-initializing', 'true');
            }
            
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', () => {
                    this.delayedInitialize();
                });
            } else {
                this.delayedInitialize();
            }
        }

        delayedInitialize() {
            const checkDependencies = () => {
                const hasJQuery = typeof window.$ !== 'undefined';
                const hasSummernote = hasJQuery && window.$.fn.summernote;
                const hasSelect2 = hasJQuery && window.$.fn.select2;
                const hasBootstrap = typeof window.bootstrap !== 'undefined';
                
                if (hasJQuery && hasSummernote && hasSelect2 && hasBootstrap) {
                    setTimeout(() => this.initialize(), 50);
                } else if (this.retryCount < this.maxRetries) {
                    this.retryCount++;
                    setTimeout(checkDependencies, 200);
                } else {
                    this.showError('Failed to load editor dependencies. Please refresh the page.');
                }
            };
            
            checkDependencies();
        }

        initialize() {
            if (this.isInitialized) return;
            
            try {
                this.debouncedSaveDraft = this.debounce(this.saveDraft.bind(this), 1000);
                this.debouncedWordCount = this.debounce(this.updateWordCount.bind(this), 300);
                
                this.initializeForm();
                this.initializeSelect2();
                this.initializeEditor();
                this.initializeImageUpload();
                this.initializeEventListeners();
                this.initializeUnsavedChangesWarning();
                this.loadDraft();
                
                this.isInitialized = true;
                if (document.body) {
                    document.body.setAttribute('data-page-editor-initialized', 'true');
                    document.body.removeAttribute('data-page-editor-initializing');
                }
                
            } catch (error) {
                this.showError('Failed to initialize editor. Please refresh the page.');
            }
        }


initializeCrossTabSync() {
    try {
        window.addEventListener('storage', (e) => {
            if (!e || e.storageArea !== localStorage || e.key !== this.pageKey) return;
            // If another tab cleared the draft, ignore
            if (!e.newValue) return;
            // If we don't have jQuery/Summernote yet, just loadDraft()
            if (typeof window.$ === 'undefined' || !window.$.fn || !window.$.fn.summernote) {
                this.loadDraft();
                return;
            }
            const current = window.$('#editor').summernote('code') || '';
            let incomingHtml = '';
            try {
                const data = JSON.parse(e.newValue);
                incomingHtml = (data && data.content) ? String(data.content) : '';
            } catch (_) {}
            // If same content, ignore
            if (!incomingHtml || current.trim() === incomingHtml.trim()) return;
            const shouldLoad = !this.hasUnsavedChanges || confirm('A newer draft was saved in another tab. Load it here?');
            if (shouldLoad) {
                this.loadDraft();
                this.showAlert('Draft loaded from another tab.', 'info');
            } else {
                this.showAlert('Kept your local changes. Other tab also has a draft.', 'info');
            }
        });
    } catch (_) {}
}

        debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }

        initializeForm() {
            this.form = document.getElementById('page-form');
            if (!this.form) {
                return;
            }

            this.form.addEventListener('submit', this.handleFormSubmit);
            this.form.addEventListener('input', this.debounce(this.handleFormChange.bind(this), 300));
            this.form.addEventListener('change', this.handleFormChange);
            
            const requiredFields = this.form.querySelectorAll('[required]');
            requiredFields.forEach(field => {
                field.addEventListener('blur', () => this.validateField(field));
                field.addEventListener('input', this.debounce(() => this.validateField(field), 500));
            });
        }

        validateField(field) {
            const value = field.value.trim();
            let isValid = field.checkValidity() && value.length > 0;
            
            if (field.id === 'paramlink' && value.length > 0) {
                isValid = /^[a-z0-9]+(?:-[a-z0-9]+)*$/.test(value);
            }
            
            field.classList.toggle('is-invalid', !isValid);
            field.classList.toggle('is-valid', isValid && value.length > 0);
            
            const feedback = field.parentNode.querySelector('.invalid-feedback');
            if (!isValid && feedback && field.id === 'paramlink' && value.length > 0) {
                feedback.innerHTML = '<i class="fas fa-exclamation-circle me-1"></i>URL slug can only contain lowercase letters, numbers, and hyphens';
            }
            
            return isValid;
        }

        initializeSelect2() {
            if (typeof window.$ === 'undefined' || !window.$.fn.select2) {
                return;
            }

            const $ = window.$;
            
            try {
                $('#websiteSelect').select2({
                    placeholder: "-- Select Website --",
                    allowClear: true,
                    width: '100%',
                    dropdownAutoWidth: true,
                    minimumResultsForSearch: 5
                });

                $('#categorySelect').select2({
                    placeholder: "Select relevant categories for this page",
                    allowClear: true,
                    width: '100%',
                    dropdownAutoWidth: true,
                    tags: true,
                    tokenSeparators: [','],
                    minimumResultsForSearch: 3
                });

                $('#websiteSelect, #categorySelect').on('select2:select select2:unselect select2:clear', () => {
                    this.markUnsaved();
                });

            } catch (error) {
                this.showError('Failed to initialize dropdowns. Some features may not work properly.');
            }
        }

        initializeEditor() {
            if (typeof window.$ === 'undefined' || !window.$.fn.summernote) {
                return;
            }

            const $ = window.$;
            
            try {
                $('#wordCount').remove();
                
                $('#editor').after('<div id="wordCount" class="text-end small mt-1">0 words</div>');

                this.editor = $('#editor').summernote({
                    height: 400,
                    placeholder: 'Write your content here...',
                    spellCheck: true,
                    focus: false,
                    toolbar: [
                        ['style', ['style', 'bold', 'italic', 'underline', 'strikethrough']],
                        ['fontsize', ['fontsize']],
                        ['fontname', ['fontname']],
                        ['color', ['color']],
                        ['para', ['ul', 'ol', 'paragraph', 'align']],
                        ['height', ['height']],
                        ['table', ['table']],
                        ['insert', ['link', 'picture', 'hr', 'video']],
                        ['view', ['fullscreen', 'codeview']],
                        ['history', ['undo', 'redo']],
                        ['misc', ['insertDate', 'templates']],
                        ['clear', ['removeFormat']],
                        ['help', ['help']]
                    ],
                    buttons: {
                        insertDate: this.createInsertDateButton.bind(this),
                        templates: this.createTemplatesButton.bind(this)
                    },
                    callbacks: {
                        onInit: this.onEditorInit.bind(this),
                        onChange: this.onEditorChange,
                        onKeyup: this.updateWordCount.bind(this),
                        onImageUpload: this.handleImageUpload.bind(this),
                        onPaste: this.handlePaste.bind(this)
                    }
                });

            } catch (error) {
                this.showError('Failed to initialize text editor. Please refresh the page.');
            }
        }

        createInsertDateButton() {
            if (typeof window.$ === 'undefined') return null;
            
            const $ = window.$;
            const ui = $.summernote.ui;
            
            return ui.button({
                contents: '<i class="fas fa-calendar-alt"></i>',
                tooltip: 'Insert Current Date',
                container: 'body',
                click: (e) => {
                    e.preventDefault();
                    const today = new Date();
                    const options = { 
                        year: 'numeric', 
                        month: 'long', 
                        day: 'numeric',
                        hour: '2-digit', 
                        minute: '2-digit'
                    };
                    const formattedDate = today.toLocaleDateString('en-US', options);
                    $('#editor').summernote('editor.insertText', formattedDate);
                }
            }).render();
        }

        createTemplatesButton() {
            if (typeof window.$ === 'undefined') return null;
            
            const $ = window.$;
            const templates = {
                'Alert Box': `
                    <div class="alert alert-info border-0 shadow-sm" role="alert">
                        <h4 class="alert-heading"><i class="fas fa-info-circle me-2"></i>Information</h4>
                        <p class="mb-0">Your important message goes here...</p>
                    </div>
                `,
                'Call to Action': `
                    <div class="text-center p-4 bg-light rounded-3 my-4">
                        <h4 class="mb-3">Ready to Get Started?</h4>
                        <p class="mb-4 text-muted">Join thousands of satisfied customers today!</p>
                        <a href="#" class="btn btn-primary btn-lg px-4">Get Started Now</a>
                    </div>
                `,
                'Quote Block': `
                    <blockquote class="blockquote text-center border-start border-5 border-primary ps-4 my-4">
                        <p class="mb-3 fs-5 fst-italic">"Your inspiring quote goes here..."</p>
                        <footer class="blockquote-footer mt-3">
                            <cite title="Source Title">Author Name</cite>
                        </footer>
                    </blockquote>
                `,
                'Feature Box': `
                    <div class="row my-4">
                        <div class="col-md-6 mb-3">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="card-body">
                                    <h5 class="card-title"><i class="fas fa-star text-warning me-2"></i>Feature Title</h5>
                                    <p class="card-text">Describe your feature here...</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="card-body">
                                    <h5 class="card-title"><i class="fas fa-heart text-danger me-2"></i>Another Feature</h5>
                                    <p class="card-text">Describe your second feature here...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                `,
                'Pricing Table': `
                    <div class="row my-4">
                        <div class="col-md-4 mb-3">
                            <div class="card text-center border-0 shadow-sm">
                                <div class="card-header bg-primary text-white">
                                    <h4>Basic</h4>
                                </div>
                                <div class="card-body">
                                    <h2 class="card-title">$19<small class="text-muted">/month</small></h2>
                                    <ul class="list-unstyled">
                                        <li>Feature 1</li>
                                        <li>Feature 2</li>
                                        <li>Feature 3</li>
                                    </ul>
                                    <a href="#" class="btn btn-primary">Choose Plan</a>
                                </div>
                            </div>
                        </div>
                    </div>
                `
            };

            const $group = $('<div class="btn-group note-btn-group"></div>');
            const $button = $('<button type="button" class="note-btn btn btn-light dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false"><i class="fas fa-th-large me-1"></i>Templates</button>');
            const $menu = $('<div class="dropdown-menu"></div>');

            Object.keys(templates).forEach(key => {
                const $item = $(`<a class="dropdown-item" href="#"><i class="fas fa-plus-circle me-2 text-primary"></i>${key}</a>`);
                $item.on('click', (e) => {
                    e.preventDefault();
                    $('#editor').summernote('editor.pasteHTML', templates[key]);
                });
                $menu.append($item);
            });

            $group.append($button).append($menu);
            return $group[0];
        }

        onEditorInit() {
            setTimeout(() => {
                this.updateWordCount();
                const existingContent = document.querySelector('#editor').value;
                if (existingContent && typeof window.$ !== 'undefined') {
                    window.$('#editor').summernote('code', existingContent);
                }
            }, 200);
        }

        onEditorChange(contents) {
            this.saveDraft(contents);
            this.updateWordCount();
            this.markUnsaved();
        }

        updateWordCount() {
            if (typeof window.$ === 'undefined') return;
            
            const $ = window.$;
            const text = $('<div>').html($('#editor').summernote('code')).text().trim();
            this.wordCount = text ? text.split(/\s+/).filter(word => word.length > 0).length : 0;
            
            const $wordCount = $('#wordCount');
            if ($wordCount.length) {
                $wordCount.html(`
                    <i class="fas fa-pen-nib me-1"></i>
                    ${this.wordCount} words
                    <i class="fas fa-clock ms-2 me-1"></i>
                    ~${Math.ceil(this.wordCount / 200)} min read
                `);
            }
        }

        handleImageUpload(files) {
            if (!files || files.length === 0) return;

            const csrfToken = document.querySelector('meta[name="csrf-token"]');
            if (!csrfToken) {
                this.showError('CSRF token not found. Please refresh the page.');
                return;
            }

            const file = files[0];
            
            if (file.size > 5 * 1024 * 1024) {
                this.showError('Image size must be less than 5MB');
                return;
            }

            if (!file.type.match(/^image\/(jpeg|jpg|png|webp|gif)$/)) {
                this.showError('Please select a valid image file (JPG, PNG, WebP, GIF)');
                return;
            }

            const data = new FormData();
            data.append('image', file);
            data.append('_token', csrfToken.getAttribute('content'));

            this.showLoadingState('.editor-container');
            this.showInfo('Uploading image...');

            fetch('/api/upload-image', {
                method: 'POST',
                body: data,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.url) {
                    if (typeof window.$ !== 'undefined') {
                        window.$('#editor').summernote('insertImage', data.url, function($image) {
                            $image.addClass('img-fluid rounded');
                            $image.attr('alt', 'Uploaded image');
                        });
                    }
                    this.showSuccess('Image uploaded successfully!');
                } else {
                    this.showError(data.message || 'Failed to upload image. Please try again.');
                }
            })
            .catch(error => {
                this.showError('An error occurred while uploading the image.');
            })
            .finally(() => {
                this.hideLoadingState('.editor-container');
            });
        }

        

cleanPasteHtml(html) {
    if (!html) return '';
    // Strip comments, styles, MSO classes, and inline widths/heights
    html = html
        .replace(/<!--[\s\S]*?-->/g, '')
        .replace(/\sclass="Mso[\w\s\-]*"/gi, '')
        .replace(/\sstyle="[^"]*"/gi, '')
        .replace(/<(meta|link)[^>]*>/gi, '')
        .replace(/<(span)([^>]*)>/gi, '<span>')  // drop extra attrs on spans
        .replace(/<o:p>.*?<\/o:p>/gi, '')
        .replace(/\s(width|height)="\d+%?"/gi, '');
    return html;
}

handlePaste(e) {
    try {
        const evt = e.originalEvent || e;
        const data = (evt && evt.clipboardData) ? evt.clipboardData : window.clipboardData;
        if (data) {
            const html = data.getData('text/html');
            const text = data.getData('text/plain');
            if (html) {
                e.preventDefault();
                const cleaned = this.cleanPasteHtml(html);
                if (typeof window.$ !== 'undefined') {
                    window.$('#editor').summernote('pasteHTML', cleaned);
                }
            } else if (text) {
                e.preventDefault();
                if (document.execCommand) {
                    document.execCommand('insertText', false, text);
                } else if (typeof window.$ !== 'undefined') {
                    window.$('#editor').summernote('pasteHTML', text);
                }
            }
        }
    } catch (_) {}
    // Update counters shortly after paste
    setTimeout(() => {
        this.updateWordCount();
        this.markUnsaved();
    }, 120);
}

        initializeImageUpload() {
            const imageInput = document.getElementById('imageInput');
            const imagePreview = document.getElementById('imagePreview');
            const removeButton = document.getElementById('removeImage');

            if (!imageInput || !imagePreview) return;

            if (imagePreview.src && !imagePreview.src.includes('placeholder.png')) {
                if (removeButton) removeButton.style.display = 'inline-block';
            }

            imageInput.addEventListener('change', (e) => {
                const file = e.target.files[0];
                
                if (file) {
                    if (file.size > 2 * 1024 * 1024) {
                        this.showError('Image size must be less than 2MB');
                        imageInput.value = '';
                        return;
                    }

                    if (!file.type.match(/^image\/(jpeg|jpg|png|webp)$/)) {
                        this.showError('Please select a valid image file (JPG, PNG, WebP)');
                        imageInput.value = '';
                        return;
                    }

                    const reader = new FileReader();
                    reader.onload = (evt) => {
                        imagePreview.src = evt.target.result;
                        if (removeButton) {
                            removeButton.style.display = 'inline-block';
                        }
                    };
                    reader.onerror = () => {
                        this.showError('Error reading file. Please try again.');
                    };
                    reader.readAsDataURL(file);
                    this.markUnsaved();
                }
            });

            if (removeButton) {
                removeButton.addEventListener('click', () => {
                    imageInput.value = '';
                    imagePreview.src = '/assets/images/placeholder.png';
                    removeButton.style.display = 'none';
                    this.markUnsaved();
                });
            }
        }

        initializeEventListeners() {
            const pageNameInput = document.getElementById('pageName');
            const paramlinkInput = document.getElementById('paramlink');

            if (pageNameInput && paramlinkInput) {
                pageNameInput.addEventListener('input', (e) => {
                    const cleaned = e.target.value.toLowerCase()
                        .replace(/[^a-z0-9\s-]/g, '')
                        .replace(/\s+/g, '-')
                        .replace(/--+/g, '-')
                        .replace(/^-+|-+$/g, '');
                    paramlinkInput.value = cleaned;
                    this.markUnsaved();
                });
            }

            const autoSaveCheckbox = document.getElementById('autoSave');
            if (autoSaveCheckbox) {
                autoSaveCheckbox.addEventListener('change', (e) => {
                    if (e.target.checked) {
                        this.startAutoSave();
                        this.showInfo('Auto-save enabled');
                    } else {
                        this.stopAutoSave();
                        this.showInfo('Auto-save disabled');
                    }
                });
            }
        }

        initializeUnsavedChangesWarning() {
            window.addEventListener('beforeunload', this.handleBeforeUnload);

            document.addEventListener('click', (e) => {
                const link = e.target.closest('a[href]');
                if (link && this.hasUnsavedChanges && !link.href.includes('#') && !link.hasAttribute('data-bs-toggle')) {
                    e.preventDefault();
                    this.showUnsavedChangesModal(link.href);
                }
            });
        }

        handleBeforeUnload(e) {
            if (this.hasUnsavedChanges) {
                e.preventDefault();
                e.returnValue = 'You have unsaved changes. Are you sure you want to leave?';
                return 'You have unsaved changes. Are you sure you want to leave?';
            }
        }

        showUnsavedChangesModal(targetUrl) {
            const modal = document.getElementById('unsavedChangesModal');
            const confirmButton = document.getElementById('confirmLeave');
            
            if (modal && confirmButton && typeof window.bootstrap !== 'undefined') {
                confirmButton.onclick = () => {
                    this.hasUnsavedChanges = false;
                    window.location.href = targetUrl;
                };
                
                const bootstrapModal = new window.bootstrap.Modal(modal);
                bootstrapModal.show();
            }
        }

        handleFormSubmit(e) {
            e.preventDefault();
            
            const requiredFields = this.form.querySelectorAll('[required]');
            let isValid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    field.classList.add('is-invalid');
                    isValid = false;
                } else {
                    field.classList.remove('is-invalid');
                }
            });
            
            if (!isValid) {
                this.showError('Please fill in all required fields.');
                return;
            }
            
            const submitButton = document.getElementById('submitBtn');
            const spinner = submitButton ? submitButton.querySelector('.spinner-border') : null;
            
            if (submitButton) {
                submitButton.disabled = true;
                submitButton.classList.add('loading');
            }
            if (spinner) {
                spinner.style.display = 'inline-block';
            }
            
            this.removeDraft();
            this.hasUnsavedChanges = false;
            
            this.form.submit();
        }

        handleFormChange() {
            this.markUnsaved();
        }

        markUnsaved() {
            this.hasUnsavedChanges = true;
            if (this.form) {
                this.form.setAttribute('data-unsaved-changes', 'true');
            }
            
            this.showDraftIndicator();
        }

        markSaved() {
            this.hasUnsavedChanges = false;
            if (this.form) {
                this.form.setAttribute('data-unsaved-changes', 'false');
            }
            this.hideDraftIndicator();
        }

        showDraftIndicator() {
            let indicator = document.querySelector('.draft-indicator');
            if (!indicator && document.body) {
                indicator = document.createElement('div');
                indicator.className = 'draft-indicator';
                indicator.innerHTML = '<i class="fas fa-save me-2"></i>Draft';
                document.body.appendChild(indicator);
            }
        }

        hideDraftIndicator() {
            const indicator = document.querySelector('.draft-indicator');
            if (indicator) {
                indicator.remove();
            }
        }

        saveDraft(content) {
            try {
                const draftData = {
                    content: content,
                    timestamp: Date.now(),
                    wordCount: this.wordCount,
                    pageName: document.getElementById('pageName')?.value || '',
                    paramlink: document.getElementById('paramlink')?.value || ''
                };
                localStorage.setItem(this.pageKey, JSON.stringify(draftData));
            } catch (error) {
            }
        }

        loadDraft() {
            try {
                const draftData = localStorage.getItem(this.pageKey);
                if (draftData && typeof window.$ !== 'undefined') {
                    const parsed = JSON.parse(draftData);
                    if (parsed.content && parsed.content.trim()) {
                        window.$('#editor').summernote('code', parsed.content);
                        this.showInfo(`Draft restored from ${new Date(parsed.timestamp).toLocaleString()}`);
                    }
                }
            } catch (error) {
            }
        }

        removeDraft() {
            try {
                localStorage.removeItem(this.pageKey);
                this.hideDraftIndicator();
            } catch (error) {
            }
        }

        startAutoSave() {
            this.stopAutoSave();
            this.autoSaveTimer = setInterval(() => {
                if (this.hasUnsavedChanges && typeof window.$ !== 'undefined') {
                    const content = window.$('#editor').summernote('code');
                    this.saveDraft(content);
                }
            }, 30000);
        }

        stopAutoSave() {
            if (this.autoSaveTimer) {
                clearInterval(this.autoSaveTimer);
                this.autoSaveTimer = null;
            }
        }

        showLoadingState(selector) {
            const element = document.querySelector(selector);
            if (element) {
                element.classList.add('loading');
            }
        }

        hideLoadingState(selector) {
            const element = document.querySelector(selector);
            if (element) {
                element.classList.remove('loading');
            }
        }

        showError(message) {
            this.showAlert(message, 'danger', 'fas fa-exclamation-triangle');
        }

        showSuccess(message) {
            this.showAlert(message, 'success', 'fas fa-check-circle');
        }

        showInfo(message) {
            this.showAlert(message, 'info', 'fas fa-info-circle');
        }

        showAlert(message, type = 'info', icon = 'fas fa-info-circle') {
            const alertHtml = `
                <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                    <i class="${icon} me-2"></i>${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            `;
            
            const container = document.getElementById('page-editor-container');
            if (container) {
                container.insertAdjacentHTML('afterbegin', alertHtml);
                
                if (type === 'info' || type === 'success') {
                    setTimeout(() => {
                        const alert = container.querySelector('.alert');
                        if (alert && typeof window.bootstrap !== 'undefined') {
                            const bsAlert = new window.bootstrap.Alert(alert);
                            bsAlert.close();
                        }
                    }, 5000);
                }
            }
        }

        getWordCount() {
            return this.wordCount;
        }

        getContent() {
            return typeof window.$ !== 'undefined' ? window.$('#editor').summernote('code') : '';
        }

        setContent(content) {
            if (typeof window.$ !== 'undefined') {
                window.$('#editor').summernote('code', content);
                this.updateWordCount();
            }
        }

        destroy() {
            this.stopAutoSave();
            if (typeof window.$ !== 'undefined' && this.editor) {
                window.$('#editor').summernote('destroy');
            }
            
            if (document.body) {
                document.body.removeAttribute('data-page-editor-initialized');
            }
            this.isInitialized = false;
        }
    }

    if (!window.pageEditor) {
        window.PageEditor = PageEditor;
        window.pageEditor = new PageEditor();
    }

})(window, document);

