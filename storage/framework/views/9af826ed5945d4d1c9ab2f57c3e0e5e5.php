
<script src="https://cdn.jsdelivr.net/npm/tinymce@7.9.3/tinymce.min.js"></script>
<script>
    if (window.tinymce) {
        tinymce.init({
            selector: 'textarea.editor-html',
            license_key: 'gpl',
            language: 'id',
            language_url: 'https://cdn.jsdelivr.net/npm/tinymce-i18n@26.8.2/langs7/id.js',
            height: 240,
            menubar: false,
            branding: false,
            promotion: false,
            plugins: 'lists table code fullscreen',
            toolbar: 'undo redo | blocks | bold italic underline | bullist numlist | outdent indent | table | removeformat | fullscreen code',
            paste_data_images: false,
            content_style: 'body { font-family: system-ui, -apple-system, "Segoe UI", sans-serif; font-size: 14px; }'
        });

        // Salin isi editor ke <textarea> sebelum form apa pun dikirim.
        document.addEventListener('submit', function () { tinymce.triggerSave(); }, true);
    }
</script>
<?php /**PATH C:\laragon\www\kurikulum\resources\views/partials/tinymce.blade.php ENDPATH**/ ?>