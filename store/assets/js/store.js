/**
 * LatinosPC Store - Core Client Utilities
 * Handles theme toggling, image previews, and UI helpers.
 */

// Instant theme initialization
(function() {
    const currentTheme = localStorage.getItem('theme') || 'light';
    document.documentElement.setAttribute('data-theme', currentTheme);
})();

/**
 * Toggle between light and dark themes
 */
function toggleTheme() {
    const current = document.documentElement.getAttribute('data-theme');
    const next = current === 'dark' ? 'light' : 'dark';
    document.documentElement.setAttribute('data-theme', next);
    localStorage.setItem('theme', next);
}

/**
 * Image preview handler for product cards
 */
function previewImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const card = input.closest('.card');
            if (card) {
                const img = card.querySelector('.card-img');
                if (img) img.src = e.target.result;
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
}

/**
 * Image preview handler for the "Add Item" form
 */
function previewAddPhoto(input) {
    const preview = document.getElementById('add-photo-preview');
    if (!preview) return;
    if (input.files && input.files[0]) {
        const file = input.files[0];
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.innerHTML = `
                <div style="display: flex; align-items: center; gap: 8px; padding: 4px 8px; background: rgba(0, 102, 255, 0.08); border: 1px dashed var(--primary-color); border-radius: 4px;">
                    <img src="${e.target.result}" style="width: 36px; height: 36px; object-fit: cover; border-radius: 4px;" alt="Preview">
                    <span style="font-size: 0.72rem; color: var(--text-color); overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">${file.name}</span>
                </div>
            `;
        };
        reader.readAsDataURL(file);
    } else {
        preview.innerHTML = '';
    }
}
