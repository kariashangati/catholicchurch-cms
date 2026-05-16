(function () {
    'use strict';

    function ready(callback) {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', callback);
        } else {
            callback();
        }
    }

    function safeJson(response) {
        if (!response.ok) {
            throw new Error('Request failed');
        }

        return response.json();
    }

    function setSelectOptions(selectEl, options, placeholder, mapper) {
        if (!selectEl) return;

        selectEl.innerHTML = '';

        const placeholderOption = document.createElement('option');
        placeholderOption.value = '';
        placeholderOption.textContent = placeholder || 'Select';
        selectEl.appendChild(placeholderOption);

        options.forEach(item => {
            const mapped = mapper(item);
            const option = document.createElement('option');
            option.value = mapped.value;
            option.textContent = mapped.label;

            if (mapped.dataset) {
                Object.entries(mapped.dataset).forEach(([key, value]) => {
                    option.dataset[key] = value ?? '';
                });
            }

            selectEl.appendChild(option);
        });
    }

    function initPasswordModeToggle() {
        const modeSelectors = [
            {
                select: document.getElementById('passwordModeSelect'),
                wrap: document.getElementById('passwordInputWrap')
            },
            {
                select: document.getElementById('editPasswordModeSelect'),
                wrap: document.getElementById('editPasswordInputWrap')
            }
        ];

        modeSelectors.forEach(item => {
            if (!item.select || !item.wrap) return;

            const toggle = () => {
                item.wrap.style.display = item.select.value === 'manual' ? 'block' : 'none';
            };

            item.select.addEventListener('change', toggle);
            toggle();
        });
    }

    function initCreateAdminUserForm() {
        const form = document.getElementById('adminUserCreateForm');
        if (!form) return;

        const kandaSelect = document.getElementById('kandaSelect');
        const jumuiyaSelect = document.getElementById('jumuiyaSelect');
        const memberSelect = document.getElementById('memberSelect');
        const memberPreview = document.getElementById('memberPreview');

        if (!kandaSelect || !jumuiyaSelect || !memberSelect || !memberPreview) return;

        const routes = window.accessControlRoutes || {};
        const labels = window.accessControlLabels || {};

        const previewEmpty = () => {
            memberPreview.innerHTML = `
                <div class="access-member-preview-title">${labels.memberPreview || 'Member Preview'}</div>
                <div class="access-member-preview-meta">${labels.selectMemberPreview || 'Select member to preview details'}</div>
            `;
        };

        const previewMember = () => {
            const selected = memberSelect.options[memberSelect.selectedIndex];

            if (!selected || !selected.value) {
                previewEmpty();
                return;
            }

            memberPreview.innerHTML = `
                <div class="access-member-preview-title">${selected.dataset.name || '-'}</div>
                <div class="access-member-preview-meta">${labels.memberCode || 'Member Code'}: ${selected.dataset.memberCode || '-'}</div>
                <div class="access-member-preview-meta">${labels.phone || 'Phone'}: ${selected.dataset.phone || '-'}</div>
                <div class="access-member-preview-meta">${labels.kanda || 'Kanda'}: ${selected.dataset.kanda || '-'} • ${labels.jumuiya || 'Jumuiya'}: ${selected.dataset.jumuiya || '-'}</div>
            `;
        };

        const loadJumuiyas = async () => {
            previewEmpty();
            setSelectOptions(jumuiyaSelect, [], labels.selectJumuiya || 'Select Jumuiya');
            setSelectOptions(memberSelect, [], labels.selectMember || 'Select Member');

            if (!kandaSelect.value || !routes.jumuiyas) return;

            try {
                const response = await fetch(`${routes.jumuiyas}?kanda_id=${encodeURIComponent(kandaSelect.value)}`);
                const data = await safeJson(response);

                setSelectOptions(
                    jumuiyaSelect,
                    data,
                    labels.selectJumuiya || 'Select Jumuiya',
                    (item) => ({
                        value: item.id,
                        label: item.name
                    })
                );
            } catch (error) {
                console.error(error);
            }
        };

        const loadMembers = async () => {
            previewEmpty();
            setSelectOptions(memberSelect, [], labels.selectMember || 'Select Member');

            const params = new URLSearchParams();

            if (kandaSelect.value) params.append('kanda_id', kandaSelect.value);
            if (jumuiyaSelect.value) params.append('jumuiya_id', jumuiyaSelect.value);

            if (!params.toString() || !routes.members) return;

            try {
                const response = await fetch(`${routes.members}?${params.toString()}`);
                const data = await safeJson(response);

                setSelectOptions(
                    memberSelect,
                    data,
                    labels.selectMember || 'Select Member',
                    (item) => ({
                        value: item.id,
                        label: `${item.name}${item.member_code ? ' • ' + item.member_code : ''}`,
                        dataset: {
                            name: item.name || '',
                            memberCode: item.member_code || '',
                            phone: item.phone || '',
                            kanda: item.kanda_name || '',
                            jumuiya: item.jumuiya_name || ''
                        }
                    })
                );
            } catch (error) {
                console.error(error);
            }
        };

        kandaSelect.addEventListener('change', loadJumuiyas);
        jumuiyaSelect.addEventListener('change', loadMembers);
        memberSelect.addEventListener('change', previewMember);

        previewEmpty();
    }

    function initPermissionBulkHelpers() {
        document.querySelectorAll('[data-module-check-all]').forEach(button => {
            button.addEventListener('click', function () {
                const target = this.getAttribute('data-module-check-all');
                document.querySelectorAll(`[data-permission-module="${target}"]`).forEach(input => {
                    input.checked = true;
                });
            });
        });

        document.querySelectorAll('[data-module-clear-all]').forEach(button => {
            button.addEventListener('click', function () {
                const target = this.getAttribute('data-module-clear-all');
                document.querySelectorAll(`[data-permission-module="${target}"]`).forEach(input => {
                    input.checked = false;
                });
            });
        });
    }

    function initTemplateTokenCopy() {
        document.querySelectorAll('[data-copy-template-token]').forEach(button => {
            button.addEventListener('click', async function () {
                const token = this.getAttribute('data-copy-template-token');
                if (!token || !navigator.clipboard) return;

                try {
                    await navigator.clipboard.writeText(token);
                    this.classList.add('btn-success');
                    setTimeout(() => this.classList.remove('btn-success'), 1000);
                } catch (error) {
                    console.error(error);
                }
            });
        });
    }

    ready(function () {
        initPasswordModeToggle();
        initCreateAdminUserForm();
        initPermissionBulkHelpers();
        initTemplateTokenCopy();
    });
})();