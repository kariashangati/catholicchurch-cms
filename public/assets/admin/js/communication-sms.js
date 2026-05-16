document.addEventListener('DOMContentLoaded', function () {
    const config = window.communicationSmsConfig || {};
    const form = document.getElementById('communicationSmsForm');
    const audienceType = document.getElementById('audienceType');
    const sendMode = document.getElementById('sendMode');
    const scheduledAtWrap = document.getElementById('scheduledAtWrap');
    const previewBtn = document.getElementById('previewBtn');
    const fields = document.querySelectorAll('.cc-target-field');

    function toggleAudienceFields() {
        const value = audienceType ? audienceType.value : '';
        fields.forEach((field) => {
            const audience = field.getAttribute('data-audience');
            field.classList.toggle('is-visible', audience === value);
        });
    }

    function toggleScheduleFields() {
        if (!sendMode || !scheduledAtWrap) return;
        scheduledAtWrap.classList.toggle('d-none', sendMode.value !== 'later');
    }

    async function previewCampaign() {
        if (!config.previewUrl || !form) return;
        const formData = new FormData(form);
        const payload = Object.fromEntries(formData.entries());
        payload.filters = {};
        form.querySelectorAll('[name^="filters["]').forEach((input) => {
            const match = input.name.match(/^filters\[(.+)\]$/);
            if (match) payload.filters[match[1]] = input.value;
        });

        try {
            previewBtn.disabled = true;
            previewBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i><span>Previewing...</span>';
            const response = await fetch(config.previewUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': config.csrfToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(payload),
            });
            const data = await response.json();
            if (!response.ok) throw new Error(data.message || 'Preview failed.');
            const set = (id, value) => { const el = document.getElementById(id); if (el) el.textContent = value; };
            set('previewRecipients', data.count ?? 0);
            set('previewSegments', data.segments ?? 0);
            set('previewTotalSegments', data.total_segments ?? 0);
            set('previewEncoding', data.encoding ?? '-');
        } catch (error) {
            alert(error.message || 'Preview failed.');
        } finally {
            previewBtn.disabled = false;
            previewBtn.innerHTML = '<i class="fas fa-search"></i><span>Preview</span>';
        }
    }

    if (audienceType) { audienceType.addEventListener('change', toggleAudienceFields); toggleAudienceFields(); }
    if (sendMode) { sendMode.addEventListener('change', toggleScheduleFields); toggleScheduleFields(); }
    if (previewBtn) { previewBtn.addEventListener('click', previewCampaign); }
});
