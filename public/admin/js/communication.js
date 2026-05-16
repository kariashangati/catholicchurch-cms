document.getElementById('previewBtn').addEventListener('click', function () {

    fetch("{{ route('admin.communication.sms.preview') }}", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            message: document.querySelector('[name=message]').value,
            audience_type: document.querySelector('[name=audience_type]').value
        })
    })
    .then(res => res.json())
    .then(data => {
        document.getElementById('previewBox').innerHTML = `
            <p>Recipients: ${data.count}</p>
            <p>Segments: ${data.segments}</p>
            <p>Total Segments: ${data.total_segments}</p>
        `;
    });

});