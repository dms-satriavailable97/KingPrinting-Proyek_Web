<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("location: index.html");
    exit;
}
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Pesanan - King Advertising</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="dashboard.css">
    <style>
        /* Style untuk Dropdown Status Interaktif */
        .status-wrapper { position: relative; display: inline-block; }
        .status.interactive { cursor: pointer; transition: background-color 0.2s, color 0.2s; }
        .status.interactive:hover { filter: brightness(1.1); }
        .status-dropdown { display: none; position: absolute; top: 100%; left: 0; background-color: white; border: 1px solid #ddd; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); min-width: 120px; list-style: none; padding: 5px 0; z-index: 10; margin-top: 5px; }
        .status-dropdown li { padding: 8px 15px; font-size: 0.9rem; cursor: pointer; transition: background-color 0.2s; }
        .status-dropdown li:hover { background-color: #f5f5f5; }

        /* Style untuk Modal Detail (sama seperti di riwayat) */
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
        .detail-item textarea { width: 100%; height: 100px; background: #f9f9f9; border: 1px solid #eee; border-radius: 5px; padding: 10px; font-family: 'Poppins', sans-serif; resize: vertical; }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="sidebar-header"><i class="fas fa-crown"></i><h2>King Printing</h2></div>
            <nav class="sidebar-nav">
                <ul>
                    <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="pesanan.php" class="active"><i class="fas fa-inbox"></i> Pesanan</a></li>
                    <li><a href="riwayat-pesanan.php"><i class="fas fa-history"></i> Riwayat Pesanan</a></li>
                </ul>
            </nav>
            <div class="sidebar-footer"><a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a></div>
        </aside>
        <main class="main-content">
            <header class="main-header">
                <div class="header-left"><h3>Pesanan Tertunda</h3></div>
                <div class="header-right"><div class="search-box"><i class="fas fa-search"></i><input type="text" placeholder="Cari Pesanan..."></div></div>
            </header>
            
            <section class="customers-table">
                <div class="table-header"><h3>Semua Pesanan yang Perlu Dikerjakan</h3></div>
                <table id="ordersTable">
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
                        $sql = "SELECT id, nama_pemesan, produk, tanggal_masuk, status FROM pesanan WHERE status = 'Tertunda' ORDER BY tanggal_masuk DESC";
                        $result = $conn->query($sql);
                        if ($result->num_rows > 0) {
                            while($row = $result->fetch_assoc()) {
                                $status = htmlspecialchars($row['status']);
                                $status_class = strtolower($status);
                                echo "<tr data-id='" . $row['id'] . "'>";
                                echo "<td>#KP" . str_pad($row['id'], 3, '0', STR_PAD_LEFT) . "</td>";
                                echo "<td>" . htmlspecialchars($row['nama_pemesan']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['produk']) . "</td>";
                                echo "<td>" . date('d M Y, H:i', strtotime($row['tanggal_masuk'])) . "</td>";
                                echo "<td>
                                        <div class='status-wrapper'>
                                            <span class='status interactive " . $status_class . "'>" . $status . "</span>
                                            <ul class='status-dropdown'></ul>
                                        </div>
                                      </td>";
                                echo "<td><button class='action-btn detail'>Detail</button></td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='6' style='text-align:center; padding: 2rem;'>Tidak ada pesanan tertunda saat ini.</td></tr>";
                        }
                        $conn->close();
                        ?>
                    </tbody>
                </table>
            </section>
        </main>
    </div>

    <!-- Modal untuk Detail Pesanan (ditambahkan di sini) -->
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
    const table = document.getElementById('ordersTable');
    const detailModal = document.getElementById('detailModal');
    const closeDetailModal = document.querySelector('.close-detail-modal');
    let activeDropdown = null;

    // --- FUNGSI UNTUK MENGUBAH STATUS PESANAN ---
    function updateOrderStatus(orderId, newStatus, statusElement) {
        statusElement.textContent = '...';
        const formData = new FormData();
        formData.append('id', orderId);
        formData.append('status', newStatus);

        fetch('update-status.php', { method: 'POST', body: formData })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const row = statusElement.closest('tr');
                row.style.transition = 'opacity 0.5s ease';
                row.style.opacity = '0';
                setTimeout(() => row.remove(), 500);
            } else {
                alert('Gagal: ' + data.message);
                statusElement.textContent = 'Tertunda';
            }
        }).catch(error => {
            alert('Terjadi kesalahan jaringan.');
            statusElement.textContent = 'Tertunda';
        });
    }

    // --- EVENT LISTENER UTAMA PADA TABEL ---
    table.addEventListener('click', function(e) {
        const statusTrigger = e.target.closest('.status.interactive');
        const dropdownItem = e.target.closest('.status-dropdown li');
        const detailButton = e.target.closest('.action-btn.detail');

        // --- Logika untuk membuka dropdown status ---
        if (statusTrigger) {
            const dropdown = statusTrigger.nextElementSibling;
            if (activeDropdown && activeDropdown !== dropdown) activeDropdown.style.display = 'none';
            dropdown.innerHTML = `<li data-new-status="Selesai">Selesai</li>`;
            const isVisible = dropdown.style.display === 'block';
            dropdown.style.display = isVisible ? 'none' : 'block';
            activeDropdown = isVisible ? null : dropdown;
            return;
        }

        // --- Logika untuk memilih item dari dropdown ---
        if (dropdownItem) {
            const newStatus = dropdownItem.dataset.newStatus;
            const statusElement = dropdownItem.closest('.status-wrapper').querySelector('.status.interactive');
            const orderId = dropdownItem.closest('tr').dataset.id;
            updateOrderStatus(orderId, newStatus, statusElement);
            if (activeDropdown) activeDropdown.style.display = 'none';
            activeDropdown = null;
            return;
        }

        // --- Logika untuk tombol "Detail" (BARU DITAMBAHKAN) ---
        if (detailButton) {
            const orderId = detailButton.closest('tr').getAttribute('data-id');
            if (orderId) {
                fetch(`get-order-details.php?id=${orderId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const order = data.order;
                        document.getElementById('detailModalTitle').textContent = `Detail Pesanan #${order.id_formatted}`;
                        const modalBody = document.getElementById('detailModalBody');
                        modalBody.innerHTML = `
                            <div class="detail-item"><strong>Nama Pemesan</strong><span>${order.nama_pemesan}</span></div>
                            <div class="detail-item"><strong>Telepon</strong><span>${order.telepon}</span></div>
                            <div class="detail-item"><strong>Produk</strong><span>${order.produk}</span></div>
                            <div class="detail-item"><strong>Tanggal Masuk</strong><span>${order.tanggal_masuk}</span></div>
                            <div class="detail-item"><strong>Ukuran</strong><span>${order.ukuran || '-'} cm</span></div>
                            <div class="detail-item"><strong>Bahan</strong><span>${order.bahan || '-'}</span></div>
                            <div class="detail-item"><strong>Jumlah</strong><span>${order.jumlah} pcs</span></div>
                            <div class="detail-item"><strong>Status</strong><span class="status tertunda">${order.status}</span></div>
                            <div class="detail-item full-width"><strong>Catatan</strong><textarea readonly>${order.catatan || 'Tidak ada catatan.'}</textarea></div>
                        `;
                        detailModal.style.display = 'block';
                    } else {
                        alert('Gagal memuat detail: ' + data.message);
                    }
                }).catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan jaringan.');
                });
            }
            return;
        }
    });

    // --- EVENT LISTENER UNTUK MENUTUP DROPDOWN/MODAL ---
    document.addEventListener('click', function(e) {
        // Menutup dropdown jika klik di luar
        if (!e.target.closest('.status-wrapper') && activeDropdown) {
            activeDropdown.style.display = 'none';
            activeDropdown = null;
        }
    });
    // Menutup modal jika klik tombol (x) atau di luar area modal
    if(closeDetailModal) {
        closeDetailModal.onclick = () => { detailModal.style.display = "none"; }
    }
    window.onclick = (event) => {
        if (event.target == detailModal) { detailModal.style.display = "none"; }
    }
});
</script>
</body>
</html>