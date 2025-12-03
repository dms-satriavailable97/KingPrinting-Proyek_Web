<?php
require_once 'config.php';

// --- KONFIGURASI ---
$total_target = 15;      // Target 15 data
$status_target = 'Tertunda'; // Status fixed
$date_target = date('Y-m-d'); // Tanggal hari ini

// --- DATA DUMMY (Variasi Data) ---
$names = [
    'Ahmad', 'Budi', 'Citra', 'Dewi', 'Eko', 'Fajar', 'Gita', 'Hadi', 'Indah', 'Joko',
    'Kartika', 'Lina', 'Mamat', 'Nina', 'Oscar', 'Putri', 'Qori', 'Rizky', 'Siti', 'Tono',
    'Umar', 'Vina', 'Wahyu', 'Xena', 'Yanto', 'Zainal', 'Agus', 'Bayu', 'Candra', 'Dian',
    'Erik', 'Farid', 'Galih', 'Hendra', 'Irfan', 'Jamal', 'Kiki', 'Lukman', 'Mira', 'Noval'
];
$products = ['Spanduk & Banner', 'Stiker & Label', 'Baliho & Billboard', 'Brosur & Flyer', 'Kemasan & Dus', 'Produk Custom'];
$materials = ['Vinyl', 'Flexy China', 'Kanvas', 'Art Paper', 'Sticker Vinyl', 'Bontax', 'Ivory', 'Albatros', 'Luster'];
$sizes = ['100x100', '200x100', '300x100', 'A4', 'A3', 'A5', 'Custom', '50x50', 'X-Banner'];

echo "<h1>Memproses Data Dummy...</h1>";
echo "<div style='background:#f0f0f0; padding:10px; border:1px solid #ccc; font-family:monospace;'>";

$batch_data = [];

// Loop pembuatan data
for ($i = 1; $i <= $total_target; $i++) {
    // 1. Generate Data Random (Nama, Produk, dll)
    $nama    = $names[array_rand($names)] . ' ' . $names[array_rand($names)];
    $telepon = '08' . rand(10, 99) . '-' . rand(1000, 9999) . '-' . rand(1000, 9999);
    $produk  = $products[array_rand($products)];
    $ukuran  = $sizes[array_rand($sizes)];
    $bahan   = $materials[array_rand($materials)];
    $jumlah  = rand(1, 100);
    
    // 2. SET STATUS & TANGGAL (FIXED)
    $status  = $status_target;
    
    // Generate jam acak antara jam 08:00 s/d 20:00 di hari yang SAMA
    $hour = str_pad(rand(8, 20), 2, '0', STR_PAD_LEFT);
    $minute = str_pad(rand(0, 59), 2, '0', STR_PAD_LEFT);
    $second = str_pad(rand(0, 59), 2, '0', STR_PAD_LEFT);
    
    $tanggal_masuk = "$date_target $hour:$minute:$second";
    
    // Escape string untuk keamanan SQL
    $nama    = $conn->real_escape_string($nama);
    $produk  = $conn->real_escape_string($produk);
    $ukuran  = $conn->real_escape_string($ukuran);
    $bahan   = $conn->real_escape_string($bahan);
    $catatan = (rand(1, 100) <= 30) ? "Contoh Pesanan Tertunda #$i" : "";
    
    // Masukkan ke antrian query value
    $batch_data[] = "('$nama', '$telepon', '$produk', '$ukuran', '$bahan', $jumlah, '$catatan', '$tanggal_masuk', '$status')";
}

// Eksekusi Query INSERT sekaligus (Batch)
if (!empty($batch_data)) {
    $values_sql = implode(", ", $batch_data);
    $sql = "INSERT INTO pesanan (nama_pemesan, telepon, produk, ukuran, bahan, jumlah, catatan, tanggal_masuk, status) VALUES $values_sql";
    
    if ($conn->query($sql)) {
        echo "Berhasil menambahkan <b>" . count($batch_data) . "</b> data pesanan dengan status <b>'$status_target'</b> pada tanggal <b>$date_target</b>.<br>";
    } else {
        echo "<span style='color:red'>Error: " . $conn->error . "</span><br>";
    }
}

echo "</div>";
echo "<br>";
echo "<a href='pesanan.php' style='padding:10px 20px; background:#9a2020; color:white; text-decoration:none; border-radius:5px;'>Lihat Daftar Pesanan</a>";

$conn->close();
?>