document.addEventListener('DOMContentLoaded', function () {
    const audienceSelect = document.getElementById('audienceType');
    const kandaSelect = document.getElementById('kandaSelect');
    const jumuiyaSelect = document.getElementById('jumuiyaSelect');
    const familiaSelect = document.getElementById('familiaSelect');
    const messageBody = document.getElementById('messageBody');
    const templateSelect = document.getElementById('smsTemplate');
    const sendMode = document.getElementById('sendMode');
    const scheduleWrap = document.getElementById('scheduledAtWrap');
    const form = document.getElementById('communicationSmsForm');
    const submitBtn = document.getElementById('submitSmsBtn');
    const cfg = window.communicationSmsConfig || {};
    let previewTimer = null;
    let lastPreviewOk = false;

    function syncAudienceFields() {
        const selected = audienceSelect ? audienceSelect.value : '';
        document.querySelectorAll('.sms-target-field').forEach(function (field) {
            const values = (field.dataset.audience || '').split(/\s+/);
            const visible = values.includes(selected);
            field.classList.toggle('is-visible', visible);
            field.querySelectorAll('select,input,textarea').forEach(function (input) {
                input.disabled = !visible;
            });
        });
        queuePreview();
    }

    function syncHierarchy() {
        const kanda = kandaSelect ? kandaSelect.value : '';
        if (jumuiyaSelect) {
            Array.from(jumuiyaSelect.options).forEach(function (option) {
                if (!option.value) { option.hidden = false; return; }
                option.hidden = !!kanda && option.dataset.kanda !== kanda;
            });
            if (jumuiyaSelect.selectedOptions[0] && jumuiyaSelect.selectedOptions[0].hidden) jumuiyaSelect.value = '';
        }
        const jumuiya = jumuiyaSelect ? jumuiyaSelect.value : '';
        if (familiaSelect) {
            Array.from(familiaSelect.options).forEach(function (option) {
                if (!option.value) { option.hidden = false; return; }
                const matchKanda = !kanda || option.dataset.kanda === kanda;
                const matchJumuiya = !jumuiya || option.dataset.jumuiya === jumuiya;
                option.hidden = !(matchKanda && matchJumuiya);
            });
            if (familiaSelect.selectedOptions[0] && familiaSelect.selectedOptions[0].hidden) familiaSelect.value = '';
        }
        queuePreview();
    }

    function analyzeLocalMessage() {
        const message = messageBody ? messageBody.value : '';
        const chars = Array.from(message).length;
        const isGsm = /^[\x00-\x7F\n\r€£¥]*$/.test(message);
        const single = isGsm ? 160 : 70;
        const multi = isGsm ? 153 : 67;
        const segments = chars === 0 ? 0 : (chars <= single ? 1 : Math.ceil(chars / multi));
        const charCount = document.getElementById('charCount');
        const segmentCount = document.getElementById('segmentCount');
        const encodingText = document.getElementById('encodingText');
        if (charCount) charCount.textContent = chars;
        if (segmentCount) segmentCount.textContent = segments;
        if (encodingText) encodingText.textContent = isGsm ? 'GSM-7' : 'UNICODE';
        queuePreview();
    }

    function setText(id, value) {
        const el = document.getElementById(id);
        if (el) el.textContent = value;
    }

    function setSubmitState(enabled, reason) {
        if (!submitBtn) return;
        submitBtn.disabled = !enabled;
        submitBtn.classList.toggle('d-none', !enabled && reason === 'empty_balance');
    }

    function setPreview(data) {
        setText('previewRecipients', data.count || 0);
        setText('previewSegments', data.segments || 0);
        setText('previewTotalSegments', data.total_segments || 0);
        setText('previewCost', Number(data.estimated_cost || 0).toFixed(2));
        const note = document.getElementById('balanceNote');
        const enough = !!data.enough_balance;
        lastPreviewOk = enough;

        if (note) {
            note.classList.remove('good', 'bad');
            note.classList.add(enough ? 'good' : 'bad');
            if ((Number(data.balance || 0) <= 0) || (!enough && Number(data.estimated_cost || 0) > 0)) {
                note.textContent = cfg.translations?.balanceFinished || 'Salio la SMS limekwisha. Ongeza salio.';
            } else {
                note.textContent = (cfg.translations?.enoughBalance || 'Balance is enough') + ' · ' + Number(data.balance || 0).toFixed(2) + ' ' + (data.currency || cfg.currency || 'TZS');
            }
        }

        setSubmitState(enough, Number(data.balance || 0) <= 0 ? 'empty_balance' : 'low_balance');
    }

    function resetPreview() {
        lastPreviewOk = false;
        setText('previewRecipients', 0);
        setText('previewSegments', 0);
        setText('previewTotalSegments', 0);
        setText('previewCost', '0.00');
        setSubmitState(false, Number(cfg.balance || 0) <= 0 ? 'empty_balance' : 'needs_preview');
    }

    async function previewSms() {
        if (!cfg.previewUrl || !form || !messageBody || !messageBody.value.trim()) {
            resetPreview();
            return;
        }

        try {
            const response = await fetch(cfg.previewUrl, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': cfg.csrfToken, 'Accept': 'application/json' },
                body: new FormData(form)
            });
            const data = await response.json();
            if (!response.ok) throw new Error(data.message || cfg.translations?.previewError || 'Preview failed');
            setPreview(data);
        } catch (error) {
            lastPreviewOk = false;
            setSubmitState(false, 'preview_failed');
            const note = document.getElementById('balanceNote');
            if (note) {
                note.classList.remove('good');
                note.classList.add('bad');
                note.textContent = error.message;
            }
        }
    }

    function queuePreview() {
        clearTimeout(previewTimer);
        previewTimer = setTimeout(previewSms, 450);
    }

    if (audienceSelect) audienceSelect.addEventListener('change', syncAudienceFields);
    if (kandaSelect) kandaSelect.addEventListener('change', syncHierarchy);
    if (jumuiyaSelect) jumuiyaSelect.addEventListener('change', syncHierarchy);
    if (familiaSelect) familiaSelect.addEventListener('change', queuePreview);
    if (messageBody) messageBody.addEventListener('input', analyzeLocalMessage);

    if (form) {
        form.querySelectorAll('select,input').forEach(function (input) {
            if (!['message'].includes(input.name)) input.addEventListener('change', queuePreview);
        });
        form.addEventListener('submit', function (event) {
            if (!lastPreviewOk) {
                event.preventDefault();
                previewSms();
                if (window.Swal) {
                    Swal.fire({ icon: 'warning', title: cfg.translations?.balanceFinished || 'Salio la SMS halitoshi' });
                }
            }
        });
    }

    if (templateSelect && messageBody) templateSelect.addEventListener('change', function () {
        const option = templateSelect.selectedOptions[0];
        if (option && option.dataset.body) {
            messageBody.value = option.dataset.body;
            analyzeLocalMessage();
        }
    });

    if (sendMode && scheduleWrap) sendMode.addEventListener('change', function () {
        scheduleWrap.classList.toggle('d-none', sendMode.value !== 'later');
    });

    const previewBtn = document.getElementById('previewBtn');
    if (previewBtn) previewBtn.classList.add('d-none');

    syncAudienceFields();
    syncHierarchy();
    analyzeLocalMessage();

    if (Number(cfg.balance || 0) <= 0) {
        const note = document.getElementById('balanceNote');
        if (note) {
            note.classList.add('bad');
            note.textContent = cfg.translations?.balanceFinished || 'Salio la SMS limekwisha. Ongeza salio.';
        }
        setSubmitState(false, 'empty_balance');
    }
});
