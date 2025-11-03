// Mobile menu toggle
const hamburger = document.getElementById('hamburger');
const nav = document.querySelector('.nav');

hamburger.addEventListener('click', function() {
    this.classList.toggle('active');
    nav.classList.toggle('active');
});

// Modal functionality
const modal = document.getElementById('orderModal');
const closeBtn = document.querySelector('.close');
const orderButtons = document.querySelectorAll('.btn-order');
const orderForm = document.getElementById('orderForm');
const produkNameInput = document.getElementById('produkName');

// Open modal when order button is clicked
orderButtons.forEach(button => {
    button.addEventListener('click', function() {
        const produkName = this.getAttribute('data-produk');
        produkNameInput.value = produkName;
        modal.style.display = 'block';
        document.body.style.overflow = 'hidden'; // Prevent scrolling
    });
});

// Close modal when close button is clicked
closeBtn.addEventListener('click', function() {
    modal.style.display = 'none';
    document.body.style.overflow = 'auto'; // Re-enable scrolling
});

// Close modal when clicking outside the modal
window.addEventListener('click', function(event) {
    if (event.target === modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto'; // Re-enable scrolling
    }
});

// Handle form submission
orderForm.addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Get form values
    const produk = document.getElementById('produkName').value;
    const ukuran = document.getElementById('ukuran').value;
    const bahan = document.getElementById('bahan').value;
    const jumlah = document.getElementById('jumlah').value;
    const catatan = document.getElementById('catatan').value;
    const nama = document.getElementById('nama').value;
    const telepon = document.getElementById('telepon').value;
    
    // Validate required fields
    if (!nama || !telepon) {
        alert('Harap isi nama dan nomor telepon Anda.');
        return;
    }
    
    // Create WhatsApp message
    const message = `Halo King Advertising, saya ingin memesan:\n\n` +
                   `📋 *Detail Pesanan:*\n` +
                   `Produk: ${produk}\n` +
                   `Ukuran: ${ukuran} cm\n` +
                   `Bahan: ${bahan}\n` +
                   `Jumlah: ${jumlah} pcs\n` +
                   `Catatan: ${catatan || '-'}\n\n` +
                   `👤 *Data Pemesan:*\n` +
                   `Nama: ${nama}\n` +
                   `Telepon: ${telepon}\n\n` +
                   `Saya ingin mengetahui harga dan estimasi pengerjaannya. Terima kasih.`;
    
    // Encode message for URL
    const encodedMessage = encodeURIComponent(message);
    
    // WhatsApp number (replace with actual number)
    const whatsappNumber = '6281234567890';
    
    // Create WhatsApp URL
    const whatsappURL = `https://wa.me/${whatsappNumber}?text=${encodedMessage}`;
    
    // Open WhatsApp
    window.open(whatsappURL, '_blank');
    
    // Reset form and close modal
    orderForm.reset();
    modal.style.display = 'none';
    document.body.style.overflow = 'auto'; // Re-enable scrolling
    
    // Show confirmation
    alert('Terima kasih! Pesanan Anda akan dibuka di WhatsApp. Silakan lanjutkan proses pemesanan di sana.');
});

// Smooth scrolling for anchor links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
        e.preventDefault();
        
        const targetId = this.getAttribute('href');
        if (targetId === '#') return;
        
        const targetElement = document.querySelector(targetId);
        if (targetElement) {
            window.scrollTo({
                top: targetElement.offsetTop - 80,
                behavior: 'smooth'
            });
            
            // Close mobile menu if open
            if (window.innerWidth <= 768) {
                hamburger.classList.remove('active');
                nav.classList.remove('active');
            }
        }
    });
});

// Add animation on scroll
function animateOnScroll() {
    const elements = document.querySelectorAll('.produk-item, .step, .keunggulan-item');
    
    elements.forEach(element => {
        const elementTop = element.getBoundingClientRect().top;
        const elementVisible = 150;
        
        if (elementTop < window.innerHeight - elementVisible) {
            element.style.opacity = "1";
            element.style.transform = "translateY(0)";
        }
    });
}

// Set initial state for animation
document.addEventListener('DOMContentLoaded', function() {
    const elements = document.querySelectorAll('.produk-item, .step, .keunggulan-item');
    
    elements.forEach(element => {
        element.style.opacity = "0";
        element.style.transform = "translateY(20px)";
        element.style.transition = "opacity 0.5s ease, transform 0.5s ease";
    });
    
    window.addEventListener('scroll', animateOnScroll);
    // Trigger once on load for elements already in view
    animateOnScroll();
});