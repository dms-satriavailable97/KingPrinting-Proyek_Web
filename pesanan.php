<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("location: index.php");
    exit;
}
require_once 'config.php';

// === KONFIGURASI PAGINATION ===
$limit = 10; // Jumlah data per halaman
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

// 1. Hitung Total Data (Status Tertunda & Proses)
$sql_count = "SELECT COUNT(*) as total FROM pesanan WHERE status IN ('Tertunda', 'Proses')";
$result_count = $conn->query($sql_count);
$total_data = $result_count->fetch_assoc()['total'];
$total_pages = ceil($total_data / $limit);

// 2. Ambil Data dengan Limit & Offset
$sql = "SELECT id, nama_pemesan, produk, tanggal_masuk, status 
        FROM pesanan 
        WHERE status IN ('Tertunda', 'Proses') 
        ORDER BY FIELD(status, 'Proses', 'Tertunda'), tanggal_masuk DESC 
        LIMIT $limit OFFSET $offset";
$result = $conn->query($sql);
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
        /* --- CSS Tambahan --- */
        .status-wrapper { position: relative; display: inline-block; }
        .status.interactive { cursor: pointer; transition: background-color 0.2s, color 0.2s; }
        .status.interactive:hover { filter: brightness(1.1); }
        .status-dropdown { display: none; position: absolute; top: 100%; left: 0; background-color: white; border: 1px solid #ddd; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); min-width: 120px; list-style: none; padding: 5px 0; z-index: 10; margin-top: 5px; }
        .status-dropdown li { padding: 8px 15px; font-size: 0.9rem; cursor: pointer; transition: background-color 0.2s; }
        .status-dropdown li:hover { background-color: #f5f5f5; }

        /* === MODAL CENTERED FIX === */
        .custom-modal { 
            display: none; 
            position: fixed; 
            z-index: 1001; 
            left: 0; 
            top: 0; 
            width: 100%; 
            height: 100%; 
            overflow: hidden; /* Mencegah scroll background */
            background-color: rgba(0,0,0,0.6); 
            backdrop-filter: blur(5px); 
        }

        .custom-modal-content { 
            background-color: #fefefe; 
            padding: 25px 35px; 
            border-radius: 15px; 
            width: 90%; 
            
            /* POSISI FIXED DI TENGAH */
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            margin: 0;
            
            /* Scroll jika konten terlalu panjang */
            max-height: 90vh;
            overflow-y: auto;
            
            box-shadow: 0 10px 30px rgba(0,0,0,0.3); 
            animation: fadein 0.3s ease-out; 
        }

        @keyframes fadein { 
            from { opacity: 0; transform: translate(-50%, -55%); } 
            to { opacity: 1; transform: translate(-50%, -50%); } 
        }
        
        .detail-modal-content { max-width: 600px; }
        .detail-modal-header { padding-bottom: 15px; border-bottom: 1px solid #e0e0e0; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; }
        .detail-modal-header h2 { margin: 0; font-size: 1.6rem; color: var(--brand-dark-red); }
        .close-modal { color: #aaa; font-size: 28px; font-weight: bold; cursor: pointer; transition: color 0.2s; }
        .close-modal:hover { color: #333; }

        .detail-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px 25px; }
        .detail-item { font-size: 0.95rem; }
        .detail-item strong { display: block; color: #888; font-weight: 500; margin-bottom: 4px; font-size: 0.85rem; }
        .detail-item span { color: var(--text-primary); font-weight: 500; }
        .detail-item.full-width { grid-column: 1 / -1; }
        .detail-item textarea { width: 100%; height: 100px; background: #f9f9f9; border: 1px solid #eee; border-radius: 5px; padding: 10px; font-family: 'Poppins', sans-serif; resize: vertical; }
        
        .confirm-modal-content { max-width: 420px; text-align: center; }
        .confirm-icon { font-size: 3.5rem; color: #e74c3c; margin-bottom: 1rem; }
        .confirm-modal-content h3 { font-size: 1.5rem; color: #333; margin-bottom: 0.5rem; }
        .confirm-modal-content p { color: #666; margin-bottom: 1.5rem; line-height: 1.5; }
        .confirm-actions { display: flex; justify-content: center; gap: 1rem; }

        .action-cell { display: flex; gap: 0.8rem; align-items: center; justify-content: flex-start; }
        .action-btn.delete { background-color: #e74c3c; padding: 0.4rem 0.7rem; }
        .action-btn.delete:hover { background-color: #c0392b; }
        .action-btn.delete i { color: white; font-size: 0.8rem; }
        #ordersTable th:last-child { text-align: left; }
        
        /* Pagination */
        .pagination { display: flex; justify-content: center; margin-top: 20px; gap: 5px; }
        .pagination a { padding: 8px 12px; border: 1px solid #ddd; color: #333; text-decoration: none; border-radius: 5px; transition: 0.3s; }
        .pagination a.active { background-color: #9a2020; color: white; border-color: #9a2020; }
        .pagination a:hover:not(.active) { background-color: #f0f0f0; }
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
                        <input type="text" id="searchInput" placeholder="Cari Pesanan (Halaman ini)...">
                    </div>
                </div>
            </header>
            
            <section class="customers-table">
                <div class="table-header">
                    <h3>Semua Pesanan (Total: <?php echo $total_data; ?>)</h3>
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
                                echo "<tr data-id='" . $row['id'] . "'>";
                                echo "<td>#KP" . str_pad($row['id'], 3, '0', STR_PAD_LEFT) . "</td>";
                                echo "<td>" . htmlspecialchars($row['nama_pemesan']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['produk']) . "</td>";
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
                            echo "<tr><td colspan='6' style='text-align:center; padding: 2rem;'>Tidak ada pesanan aktif saat ini.</td></tr>";
                        }
                        $conn->close();
                        ?>
                    </tbody>
                </table>

                <!-- Pagination Links -->
                <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page - 1; ?>">&laquo; Prev</a>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="?page=<?php echo $i; ?>" class="<?php echo ($i == $page) ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>

                    <?php if ($page < $total_pages): ?>
                        <a href="?page=<?php echo $page + 1; ?>">Next &raquo;</a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

            </section>
        </main>
    </div>

    <!-- Modal Detail, Delete, dll -->
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
            <p>Anda yakin ingin menghapus pesanan ini secara permanen? Tindakan ini tidak dapat dibatalkan.</p>
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

        const searchInput = document.getElementById('searchInput');
        const tableRows = document.querySelectorAll('#ordersTable tbody tr');
        if (searchInput) {
            searchInput.addEventListener('keyup', function() {
                const searchTerm = this.value.toLowerCase().trim();
                tableRows.forEach(row => {
                    if (row.cells.length === 1 && row.cells[0].getAttribute('colspan')) return;
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

        function updateOrderStatus(orderId, newStatus, statusElement) {
            const originalStatus = statusElement.textContent;
            statusElement.textContent = '...';
            const formData = new FormData();
            formData.append('id', orderId);
            formData.append('status', newStatus);

            fetch('update-status.php', { method: 'POST', body: formData })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                } else {
                    alert('Gagal: ' + data.message);
                    statusElement.textContent = originalStatus;
                }
            }).catch(error => {
                alert('Terjadi kesalahan jaringan.');
                statusElement.textContent = originalStatus;
            });
        }

        function executeDelete(orderId) {
            const formData = new FormData();
            formData.append('id', orderId);
            fetch('delete-order.php', { method: 'POST', body: formData })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                } else {
                    alert('Gagal menghapus: ' + data.message);
                }
            })
            .catch(() => alert('Terjadi kesalahan jaringan.'))
            .finally(() => {
                confirmDeleteModal.style.display = 'none';
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
                    if (activeDropdown && activeDropdown !== dropdown) activeDropdown.style.display = 'none';
                    const currentStatus = statusTrigger.dataset.currentStatus;
                    let options = '';
                    if (currentStatus === 'Tertunda') options = `<li data-new-status="Proses">Proses</li><li data-new-status="Selesai">Selesai</li>`;
                    else if (currentStatus === 'Proses') options = `<li data-new-status="Selesai">Selesai</li><li data-new-status="Tertunda">Tertunda</li>`;
                    dropdown.innerHTML = options;
                    dropdown.style.display = (dropdown.style.display === 'block') ? 'none' : 'block';
                    activeDropdown = (dropdown.style.display === 'block') ? dropdown : null;
                    return;
                }

                if (dropdownItem) {
                    const newStatus = dropdownItem.dataset.newStatus;
                    const statusElement = dropdownItem.closest('.status-wrapper').querySelector('.status.interactive');
                    const orderId = dropdownItem.closest('tr').dataset.id;
                    updateOrderStatus(orderId, newStatus, statusElement);
                    if (activeDropdown) activeDropdown.style.display = 'none';
                    activeDropdown = null;
                    return;
                }

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
                                alert('Gagal: ' + data.message);
                            }
                        });
                    }
                    return;
                }

                if (deleteButton) {
                    orderIdToDelete = deleteButton.closest('tr').getAttribute('data-id');
                    confirmDeleteModal.style.display = 'block';
                    return;
                }
            });
        }

        closeButtons.forEach(btn => btn.onclick = () => btn.closest('.custom-modal').style.display = 'none');
        if(cancelDeleteBtn) cancelDeleteBtn.onclick = () => { confirmDeleteModal.style.display = 'none'; orderIdToDelete = null; };
        if(confirmDeleteBtn) confirmDeleteBtn.onclick = () => { if (orderIdToDelete) executeDelete(orderIdToDelete); };
        window.onclick = (event) => { if (event.target.classList.contains('custom-modal')) event.target.style.display = 'none'; };
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.status-wrapper') && activeDropdown) {
                activeDropdown.style.display = 'none';
                activeDropdown = null;
            }
        });
    });
    </script>
</body>
</html>