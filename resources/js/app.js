import Alpine from 'alpinejs';
import './form-builder';
import './respondent-form';

window.Alpine = Alpine;
Alpine.start();

const copyWithFallback = (text) => {
    const textArea = document.createElement('textarea');
    textArea.value = text;
    textArea.setAttribute('readonly', '');
    textArea.style.position = 'fixed';
    textArea.style.opacity = '0';
    textArea.style.pointerEvents = 'none';
    document.body.appendChild(textArea);
    textArea.select();

    const copied = document.execCommand('copy');
    textArea.remove();

    if (!copied) {
        throw new Error('Copy command was not available.');
    }
};

const copyText = async (text) => {
    if (navigator.clipboard && window.isSecureContext) {
        await navigator.clipboard.writeText(text);
        return;
    }

    copyWithFallback(text);
};

document.addEventListener('click', async (event) => {
    const button = event.target.closest('[data-copy-url]');

    if (!button) return;

    const url = button.dataset.copyUrl;
    const label = button.querySelector('[data-copy-label]');
    const control = button.closest('[data-share-control]');
    const feedback = control?.querySelector('[data-copy-feedback]');
    const originalLabel = button.dataset.originalCopyLabel || label?.textContent || 'Copy';

    if (!url || button.disabled) return;

    button.dataset.originalCopyLabel = originalLabel;
    button.disabled = true;

    try {
        await copyText(url);

        if (label) label.textContent = 'Copied';
        feedback?.classList.remove('hidden');
        button.dataset.copyState = 'success';

        window.setTimeout(() => {
            if (label) label.textContent = originalLabel;
            feedback?.classList.add('hidden');
            delete button.dataset.copyState;
            button.disabled = false;
        }, 2200);
    } catch (error) {
        if (label) label.textContent = 'Copy failed';
        button.dataset.copyState = 'error';
        if (feedback) {
            feedback.textContent = 'Could not copy automatically. Select the URL and copy it manually.';
            feedback.classList.remove('hidden', 'text-emerald-600');
            feedback.classList.add('text-rose-600');
        }

        window.setTimeout(() => {
            if (label) label.textContent = originalLabel;
            delete button.dataset.copyState;
            if (feedback) {
                feedback.textContent = 'URL copied to clipboard.';
                feedback.classList.add('hidden', 'text-emerald-600');
                feedback.classList.remove('text-rose-600');
            }
            button.disabled = false;
        }, 3200);

        console.error(error);
    }
});
