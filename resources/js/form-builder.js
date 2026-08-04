const initializeFormBuilder = () => {
    const root = document.querySelector('[data-form-builder]');

    if (!root || root.dataset.initialized === 'true') {
        return;
    }

    root.dataset.initialized = 'true';

    const elements = {
        form: root.querySelector('#form-builder-form'),
        canvas: root.querySelector('#field-canvas'),
        library: root.querySelector('#field-library'),
        fieldSearch: root.querySelector('#field-search'),
        fieldSearchEmpty: root.querySelector('#field-search-empty'),
        fieldLibraryCount: root.querySelector('#field-library-count'),
        schemaInput: root.querySelector('#json-schema'),
        jsonDialog: root.querySelector('#json-dialog'),
        jsonEditor: root.querySelector('#json-editor'),
        jsonFeedback: root.querySelector('#json-feedback'),
        fieldDetails: root.querySelector('#field-details'),
        fieldProperties: root.querySelector('#field-properties'),
        inspectorEmpty: root.querySelector('#inspector-empty'),
        selectedFieldType: root.querySelector('#selected-field-type'),
        fieldCount: root.querySelector('#field-count'),
        submitPreview: root.querySelector('#canvas-submit-preview'),
        formTitle: root.querySelector('#form-title'),
        formDescription: root.querySelector('#form-description'),
        formIsPublic: root.querySelector('#form-is-public'),
        canvasFormTitle: root.querySelector('#canvas-form-title'),
        canvasFormDescription: root.querySelector('#canvas-form-description'),
        undo: root.querySelector('#undo-builder'),
        redo: root.querySelector('#redo-builder'),
        validate: root.querySelector('#validate-canvas'),
        toggleJson: root.querySelector('#toggle-json'),
        closeJson: root.querySelector('#close-json-dialog'),
        cancelJson: root.querySelector('#cancel-json'),
        applyJson: root.querySelector('#apply-json'),
        formatJson: root.querySelector('#format-json'),
        saveButton: root.querySelector('#save-form-button'),
        toast: root.querySelector('#builder-toast'),
        autosaveStatus: root.querySelector('#autosave-status'),
        aiSchemaAlert: root.querySelector('#ai-schema-alert'),
        clearAiSchema: root.querySelector('#clear-ai-schema'),
    };

    const fieldTemplates = {
        text: { type: 'text', key: 'short_text', label: 'Short text', placeholder: 'Type your answer', help_text: '', default: '', required: false, options: [], validations: [] },
        textarea: { type: 'textarea', key: 'long_text', label: 'Long text', placeholder: 'Type your answer', help_text: '', default: '', required: false, options: [], validations: [] },
        number: { type: 'number', key: 'number', label: 'Number', placeholder: '0', help_text: '', default: '', required: false, options: [], validations: ['numeric'] },
        email: { type: 'email', key: 'email', label: 'Email address', placeholder: 'name@example.com', help_text: '', default: '', required: false, options: [], validations: ['email'] },
        phone: { type: 'phone', key: 'phone', label: 'Phone number', placeholder: '+91 98765 43210', help_text: '', default: '', required: false, options: [], validations: [] },
        date: { type: 'date', key: 'date', label: 'Date', placeholder: '', help_text: '', default: '', required: false, options: [], validations: [] },
        dropdown: { type: 'dropdown', key: 'dropdown', label: 'Choose an option', placeholder: 'Select an option', help_text: '', default: '', required: false, options: ['Option 1', 'Option 2'], validations: [] },
        radio: { type: 'radio', key: 'radio_group', label: 'Choose one', placeholder: '', help_text: '', default: '', required: false, options: ['Option 1', 'Option 2'], validations: [] },
        checkbox: { type: 'checkbox', key: 'checkbox_group', label: 'Select all that apply', placeholder: '', help_text: '', default: '', required: false, options: ['Option 1', 'Option 2'], validations: [] },
        file: { type: 'file', key: 'file_upload', label: 'Upload a file', placeholder: '', help_text: 'Attach one file to your response.', default: '', required: false, options: [], validations: ['file'], accepted_file_types: '', max_file_size_mb: 10 },
        section_heading: { type: 'section_heading', key: 'section_heading', label: 'Section heading', placeholder: '', help_text: 'Add context or instructions for the next group of questions.', default: '', required: false, options: [], validations: [] },
        rating: { type: 'rating', key: 'rating', label: 'How would you rate this?', placeholder: '', help_text: '', default: '', required: false, options: ['1', '2', '3', '4', '5'], validations: [] },
    };

    const choiceTypes = new Set(['dropdown', 'radio', 'checkbox', 'rating']);
    const placeholderTypes = new Set(['text', 'textarea', 'number', 'email', 'phone', 'dropdown']);
    const requiredTypes = new Set(['text', 'textarea', 'number', 'email', 'phone', 'date', 'dropdown', 'radio', 'checkbox', 'file', 'rating']);

    let activeSchema = [];
    let selectedFieldIndex = null;
    let history = [];
    let future = [];
    let propertyEditSnapshot = null;
    let autosaveTimer = null;
    let toastTimer = null;
    let pendingDropIndex = null;

    const escapeHtml = (value) => String(value ?? '').replace(/[&<>'"]/g, (character) => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        "'": '&#039;',
        '"': '&quot;',
    }[character]));

    const clone = (value) => JSON.parse(JSON.stringify(value));
    const serialize = () => JSON.stringify(activeSchema);

    const normalizeField = (field, index = 0) => {
        const rawType = typeof field?.type === 'string' ? field.type : 'text';
        const type = fieldTemplates[rawType] ? rawType : 'text';
        const template = clone(fieldTemplates[type]);
        const normalized = { ...template, ...(field && typeof field === 'object' ? field : {}) };

        normalized.type = type;
        normalized.label = String(normalized.label || template.label);
        normalized.key = String(normalized.key || `${type}_${index + 1}`);
        normalized.placeholder = String(normalized.placeholder || '');
        normalized.help_text = String(normalized.help_text || '');
        normalized.default = String(normalized.default ?? '');
        normalized.required = Boolean(normalized.required);
        normalized.options = Array.isArray(normalized.options) ? normalized.options.map((option) => String(option)) : [];
        normalized.validations = Array.isArray(normalized.validations) ? normalized.validations : [];

        return normalized;
    };

    const parseSchema = (raw) => {
        try {
            const parsed = JSON.parse(raw || '[]');
            return Array.isArray(parsed) ? parsed.map(normalizeField) : [];
        } catch (error) {
            console.warn('Unable to parse the form schema.', error);
            return [];
        }
    };

    activeSchema = parseSchema(elements.schemaInput?.value);

    const fieldTypeLabel = (type) => ({
        text: 'Short text',
        textarea: 'Long text',
        number: 'Number',
        email: 'Email',
        phone: 'Phone',
        date: 'Date',
        dropdown: 'Dropdown',
        radio: 'Radio group',
        checkbox: 'Checkboxes',
        file: 'File upload',
        section_heading: 'Section heading',
        rating: 'Rating',
    }[type] || 'Field');

    const fieldIcon = (type) => {
        const icons = {
            text: '<path d="M4 5H16M10 5V15M7 15H13" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>',
            textarea: '<rect x="3" y="4" width="14" height="12" rx="2" stroke="currentColor" stroke-width="1.5"/><path d="M6 8H14M6 11H12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>',
            number: '<path d="M7 3L5 17M15 3L13 17M3 8H17M2 13H16" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>',
            email: '<rect x="3" y="5" width="14" height="10" rx="2" stroke="currentColor" stroke-width="1.5"/><path d="M4 6L10 11L16 6" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>',
            phone: '<path d="M6.5 3.5L8.5 7L6.7 8.4C7.7 10.6 9.4 12.3 11.6 13.3L13 11.5L16.5 13.5L15.5 16.2C15.2 17 14.4 17.4 13.6 17.2C8.1 15.8 4.2 11.9 2.8 6.4C2.6 5.6 3 4.8 3.8 4.5L6.5 3.5Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>',
            date: '<rect x="3" y="4.5" width="14" height="12.5" rx="2" stroke="currentColor" stroke-width="1.5"/><path d="M6 3V6M14 3V6M3 8H17" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>',
            dropdown: '<rect x="3" y="4" width="14" height="12" rx="2" stroke="currentColor" stroke-width="1.5"/><path d="M8 9L10 11L12 9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>',
            radio: '<circle cx="6" cy="6" r="2.5" stroke="currentColor" stroke-width="1.5"/><circle cx="6" cy="14" r="2.5" stroke="currentColor" stroke-width="1.5"/><path d="M11 6H17M11 14H17" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>',
            checkbox: '<rect x="3.5" y="3.5" width="5" height="5" rx="1" stroke="currentColor" stroke-width="1.5"/><rect x="3.5" y="11.5" width="5" height="5" rx="1" stroke="currentColor" stroke-width="1.5"/><path d="M11 6H17M11 14H17" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>',
            file: '<path d="M5 3.5H11L15 7.5V16.5H5V3.5Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/><path d="M11 3.5V7.5H15M10 10V14M8 12H12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>',
            section_heading: '<path d="M4 5H16M4 9H13M4 14H10" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>',
            rating: '<path d="M10 2.8L12.1 7L16.8 7.7L13.4 11L14.2 15.7L10 13.5L5.8 15.7L6.6 11L3.2 7.7L7.9 7L10 2.8Z" stroke="currentColor" stroke-width="1.4" stroke-linejoin="round"/>',
        };

        return `<svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" aria-hidden="true">${icons[type] || icons.text}</svg>`;
    };

    const renderFieldControl = (field) => {
        const placeholder = escapeHtml(field.placeholder || '');
        const options = field.options || [];
        const controlClass = 'mt-2.5 block w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm text-slate-400 shadow-sm';

        switch (field.type) {
            case 'textarea':
                return `<textarea disabled rows="3" class="${controlClass} resize-none" placeholder="${placeholder}"></textarea>`;
            case 'number':
                return `<input disabled type="number" class="${controlClass}" placeholder="${placeholder || '0'}">`;
            case 'email':
                return `<input disabled type="email" class="${controlClass}" placeholder="${placeholder || 'name@example.com'}">`;
            case 'phone':
                return `<input disabled type="tel" class="${controlClass}" placeholder="${placeholder || '+91 98765 43210'}">`;
            case 'date':
                return `<input disabled type="date" class="${controlClass}">`;
            case 'dropdown':
                return `<div class="${controlClass} flex items-center justify-between"><span>${placeholder || escapeHtml(options[0] || 'Select an option')}</span><svg class="h-4 w-4" viewBox="0 0 20 20" fill="none"><path d="M6 8L10 12L14 8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg></div>`;
            case 'radio':
                return `<div class="mt-3 space-y-2.5">${options.map((option) => `<div class="flex items-center gap-2.5 text-sm text-slate-600"><span class="h-4 w-4 rounded-full border border-slate-300 bg-white"></span><span>${escapeHtml(option)}</span></div>`).join('') || '<p class="text-xs text-slate-400">Add options in the properties panel.</p>'}</div>`;
            case 'checkbox':
                return `<div class="mt-3 space-y-2.5">${options.map((option) => `<div class="flex items-center gap-2.5 text-sm text-slate-600"><span class="h-4 w-4 rounded border border-slate-300 bg-white"></span><span>${escapeHtml(option)}</span></div>`).join('') || '<p class="text-xs text-slate-400">Add options in the properties panel.</p>'}</div>`;
            case 'file':
                return '<div class="mt-3 flex items-center justify-center gap-3 rounded-xl border border-dashed border-slate-300 bg-slate-50 px-4 py-6 text-center"><span class="grid h-9 w-9 place-items-center rounded-xl bg-white text-slate-400 shadow-sm"><svg class="h-4 w-4" viewBox="0 0 20 20" fill="none"><path d="M10 13V4M6.5 7.5L10 4L13.5 7.5M4 12V16H16V12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg></span><div class="text-left"><p class="text-xs font-bold text-slate-600">Choose a file or drag it here</p><p class="mt-0.5 text-[11px] text-slate-400">File upload preview</p></div></div>';
            case 'rating':
                return `<div class="mt-3 flex flex-wrap gap-2">${(options.length ? options : ['1', '2', '3', '4', '5']).map((option) => `<span class="grid h-9 min-w-9 place-items-center rounded-xl border border-slate-200 bg-slate-50 px-2 text-xs font-bold text-slate-500">${escapeHtml(option)}</span>`).join('')}</div>`;
            default:
                return `<input disabled type="text" class="${controlClass}" placeholder="${placeholder || 'Type your answer'}">`;
        }
    };

    const fieldCardMarkup = (field, index) => {
        const isSelected = selectedFieldIndex === index;
        const requiredBadge = field.required ? '<span class="rounded-md bg-rose-50 px-1.5 py-0.5 text-[10px] font-bold text-rose-600">Required</span>' : '';
        const isSection = field.type === 'section_heading';

        return `
            <article class="field-card group ${isSelected ? 'is-selected' : ''}" data-field-index="${index}" draggable="true" tabindex="0" aria-label="${escapeHtml(field.label)} field">
                <div class="flex items-start gap-3">
                    <button type="button" class="mt-0.5 grid h-7 w-7 shrink-0 cursor-grab place-items-center rounded-lg text-slate-300 transition hover:bg-slate-100 hover:text-slate-500 active:cursor-grabbing" data-action="drag-handle" aria-label="Drag to reorder" title="Drag to reorder">
                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><circle cx="7" cy="5" r="1.2"/><circle cx="13" cy="5" r="1.2"/><circle cx="7" cy="10" r="1.2"/><circle cx="13" cy="10" r="1.2"/><circle cx="7" cy="15" r="1.2"/><circle cx="13" cy="15" r="1.2"/></svg>
                    </button>
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="grid h-7 w-7 place-items-center rounded-lg bg-slate-100 text-slate-500">${fieldIcon(field.type)}</span>
                            <span class="text-[10px] font-extrabold uppercase tracking-[0.12em] text-slate-400">${escapeHtml(fieldTypeLabel(field.type))}</span>
                            ${requiredBadge}
                        </div>
                        ${isSection ? `
                            <div class="mt-4 border-b border-slate-200 pb-3">
                                <h4 class="text-lg font-extrabold tracking-tight text-slate-900">${escapeHtml(field.label || 'Section heading')}</h4>
                                ${field.help_text ? `<p class="mt-1 text-sm leading-6 text-slate-500">${escapeHtml(field.help_text)}</p>` : ''}
                            </div>
                        ` : `
                            <div class="mt-4">
                                <label class="text-sm font-bold text-slate-800">${escapeHtml(field.label || 'Untitled field')}${field.required ? '<span class="ml-1 text-rose-500">*</span>' : ''}</label>
                                ${renderFieldControl(field)}
                                ${field.help_text ? `<p class="mt-2 text-xs leading-5 text-slate-400">${escapeHtml(field.help_text)}</p>` : ''}
                            </div>
                        `}
                    </div>
                    <div class="flex shrink-0 items-center gap-0.5 rounded-xl border border-slate-200 bg-white p-0.5 opacity-100 shadow-sm sm:opacity-0 sm:transition sm:group-hover:opacity-100 field-actions">
                        <button type="button" class="app-icon-button !h-8 !w-8 !rounded-lg" data-action="move-up" data-index="${index}" aria-label="Move field up" title="Move up" ${index === 0 ? 'disabled' : ''}>
                            <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="none"><path d="M5 12L10 7L15 12" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </button>
                        <button type="button" class="app-icon-button !h-8 !w-8 !rounded-lg" data-action="move-down" data-index="${index}" aria-label="Move field down" title="Move down" ${index === activeSchema.length - 1 ? 'disabled' : ''}>
                            <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="none"><path d="M5 8L10 13L15 8" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </button>
                        <button type="button" class="app-icon-button !h-8 !w-8 !rounded-lg" data-action="duplicate" data-index="${index}" aria-label="Duplicate field" title="Duplicate">
                            <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="none"><rect x="6" y="6" width="9" height="9" rx="1.5" stroke="currentColor" stroke-width="1.5"/><path d="M12 6V5C12 4.4 11.6 4 11 4H5C4.4 4 4 4.4 4 5V11C4 11.6 4.4 12 5 12H6" stroke="currentColor" stroke-width="1.5"/></svg>
                        </button>
                        <button type="button" class="app-icon-button !h-8 !w-8 !rounded-lg hover:!border-rose-200 hover:!bg-rose-50 hover:!text-rose-600" data-action="delete" data-index="${index}" aria-label="Delete field" title="Delete">
                            <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="none"><path d="M5 6H15M8 6V4H12V6M7 8.5V14M10 8.5V14M13 8.5V14M6 6L6.7 16H13.3L14 6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </button>
                    </div>
                </div>
            </article>
        `;
    };

    const updateCanvasHeader = () => {
        const title = elements.formTitle?.value.trim();
        const description = elements.formDescription?.value.trim();

        if (elements.canvasFormTitle) {
            elements.canvasFormTitle.textContent = title || 'Untitled form';
        }

        if (elements.canvasFormDescription) {
            elements.canvasFormDescription.textContent = description || 'Add a short description to help respondents understand this form.';
        }
    };

    const syncSchemaInput = () => {
        if (elements.schemaInput) {
            elements.schemaInput.value = JSON.stringify(activeSchema, null, 2);
        }
    };

    const updateHistoryButtons = () => {
        if (elements.undo) elements.undo.disabled = history.length === 0;
        if (elements.redo) elements.redo.disabled = future.length === 0;
    };

    const renderCanvas = () => {
        if (!elements.canvas) return;

        if (activeSchema.length === 0) {
            elements.canvas.innerHTML = `
                <div class="flex min-h-[310px] flex-col items-center justify-center rounded-2xl border-2 border-dashed border-slate-200 bg-slate-50/70 px-6 text-center transition" data-empty-canvas>
                    <span class="grid h-14 w-14 place-items-center rounded-2xl bg-white text-indigo-500 shadow-sm ring-1 ring-slate-200">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none"><path d="M12 5V19M5 12H19" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/><path d="M5 3H19C20.1 3 21 3.9 21 5V19C21 20.1 20.1 21 19 21H5C3.9 21 3 20.1 3 19V5C3 3.9 3.9 3 5 3Z" stroke="currentColor" stroke-width="1.5" stroke-dasharray="3 3"/></svg>
                    </span>
                    <h4 class="mt-4 text-sm font-extrabold text-slate-800">Start building your form</h4>
                    <p class="mt-1.5 max-w-xs text-xs leading-5 text-slate-500">Drag a field from the left panel or click one to add it here. Your first question is one tiny click away.</p>
                </div>
            `;
        } else {
            elements.canvas.innerHTML = activeSchema.map(fieldCardMarkup).join('');
        }

        if (elements.fieldCount) {
            elements.fieldCount.textContent = `${activeSchema.length} ${activeSchema.length === 1 ? 'field' : 'fields'}`;
        }

        elements.submitPreview?.classList.toggle('hidden', activeSchema.length === 0);
        syncSchemaInput();
        updateCanvasHeader();
        updateHistoryButtons();
    };

    const inspectorInput = ({ label, property, value, type = 'text', placeholder = '', help = '' }) => `
        <div>
            <label class="text-xs font-bold text-slate-600" for="field-${property}">${escapeHtml(label)}</label>
            ${type === 'textarea'
                ? `<textarea id="field-${property}" data-property="${property}" rows="${property === 'options' ? '5' : '3'}" class="app-input mt-1.5 resize-y" placeholder="${escapeHtml(placeholder)}">${escapeHtml(value)}</textarea>`
                : `<input id="field-${property}" data-property="${property}" type="${type}" value="${escapeHtml(value)}" class="app-input mt-1.5" placeholder="${escapeHtml(placeholder)}">`
            }
            ${help ? `<p class="mt-1.5 text-[11px] leading-4 text-slate-400">${escapeHtml(help)}</p>` : ''}
        </div>
    `;

    const renderInspector = () => {
        const field = selectedFieldIndex === null ? null : activeSchema[selectedFieldIndex];

        if (!field) {
            elements.inspectorEmpty?.classList.remove('hidden');
            elements.fieldProperties?.classList.add('hidden');
            elements.selectedFieldType?.classList.add('hidden');
            if (elements.fieldProperties) elements.fieldProperties.innerHTML = '';
            return;
        }

        elements.inspectorEmpty?.classList.add('hidden');
        elements.fieldProperties?.classList.remove('hidden');
        elements.selectedFieldType?.classList.remove('hidden');
        if (elements.selectedFieldType) elements.selectedFieldType.textContent = fieldTypeLabel(field.type);

        const isSection = field.type === 'section_heading';
        const options = (field.options || []).join('\n');

        elements.fieldProperties.innerHTML = `
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-3.5">
                <div class="flex items-center gap-3">
                    <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-white text-indigo-600 shadow-sm ring-1 ring-slate-200">${fieldIcon(field.type)}</span>
                    <div class="min-w-0">
                        <p class="truncate text-sm font-extrabold text-slate-800">${escapeHtml(field.label || 'Untitled field')}</p>
                        <p class="mt-0.5 text-[11px] text-slate-400">Field ${selectedFieldIndex + 1} of ${activeSchema.length}</p>
                    </div>
                </div>
            </div>

            ${inspectorInput({ label: isSection ? 'Heading text' : 'Question label', property: 'label', value: field.label, placeholder: 'Enter a clear label' })}
            ${inspectorInput({ label: 'Field key', property: 'key', value: field.key, placeholder: 'unique_field_key', help: 'Used in submissions and exports. Keep it unique and use letters, numbers, or underscores.' })}
            ${placeholderTypes.has(field.type) ? inspectorInput({ label: 'Placeholder', property: 'placeholder', value: field.placeholder, placeholder: 'Example response' }) : ''}
            ${inspectorInput({ label: isSection ? 'Supporting text' : 'Help text', property: 'help_text', value: field.help_text, type: 'textarea', placeholder: 'Optional guidance for respondents' })}
            ${choiceTypes.has(field.type) ? inspectorInput({ label: 'Options', property: 'options', value: options, type: 'textarea', placeholder: 'One option per line', help: 'Enter one option on each line.' }) : ''}
            ${field.type === 'file' ? inspectorInput({ label: 'Accepted file types', property: 'accepted_file_types', value: field.accepted_file_types || '', placeholder: '.pdf,.docx,image/*', help: 'Optional browser filter. Separate values with commas.' }) : ''}
            ${field.type === 'file' ? inspectorInput({ label: 'Maximum size (MB)', property: 'max_file_size_mb', value: field.max_file_size_mb || 10, type: 'number', placeholder: '10', help: 'Allowed range: 1–50 MB.' }) : ''}
            ${!isSection && !choiceTypes.has(field.type) && field.type !== 'file' ? inspectorInput({ label: 'Default value', property: 'default', value: field.default, placeholder: 'Optional default response' }) : ''}

            ${requiredTypes.has(field.type) ? `
                <label class="flex cursor-pointer items-center justify-between rounded-2xl border border-slate-200 bg-white px-3.5 py-3">
                    <span>
                        <span class="block text-xs font-extrabold text-slate-700">Required field</span>
                        <span class="mt-0.5 block text-[11px] text-slate-400">Respondents must answer this question.</span>
                    </span>
                    <span class="relative inline-flex h-5 w-9 shrink-0 items-center">
                        <input type="checkbox" data-property="required" class="peer sr-only" ${field.required ? 'checked' : ''}>
                        <span class="absolute inset-0 rounded-full bg-slate-300 transition peer-checked:bg-indigo-600 peer-focus:ring-4 peer-focus:ring-indigo-500/15"></span>
                        <span class="absolute left-0.5 h-4 w-4 rounded-full bg-white shadow-sm transition peer-checked:translate-x-4"></span>
                    </span>
                </label>
            ` : ''}

            <div class="grid grid-cols-2 gap-2 border-t border-slate-100 pt-5">
                <button type="button" data-inspector-action="duplicate" class="app-button-secondary !py-2.5">
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none"><rect x="6" y="6" width="9" height="9" rx="1.5" stroke="currentColor" stroke-width="1.5"/><path d="M12 6V5C12 4.4 11.6 4 11 4H5C4.4 4 4 4.4 4 5V11C4 11.6 4.4 12 5 12H6" stroke="currentColor" stroke-width="1.5"/></svg>
                    Duplicate
                </button>
                <button type="button" data-inspector-action="delete" class="app-button-secondary !border-rose-200 !py-2.5 !text-rose-600 hover:!bg-rose-50">
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none"><path d="M5 6H15M8 6V4H12V6M7 8.5V14M10 8.5V14M13 8.5V14M6 6L6.7 16H13.3L14 6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    Delete
                </button>
            </div>
        `;

        bindInspectorEvents();
    };

    const renderAll = () => {
        renderCanvas();
        renderInspector();
        scheduleAutosave();
    };

    const pushHistory = (snapshot) => {
        if (!snapshot || snapshot === serialize()) return;
        history.push(snapshot);
        if (history.length > 40) history.shift();
        future = [];
        updateHistoryButtons();
    };

    const commitMutation = (mutator, { select = selectedFieldIndex, message = null } = {}) => {
        const before = serialize();
        mutator();
        activeSchema = activeSchema.map(normalizeField);
        selectedFieldIndex = select === null ? null : Math.min(Math.max(select, 0), Math.max(activeSchema.length - 1, 0));
        if (activeSchema.length === 0) selectedFieldIndex = null;
        pushHistory(before);
        renderAll();
        if (message) showToast(message);
    };

    const uniqueKey = (baseKey, ignoreIndex = null) => {
        const cleanBase = String(baseKey || 'field')
            .toLowerCase()
            .trim()
            .replace(/[^a-z0-9]+/g, '_')
            .replace(/^_+|_+$/g, '') || 'field';
        const usedKeys = new Set(activeSchema.filter((_, index) => index !== ignoreIndex).map((field) => field.key));

        if (!usedKeys.has(cleanBase)) return cleanBase;

        let suffix = 2;
        while (usedKeys.has(`${cleanBase}_${suffix}`)) suffix += 1;
        return `${cleanBase}_${suffix}`;
    };

    const addField = (type, insertionIndex = activeSchema.length) => {
        if (!fieldTemplates[type]) return;

        const field = clone(fieldTemplates[type]);
        field.key = uniqueKey(field.key);
        const index = Math.max(0, Math.min(insertionIndex, activeSchema.length));

        commitMutation(() => {
            activeSchema.splice(index, 0, field);
        }, { select: index, message: `${fieldTypeLabel(type)} added` });

        requestAnimationFrame(() => {
            elements.canvas?.querySelector(`[data-field-index="${index}"]`)?.scrollIntoView({ behavior: 'smooth', block: 'center' });
        });
    };

    const duplicateField = (index) => {
        const source = activeSchema[index];
        if (!source) return;

        const duplicate = clone(source);
        duplicate.label = `${source.label} copy`;
        duplicate.key = uniqueKey(source.key);

        commitMutation(() => {
            activeSchema.splice(index + 1, 0, duplicate);
        }, { select: index + 1, message: 'Field duplicated' });
    };

    const deleteField = (index) => {
        if (!activeSchema[index]) return;
        const label = activeSchema[index].label || 'Field';
        const nextSelection = activeSchema.length <= 1 ? null : Math.min(index, activeSchema.length - 2);

        commitMutation(() => {
            activeSchema.splice(index, 1);
        }, { select: nextSelection, message: `${label} deleted — use Undo to restore` });
    };

    const moveField = (from, to) => {
        if (from === to || from < 0 || to < 0 || from >= activeSchema.length || to >= activeSchema.length) return;

        commitMutation(() => {
            const [field] = activeSchema.splice(from, 1);
            activeSchema.splice(to, 0, field);
        }, { select: to });
    };

    function bindInspectorEvents() {
        if (!elements.fieldProperties) return;

        elements.fieldProperties.querySelectorAll('[data-property]').forEach((input) => {
            const property = input.dataset.property;

            if (property === 'required') {
                input.addEventListener('change', () => {
                    const index = selectedFieldIndex;
                    if (index === null || !activeSchema[index]) return;
                    commitMutation(() => {
                        activeSchema[index].required = input.checked;
                    }, { select: index });
                });
                return;
            }

            input.addEventListener('focus', () => {
                propertyEditSnapshot = serialize();
            });

            input.addEventListener('input', () => {
                const index = selectedFieldIndex;
                if (index === null || !activeSchema[index]) return;

                if (property === 'options') {
                    activeSchema[index].options = input.value.split('\n').map((option) => option.trim()).filter(Boolean);
                } else if (property === 'key') {
                    activeSchema[index].key = input.value.toLowerCase().replace(/[^a-z0-9_]/g, '_');
                } else {
                    activeSchema[index][property] = input.value;
                }

                renderCanvas();
                scheduleAutosave();
            });

            input.addEventListener('blur', () => {
                if (propertyEditSnapshot && propertyEditSnapshot !== serialize()) {
                    pushHistory(propertyEditSnapshot);
                }
                propertyEditSnapshot = null;
                renderInspector();
            });
        });

        elements.fieldProperties.querySelector('[data-inspector-action="duplicate"]')?.addEventListener('click', () => {
            if (selectedFieldIndex !== null) duplicateField(selectedFieldIndex);
        });

        elements.fieldProperties.querySelector('[data-inspector-action="delete"]')?.addEventListener('click', () => {
            if (selectedFieldIndex !== null) deleteField(selectedFieldIndex);
        });
    }

    const showToast = (message, tone = 'default') => {
        if (!elements.toast) return;
        clearTimeout(toastTimer);
        elements.toast.textContent = message;
        elements.toast.classList.remove('hidden', 'bg-slate-950', 'bg-emerald-700', 'bg-rose-700', 'toast-enter');
        elements.toast.classList.add(tone === 'success' ? 'bg-emerald-700' : tone === 'error' ? 'bg-rose-700' : 'bg-slate-950', 'toast-enter');
        toastTimer = window.setTimeout(() => elements.toast.classList.add('hidden'), 3000);
    };

    const updateAutosaveStatus = (text) => {
        if (!elements.autosaveStatus) return;
        const label = elements.autosaveStatus.lastChild;
        if (label) label.textContent = ` ${text}`;
    };

    const saveDraft = () => {
        try {
            const payload = {
                schema: activeSchema,
                title: elements.formTitle?.value || '',
                description: elements.formDescription?.value || '',
                isPublic: Boolean(elements.formIsPublic?.checked),
                savedAt: Date.now(),
            };
            localStorage.setItem(root.dataset.draftKey, JSON.stringify(payload));
            const time = new Intl.DateTimeFormat([], { hour: 'numeric', minute: '2-digit' }).format(new Date(payload.savedAt));
            updateAutosaveStatus(`Saved locally at ${time}`);
        } catch (error) {
            console.warn('Unable to save local form draft.', error);
            updateAutosaveStatus('Local save unavailable');
        }
    };

    const scheduleAutosave = () => {
        clearTimeout(autosaveTimer);
        updateAutosaveStatus('Saving…');
        autosaveTimer = window.setTimeout(saveDraft, 450);
    };

    const restoreDraft = () => {
        try {
            const raw = localStorage.getItem(root.dataset.draftKey);
            if (!raw) return false;

            const draft = JSON.parse(raw);
            const serverUpdatedAt = Number(root.dataset.serverUpdatedAt || 0);
            if (!draft?.savedAt || draft.savedAt <= serverUpdatedAt || !Array.isArray(draft.schema)) return false;

            activeSchema = draft.schema.map(normalizeField);
            if (elements.formTitle && typeof draft.title === 'string') elements.formTitle.value = draft.title;
            if (elements.formDescription && typeof draft.description === 'string') elements.formDescription.value = draft.description;
            if (elements.formIsPublic) elements.formIsPublic.checked = Boolean(draft.isPublic);
            updateAutosaveStatus('Recovered local draft');
            showToast('Recovered your unsaved local draft');
            return true;
        } catch (error) {
            console.warn('Unable to restore local form draft.', error);
            return false;
        }
    };

    const loadAiSchema = () => {
        if (root.dataset.isEditing === 'true') return false;
        const stored = localStorage.getItem('ai_generated_schema');
        if (!stored) return false;

        try {
            const parsed = JSON.parse(stored);
            if (!Array.isArray(parsed)) return false;
            activeSchema = parsed.map(normalizeField);
            elements.aiSchemaAlert?.classList.remove('hidden');
            selectedFieldIndex = activeSchema.length ? 0 : null;
            return true;
        } catch (error) {
            console.warn('Invalid AI schema in local storage.', error);
            localStorage.removeItem('ai_generated_schema');
            return false;
        }
    };

    const localValidation = () => {
        const errors = [];
        const keys = new Map();

        if (activeSchema.length === 0) {
            errors.push('Add at least one field before saving.');
        }

        activeSchema.forEach((field, index) => {
            const position = index + 1;
            if (!field.label.trim()) errors.push(`Field ${position} needs a label.`);
            if (!field.key.trim()) errors.push(`Field ${position} needs a key.`);
            if (!/^[a-z0-9_]+$/.test(field.key)) errors.push(`Field ${position} key can only use lowercase letters, numbers, and underscores.`);
            if (keys.has(field.key)) errors.push(`The key “${field.key}” is used more than once.`);
            keys.set(field.key, true);
            if (choiceTypes.has(field.type) && (field.options || []).length === 0) errors.push(`${field.label || `Field ${position}`} needs at least one option.`);
        });

        return errors;
    };

    const setJsonFeedback = (message, tone = 'default') => {
        if (!elements.jsonFeedback) return;
        const dot = tone === 'success' ? 'bg-emerald-500' : tone === 'error' ? 'bg-rose-500' : 'bg-slate-300';
        const text = tone === 'success' ? 'text-emerald-700' : tone === 'error' ? 'text-rose-700' : 'text-slate-500';
        elements.jsonFeedback.className = `mt-3 flex items-center gap-2 text-xs font-medium ${text}`;
        elements.jsonFeedback.innerHTML = `<span class="h-2 w-2 rounded-full ${dot}"></span>${escapeHtml(message)}`;
    };

    const validateSchema = async () => {
        const errors = localValidation();
        if (errors.length) {
            showToast(errors[0], 'error');
            setJsonFeedback(errors.join(' '), 'error');
            return false;
        }

        elements.validate.disabled = true;
        const original = elements.validate.innerHTML;
        elements.validate.innerHTML = '<span class="h-4 w-4 animate-spin rounded-full border-2 border-slate-300 border-t-indigo-600"></span><span class="hidden sm:inline">Checking</span>';

        try {
            const response = await fetch(root.dataset.validateUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                },
                body: JSON.stringify({ schema: activeSchema }),
            });

            if (!response.ok) throw new Error(`Validation request failed with status ${response.status}.`);
            const result = await response.json();

            if (result.valid) {
                showToast('Schema is valid and ready to save', 'success');
                setJsonFeedback('Schema is valid and ready to save.', 'success');
                return true;
            }

            const message = Array.isArray(result.errors) ? result.errors.join(' ') : 'The schema is invalid.';
            showToast(message, 'error');
            setJsonFeedback(message, 'error');
            return false;
        } catch (error) {
            console.error(error);
            showToast('Could not reach the validation service', 'error');
            setJsonFeedback('Could not validate the schema. Check your connection and try again.', 'error');
            return false;
        } finally {
            elements.validate.disabled = false;
            elements.validate.innerHTML = original;
        }
    };

    const clearDropIndicators = () => {
        elements.canvas?.querySelectorAll('.drop-before, .drop-after').forEach((card) => card.classList.remove('drop-before', 'drop-after'));
        elements.canvas?.querySelector('[data-empty-canvas]')?.classList.remove('border-indigo-400', 'bg-indigo-50/60');
        pendingDropIndex = null;
    };

    const calculateDropIndex = (clientY) => {
        const cards = [...elements.canvas.querySelectorAll('[data-field-index]')];
        clearDropIndicators();

        if (cards.length === 0) {
            const empty = elements.canvas.querySelector('[data-empty-canvas]');
            empty?.classList.add('border-indigo-400', 'bg-indigo-50/60');
            pendingDropIndex = 0;
            return 0;
        }

        for (const card of cards) {
            const rect = card.getBoundingClientRect();
            if (clientY < rect.top + rect.height / 2) {
                card.classList.add('drop-before');
                pendingDropIndex = Number(card.dataset.fieldIndex);
                return pendingDropIndex;
            }
        }

        cards[cards.length - 1].classList.add('drop-after');
        pendingDropIndex = activeSchema.length;
        return pendingDropIndex;
    };

    elements.library?.querySelectorAll('[data-field-type]').forEach((button) => {
        button.addEventListener('click', () => addField(button.dataset.fieldType));
        button.addEventListener('dragstart', (event) => {
            event.dataTransfer.effectAllowed = 'copy';
            event.dataTransfer.setData('application/x-form-field', button.dataset.fieldType);
            event.dataTransfer.setData('text/plain', button.dataset.fieldType);
        });
    });

    elements.canvas?.addEventListener('dragstart', (event) => {
        const card = event.target.closest('[data-field-index]');
        if (!card) return;
        const index = Number(card.dataset.fieldIndex);
        event.dataTransfer.effectAllowed = 'move';
        event.dataTransfer.setData('application/x-form-index', String(index));
        event.dataTransfer.setData('text/plain', String(index));
        window.setTimeout(() => card.classList.add('is-dragging'), 0);
    });

    elements.canvas?.addEventListener('dragend', () => {
        elements.canvas.querySelectorAll('.is-dragging').forEach((card) => card.classList.remove('is-dragging'));
        clearDropIndicators();
    });

    elements.canvas?.addEventListener('dragover', (event) => {
        event.preventDefault();
        event.dataTransfer.dropEffect = Array.from(event.dataTransfer.types).includes('application/x-form-field') ? 'copy' : 'move';
        calculateDropIndex(event.clientY);
    });

    elements.canvas?.addEventListener('dragleave', (event) => {
        if (!elements.canvas.contains(event.relatedTarget)) clearDropIndicators();
    });

    elements.canvas?.addEventListener('drop', (event) => {
        event.preventDefault();
        const insertionIndex = pendingDropIndex ?? activeSchema.length;
        const libraryType = event.dataTransfer.getData('application/x-form-field');
        const sourceValue = event.dataTransfer.getData('application/x-form-index');
        clearDropIndicators();

        if (libraryType && fieldTemplates[libraryType]) {
            addField(libraryType, insertionIndex);
            return;
        }

        if (sourceValue === '') return;
        const sourceIndex = Number(sourceValue);
        if (!Number.isInteger(sourceIndex) || !activeSchema[sourceIndex]) return;

        let destination = insertionIndex;
        if (sourceIndex < destination) destination -= 1;
        if (destination === sourceIndex) return;

        commitMutation(() => {
            const [field] = activeSchema.splice(sourceIndex, 1);
            activeSchema.splice(destination, 0, field);
        }, { select: destination, message: 'Field reordered' });
    });

    elements.canvas?.addEventListener('click', (event) => {
        const card = event.target.closest('[data-field-index]');
        if (!card) return;
        const index = Number(card.dataset.fieldIndex);
        const action = event.target.closest('[data-action]')?.dataset.action;

        if (action === 'delete') return deleteField(index);
        if (action === 'duplicate') return duplicateField(index);
        if (action === 'move-up') return moveField(index, index - 1);
        if (action === 'move-down') return moveField(index, index + 1);
        if (action === 'drag-handle') return;

        selectedFieldIndex = index;
        renderCanvas();
        renderInspector();
    });

    elements.canvas?.addEventListener('keydown', (event) => {
        const card = event.target.closest('[data-field-index]');
        if (!card || !['Enter', ' '].includes(event.key)) return;
        event.preventDefault();
        selectedFieldIndex = Number(card.dataset.fieldIndex);
        renderCanvas();
        renderInspector();
    });

    elements.fieldSearch?.addEventListener('input', () => {
        const query = elements.fieldSearch.value.trim().toLowerCase();
        let visibleCount = 0;

        elements.library.querySelectorAll('[data-field-type]').forEach((button) => {
            const isVisible = !query || button.dataset.fieldLabel.includes(query);
            button.hidden = !isVisible;
            if (isVisible) visibleCount += 1;
        });

        elements.library.querySelectorAll('[data-field-group]').forEach((group) => {
            group.hidden = !group.querySelector('[data-field-type]:not([hidden])');
        });

        elements.fieldSearchEmpty?.classList.toggle('hidden', visibleCount !== 0);
        if (elements.fieldLibraryCount) elements.fieldLibraryCount.textContent = visibleCount;
    });

    elements.formTitle?.addEventListener('input', () => {
        updateCanvasHeader();
        scheduleAutosave();
    });
    elements.formDescription?.addEventListener('input', () => {
        updateCanvasHeader();
        scheduleAutosave();
    });
    elements.formIsPublic?.addEventListener('change', scheduleAutosave);

    elements.undo?.addEventListener('click', () => {
        if (!history.length) return;
        future.push(serialize());
        activeSchema = parseSchema(history.pop());
        selectedFieldIndex = null;
        renderAll();
        showToast('Undid last change');
    });

    elements.redo?.addEventListener('click', () => {
        if (!future.length) return;
        history.push(serialize());
        activeSchema = parseSchema(future.pop());
        selectedFieldIndex = null;
        renderAll();
        showToast('Redid last change');
    });

    elements.validate?.addEventListener('click', validateSchema);

    const openJsonDialog = () => {
        elements.jsonEditor.value = JSON.stringify(activeSchema, null, 2);
        setJsonFeedback('Edit the JSON directly, then apply it to the canvas.');
        if (typeof elements.jsonDialog.showModal === 'function') {
            elements.jsonDialog.showModal();
        } else {
            elements.jsonDialog.setAttribute('open', '');
        }
        window.setTimeout(() => elements.jsonEditor.focus(), 50);
    };

    const closeJsonDialog = () => {
        if (typeof elements.jsonDialog.close === 'function') elements.jsonDialog.close();
        else elements.jsonDialog.removeAttribute('open');
    };

    elements.toggleJson?.addEventListener('click', openJsonDialog);
    elements.closeJson?.addEventListener('click', closeJsonDialog);
    elements.cancelJson?.addEventListener('click', closeJsonDialog);
    elements.jsonDialog?.addEventListener('click', (event) => {
        if (event.target === elements.jsonDialog) closeJsonDialog();
    });

    elements.formatJson?.addEventListener('click', () => {
        try {
            const parsed = JSON.parse(elements.jsonEditor.value);
            elements.jsonEditor.value = JSON.stringify(parsed, null, 2);
            setJsonFeedback('JSON formatted successfully.', 'success');
        } catch (error) {
            setJsonFeedback('Fix the JSON syntax before formatting.', 'error');
        }
    });

    elements.applyJson?.addEventListener('click', () => {
        try {
            const parsed = JSON.parse(elements.jsonEditor.value);
            if (!Array.isArray(parsed)) throw new Error('Schema must be an array.');
            const before = serialize();
            activeSchema = parsed.map(normalizeField);
            selectedFieldIndex = activeSchema.length ? 0 : null;
            pushHistory(before);
            renderAll();
            closeJsonDialog();
            showToast('JSON changes applied', 'success');
        } catch (error) {
            setJsonFeedback(error.message || 'The JSON schema is invalid.', 'error');
        }
    });

    elements.clearAiSchema?.addEventListener('click', () => {
        localStorage.removeItem('ai_generated_schema');
        elements.aiSchemaAlert?.classList.add('hidden');
        commitMutation(() => {
            activeSchema = [];
        }, { select: null, message: 'AI-generated fields cleared' });
    });

    root.querySelectorAll('[data-copy-url]').forEach((button) => {
        button.addEventListener('click', async () => {
            try {
                await navigator.clipboard.writeText(button.dataset.copyUrl);
                showToast('Public URL copied', 'success');
            } catch (error) {
                showToast('Could not copy the public URL', 'error');
            }
        });
    });

    elements.form?.addEventListener('submit', (event) => {
        const errors = localValidation();
        if (errors.length) {
            event.preventDefault();
            showToast(errors[0], 'error');
            return;
        }

        syncSchemaInput();
        elements.saveButton.disabled = true;
        elements.saveButton.innerHTML = '<span class="h-4 w-4 animate-spin rounded-full border-2 border-white/40 border-t-white"></span>Saving…';
        saveDraft();
        localStorage.removeItem('ai_generated_schema');
        if (root.dataset.isEditing !== 'true') {
            localStorage.removeItem(root.dataset.draftKey);
        }
    });

    document.addEventListener('keydown', (event) => {
        const modifier = event.ctrlKey || event.metaKey;
        const targetIsEditable = ['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement?.tagName) || document.activeElement?.isContentEditable;

        if (modifier && event.key.toLowerCase() === 's') {
            event.preventDefault();
            elements.form?.requestSubmit();
            return;
        }

        if (modifier && event.key.toLowerCase() === 'z' && !targetIsEditable) {
            event.preventDefault();
            if (event.shiftKey) elements.redo?.click();
            else elements.undo?.click();
        }
    });

    const aiLoaded = loadAiSchema();
    if (!aiLoaded) restoreDraft();
    renderAll();

    if (activeSchema.length && selectedFieldIndex === null && aiLoaded) {
        selectedFieldIndex = 0;
        renderAll();
    }
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeFormBuilder, { once: true });
} else {
    initializeFormBuilder();
}
