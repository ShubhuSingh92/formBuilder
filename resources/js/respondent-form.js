const formatBytes = (bytes) => {
    if (!Number.isFinite(bytes) || bytes <= 0) return '0 KB';
    if (bytes >= 1024 * 1024) return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
    return `${Math.max(1, Math.round(bytes / 1024))} KB`;
};

const initializeRespondentForms = () => {
    document.querySelectorAll('[data-respondent-form]').forEach((form) => {
        if (form.dataset.initialized === 'true') return;
        form.dataset.initialized = 'true';

        const fields = [...form.querySelectorAll('[data-response-field]')];
        const answeredElement = form.querySelector('[data-progress-answered]');
        const percentElement = form.querySelector('[data-progress-percent]');
        const progressBar = form.querySelector('[data-progress-bar]');
        const submitButton = form.querySelector('[data-submit-button]');
        const submitLabel = form.querySelector('[data-submit-label]');
        const submitSpinner = form.querySelector('[data-submit-spinner]');

        const isFieldAnswered = (field) => {
            const type = field.dataset.fieldType;
            const controls = [...field.querySelectorAll('input, textarea, select')];

            if (type === 'checkbox') {
                return controls.some((control) => control.checked);
            }

            if (type === 'radio' || type === 'rating') {
                return controls.some((control) => control.checked);
            }

            if (['file', 'upload', 'file_upload'].includes(type)) {
                return controls.some((control) => control.files?.length > 0);
            }

            return controls.some((control) => String(control.value ?? '').trim() !== '');
        };

        const updateProgress = () => {
            const answered = fields.filter(isFieldAnswered).length;
            const percentage = fields.length ? Math.round((answered / fields.length) * 100) : 100;

            if (answeredElement) answeredElement.textContent = String(answered);
            if (percentElement) percentElement.textContent = `${percentage}%`;
            if (progressBar) progressBar.style.width = `${percentage}%`;
        };

        form.addEventListener('input', updateProgress);
        form.addEventListener('change', updateProgress);

        form.querySelectorAll('[data-file-drop]').forEach((dropZone) => {
            const input = dropZone.querySelector('[data-file-input]');
            const fileName = dropZone.querySelector('[data-file-name]');
            const fileHelp = dropZone.querySelector('[data-file-help]');
            const defaultName = fileName?.innerHTML;
            const defaultHelp = fileHelp?.textContent;

            const showSelectedFile = () => {
                const file = input?.files?.[0];

                if (!file) {
                    if (fileName && defaultName) fileName.innerHTML = defaultName;
                    if (fileHelp && defaultHelp) fileHelp.textContent = defaultHelp;
                    dropZone.classList.remove('has-file');
                    updateProgress();
                    return;
                }

                if (fileName) fileName.textContent = file.name;
                if (fileHelp) fileHelp.textContent = `${formatBytes(file.size)} · Click or drop another file to replace it`;
                dropZone.classList.add('has-file');
                updateProgress();
            };

            input?.addEventListener('change', showSelectedFile);

            ['dragenter', 'dragover'].forEach((eventName) => {
                dropZone.addEventListener(eventName, () => dropZone.classList.add('is-dragging'));
            });

            ['dragleave', 'drop'].forEach((eventName) => {
                dropZone.addEventListener(eventName, () => dropZone.classList.remove('is-dragging'));
            });
        });

        form.addEventListener('submit', () => {
            if (!submitButton || !form.checkValidity()) return;

            submitButton.disabled = true;
            if (submitLabel) submitLabel.textContent = 'Submitting…';
            submitButton.querySelectorAll('svg:not([data-submit-spinner])').forEach((icon) => icon.classList.add('hidden'));
            submitSpinner?.classList.remove('hidden');
        });

        updateProgress();
    });
};

document.addEventListener('DOMContentLoaded', initializeRespondentForms);
document.addEventListener('livewire:navigated', initializeRespondentForms);

initializeRespondentForms();
