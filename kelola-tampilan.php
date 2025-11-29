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
    <title>Kelola Tampilan - King Advertising</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="dashboard.css">
    <style>
        /* === CSS UNTUK CUSTOMIZER CARDS === */
        .customizer-cards {
            margin-bottom: 2rem;
        }

        .customizer-cards .table-header {
            margin-bottom: 1.5rem;
        }

        .customizer-cards .table-header p {
            color: #666;
            font-size: 0.9rem;
            margin-top: 0.5rem;
        }

        .cards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
        }

        .customizer-card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-left: 4px solid #FFD700;
            font-family: 'Poppins', sans-serif;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .customizer-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        }

        .customizer-card .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .customizer-card .card-header h3 {
            color: #9a2020;
            font-size: 1.2rem;
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            margin: 0;
        }

        .customizer-card .card-description {
            color: #666;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
            font-family: 'Poppins', sans-serif;
            line-height: 1.5;
        }

        .customizer-card .card-actions {
            display: flex;
            gap: 0.5rem;
        }

        .customizer-card .btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-family: 'Poppins', sans-serif;
            font-weight: 500;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 0.9rem;
        }

        .customizer-card .btn-primary {
            background: #9a2020;
            color: white;
        }

        .customizer-card .btn-primary:hover {
            background: #7a1a1a;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="sidebar-header"><i class="fas fa-crown"></i><h2>King Printing</h2></div>
            <nav class="sidebar-nav">
                <ul>
                    <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="pesanan.php"><i class="fas fa-inbox"></i> Pesanan</a></li>
                    <li><a href="riwayat-pesanan.php"><i class="fas fa-history"></i> Riwayat Pesanan</a></li>
                    <li><a href="kelola-produk.php"><i class="fas fa-box-open"></i> Kelola Produk</a></li>
                    <li><a href="kelola-tampilan.php" class="active"><i class="fas fa-palette"></i> Kelola Tampilan</a></li>
                </ul>
            </nav>
            <div class="sidebar-footer"><a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a></div>
        </aside>
        <main class="main-content">
            <header class="main-header">
                <div class="header-left">
                    <h3>Kelola Tampilan Website</h3>
                </div>
                <div class="header-right">
                    <a href="index.php" target="_blank" class="action-btn detail">
                        <i class="fas fa-eye"></i> Lihat Website
                    </a>
                </div>
            </header>
            
            <section class="customizer-cards">
                <div class="table-header">
                    <p>Ubah konten dan tampilan website langsung dari sini</p>
                </div>
                <div class="cards-grid">
                    <!-- Hero Section Card -->
                    <div class="customizer-card">
                        <div class="card-header">
                            <h3>Hero Section</h3>
                        </div>
                        <p class="card-description">Kelola slider hero, judul, deskripsi, dan tombol utama website</p>
                        <div class="card-actions">
                            <a href="kelola-hero.php" class="btn btn-primary">
                                <i class="fas fa-edit"></i> Kelola Hero
                            </a>
                        </div>
                    </div>

                    <!-- Layanan Card -->
                    <div class="customizer-card">
                        <div class="card-header">
                            <h3>Layanan Kami</h3>
                        </div>
                        <p class="card-description">Kelola daftar layanan, icon, deskripsi, dan tombol pesan</p>
                        <div class="card-actions">
                            <a href="kelola-layanan.php" class="btn btn-primary">
                                <i class="fas fa-edit"></i> Kelola Layanan
                            </a>
                        </div>
                    </div>

                    <!-- Cara Memesan Card -->
                    <div class="customizer-card">
                        <div class="card-header">
                            <h3>Cara Memesan</h3>
                        </div>
                        <p class="card-description">Kelola langkah-langkah pemesanan dan icon setiap langkah</p>
                        <div class="card-actions">
                            <a href="kelola-cara-memesan.php" class="btn btn-primary">
                                <i class="fas fa-edit"></i> Kelola Cara Memesan
                            </a>
                        </div>
                    </div>

                    <!-- Mengapa Memilih Card -->
                    <div class="customizer-card">
                        <div class="card-header">
                            <h3>Mengapa Memilih Kami?</h3>
                        </div>
                        <p class="card-description">Kelola keunggulan perusahaan dan icon setiap poin</p>
                        <div class="card-actions">
                            <a href="kelola-keunggulan.php" class="btn btn-primary">
                                <i class="fas fa-edit"></i> Kelola Keunggulan
                            </a>
                        </div>
                    </div>

                    <!-- FAQ Card -->
                    <div class="customizer-card">
                        <div class="card-header">
                            <h3>FAQ</h3>
                        </div>
                        <p class="card-description">Kelola pertanyaan umum dan jawabannya</p>
                        <div class="card-actions">
                            <a href="kelola-faq.php" class="btn btn-primary">
                                <i class="fas fa-edit"></i> Kelola FAQ
                            </a>
                        </div>
                    </div>

                    <!-- Kontak Card -->
                    <div class="customizer-card">
                        <div class="card-header">
                            <h3>Kontak & Footer</h3>
                        </div>
                        <p class="card-description">Kelola informasi kontak, alamat, map, dan footer website</p>
                        <div class="card-actions">
                            <a href="kelola-kontak.php" class="btn btn-primary">
                                <i class="fas fa-edit"></i> Kelola Kontak
                            </a>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>
</body>
</html>