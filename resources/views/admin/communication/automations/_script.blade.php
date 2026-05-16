<script>
document.addEventListener('DOMContentLoaded', () => {
    const eventSelect = document.getElementById('event_key');
    const schemaWrap = document.getElementById('conditionSchemaWrap');

    const renderSchema = (schema) => {
        if (!schemaWrap) return;
        if (!schema || !schema.length) {
            schemaWrap.innerHTML = '<span class="text-muted">No special conditions for this event.</span>';
            return;
        }

        schemaWrap.innerHTML = schema.map(item => `<div>• ${item.label}</div>`).join('');
    };

    const loadSchema = async () => {
        if (!eventSelect || !eventSelect.value) {
            renderSchema([]);
            return;
        }

        try {
            const response = await fetch(`{{ route('admin.communication.automations.schema', ['eventKey' => '___EVENT___']) }}`.replace('___EVENT___', eventSelect.value));
            const data = await response.json();
            renderSchema(data.schema || []);
        } catch (error) {
            renderSchema([]);
        }
    };

    if (eventSelect) {
        eventSelect.addEventListener('change', loadSchema);
        loadSchema();
    }
});
</script>
