(function () {
    const bodyField = document.getElementById('template-body');
    const previewBody = document.getElementById('template-preview-body');
    const previewVariables = document.getElementById('template-preview-variables');
    const tokenButtons = document.querySelectorAll('[data-insert-token]');
    const previewButton = document.querySelector('[data-template-preview-url]');
    const variablesInput = document.querySelector('[data-template-variables-input]');
    const subjectField = document.getElementById('template-subject');

    const extractVariables = (text) => {
        const regex = /\{\{\s*([a-zA-Z0-9_\.]+)\s*\}\}/g;
        const found = new Set();
        let match;

        while ((match = regex.exec(text)) !== null) {
            found.add(match[1]);
        }

        return Array.from(found);
    };

    const syncDetectedVariables = () => {
        if (!bodyField || !previewVariables) return;
        const variables = extractVariables((subjectField?.value || '') + ' ' + bodyField.value);
        previewVariables.textContent = variables.length ? variables.join(', ') : '—';
        if (variablesInput) {
            variablesInput.value = variables.join(', ');
        }
    };

    const insertAtCursor = (field, text) => {
        const start = field.selectionStart ?? field.value.length;
        const end = field.selectionEnd ?? field.value.length;
        field.value = field.value.substring(0, start) + text + field.value.substring(end);
        field.focus();
        field.selectionStart = field.selectionEnd = start + text.length;
        field.dispatchEvent(new Event('input'));
    };

    if (bodyField && previewBody) {
        bodyField.addEventListener('input', function () {
            previewBody.textContent = this.value.trim() || 'Preview message will appear here.';
            syncDetectedVariables();
        });
        syncDetectedVariables();
    }

    tokenButtons.forEach((button) => {
        button.addEventListener('click', function () {
            if (!bodyField) return;
            insertAtCursor(bodyField, ' ' + this.dataset.insertToken + ' ');
        });
    });

    if (previewButton && bodyField) {
        previewButton.addEventListener('click', async function () {
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const url = this.dataset.templatePreviewUrl;

            const variables = (variablesInput?.value || '')
                .split(',')
                .map((item) => item.trim())
                .filter(Boolean);

            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        subject: subjectField?.value || '',
                        body: bodyField.value,
                        variables: variables
                    })
                });

                const data = await response.json();

                if (data.rendered_body && previewBody) {
                    previewBody.textContent = data.rendered_body;
                }

                if (previewVariables) {
                    previewVariables.textContent = data.variables?.length ? data.variables.join(', ') : '—';
                }
            } catch (error) {
                console.error('Template preview failed', error);
            }
        });
    }
})();
