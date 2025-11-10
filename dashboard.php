<?php
// Memulai session
session_start();

// Memeriksa apakah user sudah login. Jika belum, tendang ke halaman utama.
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("location: index.html");
    exit;
}
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
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="sidebar-header">
                <i class="fas fa-crown"></i>
                <h2>King Printing</h2>
            </div>
            <nav class="sidebar-nav">
                <ul>
                    <!-- LINK DIPERBARUI -->
                    <li><a href="dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="pesanan.php"><i class="fas fa-inbox"></i> Pesanan</a></li>
                    <li><a href="riwayat-pesanan.php"><i class="fas fa-history"></i> Riwayat Pesanan</a></li>
                </ul>
            </nav>
            <div class="sidebar-footer">
                <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </aside>
        <main class="main-content">
            <header class="main-header">
                <div class="header-left">
                    <h3>Hello, <span class="username"><?php echo ucfirst(htmlspecialchars($_SESSION['username'])); ?></span> 👋</h3>
                </div>
                <div class="header-right">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" placeholder="Cari Pesanan...">
                    </div>
                </div>
            </header>
            
            <section class="stats-cards">
                <div class="card">
                    <div class="card-icon" style="color: #38a169;"><i class="fas fa-users"></i></div>
                    <div class="card-info">
                        <p>Total Customers</p>
                        <h4>5,423</h4>
                        <span><i class="fas fa-arrow-up"></i> 16% this month</span>
                    </div>
                </div>
                <div class="card">
                    <div class="card-icon" style="color: #4299e1;"><i class="fas fa-shopping-cart"></i></div>
                    <div class="card-info">
                        <p>Total Orders</p>
                        <h4>1,893</h4>
                        <span class="down"><i class="fas fa-arrow-down"></i> 1% this month</span>
                    </div>
                </div>
                 <div class="card">
                    <div class="card-icon" style="color: #d53f8c;"><i class="fas fa-spinner"></i></div>
                    <div class="card-info">
                        <p>Pesanan Diproses</p>
                        <h4>189</h4>
                    </div>
                </div>
            </section>

            <section class="customers-table">
                <div class="table-header">
                    <h3>Ringkasan Pesanan Terbaru</h3>
                    <div class="table-actions">
                         <span>Urutkan: Terbaru <i class="fas fa-chevron-down"></i></span>
                    </div>
                </div>
                <table>
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
                        <tr>
                            <td>#KP001</td>
                            <td>Jane Cooper</td>
                            <td>Spanduk & Banner</td>
                            <td>10 Nov 2025</td>
                            <td><span class="status new">Baru</span></td>
                            <td><button class="action-btn process">Proses</button></td>
                        </tr>
                        <tr>
                            <td>#KP003</td>
                            <td>Ronald Richards</td>
                            <td>Brosur & Flyer</td>
                            <td>09 Nov 2025</td>
                            <td><span class="status processing">Diproses</span></td>
                            <td><button class="action-btn complete">Selesai</button></td>
                        </tr>
                        <tr>
                            <td>#KP004</td>
                            <td>Marvin McKinney</td>
                            <td>Kemasan & Dus</td>
                            <td>08 Nov 2025</td>
                            <td><span class="status completed">Selesai</span></td>
                            <td><button class="action-btn detail">Detail</button></td>
                        </tr>
                    </tbody>
                </table>
                 <div class="table-footer">
                    <span>Menampilkan ringkasan pesanan</span>
                 </div>
            </section>
        </main>
    </div>
</body>
</html>