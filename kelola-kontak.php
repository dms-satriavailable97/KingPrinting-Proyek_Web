<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("location: index.php");
    exit;
}

// Get contact data
$contact_section = [];
$result = $conn->query("SELECT * FROM website_sections WHERE section_name = 'contact'");
if ($result && $result->num_rows > 0) {
    $contact_section = $result->fetch_assoc();
}

// Get operational hours
$operational_hours = [];
$result = $conn->query("SELECT * FROM operational_hours ORDER BY sort_order");
if ($result) {
    while($row = $result->fetch_assoc()) {
        $operational_hours[$row['day_type']] = $row;
    }
}

// Set default values from database
$phone = $contact_section['title'] ?? '+62 887-0584-4251';
$email = $contact_section['subtitle'] ?? 'info@kingadvertising.com';
$address = $contact_section['description'] ?? 'Jl. Ahmad Yani 2 No.12 RT.10, Temindung Permai, Kec. Sungai Pinang, Kota Samarinda, Kalimantan Timur';
$map_embed = $contact_section['additional_info'] ?? '<iframe class="map-iframe" src="https://www.google.com/maps/embed?pb=!4v1762266865140!6m8!1m7!1sWhc2abtYaDzUcfT_F7LnHg!2m2!1d-0.4730531793778449!2d117.1653334981797!3f326.50841155499506!4f-15.840080292808594!5f1.0886293032444474" width="100%" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>';
$footer_text = $contact_section['meta_data'] ?? '© 2025 King Advertising. All rights reserved.';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_contact'])) {
        $phone = $conn->real_escape_string($_POST['phone']);
        $email = $conn->real_escape_string($_POST['email']);
        $address = $conn->real_escape_string($_POST['address']);
        $map_embed = $conn->real_escape_string($_POST['map_embed']);
        $footer_text = $conn->real_escape_string($_POST['footer_text']);
        
        // Update semua data contact sekaligus
        $sql = "UPDATE website_sections SET 
                title = '$phone', 
                subtitle = '$email', 
                description = '$address',
                additional_info = '$map_embed',
                meta_data = '$footer_text'
                WHERE section_name = 'contact'";
        
        if ($conn->query($sql)) {
            $success = "Informasi kontak berhasil diperbarui!";
            // Refresh data langsung
            $result = $conn->query("SELECT * FROM website_sections WHERE section_name = 'contact'");
            if ($result && $result->num_rows > 0) {
                $contact_section = $result->fetch_assoc();
                // Update variabel dengan data terbaru
                $phone = $contact_section['title'];
                $email = $contact_section['subtitle'];
                $address = $contact_section['description'];
                $map_embed = $contact_section['additional_info'];
                $footer_text = $contact_section['meta_data'];
            }
        } else {
            $error = "Error: " . $conn->error;
        }
    }
    
    // Update jam operasional
    if (isset($_POST['update_hours'])) {
        $weekdays = $conn->real_escape_string($_POST['weekdays']);
        $saturday = $conn->real_escape_string($_POST['saturday']);
        $sunday = $conn->real_escape_string($_POST['sunday']);
        
        // Update ke database
        $sql_weekdays = "UPDATE operational_hours SET hours_text = '$weekdays' WHERE day_type = 'weekdays'";
        $sql_saturday = "UPDATE operational_hours SET hours_text = '$saturday' WHERE day_type = 'saturday'";
        $sql_sunday = "UPDATE operational_hours SET hours_text = '$sunday' WHERE day_type = 'sunday'";
        
        $success_weekdays = $conn->query($sql_weekdays);
        $success_saturday = $conn->query($sql_saturday);
        $success_sunday = $conn->query($sql_sunday);
        
        if ($success_weekdays && $success_saturday && $success_sunday) {
            $success_hours = "Jam operasional berhasil diperbarui!";
            // Refresh data jam operasional
            $result = $conn->query("SELECT * FROM operational_hours ORDER BY sort_order");
            if ($result) {
                $operational_hours = [];
                while($row = $result->fetch_assoc()) {
                    $operational_hours[$row['day_type']] = $row;
                }
            }
        } else {
            $error_hours = "Error memperbarui jam operasional!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Kontak - King Printing</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: #f5f5f5;
            color: #333;
        }

        .admin-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        .main-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .main-header h1 {
            color: #9a2020;
            font-size: 1.8rem;
            font-weight: 600;
        }

        .form-section {
            background: white;
            border-radius: 10px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .section-title {
            color: #9a2020;
            margin-bottom: 1.5rem;
            border-bottom: 2px solid #FFD700;
            padding-bottom: 0.5rem;
            font-weight: 600;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #333;
        }

        .form-control {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-family: 'Poppins', sans-serif;
            font-size: 0.9rem;
        }

        .btn {
            padding: 0.8rem 1.5rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-family: 'Poppins', sans-serif;
            font-weight: 500;
            font-size: 0.9rem;
            transition: all 0.3s;
        }

        .btn-primary {
            background: #9a2020;
            color: white;
        }

        .btn-primary:hover {
            background: #7a1a1a;
        }

        .btn-success {
            background: #28a745;
            color: white;
        }

        .btn-success:hover {
            background: #218838;
        }

        .alert {
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 5px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .help-text {
            font-size: 0.8rem;
            color: #666;
            margin-top: 0.3rem;
        }

        .form-row {
            display: flex;
            gap: 1rem;
        }

        .form-row .form-group {
            flex: 1;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <header class="main-header">
            <h1>Kelola Kontak & Footer</h1>
            <a href="kelola-tampilan.php" class="btn btn-primary">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </header>

        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="form-section">
            <h2 class="section-title">Informasi Kontak</h2>
            <form method="POST">
                <div class="form-row">
                    <div class="form-group">
                        <label>Nomor Telepon/WhatsApp</label>
                        <input type="text" name="phone" class="form-control" 
                               value="<?php echo htmlspecialchars($phone); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control" 
                               value="<?php echo htmlspecialchars($email); ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Alamat Lengkap</label>
                    <textarea name="address" class="form-control" rows="3" required><?php echo htmlspecialchars($address); ?></textarea>
                </div>

                <div class="form-group">
                    <label>Embed Google Maps</label>
                    <textarea name="map_embed" class="form-control" rows="4" placeholder='&lt;iframe src="https://www.google.com/maps/embed?pb=..."&gt;&lt;/iframe&gt;'><?php echo htmlspecialchars($map_embed); ?></textarea>
                    <div class="help-text">Salin kode embed dari Google Maps dan paste di sini</div>
                </div>

                <div class="form-group">
                    <label>Footer Text</label>
                    <input type="text" name="footer_text" class="form-control" 
                           value="<?php echo htmlspecialchars($footer_text); ?>" required>
                </div>

                <button type="submit" name="update_contact" class="btn btn-success">
                    <i class="fas fa-save"></i> Simpan Perubahan Kontak
                </button>
            </form>
        </div>

        <!-- Form untuk jam operasional -->
        <div class="form-section">
            <h2 class="section-title">Jam Operasional</h2>
            
            <?php if (isset($success_hours)): ?>
                <div class="alert alert-success"><?php echo $success_hours; ?></div>
            <?php endif; ?>

            <?php if (isset($error_hours)): ?>
                <div class="alert alert-error"><?php echo $error_hours; ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-row">
                    <div class="form-group">
                        <label>Senin - Jumat</label>
                        <input type="text" name="weekdays" class="form-control" 
                               value="<?php echo htmlspecialchars($operational_hours['weekdays']['hours_text'] ?? 'Senin - Jumat: 08.00 - 17.00'); ?>"
                               placeholder="Contoh: Senin - Jumat: 08.00 - 17.00">
                    </div>
                    <div class="form-group">
                        <label>Sabtu</label>
                        <input type="text" name="saturday" class="form-control" 
                               value="<?php echo htmlspecialchars($operational_hours['saturday']['hours_text'] ?? 'Sabtu: 08.00 - 15.00'); ?>"
                               placeholder="Contoh: Sabtu: 08.00 - 15.00">
                    </div>
                    <div class="form-group">
                        <label>Minggu</label>
                        <input type="text" name="sunday" class="form-control" 
                               value="<?php echo htmlspecialchars($operational_hours['sunday']['hours_text'] ?? 'Minggu: Tutup'); ?>"
                               placeholder="Contoh: Minggu: Tutup">
                    </div>
                </div>
                <button type="submit" name="update_hours" class="btn btn-success">
                    <i class="fas fa-save"></i> Simpan Jam Operasional
                </button>
            </form>
        </div>
    </div>
</body>
</html>