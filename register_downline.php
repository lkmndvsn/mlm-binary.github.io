<?php
session_start();
include('config/db.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $pin_code = $_POST['pin_code'];

    // Pastikan pin valid
    $stmt = $conn->prepare("SELECT * FROM pins WHERE code = ? AND status = 'unused' LIMIT 1");
    $stmt->bind_param("s", $pin_code);
    $stmt->execute();
    $pin_result = $stmt->get_result();

    if ($pin_result->num_rows > 0) {
        $pin_data = $pin_result->fetch_assoc();
        $referral_id = $pin_data['user_id']; // Pemilik pin adalah referral

        // Insert data user baru
        $stmt = $conn->prepare("INSERT INTO users (name, email, password, referral_id) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sssi", $name, $email, $password, $referral_id);
        if ($stmt->execute()) {
            // Update status pin menjadi digunakan
            $stmt = $conn->prepare("UPDATE pins SET status = 'used' WHERE id = ?");
            $stmt->bind_param("i", $pin_data['id']);
            $stmt->execute();
            
            $_SESSION['success'] = "Pendaftaran berhasil! User telah ditambahkan.";
            header("Location: pin.php");
        } else {
            $_SESSION['error'] = "Terjadi kesalahan: " . $stmt->error;
            header("Location: pin.php");
        }
    } else {
        $_SESSION['error'] = "Pin tidak valid atau sudah digunakan.";
        header("Location: pin.php");
    }

    exit;
}
?>
