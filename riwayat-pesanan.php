<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("location: index.php");
    exit;
}
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Pesanan - King Advertising</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="dashboard.css">
    <style>
        /* Style untuk Modal Detail */
        .detail-modal { display: none; position: fixed; z-index: 1001; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.6); backdrop-filter: blur(5px); }
        .detail-modal-content { background-color: #fefefe; margin: 10% auto; padding: 25px 35px; border-radius: 15px; width: 80%; max-width: 600px; position: relative; }
        .detail-modal-header { padding-bottom: 15px; border-bottom: 1px solid #e0e0e0; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; }
        .detail-modal-header h2 { margin: 0; font-size: 1.6rem; color: var(--brand-dark-red); }
        .close-detail-modal { color: #aaa; font-size: 28px; font-weight: bold; cursor: pointer; }
        .detail-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px 25px; }
        .detail-item { font-size: 0.95rem; }
        .detail-item strong { display: block; color: #888; font-weight: 500; margin-bottom: 4px; font-size: 0.85rem; }
        .detail-item span { color: var(--text-primary); font-weight: 500; }
        .detail-item.full-width { grid-column: 1 / -1; }
        .detail-item textarea { width: 100%; height: 100px; background: #f9f9f9; border: 1px solid #eee; border-radius: 5px; padding: 10px; font-family: 'Poppins', sans-serif; }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="sidebar-header"><i class="fas fa-crown"></i><h2>King Printing</h2></div>
            <nav class="sidebar-nav">
                <ul>
                    <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="pesanan.php" ><i class="fas fa-inbox"></i> Pesanan</a></li>
                    <li><a href="riwayat-pesanan.php" class="active"><i class="fas fa-history"></i> Riwayat Pesanan</a></li>
                    
                    <li><a href="#" id="bukaProdukModalBtn"><i class="fas fa-palette"></i> Lihat Produk </a></li>
                    <li><a href="index.php"> <i class="fas fa-home"></i> Kembali</a></li>

                </ul>
            </nav>
            <div class="sidebar-footer"><a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a></div>
        </aside>
        <main class="main-content">
            <header class="main-header">
                <div class="header-left"><h3>Riwayat Pesanan Selesai</h3></div>
                <div class="header-right"><div class="search-box"><i class="fas fa-search"></i><input type="text" placeholder="Cari Riwayat..."></div></div>
            </header>
            
            <section class="customers-table">
                <div class="table-header"><h3>Semua Pesanan yang Telah Selesai</h3></div>
                <table id="historyTable">
                    <thead>
                        <tr>
                            <th>ID Pesanan</th>
                            <th>Nama Pemesan</th>
                            <th>Produk</th>
                            <th>Tanggal Masuk</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT id, nama_pemesan, produk, tanggal_masuk, status FROM pesanan WHERE status = 'Selesai' ORDER BY tanggal_masuk DESC";
                        $result = $conn->query($sql);
                        if ($result->num_rows > 0) {
                            while($row = $result->fetch_assoc()) {
                                echo "<tr data-id='" . $row['id'] . "'>"; // Tambahkan data-id di sini
                                echo "<td>#KP" . str_pad($row['id'], 3, '0', STR_PAD_LEFT) . "</td>";
                                echo "<td>" . htmlspecialchars($row['nama_pemesan']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['produk']) . "</td>";
                                echo "<td>" . date('d M Y, H:i', strtotime($row['tanggal_masuk'])) . "</td>";
                                echo "<td><span class='status completed'>" . htmlspecialchars($row['status']) . "</span></td>";
                                echo "<td><button class='action-btn detail'>Detail</button></td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='6' style='text-align:center; padding: 2rem;'>Belum ada riwayat pesanan yang selesai.</td></tr>";
                        }
                        $conn->close();
                        ?>
                    </tbody>
                </table>
            </section>
        </main>
    </div>

    <!-- Modal untuk Detail Pesanan -->
    <div id="detailModal" class="detail-modal">
        <div class="detail-modal-content">
            <div class="detail-modal-header">
                <h2 id="detailModalTitle">Detail Pesanan</h2>
                <span class="close-detail-modal">&times;</span>
            </div>
            <div id="detailModalBody" class="detail-grid">
                <!-- Konten detail akan di-load di sini oleh JavaScript -->
            </div>
        </div>
    </div>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('detailModal');
        const closeModal = document.querySelector('.close-detail-modal');
        const table = document.getElementById('historyTable');

        if(table) {
            table.addEventListener('click', function(e) {
                if (e.target && e.target.classList.contains('detail')) {
                    const button = e.target;
                    const row = button.closest('tr');
                    const orderId = row.getAttribute('data-id');
                    
                    if(orderId) {
                        fetch(`get-order-details.php?id=${orderId}`)
                        .then(response => response.json())
                        .then(data => {
                            if(data.success) {
                                const order = data.order;
                                document.getElementById('detailModalTitle').textContent = `Detail Pesanan #${order.id_formatted}`;
                                const body = document.getElementById('detailModalBody');
                                body.innerHTML = `
                                    <div class="detail-item"><strong>Nama Pemesan</strong><span>${order.nama_pemesan}</span></div>
                                    <div class="detail-item"><strong>Telepon</strong><span>${order.telepon}</span></div>
                                    <div class="detail-item"><strong>Produk</strong><span>${order.produk}</span></div>
                                    <div class="detail-item"><strong>Tanggal Masuk</strong><span>${order.tanggal_masuk}</span></div>
                                    <div class="detail-item"><strong>Ukuran</strong><span>${order.ukuran || '-'} cm</span></div>
                                    <div class="detail-item"><strong>Bahan</strong><span>${order.bahan || '-'}</span></div>
                                    <div class="detail-item"><strong>Jumlah</strong><span>${order.jumlah} pcs</span></div>
                                    <div class="detail-item"><strong>Status</strong><span><span class="status completed">${order.status}</span></span></div>
                                    <div class="detail-item full-width"><strong>Catatan</strong><textarea readonly>${order.catatan || 'Tidak ada catatan.'}</textarea></div>
                                `;
                                modal.style.display = 'block';
                            } else {
                                alert('Gagal memuat detail: ' + data.message);
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('Terjadi kesalahan jaringan.');
                        });
                    }
                }
            });
        }
        if(closeModal) {
            closeModal.onclick = () => { modal.style.display = "none"; }
        }
        window.onclick = (event) => {
            if (event.target == modal) { modal.style.display = "none"; }
        }
    });
    </script>

<div id="pilihProdukModal" class="admin-modal">
    <div class="admin-modal-content">
        <span class="admin-modal-close">&times;</span>
        <h3>Pilih Produk</h3>
        <p>Pilih produk yang ingin Anda lihat atau kelola desainnya:</p>

        <ul class="admin-produk-list">
            <li><a href="produk.php?produk=Spanduk & Banner" >Spanduk & Banner</a></li>
            <li><a href="produk.php?produk=Stiker & Label" >Stiker & Label</a></li>
            <li><a href="produk.php?produk=Baliho & Billboard" >Baliho & Billboard</a></li>
            <li><a href="produk.php?produk=Brosur & Flyer" >Brosur & Flyer</a></li>
            <li><a href="produk.php?produk=Kemasan & Dus" >Kemasan & Dus</a></li>
            <li><a href="produk.php?produk=Produk Custom" >Produk Custom</a></li>
        </ul>
    </div>
</div>
<script>
document.addEventListener("DOMContentLoaded", function() {
    // Ambil elemen-elemen modal
    var modal = document.getElementById("pilihProdukModal");
    var btn = document.getElementById("bukaProdukModalBtn");
    var span = document.getElementsByClassName("admin-modal-close")[0];

    // Cek dulu apakah tombolnya ada di halaman ini
    if (btn) {
        // Saat tombol "Lihat Produk (Admin)" diklik
        btn.onclick = function(e) {
            e.preventDefault(); // Mencegah link '#' melompat ke atas
            modal.style.display = "block";
        }
    }

    // Cek apakah modalnya dan tombol closenya ada
    if (modal && span) {
        // Saat tombol 'x' diklik
        span.onclick = function() {
            modal.style.display = "none";
        }
        // Saat klik di luar area modal
        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }
    }
});
</script>
</body>
</html>