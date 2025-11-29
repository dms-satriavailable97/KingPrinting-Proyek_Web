// Icon picker modal
const iconList = [
    'fas fa-flag', 'fas fa-sticky-note', 'fas fa-bullhorn', 'fas fa-newspaper', 
    'fas fa-box', 'fas fa-palette', 'fas fa-print', 'fas fa-paint-brush',
    'fas fa-tags', 'fas fa-shipping-fast', 'fas fa-clock', 'fas fa-crown',
    'fas fa-bolt', 'fas fa-headset', 'fas fa-award', 'fas fa-gem',
    'fas fa-magic', 'fas fa-rocket', 'fas fa-star', 'fas fa-heart',
    'fas fa-thumbs-up', 'fas fa-check-circle', 'fas fa-gift', 'fas fa-trophy',
    'fas fa-mouse-pointer', 'fas fa-ruler-combined', 'fab fa-whatsapp', 'fas fa-phone',
    'fas fa-envelope', 'fas fa-map-marker-alt', 'fas fa-users', 'fas fa-cog'
];

function openIconPicker(currentIcon = '') {
    const modal = document.createElement('div');
    modal.style.cssText = `
        position: fixed; top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(0,0,0,0.8); display: flex; align-items: center;
        justify-content: center; z-index: 10000;
    `;
    
    const content = document.createElement('div');
    content.style.cssText = `
        background: white; padding: 2rem; border-radius: 10px;
        max-width: 500px; max-height: 80vh; overflow-y: auto;
    `;
    
    content.innerHTML = `
        <h3>Pilih Icon</h3>
        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin: 1rem 0;">
            ${iconList.map(icon => `
                <div class="icon-option" data-icon="${icon}" 
                     style="padding: 1rem; text-align: center; border: 2px solid ${currentIcon === icon ? '#9a2020' : '#ddd'}; 
                            border-radius: 5px; cursor: pointer; background: ${currentIcon === icon ? '#fff3cd' : 'white'};">
                    <i class="${icon}" style="font-size: 1.5rem;"></i>
                    <div style="font-size: 0.7rem; margin-top: 0.5rem;">${icon.replace('fas fa-', '').replace('fab fa-', '')}</div>
                </div>
            `).join('')}
        </div>
        <button onclick="this.closest('.icon-picker-modal').remove()" 
                style="padding: 0.5rem 1rem; background: #6c757d; color: white; border: none; border-radius: 5px;">
            Batal
        </button>
    `;
    
    modal.appendChild(content);
    modal.classList.add('icon-picker-modal');
    document.body.appendChild(modal);
    
    // Handle icon selection
    modal.querySelectorAll('.icon-option').forEach(option => {
        option.addEventListener('click', function() {
            const selectedIcon = this.dataset.icon;
            // Kirim event dengan icon yang dipilih
            const event = new CustomEvent('iconSelected', { detail: selectedIcon });
            document.dispatchEvent(event);
            modal.remove();
        });
    });
}

// Listen for icon selection
document.addEventListener('iconSelected', function(e) {
    const activeIconInput = document.querySelector('.icon-input-active');
    if (activeIconInput) {
        activeIconInput.value = e.detail;
        activeIconInput.dispatchEvent(new Event('input'));
        activeIconInput.classList.remove('icon-input-active');
    }
});