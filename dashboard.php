<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("location: index.php");
    exit;
}

// Hitung Statistik
$sql_tertunda = "SELECT COUNT(*) as count FROM pesanan WHERE status = 'Tertunda'";
$result_tertunda = $conn->query($sql_tertunda);
$jumlah_tertunda = $result_tertunda ? $result_tertunda->fetch_assoc()['count'] : 0;

$sql_proses = "SELECT COUNT(*) as count FROM pesanan WHERE status = 'Proses'";
$result_proses = $conn->query($sql_proses);
$jumlah_proses = $result_proses ? $result_proses->fetch_assoc()['count'] : 0;

$sql_orders = "SELECT COUNT(*) as count FROM pesanan";
$result_orders = $conn->query($sql_orders);
$total_orders = $result_orders ? $result_orders->fetch_assoc()['count'] : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - King Printing</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="dashboard.css">
    <style>
        /* Modal Styles */
        .detail-modal { display: none; position: fixed; z-index: 1001; left: 0; top: 0; width: 100%; height: 100%; overflow: hidden; background-color: rgba(0,0,0,0.6); backdrop-filter: blur(5px); }
        .detail-modal-content { background-color: #fefefe; padding: 25px 35px; border-radius: 15px; width: 90%; max-width: 600px; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); margin: 0; max-height: 90vh; overflow-y: auto; box-shadow: 0 10px 30px rgba(0,0,0,0.3); animation: fadein 0.3s ease-out; }
        @keyframes fadein { from { opacity: 0; transform: translate(-50%, -55%); } to { opacity: 1; transform: translate(-50%, -50%); } }
        .detail-modal-header { padding-bottom: 15px; border-bottom: 1px solid #e0e0e0; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; }
        .detail-modal-header h2 { margin: 0; font-size: 1.6rem; color: var(--brand-dark-red); }
        .close-detail-modal { color: #aaa; font-size: 28px; font-weight: bold; cursor: pointer; }
        .detail-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px 25px; }
        .detail-item strong { display: block; color: #888; font-weight: 500; margin-bottom: 4px; font-size: 0.85rem; }
        .detail-item span { color: var(--text-primary); font-weight: 500; }
        .detail-item.full-width { grid-column: 1 / -1; }
        .detail-item textarea { width: 100%; height: 100px; background: #f9f9f9; border: 1px solid #eee; border-radius: 5px; padding: 10px; font-family: 'Poppins', sans-serif; resize: vertical; }
        
        /* Status Dropdown */
        .status-wrapper { position: relative; display: inline-block; }
        .status.interactive { cursor: pointer; transition: opacity 0.2s; }
        .status.interactive:hover { opacity: 0.8; }
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
                    <li><a href="kelola-tampilan.php"><i class="fas fa-palette"></i> Kelola Tampilan</a></li>
                    <li><a href="kelola-akun.php"><i class="fas fa-users-cog"></i> Kelola Akun</a></li>
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
                        <input type="text" id="searchInput" placeholder="Cari Pesanan (Halaman ini)...">
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
                    <div class="card-info"><p>Pesanan Tertunda</p><h4 id="countTertunda"><?php echo $jumlah_tertunda; ?></h4></div>
                </div>
                <div class="card">
                    <div class="card-icon" style="color: #4299e1;"><i class="fas fa-sync-alt"></i></div>
                    <div class="card-info"><p>Pesanan Diproses</p><h4 id="countProses"><?php echo $jumlah_proses; ?></h4></div>
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
                        $sql = "SELECT id, nama_pemesan, produk, tanggal_masuk, status, telepon, ukuran, bahan, jumlah, catatan 
                                FROM pesanan 
                                WHERE status IN ('Tertunda', 'Proses') 
                                ORDER BY tanggal_masuk DESC LIMIT 5";
                        $result = $conn->query($sql);

                        if ($result->num_rows > 0) {
                            while($row = $result->fetch_assoc()) {
                                $status = htmlspecialchars($row['status']);
                                $status_class = strtolower($status);
                                
                                $nama_aman = htmlspecialchars($row['nama_pemesan']);
                                $produk_aman = htmlspecialchars($row['produk']);

                                echo "<tr data-id='" . $row['id'] . "'>";
                                echo "<td>#KP" . str_pad($row['id'], 3, '0', STR_PAD_LEFT) . "</td>";
                                echo "<td title='$nama_aman'>" . $nama_aman . "</td>";
                                echo "<td title='$produk_aman'>" . $produk_aman . "</td>";
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
                 <div class="table-footer"><span>Menampilkan 5 pesanan aktif terbaru</span></div>
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
            <div id="detailModalBody" class="detail-grid"></div>
        </div>
    </div>

    <script>
    document.addEventListener("DOMContentLoaded", function() {
        const table = document.getElementById('ordersTable');
        const detailModal = document.getElementById('detailModal');
        const closeDetailModal = document.querySelector('.close-detail-modal');
        let activeDropdown = null;

        // Pencarian Client-side
        const searchInput = document.getElementById('searchInput');
        const tableRows = document.querySelectorAll('#ordersTable tbody tr');
        if (searchInput) {
            searchInput.addEventListener('keyup', function() {
                const searchTerm = this.value.toLowerCase().trim();
                tableRows.forEach(row => {
                    if (row.cells.length === 1 && row.cells[0].getAttribute('colspan')) { return; }
                    const textId = row.cells[0] ? row.cells[0].textContent.toLowerCase() : '';
                    const textNama = row.cells[1] ? row.cells[1].textContent.toLowerCase() : '';
                    const textProduk = row.cells[2] ? row.cells[2].textContent.toLowerCase() : '';
                    if (textId.includes(searchTerm) || textNama.includes(searchTerm) || textProduk.includes(searchTerm)) {
                        row.style.display = ""; 
                    } else {
                        row.style.display = "none"; 
                    }
                });
            });
        }

        // UPDATE STATUS (AJAX)
        function updateOrderStatus(orderId, newStatus, statusElement) {
            const originalStatus = statusElement.textContent;
            statusElement.style.opacity = '0.5';

            const formData = new FormData();
            formData.append('id', orderId);
            formData.append('status', newStatus);
            
            fetch('update-status.php', { method: 'POST', body: formData })
            .then(response => response.json())
            .then(data => {
                statusElement.style.opacity = '1';
                
                if (data.success) {
                    // 1. Jika jadi "Selesai", hapus dari dashboard
                    if (newStatus === 'Selesai') {
                        const row = statusElement.closest('tr');
                        if (row) {
                            row.style.transition = 'opacity 0.5s';
                            row.style.opacity = '0';
                            setTimeout(() => {
                                row.remove();
                                const tbody = document.querySelector('#ordersTable tbody');
                                if (tbody.rows.length === 0) {
                                    tbody.innerHTML = "<tr><td colspan='6' style='text-align:center; padding: 2rem;'>Tidak ada pesanan aktif untuk diproses.</td></tr>";
                                }
                            }, 500);
                        }
                    } 
                    // 2. Jika status lain, update tampilan
                    else {
                        statusElement.textContent = newStatus;
                        statusElement.classList.remove('tertunda', 'proses', 'selesai', 'completed');
                        statusElement.classList.add(newStatus.toLowerCase());
                        statusElement.setAttribute('data-current-status', newStatus);
                    }

                    updateCounters(originalStatus, newStatus);

                    if (activeDropdown) {
                        activeDropdown.style.display = 'none';
                        activeDropdown = null;
                    }
                } else {
                    alert('Gagal: ' + data.message);
                    statusElement.textContent = originalStatus;
                }
            }).catch(() => {
                statusElement.style.opacity = '1';
                statusElement.textContent = originalStatus;
                alert('Terjadi kesalahan jaringan.');
            });
        }

        function updateCounters(oldStatus, newStatus) {
            const elTertunda = document.getElementById('countTertunda');
            const elProses = document.getElementById('countProses');
            
            let valTertunda = parseInt(elTertunda.innerText);
            let valProses = parseInt(elProses.innerText);

            if (oldStatus === 'Tertunda') valTertunda--;
            else if (oldStatus === 'Proses') valProses--;

            if (newStatus === 'Tertunda') valTertunda++;
            else if (newStatus === 'Proses') valProses++;

            elTertunda.innerText = valTertunda;
            elProses.innerText = valProses;
        }

        if(table) {
            table.addEventListener('click', function(e) {
                const statusTrigger = e.target.closest('.status.interactive');
                const dropdownItem = e.target.closest('.status-dropdown li');
                const detailButton = e.target.closest('.action-btn.detail');

                if (statusTrigger) {
                    const dropdown = statusTrigger.nextElementSibling;
                    const currentStatus = statusTrigger.getAttribute('data-current-status');
                    let options = '';
                    
                    // === LOGIKA PERBAIKAN DI SINI ===
                    // Tertunda hanya bisa ke Proses
                    if (currentStatus === 'Tertunda') {
                        options = `<li data-new-status="Proses">Proses</li>`;
                    } 
                    // Proses bisa ke Selesai atau Balik ke Tertunda
                    else if (currentStatus === 'Proses') {
                        options = `<li data-new-status="Selesai">Selesai</li><li data-new-status="Tertunda">Tertunda</li>`;
                    }
                    
                    dropdown.innerHTML = options;
                    
                    if (activeDropdown && activeDropdown !== dropdown) activeDropdown.style.display = 'none';
                    dropdown.style.display = (dropdown.style.display === 'block') ? 'none' : 'block';
                    activeDropdown = (dropdown.style.display === 'block') ? dropdown : null;
                }
                else if (dropdownItem) {
                    const newStatus = dropdownItem.dataset.newStatus;
                    const statusElement = dropdownItem.closest('.status-wrapper').querySelector('.status.interactive');
                    const orderId = dropdownItem.closest('tr').dataset.id;
                    updateOrderStatus(orderId, newStatus, statusElement);
                }
                else if (detailButton) {
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
                        });
                    }
                }
            });
        }

        document.addEventListener('click', function(e) {
            if (!e.target.closest('.status-wrapper') && activeDropdown) {
                activeDropdown.style.display = 'none';
                activeDropdown = null;
            }
        });
        
        if(closeDetailModal) closeDetailModal.onclick = () => { detailModal.style.display = "none"; }
        window.onclick = (event) => { if (event.target == detailModal) detailModal.style.display = "none"; }
    });
    </script>
</body>
</html>