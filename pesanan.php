<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("location: index.php");
    exit;
}
require_once 'config.php';

// === KONFIGURASI FILTER ===
$filter_date = isset($_GET['date']) && !empty($_GET['date']) ? $_GET['date'] : date('Y-m-d');
$filter_status = isset($_GET['status_filter']) && !empty($_GET['status_filter']) ? $_GET['status_filter'] : 'all';

// === PAGINATION CONFIG ===
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;

// === QUERY WHERE CLAUSE ===
$where_clauses = ["DATE(tanggal_masuk) = '$filter_date'"];

if ($filter_status === 'Tertunda') $where_clauses[] = "status = 'Tertunda'";
elseif ($filter_status === 'Proses') $where_clauses[] = "status = 'Proses'";
elseif ($filter_status === 'all') $where_clauses[] = "status IN ('Tertunda', 'Proses')";

$where_sql = implode(' AND ', $where_clauses);

// Hitung Total Data
$sql_count = "SELECT COUNT(*) as total FROM pesanan WHERE $where_sql";
$result_count = $conn->query($sql_count);
$total_data = $result_count->fetch_assoc()['total'];
$total_pages = ceil($total_data / $limit);

// === PAGINATION REDIRECT ===
if ($page > $total_pages && $total_pages > 0) {
    $queryParams = $_GET;
    $queryParams['page'] = $total_pages;
    $newQueryString = http_build_query($queryParams);
    header("Location: ?" . $newQueryString);
    exit;
}
if ($total_pages == 0) {
    $page = 1;
}

$offset = ($page - 1) * $limit;

// Ambil Data
$sql = "SELECT id, nama_pemesan, produk, tanggal_masuk, status, telepon, ukuran, bahan, jumlah, catatan 
        FROM pesanan 
        WHERE $where_sql 
        ORDER BY tanggal_masuk DESC 
        LIMIT $limit OFFSET $offset";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Pesanan - King Printing</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="dashboard.css">
    <style>
        /* Style tambahan */
        .table-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; padding-bottom: 1rem;}
        .header-title h3 { margin: 0; font-size: 1.3rem; color: #333; font-weight: 600; }
        .header-subtitle { font-size: 0.9rem; color: #888; margin-top: 4px; }
        
        .filter-group { display: flex; gap: 10px; align-items: center; }
        .custom-select { padding: 0 15px; border: 1px solid #e0e0e0; border-radius: 8px; background-color: #fff; font-family: 'Poppins', sans-serif; color: #555; cursor: pointer; font-size: 0.9rem; height: 40px; outline: none; }
        .custom-select:hover { border-color: #bbb; }

        .date-picker-wrapper { position: relative; width: 42px; height: 40px; background-color: #9a2020; border-radius: 8px; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: background 0.3s; box-shadow: 0 2px 5px rgba(154, 32, 32, 0.2); }
        .date-picker-wrapper:hover { background-color: #7a1a1a; }
        .date-icon { color: white; font-size: 1.2rem; pointer-events: none; }
        .invisible-date-input { position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer; z-index: 10; }
        .invisible-date-input::-webkit-calendar-picker-indicator { position: absolute; top: 0; left: 0; width: 100%; height: 100%; padding: 0; margin: 0; cursor: pointer; opacity: 0; }

        /* Dropdown Status */
        .status-wrapper { position: relative; display: inline-block; }
        .status.interactive { cursor: pointer; transition: opacity 0.2s; }
        .status.interactive:hover { opacity: 0.8; }
        .status-dropdown { display: none; position: absolute; top: 100%; left: 0; background-color: white; border: 1px solid #ddd; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); min-width: 120px; list-style: none; padding: 5px 0; z-index: 10; margin-top: 5px; }
        .status-dropdown li { padding: 8px 15px; font-size: 0.9rem; cursor: pointer; }
        .status-dropdown li:hover { background-color: #f5f5f5; }

        /* Modal Styles */
        .custom-modal { display: none; position: fixed; z-index: 1001; left: 0; top: 0; width: 100%; height: 100%; overflow: hidden; background-color: rgba(0,0,0,0.6); backdrop-filter: blur(5px); }
        .custom-modal-content { background-color: #fefefe; padding: 25px 35px; border-radius: 15px; width: 90%; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); max-height: 90vh; overflow-y: auto; animation: fadein 0.3s ease-out; }
        @keyframes fadein { from { opacity: 0; transform: translate(-50%, -55%); } to { opacity: 1; transform: translate(-50%, -50%); } }
        .detail-modal-content { max-width: 600px; }
        .detail-modal-header { padding-bottom: 15px; border-bottom: 1px solid #e0e0e0; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; }
        .detail-modal-header h2 { margin: 0; font-size: 1.6rem; color: #9a2020; }
        .close-modal { color: #aaa; font-size: 28px; font-weight: bold; cursor: pointer; }
        .detail-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px 25px; }
        .detail-item strong { display: block; color: #888; font-weight: 500; margin-bottom: 4px; font-size: 0.85rem; }
        .detail-item span { color: #333; font-weight: 500; }
        .detail-item.full-width { grid-column: 1 / -1; }
        .detail-item textarea { width: 100%; height: 100px; background: #f9f9f9; border: 1px solid #eee; border-radius: 5px; padding: 10px; resize: vertical; font-family: 'Poppins', sans-serif; }
        .confirm-modal-content { max-width: 420px; text-align: center; }
        .confirm-actions { display: flex; justify-content: center; gap: 1rem; }
        
        /* Pagination */
        .pagination { display: flex; justify-content: center; margin-top: 20px; gap: 5px; align-items: center; }
        .pagination a { padding: 8px 12px; border: 1px solid #ddd; color: #333; text-decoration: none; border-radius: 5px; }
        .pagination a.active { background-color: #9a2020; color: white; border-color: #9a2020; }
        .pagination span.dots { padding: 0 5px; color: #888; }
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
                    <li><a href="kelola-produk.php"><i class="fas fa-box-open"></i> Kelola Produk</a></li>
                    <li><a href="kelola-tampilan.php"><i class="fas fa-palette"></i> Kelola Tampilan</a></li>
                    <li><a href="kelola-akun.php"><i class="fas fa-users-cog"></i> Kelola Akun</a></li>
                </ul>
            </nav>
            <div class="sidebar-footer"><a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a></div>
        </aside>
        <main class="main-content">
            <header class="main-header">
                <div class="header-left"><h3>Pesanan Aktif</h3></div>
                <div class="header-right">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" id="searchInput" placeholder="Cari Pesanan...">
                    </div>
                </div>
            </header>
            
            <section class="customers-table">
                <div class="table-header">
                    <div class="header-title">
                        <h3>
                            <?php 
                            if($filter_date == date('Y-m-d')) {
                                echo "Pesanan Hari Ini";
                            } else {
                                echo "Pesanan Tanggal " . date('d M Y', strtotime($filter_date));
                            }
                            ?>
                        </h3>
                        <div class="header-subtitle">Total Pesanan: <?php echo $total_data; ?></div>
                    </div>
                    
                    <form id="filterForm" method="GET" class="filter-group">
                        <select name="status_filter" class="custom-select" onchange="document.getElementById('filterForm').submit()">
                            <option value="all" <?php echo $filter_status == 'all' ? 'selected' : ''; ?>>Semua Status</option>
                            <option value="Tertunda" <?php echo $filter_status == 'Tertunda' ? 'selected' : ''; ?>>Tertunda</option>
                            <option value="Proses" <?php echo $filter_status == 'Proses' ? 'selected' : ''; ?>>Proses</option>
                        </select>

                        <div class="date-picker-wrapper" title="Pilih Tanggal">
                            <i class="fas fa-calendar-alt date-icon"></i>
                            <input type="date" name="date" class="invisible-date-input" 
                                   value="<?php echo $filter_date; ?>" 
                                   onchange="document.getElementById('filterForm').submit()">
                        </div>
                    </form>
                </div>

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
                                echo "<td>" . date('d M Y, H:i', strtotime($row['tanggal_masuk'])) . "</td>";
                                echo "<td>
                                        <div class='status-wrapper'>
                                            <span class='status interactive " . $status_class . "' data-current-status='" . $status . "'>" . $status . "</span>
                                            <ul class='status-dropdown'></ul>
                                        </div>
                                      </td>";
                                echo "<td class='action-cell'>
                                        <button class='action-btn detail'>Detail</button>
                                        <button class='action-btn delete'><i class='fas fa-trash'></i></button>
                                      </td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='6' style='text-align:center; padding: 3rem; color: #888;'>
                                    <i class='fas fa-box-open' style='font-size: 2.5rem; margin-bottom: 15px; color: #ddd; display:block;'></i>
                                    Tidak ada pesanan untuk filter ini.
                                  </td></tr>";
                        }
                        $conn->close();
                        ?>
                    </tbody>
                </table>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php
                    $queryParams = $_GET;
                    unset($queryParams['page']);
                    $queryString = http_build_query($queryParams);
                    $range = 2;
                    $start = max(1, $page - $range);
                    $end   = min($total_pages, $page + $range);
                    ?>
                    <?php if ($page > 1): ?><a href="?page=<?php echo $page - 1; ?>&<?php echo $queryString; ?>">&laquo; Prev</a><?php endif; ?>
                    <?php if ($start > 1): ?><a href="?page=1&<?php echo $queryString; ?>">1</a><?php if ($start > 2): ?><span class="dots">...</span><?php endif; ?><?php endif; ?>
                    <?php for ($i = $start; $i <= $end; $i++): ?><a href="?page=<?php echo $i; ?>&<?php echo $queryString; ?>" class="<?php echo ($i == $page) ? 'active' : ''; ?>"><?php echo $i; ?></a><?php endfor; ?>
                    <?php if ($end < $total_pages): ?><?php if ($end < $total_pages - 1): ?><span class="dots">...</span><?php endif; ?><a href="?page=<?php echo $total_pages; ?>&<?php echo $queryString; ?>"><?php echo $total_pages; ?></a><?php endif; ?>
                    <?php if ($page < $total_pages): ?><a href="?page=<?php echo $page + 1; ?>&<?php echo $queryString; ?>">Next &raquo;</a><?php endif; ?>
                </div>
                <?php endif; ?>
            </section>
        </main>
    </div>

    <!-- Modal & JS -->
    <div id="detailModal" class="custom-modal">
        <div class="custom-modal-content detail-modal-content">
            <div class="detail-modal-header">
                <h2 id="detailModalTitle">Detail Pesanan</h2>
                <span class="close-modal">&times;</span>
            </div>
            <div id="detailModalBody" class="detail-grid"></div>
        </div>
    </div>
    <div id="confirmDeleteModal" class="custom-modal">
        <div class="custom-modal-content confirm-modal-content">
            <div class="confirm-icon"><i class="fas fa-exclamation-triangle"></i></div>
            <h3>Konfirmasi Hapus</h3>
            <p>Anda yakin ingin menghapus pesanan ini secara permanen?</p>
            <div class="confirm-actions">
                <button id="confirmDeleteBtn" class="action-btn delete">Ya, Hapus</button>
                <button id="cancelDeleteBtn" class="action-btn detail">Batal</button>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const table = document.getElementById('ordersTable');
        const detailModal = document.getElementById('detailModal');
        const confirmDeleteModal = document.getElementById('confirmDeleteModal');
        const closeButtons = document.querySelectorAll('.close-modal');
        const cancelDeleteBtn = document.getElementById('cancelDeleteBtn');
        const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
        let activeDropdown = null;
        let orderIdToDelete = null;
        let rowToDelete = null; 

        const searchInput = document.getElementById('searchInput');
        const tableRows = document.querySelectorAll('#ordersTable tbody tr');
        
        // === Ambil Filter yang sedang Aktif ===
        const filterSelect = document.querySelector('select[name="status_filter"]');
        const currentFilter = filterSelect ? filterSelect.value : 'all'; // 'all', 'Tertunda', 'Proses'

        if (searchInput) {
            searchInput.addEventListener('keyup', function() {
                const searchTerm = this.value.toLowerCase().trim();
                tableRows.forEach(row => {
                    if (row.cells.length === 1 && row.cells[0].getAttribute('colspan')) return;
                    const textId = row.cells[0] ? row.cells[0].textContent.toLowerCase() : '';
                    const textNama = row.cells[1] ? row.cells[1].textContent.toLowerCase() : '';
                    const textProduk = row.cells[2] ? row.cells[2].textContent.toLowerCase() : '';
                    if (textId.includes(searchTerm) || textNama.includes(searchTerm) || textProduk.includes(searchTerm)) row.style.display = "";
                    else row.style.display = "none";
                });
            });
        }

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
                    // === LOGIKA CERDAS PENGHAPUSAN BARIS (Tanpa Reload) ===
                    let shouldRemove = false;

                    // 1. Jika status jadi "Selesai", HAPUS (karena ini tabel Active Orders)
                    if (newStatus === 'Selesai') {
                        shouldRemove = true;
                    }
                    // 2. Jika Filter bukan "Semua", dan status baru TIDAK sama dengan filter
                    // Contoh: Filter "Tertunda", ubah jadi "Proses" -> Hapus
                    else if (currentFilter !== 'all' && currentFilter !== newStatus) {
                        shouldRemove = true;
                    }

                    if (shouldRemove) {
                        // Ambil baris tabel
                        const row = statusElement.closest('tr');
                        if(row) {
                            // Efek fade out
                            row.style.transition = 'opacity 0.5s';
                            row.style.opacity = '0';
                            
                            // Hapus setelah 0.5 detik
                            setTimeout(() => {
                                row.remove();
                                // Opsional: Cek jika tabel kosong
                                const tbody = document.querySelector('#ordersTable tbody');
                                if (tbody && tbody.rows.length === 0) {
                                    tbody.innerHTML = `<tr><td colspan='6' style='text-align:center; padding: 3rem; color: #888;'><i class='fas fa-box-open' style='font-size: 2.5rem; margin-bottom: 15px; color: #ddd; display:block;'></i>Tidak ada pesanan untuk filter ini.</td></tr>`;
                                }
                            }, 500);
                        }
                    } else {
                        // HANYA UPDATE TAMPILAN (Jika tidak dihapus)
                        statusElement.textContent = newStatus;
                        statusElement.classList.remove('tertunda', 'proses', 'selesai', 'completed');
                        statusElement.classList.add(newStatus.toLowerCase());
                        statusElement.setAttribute('data-current-status', newStatus);
                    }
                    
                    if (activeDropdown) {
                        activeDropdown.style.display = 'none';
                        activeDropdown = null;
                    }
                }
                else { 
                    alert('Gagal: ' + data.message); 
                    statusElement.textContent = originalStatus; 
                }
            })
            .catch(err => {
                statusElement.style.opacity = '1';
                statusElement.textContent = originalStatus;
                alert('Terjadi kesalahan jaringan.');
            });
        }

        function executeDelete(orderId) {
            const formData = new FormData();
            formData.append('id', orderId);
            fetch('delete-order.php', { method: 'POST', body: formData })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    confirmDeleteModal.style.display = 'none';
                    if (rowToDelete) {
                        rowToDelete.style.transition = 'opacity 0.5s';
                        rowToDelete.style.opacity = '0';
                        setTimeout(() => rowToDelete.remove(), 500);
                    }
                }
                else alert('Gagal menghapus: ' + data.message);
            });
        }

        if(table) {
            table.addEventListener('click', function(e) {
                const statusTrigger = e.target.closest('.status.interactive');
                const dropdownItem = e.target.closest('.status-dropdown li');
                const detailButton = e.target.closest('.action-btn.detail');
                const deleteButton = e.target.closest('.action-btn.delete');

                if (statusTrigger) {
                    const dropdown = statusTrigger.nextElementSibling;
                    const currentStatus = statusTrigger.getAttribute('data-current-status');
                    let options = '';

                    if (currentStatus === 'Tertunda') {
                        options = `<li data-new-status="Proses">Proses</li>`;
                    } else if (currentStatus === 'Proses') {
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
                    fetch(`get-order-details.php?id=${orderId}`).then(r => r.json()).then(data => {
                        if(data.success) {
                            const order = data.order;
                            document.getElementById('detailModalTitle').textContent = `Detail Pesanan #${order.id_formatted}`;
                            document.getElementById('detailModalBody').innerHTML = `
                                <div class="detail-item"><strong>Nama Pemesan</strong><span>${order.nama_pemesan}</span></div>
                                <div class="detail-item"><strong>Telepon</strong><span>${order.telepon}</span></div>
                                <div class="detail-item"><strong>Produk</strong><span>${order.produk}</span></div>
                                <div class="detail-item"><strong>Tanggal Masuk</strong><span>${order.tanggal_masuk}</span></div>
                                <div class="detail-item"><strong>Ukuran</strong><span>${order.ukuran || '-'} cm</span></div>
                                <div class="detail-item"><strong>Bahan</strong><span>${order.bahan || '-'}</span></div>
                                <div class="detail-item"><strong>Jumlah</strong><span>${order.jumlah} pcs</span></div>
                                <div class="detail-item"><strong>Status</strong><span class="status ${order.status.toLowerCase()}">${order.status}</span></div>
                                <div class="detail-item full-width"><strong>Catatan</strong><textarea readonly>${order.catatan || 'Tidak ada catatan.'}</textarea></div>`;
                            detailModal.style.display = 'block';
                        }
                    });
                } 
                else if (deleteButton) {
                    rowToDelete = deleteButton.closest('tr');
                    orderIdToDelete = rowToDelete.getAttribute('data-id');
                    confirmDeleteModal.style.display = 'block';
                }
            });
        }

        closeButtons.forEach(btn => btn.onclick = () => btn.closest('.custom-modal').style.display = 'none');
        if(cancelDeleteBtn) cancelDeleteBtn.onclick = () => confirmDeleteModal.style.display = 'none';
        if(confirmDeleteBtn) confirmDeleteBtn.onclick = () => { if(orderIdToDelete) executeDelete(orderIdToDelete); };
        window.onclick = (e) => { if(e.target.classList.contains('custom-modal')) e.target.style.display = 'none'; };
        document.addEventListener('click', (e) => { if(!e.target.closest('.status-wrapper') && activeDropdown) { activeDropdown.style.display = 'none'; activeDropdown = null; }});
    });
    </script>
</body>
</html>