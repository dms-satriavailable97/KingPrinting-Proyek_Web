// Mobile menu toggle
const hamburger = document.getElementById('hamburger');
const nav = document.querySelector('.nav');
hamburger.addEventListener('click', function() {
    this.classList.toggle('active');
    nav.classList.toggle('active');
});

// Modal Pesan functionality
const orderModal = document.getElementById('orderModal');
const closeOrderBtn = document.querySelector('.close-order');
const orderButtons = document.querySelectorAll('.btn-order');
const orderForm = document.getElementById('orderForm');
const produkNameInput = document.getElementById('produkName');

orderButtons.forEach(button => {
    button.addEventListener('click', function() {
        produkNameInput.value = this.getAttribute('data-produk');
        orderModal.style.display = 'block';
        document.body.style.overflow = 'hidden';
    });
});

if(closeOrderBtn) {
    closeOrderBtn.addEventListener('click', function() {
        orderModal.style.display = 'none';
        document.body.style.overflow = 'auto';
    });
}

// Modal Login functionality
const loginModal = document.getElementById('loginModal');
const loginBtn = document.getElementById('loginBtn');
const closeLoginBtn = document.querySelector('.close-login');

if(loginBtn) {
    loginBtn.addEventListener('click', function(e) {
        e.preventDefault();
        loginModal.style.display = 'block';
        document.body.style.overflow = 'hidden';
    });
}

if(closeLoginBtn) {
    closeLoginBtn.addEventListener('click', function() {
        loginModal.style.display = 'none';
        document.body.style.overflow = 'auto';
    });
}

window.addEventListener('click', function(event) {
    if (event.target === orderModal) {
        orderModal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
    if (event.target === loginModal) {
        loginModal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
});

// Handle order form submission (DUAL ACTION: WHATSAPP + DATABASE)
if(orderForm) {
    orderForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // --- Bagian 1: Mengirim data ke Database di Latar Belakang ---
        const formData = new FormData(orderForm);
        fetch('submit-order.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log('Pesanan berhasil disimpan ke database.');
            } else {
                console.error('Gagal menyimpan ke database:', data.message);
            }
        })
        .catch(error => {
            console.error('Error saat mengirim data ke server:', error);
        });

        // --- Bagian 2: Membuka WhatsApp seperti semula ---
        const produk = document.getElementById('produkName').value;
        const ukuran = document.getElementById('ukuran').value;
        const bahan = document.getElementById('bahan').value;
        const jumlah = document.getElementById('jumlah').value;
        const catatan = document.getElementById('catatan').value;
        const nama = document.getElementById('nama').value;
        const telepon = document.getElementById('telepon').value;
        
        if (!nama || !telepon) {
            alert('Harap isi nama dan nomor telepon Anda.');
            return;
        }
        
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
        
        const encodedMessage = encodeURIComponent(message);
        const whatsappNumber = '628997800507';
        const whatsappURL = `https://wa.me/${whatsappNumber}?text=${encodedMessage}`;
        
        window.open(whatsappURL, '_blank');
        
        // --- Bagian 3: Membersihkan form dan menutup modal ---
        orderForm.reset();
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
        
        alert('Terima kasih! Pesanan Anda sedang kami proses. Silakan lanjutkan konfirmasi di WhatsApp.');
    });
}

document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
        const targetId = this.getAttribute('href');
        if (targetId.startsWith('#') && targetId.length > 1) {
            e.preventDefault();
            const targetElement = document.querySelector(targetId);
            if (targetElement) {
                window.scrollTo({
                    top: targetElement.offsetTop - 80,
                    behavior: 'smooth'
                });
                if (window.innerWidth <= 768 && nav.classList.contains('active')) {
                    hamburger.classList.remove('active');
                    nav.classList.remove('active');
                }
            }
        }
    });
});

function animateOnScroll() {
    const elements = document.querySelectorAll('.produk-item, .step, .keunggulan-item');
    elements.forEach(element => {
        const elementTop = element.getBoundingClientRect().top;
        if (elementTop < window.innerHeight - 150) {
            element.classList.add('is-visible');
        }
    });
}

document.addEventListener('DOMContentLoaded', function() {
    window.addEventListener('scroll', animateOnScroll);
    animateOnScroll();
});

// --- Logika Modal Tambah Desain ---

// Cek saat halaman selesai dimuat
document.addEventListener("DOMContentLoaded", function() {

    // Dapatkan elemen-elemen modal
    var modal = document.getElementById("tambahDesainModal");
    var btn = document.getElementById("bukaModalBtn");
    var span = document.getElementsByClassName("close-modal-btn")[0];

    // Cek apakah elemen-elemen tersebut ada di halaman ini
    // Ini agar script tidak error di halaman lain yang tidak punya tombol/modal
    // (Penting karena tombol & modal ini hanya ada untuk admin)
    if (modal && btn && span) {
        
        // Saat tombol "+ Tambah Desain" diklik, tampilkan modal
        btn.onclick = function() {
            modal.style.display = "block";
        }

        // Saat tombol 'x' (span) diklik, tutup modal
        span.onclick = function() {
            modal.style.display = "none";
        }

        // Saat pengguna mengklik di luar area modal (di latar belakang gelap), tutup juga modalnya
        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }
    }
});

// --- INISIALISASI HERO SLIDER ---
document.addEventListener("DOMContentLoaded", function() {
    // Cek apakah elemen slider ada (agar tidak error di halaman lain)
    if (document.querySelector('.myHeroSlider')) {
        var heroSwiper = new Swiper(".myHeroSlider", {
            loop: true,                 // Muter terus
            effect: "slide",            // Efek geser
            speed: 800,                 // Kecepatan transisi (ms)
            autoplay: {
                delay: 3000,            // Ganti gambar tiap 3 detik
                disableOnInteraction: false,
            },
            pagination: {
                el: ".swiper-pagination",
                clickable: true,
            },
        });
    }
});