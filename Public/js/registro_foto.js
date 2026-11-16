function previewImage(event) {
    const reader = new FileReader();
    const input = event.target;

    reader.onload = function() {
        const preview = document.getElementById('avatar-preview');
        if (preview) {
            preview.src = reader.result;
        }
    };

    if (input.files && input.files[0]) {
        reader.readAsDataURL(input.files[0]);
    }
}