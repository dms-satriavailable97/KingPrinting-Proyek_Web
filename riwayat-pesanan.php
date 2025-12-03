<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("location: index.php");
    exit;
}
require_once 'config.php';

// === FILTER DATE ===
$filter_date = isset($_GET['date']) && !empty($_GET['date']) ? $_GET['date'] : date('Y-m-d');

// === KONFIGURASI PAGINATION ===
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;

// 1. Hitung Total Data
$sql_count = "SELECT COUNT(*) as total FROM pesanan WHERE status = 'Selesai' AND DATE(tanggal_masuk) = '$filter_date'";
$result_count = $conn->query($sql_count);
$total_data = $result_count->fetch_assoc()['total'];
$total_pages = ceil($total_data / $limit);

// === PERBAIKAN PAGINATION REDIRECT (SAMA SEPERTI PESANAN.PHP) ===
// Jika halaman saat ini lebih besar dari total halaman yang ada (dan total data > 0)
if ($page > $total_pages && $total_pages > 0) {
    $queryParams = $_GET;
    $queryParams['page'] = $total_pages;
    $newQueryString = http_build_query($queryParams);
    
    header("Location: ?" . $newQueryString);
    exit;
}
// Jika total data 0, reset ke page 1
if ($total_pages == 0) {
    $page = 1;
}

$offset = ($page - 1) * $limit;

// 2. Ambil Data
$sql = "SELECT id, nama_pemesan, produk, tanggal_masuk, status 
        FROM pesanan 
        WHERE status = 'Selesai' AND DATE(tanggal_masuk) = '$filter_date'
        ORDER BY tanggal_masuk DESC 
        LIMIT $limit OFFSET $offset";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Pesanan - King Printing</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="dashboard.css">
    <style>
        /* === CSS DISAMAKAN DENGAN pesanan.php === */
        
        /* Filter Header Styles */
        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #eee;
        }
        .header-title h3 { margin: 0; font-size: 1.3rem; color: #333; font-weight: 600; }
        .header-subtitle { font-size: 0.9rem; color: #888; margin-top: 4px; }

        /* Tombol Kalender */
        .date-picker-wrapper {
            position: relative;
            width: 42px;
            height: 40px;
            background-color: #9a2020;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background 0.3s;
            box-shadow: 0 2px 5px rgba(154, 32, 32, 0.2);
        }
        .date-picker-wrapper:hover { background-color: #7a1a1a; }
        .date-icon { color: white; font-size: 1.2rem; pointer-events: none; }
        .invisible-date-input {
            position: absolute; top: 0; left: 0; width: 100%; height: 100%;
            opacity: 0; cursor: pointer; z-index: 10;
        }
        .invisible-date-input::-webkit-calendar-picker-indicator {
            position: absolute; top: 0; left: 0; width: 100%; height: 100%;
            padding: 0; margin: 0; cursor: pointer; opacity: 0;
        }

        /* Table & Buttons Styles (PERSIS pesanan.php) */
        table td, table th { vertical-align: middle !important; }
        .action-cell { white-space: nowrap; }
        
        /* Ini style tombol delete yang sudah disesuaikan agar tidak mengecil/membesar aneh */
        .action-btn.delete { 
            background-color: #e74c3c !important; 
            color: white !important; 
            padding: 0.4rem 0.7rem; 
            border-radius: 5px; 
            border: none; 
            cursor: pointer; 
            margin-left: 10px; 
            transition: 0.2s; 
        }
        /* Tidak ada override font-size icon di sini agar ikut dashboard.css (0.8rem) */

        /* Modal Styles */
        .custom-modal { display: none; position: fixed; z-index: 1001; left: 0; top: 0; width: 100%; height: 100%; overflow: hidden; background-color: rgba(0,0,0,0.6); backdrop-filter: blur(5px); }
        .custom-modal-content { background-color: #fefefe; padding: 25px 35px; border-radius: 15px; width: 90%; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); margin: 0; max-height: 90vh; overflow-y: auto; animation: fadein 0.3s ease-out; }
        @keyframes fadein { from { opacity: 0; transform: translate(-50%, -55%); } to { opacity: 1; transform: translate(-50%, -50%); } }
        
        .detail-modal-content { max-width: 600px; }
        .detail-modal-header { padding-bottom: 15px; border-bottom: 1px solid #e0e0e0; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; }
        .detail-modal-header h2 { margin: 0; font-size: 1.6rem; color: #9a2020; }
        .close-modal { color: #aaa; font-size: 28px; font-weight: bold; cursor: pointer; }
        .detail-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px 25px; }
        .detail-item strong { display: block; color: #888; font-weight: 500; margin-bottom: 4px; font-size: 0.85rem; }
        .detail-item span { color: #333; font-weight: 500; }
        .detail-item.full-width { grid-column: 1 / -1; }
        .detail-item textarea { width: 100%; height: 100px; background: #f9f9f9; border: 1px solid #eee; border-radius: 5px; padding: 10px; font-family: 'Poppins', sans-serif; resize: vertical; }
        
        .confirm-modal-content { max-width: 420px; text-align: center; }
        .confirm-actions { display: flex; justify-content: center; gap: 1rem; }
        
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
                    <li><a href="pesanan.php" ><i class="fas fa-inbox"></i> Pesanan</a></li>
                    <li><a href="riwayat-pesanan.php" class="active"><i class="fas fa-history"></i> Riwayat Pesanan</a></li>
                    <li><a href="kelola-produk.php"><i class="fas fa-box-open"></i> Kelola Produk</a></li>
                    <li><a href="kelola-tampilan.php"><i class="fas fa-palette"></i> Kelola Tampilan</a></li>
                    <li><a href="kelola-akun.php"><i class="fas fa-users-cog"></i> Kelola Akun</a></li>
                </ul>
            </nav>
            <div class="sidebar-footer"><a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a></div>
        </aside>
        <main class="main-content">
            <header class="main-header">
                <div class="header-left"><h3>Riwayat Pesanan Selesai</h3></div>
                <div class="header-right">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" id="searchInput" placeholder="Cari Riwayat (Halaman ini)...">
                    </div>
                </div>
            </header>
            
            <section class="customers-table">
                <div class="table-header">
                    <div class="header-title">
                        <h3>
                            <?php 
                            if($filter_date == date('Y-m-d')) {
                                echo "Riwayat Selesai Hari Ini";
                            } else {
                                echo "Riwayat Selesai " . date('d M Y', strtotime($filter_date));
                            }
                            ?>
                        </h3>
                        <div class="header-subtitle">Total Data: <?php echo $total_data; ?></div>
                    </div>

                    <form id="filterForm" method="GET">
                        <div class="date-picker-wrapper" title="Pilih Tanggal">
                            <i class="fas fa-calendar-alt date-icon"></i>
                            <input type="date" name="date" class="invisible-date-input" 
                                   value="<?php echo $filter_date; ?>" 
                                   onchange="document.getElementById('filterForm').submit()">
                        </div>
                    </form>
                </div>

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
                        if ($result->num_rows > 0) {
                            while($row = $result->fetch_assoc()) {
                                echo "<tr data-id='" . $row['id'] . "'>";
                                echo "<td>#KP" . str_pad($row['id'], 3, '0', STR_PAD_LEFT) . "</td>";
                                echo "<td>" . htmlspecialchars($row['nama_pemesan']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['produk']) . "</td>";
                                echo "<td>" . date('d M Y, H:i', strtotime($row['tanggal_masuk'])) . "</td>";
                                echo "<td><span class='status completed'>" . htmlspecialchars($row['status']) . "</span></td>";
                                echo "<td class='action-cell'>
                                        <button class='action-btn detail'>Detail</button>
                                        <button class='action-btn delete'><i class='fas fa-trash'></i></button>
                                      </td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='6' style='text-align:center; padding: 2rem;'>Belum ada riwayat pesanan selesai pada tanggal ini.</td></tr>";
                        }
                        $conn->close();
                        ?>
                    </tbody>
                </table>

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

    <!-- Modal Details & Delete -->
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
            <p>Anda yakin ingin menghapus riwayat pesanan ini secara permanen?</p>
            <div class="confirm-actions">
                <button id="confirmDeleteBtn" class="action-btn delete">Ya, Hapus</button>
                <button id="cancelDeleteBtn" class="action-btn detail">Batal</button>
            </div>
        </div>
    </div>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const table = document.getElementById('historyTable');
        const detailModal = document.getElementById('detailModal');
        const confirmDeleteModal = document.getElementById('confirmDeleteModal');
        const closeButtons = document.querySelectorAll('.close-modal');
        const cancelDeleteBtn = document.getElementById('cancelDeleteBtn');
        const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
        let orderIdToDelete = null;

        const searchInput = document.getElementById('searchInput');
        const tableRows = document.querySelectorAll('#historyTable tbody tr');
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

        function executeDelete(orderId) {
            const formData = new FormData();
            formData.append('id', orderId);
            fetch('delete-order.php', { method: 'POST', body: formData })
            .then(response => response.json())
            .then(data => {
                if (data.success) window.location.reload();
                else alert('Gagal menghapus: ' + data.message);
            })
            .catch(() => alert('Terjadi kesalahan jaringan saat menghapus.'));
        }

        if(table) {
            table.addEventListener('click', function(e) {
                const detailButton = e.target.closest('.action-btn.detail');
                const deleteButton = e.target.closest('.action-btn.delete');
                
                if (detailButton) {
                    const orderId = detailButton.closest('tr').getAttribute('data-id');
                    if(orderId) {
                        fetch(`get-order-details.php?id=${orderId}`)
                        .then(response => response.json())
                        .then(data => {
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
                                    <div class="detail-item"><strong>Status</strong><span><span class="status completed">${order.status}</span></span></div>
                                    <div class="detail-item full-width"><strong>Catatan</strong><textarea readonly>${order.catatan || 'Tidak ada catatan.'}</textarea></div>
                                `;
                                detailModal.style.display = 'block';
                            }
                        });
                    }
                }

                if (deleteButton) {
                    orderIdToDelete = deleteButton.closest('tr').getAttribute('data-id');
                    confirmDeleteModal.style.display = 'block';
                }
            });
        }

        closeButtons.forEach(btn => btn.onclick = () => btn.closest('.custom-modal').style.display = 'none');
        cancelDeleteBtn.onclick = () => { confirmDeleteModal.style.display = 'none'; orderIdToDelete = null; };
        confirmDeleteBtn.onclick = () => { if (orderIdToDelete) executeDelete(orderIdToDelete); };
        window.onclick = (event) => { if (event.target.classList.contains('custom-modal')) event.target.style.display = 'none'; };
    });
    </script>
</body>
</html>