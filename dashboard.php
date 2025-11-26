<?php
// Memulai session
session_start();
// Include koneksi database
require_once 'config.php';

// Memeriksa apakah user sudah login. Jika belum, tendang ke halaman utama.
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("location: index.php");
    exit;
}

// Menghitung jumlah pesanan dengan status 'Tertunda'
$sql_tertunda = "SELECT COUNT(*) as count FROM pesanan WHERE status = 'Tertunda'";
$result_tertunda = $conn->query($sql_tertunda);
$jumlah_tertunda = $result_tertunda ? $result_tertunda->fetch_assoc()['count'] : 0;

// Menghitung jumlah pesanan dengan status 'Proses'
$sql_proses = "SELECT COUNT(*) as count FROM pesanan WHERE status = 'Proses'";
$result_proses = $conn->query($sql_proses);
$jumlah_proses = $result_proses ? $result_proses->fetch_assoc()['count'] : 0;

// Menghitung jumlah total pesanan
$sql_orders = "SELECT COUNT(*) as count FROM pesanan";
$result_orders = $conn->query($sql_orders);
$total_orders = $result_orders ? $result_orders->fetch_assoc()['count'] : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - King Advertising</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="dashboard.css">
    <style>
        /* === CSS UNTUK MODAL & STATUS INTERAKTIF (FIX) === */
        /* Modal Detail */
        .detail-modal { display: none; position: fixed; z-index: 1001; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.6); backdrop-filter: blur(5px); }
        .detail-modal-content { background-color: #fefefe; margin: 10% auto; padding: 25px 35px; border-radius: 15px; width: 80%; max-width: 600px; position: relative; box-shadow: 0 5px 15px rgba(0,0,0,0.3); animation: animatetop 0.4s; }
        @keyframes animatetop { from {top: -300px; opacity: 0} to {top: 0; opacity: 1} }
        .detail-modal-header { padding-bottom: 15px; border-bottom: 1px solid #e0e0e0; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; }
        .detail-modal-header h2 { margin: 0; font-size: 1.6rem; color: var(--brand-dark-red); }
        .close-detail-modal { color: #aaa; font-size: 28px; font-weight: bold; cursor: pointer; }
        .detail-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px 25px; }
        .detail-item { font-size: 0.95rem; }
        .detail-item strong { display: block; color: #888; font-weight: 500; margin-bottom: 4px; font-size: 0.85rem; }
        .detail-item span { color: var(--text-primary); font-weight: 500; }
        .detail-item.full-width { grid-column: 1 / -1; }
        .detail-item textarea { width: 100%; height: 100px; background: #f9f9f9; border: 1px solid #eee; border-radius: 5px; padding: 10px; font-family: 'Poppins', sans-serif; resize: vertical; }
        
        /* Dropdown Status */
        .status-wrapper { position: relative; display: inline-block; }
        .status.interactive { cursor: pointer; transition: background-color 0.2s, color 0.2s; }
        .status.interactive:hover { filter: brightness(1.1); }
        .status-dropdown { display: none; position: absolute; top: 100%; left: 0; background-color: white; border: 1px solid #ddd; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); min-width: 120px; list-style: none; padding: 5px 0; z-index: 10; margin-top: 5px; }
        .status-dropdown li { padding: 8px 15px; font-size: 0.9rem; cursor: pointer; transition: background-color 0.2s; }
        .status-dropdown li:hover { background-color: #f5f5f5; }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="sidebar-header"><i class="fas fa-crown"></i><h2>King Printing</h2></div>
            <nav class="sidebar-nav">
                <ul>
                    <li><a href="dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="pesanan.php"><i class="fas fa-inbox"></i> Pesanan</a></li>
                    <li><a href="riwayat-pesanan.php"><i class="fas fa-history"></i> Riwayat Pesanan</a></li>
                    <li><a href="kelola-produk.php"><i class="fas fa-box-open"></i> Kelola Produk</a></li>
                </ul>
            </nav>
            <div class="sidebar-footer"><a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a></div>
        </aside>
        <main class="main-content">
            <header class="main-header">
                <div class="header-left">
                    <h3>Hello, <span class="username"><?php echo ucfirst(htmlspecialchars($_SESSION['username'])); ?></span> 👋</h3>
                </div>
                <div class="header-right">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <!-- Input Search dengan ID searchInput -->
                        <input type="text" id="searchInput" placeholder="Cari Pesanan...">
                    </div>
                </div>
            </header>
            
            <section class="stats-cards">
                <div class="card">
                    <div class="card-icon" style="color: #4299e1;"><i class="fas fa-shopping-cart"></i></div>
                    <div class="card-info"><p>Total Orders</p><h4><?php echo $total_orders; ?></h4></div>
                </div>
                 <div class="card">
                    <div class="card-icon" style="color: #ffc700;"><i class="fas fa-clock"></i></div>
                    <div class="card-info"><p>Pesanan Tertunda</p><h4><?php echo $jumlah_tertunda; ?></h4></div>
                </div>
                <div class="card">
                    <div class="card-icon" style="color: #4299e1;"><i class="fas fa-sync-alt"></i></div>
                    <div class="card-info"><p>Pesanan Diproses</p><h4><?php echo $jumlah_proses; ?></h4></div>
                </div>
            </section>

            <section class="customers-table">
                <div class="table-header"><h3>Ringkasan Pesanan Aktif</h3></div>
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
                        $sql = "SELECT id, nama_pemesan, produk, tanggal_masuk, status FROM pesanan WHERE status IN ('Tertunda', 'Proses') ORDER BY FIELD(status, 'Proses', 'Tertunda'), tanggal_masuk DESC LIMIT 5";
                        $result = $conn->query($sql);

                        if ($result->num_rows > 0) {
                            while($row = $result->fetch_assoc()) {
                                $status = htmlspecialchars($row['status']);
                                $status_class = strtolower($status);
                                echo "<tr data-id='" . $row['id'] . "'>";
                                echo "<td>#KP" . str_pad($row['id'], 3, '0', STR_PAD_LEFT) . "</td>";
                                echo "<td>" . htmlspecialchars($row['nama_pemesan']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['produk']) . "</td>";
                                echo "<td>" . date('d M Y', strtotime($row['tanggal_masuk'])) . "</td>";
                                echo "<td>
                                        <div class='status-wrapper'>
                                            <span class='status interactive " . $status_class . "' data-current-status='" . $status . "'>" . $status . "</span>
                                            <ul class='status-dropdown'></ul>
                                        </div>
                                      </td>";
                                echo "<td><button class='action-btn detail'>Detail</button></td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='6' style='text-align:center; padding: 2rem;'>Tidak ada pesanan aktif untuk diproses.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
                 <div class="table-footer"><span>Menampilkan pesanan aktif terbaru</span></div>
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
            <div id="detailModalBody" class="detail-grid"><!-- Konten detail di-load di sini --></div>
        </div>
    </div>

    <div id="pilihProdukModal" class="admin-modal"><!-- ... Konten Modal Produk ... --></div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const table = document.getElementById('ordersTable');
    const detailModal = document.getElementById('detailModal');
    const closeDetailModal = document.querySelector('.close-detail-modal');
    let activeDropdown = null;

    // === LOGIKA PENCARIAN REAL-TIME ===
    const searchInput = document.getElementById('searchInput');
    // Pastikan kita mengambil baris dari tbody saja, bukan header
    const tableRows = document.querySelectorAll('#ordersTable tbody tr');

    if (searchInput) {
        searchInput.addEventListener('keyup', function() {
            const searchTerm = this.value.toLowerCase().trim();

            tableRows.forEach(row => {
                // Cek apakah baris ini adalah baris pesan "Tidak ada pesanan"
                if (row.cells.length === 1 && row.cells[0].getAttribute('colspan')) {
                    return; 
                }

                // Ambil data dari kolom ID (index 0), Nama (index 1), dan Produk (index 2)
                const textId = row.cells[0] ? row.cells[0].textContent.toLowerCase() : '';
                const textNama = row.cells[1] ? row.cells[1].textContent.toLowerCase() : '';
                const textProduk = row.cells[2] ? row.cells[2].textContent.toLowerCase() : '';

                // Cek apakah ada yang cocok
                if (textId.includes(searchTerm) || textNama.includes(searchTerm) || textProduk.includes(searchTerm)) {
                    row.style.display = ""; // Tampilkan kembali
                } else {
                    row.style.display = "none"; // Sembunyikan
                }
            });
        });
    }
    // ==================================

    // --- FUNGSI UNTUK MENGUBAH STATUS PESANAN ---
    function updateOrderStatus(orderId, newStatus) {
        const formData = new FormData();
        formData.append('id', orderId);
        formData.append('status', newStatus);

        fetch('update-status.php', { method: 'POST', body: formData })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Langsung reload halaman untuk update semua data (stats dan tabel)
                window.location.reload(); 
            } else {
                alert('Gagal: ' + data.message);
            }
        }).catch(() => alert('Terjadi kesalahan jaringan.'));
    }

    // --- EVENT LISTENER UTAMA PADA TABEL ---
    if(table) {
        table.addEventListener('click', function(e) {
            const statusTrigger = e.target.closest('.status.interactive');
            const dropdownItem = e.target.closest('.status-dropdown li');
            const detailButton = e.target.closest('.action-btn.detail');

            // --- Logika untuk membuka dropdown status ---
            if (statusTrigger) {
                const dropdown = statusTrigger.nextElementSibling;
                if (activeDropdown && activeDropdown !== dropdown) activeDropdown.style.display = 'none';
                
                const currentStatus = statusTrigger.dataset.currentStatus;
                let options = '';
                if (currentStatus === 'Tertunda') {
                    options = `<li data-new-status="Proses">Proses</li><li data-new-status="Selesai">Selesai</li>`;
                } else if (currentStatus === 'Proses') {
                    options = `<li data-new-status="Selesai">Selesai</li><li data-new-status="Tertunda">Tertunda</li>`;
                }
                dropdown.innerHTML = options;

                const isVisible = dropdown.style.display === 'block';
                dropdown.style.display = isVisible ? 'none' : 'block';
                activeDropdown = isVisible ? null : dropdown;
                return;
            }

            // --- Logika untuk memilih item dari dropdown ---
            if (dropdownItem) {
                const newStatus = dropdownItem.dataset.newStatus;
                const orderId = dropdownItem.closest('tr').dataset.id;
                updateOrderStatus(orderId, newStatus);
                if (activeDropdown) activeDropdown.style.display = 'none';
                activeDropdown = null;
                return;
            }

            // --- Logika untuk tombol "Detail" ---
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
                            const statusClass = order.status.toLowerCase();
                            modalBody.innerHTML = `
                                <div class="detail-item"><strong>Nama Pemesan</strong><span>${order.nama_pemesan}</span></div>
                                <div class="detail-item"><strong>Telepon</strong><span>${order.telepon}</span></div>
                                <div class="detail-item"><strong>Produk</strong><span>${order.produk}</span></div>
                                <div class="detail-item"><strong>Tanggal Masuk</strong><span>${order.tanggal_masuk}</span></div>
                                <div class="detail-item"><strong>Ukuran</strong><span>${order.ukuran || '-'} cm</span></div>
                                <div class="detail-item"><strong>Bahan</strong><span>${order.bahan || '-'}</span></div>
                                <div class="detail-item"><strong>Jumlah</strong><span>${order.jumlah} pcs</span></div>
                                <div class="detail-item"><strong>Status</strong><span class="status ${statusClass}">${order.status}</span></div>
                                <div class="detail-item full-width"><strong>Catatan</strong><textarea readonly>${order.catatan || 'Tidak ada catatan.'}</textarea></div>
                            `;
                            detailModal.style.display = 'block';
                        } else {
                            alert('Gagal memuat detail: ' + data.message);
                        }
                    }).catch(() => alert('Terjadi kesalahan jaringan.'));
                }
                return;
            }
        });
    }

    // --- EVENT LISTENER UNTUK MENUTUP DROPDOWN/MODAL ---
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.status-wrapper') && activeDropdown) {
            activeDropdown.style.display = 'none';
            activeDropdown = null;
        }
    });
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