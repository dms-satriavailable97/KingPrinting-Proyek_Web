<?php
require_once 'config.php';

// --- KONFIGURASI ---
$total_target = 1000; // Target 1000 data

// --- DATA DUMMY (Variasi Data yang Lebih Banyak) ---
$first_names = [
    'Ahmad', 'Budi', 'Citra', 'Dewi', 'Eko', 'Fajar', 'Gita', 'Hadi', 'Indah', 'Joko',
    'Kartika', 'Lina', 'Mamat', 'Nina', 'Oscar', 'Putri', 'Qori', 'Rizky', 'Siti', 'Tono',
    'Umar', 'Vina', 'Wahyu', 'Xena', 'Yanto', 'Zainal', 'Agus', 'Bayu', 'Candra', 'Dian',
    'Erik', 'Farid', 'Galih', 'Hendra', 'Irfan', 'Jamal', 'Kiki', 'Lukman', 'Mira', 'Noval',
    'Rina', 'Sari', 'Tegar', 'Utami', 'Vicky', 'Wulan', 'Yusuf', 'Zara', 'Adit', 'Bella'
];
$last_names = [
    'Saputra', 'Wibowo', 'Lestari', 'Kusuma', 'Wijaya', 'Santoso', 'Pratama', 'Nugroho', 
    'Hidayat', 'Ramadhan', 'Setiawan', 'Kurniawan', 'Susanti', 'Rahayu', 'Mulyani', 
    'Siregar', 'Nasution', 'Pangestu', 'Utomo', 'Firmansyah'
];

$products = ['Spanduk & Banner', 'Stiker & Label', 'Baliho & Billboard', 'Brosur & Flyer', 'Kemasan & Dus', 'Produk Custom'];
$materials = ['Vinyl', 'Flexy China', 'Kanvas', 'Art Paper', 'Sticker Vinyl', 'Bontax', 'Ivory', 'Albatros', 'Luster', 'HVS'];
$sizes = ['100x100', '200x100', '300x100', 'A4', 'A3', 'A5', 'Custom', '50x50', 'X-Banner', 'Y-Banner', 'Roll Up'];
$statuses = ['Tertunda', 'Proses', 'Selesai'];

echo "<h1>Memproses 1000 Data Dummy...</h1>";
echo "<div style='background:#f0f0f0; padding:10px; border:1px solid #ccc; font-family:monospace; max-height: 400px; overflow: auto;'>";

$batch_data = [];
$count = 0;
$chunk_size = 200; // Insert per 200 baris agar database tidak berat

// Loop pembuatan data
for ($i = 1; $i <= $total_target; $i++) {
    // 1. Generate Data Random
    $nama    = $first_names[array_rand($first_names)] . ' ' . $last_names[array_rand($last_names)];
    $telepon = '08' . rand(10, 99) . '-' . rand(1000, 9999) . '-' . rand(1000, 9999);
    $produk  = $products[array_rand($products)];
    $ukuran  = $sizes[array_rand($sizes)];
    $bahan   = $materials[array_rand($materials)];
    $jumlah  = rand(1, 500); // Jumlah antara 1 - 500 pcs
    
    // Random Status
    $status  = $statuses[array_rand($statuses)];
    
    // Random Tanggal (3 Bulan Terakhir)
    // Mengambil timestamp acak dari 90 hari lalu sampai detik ini
    $timestamp = mt_rand(strtotime("-3 months"), time());
    $tanggal_masuk = date("Y-m-d H:i:s", $timestamp);
    
    // Catatan acak
    $catatan = (rand(1, 100) <= 20) ? "Catatan Dummy #$i: Tolong dipercepat ya kak." : "";
    
    // Escape string untuk keamanan SQL
    $nama    = $conn->real_escape_string($nama);
    $produk  = $conn->real_escape_string($produk);
    $ukuran  = $conn->real_escape_string($ukuran);
    $bahan   = $conn->real_escape_string($bahan);
    $catatan = $conn->real_escape_string($catatan);
    
    // Masukkan ke antrian query value
    $batch_data[] = "('$nama', '$telepon', '$produk', '$ukuran', '$bahan', $jumlah, '$catatan', '$tanggal_masuk', '$status')";
    
    // 2. Eksekusi per Chunk (Setiap 200 data)
    if (count($batch_data) >= $chunk_size) {
        $values_sql = implode(", ", $batch_data);
        $sql = "INSERT INTO pesanan (nama_pemesan, telepon, produk, ukuran, bahan, jumlah, catatan, tanggal_masuk, status) VALUES $values_sql";
        
        if ($conn->query($sql)) {
            $count += count($batch_data);
            echo "Batch insert: $count data... OK<br>";
        } else {
            echo "<span style='color:red'>Error Batch: " . $conn->error . "</span><br>";
        }
        $batch_data = []; // Kosongkan antrian
    }
}

// 3. Eksekusi Sisa Data (jika ada sisa dari pembagian chunk)
if (!empty($batch_data)) {
    $values_sql = implode(", ", $batch_data);
    $sql = "INSERT INTO pesanan (nama_pemesan, telepon, produk, ukuran, bahan, jumlah, catatan, tanggal_masuk, status) VALUES $values_sql";
    
    if ($conn->query($sql)) {
        $count += count($batch_data);
        echo "Batch insert sisa: $count data... OK<br>";
    } else {
        echo "<span style='color:red'>Error Batch Sisa: " . $conn->error . "</span><br>";
    }
}

echo "</div>";
echo "<h2>Total Berhasil: $count Data Dummy</h2>";
echo "<br>";
echo "<a href='pesanan.php' style='padding:10px 20px; background:#9a2020; color:white; text-decoration:none; border-radius:5px;'>Lihat Daftar Pesanan</a>";

$conn->close();
?>