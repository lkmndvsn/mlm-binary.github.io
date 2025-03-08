<?php
session_start();
include('config/db.php');

// Cek apakah user sudah login
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$user_email = $_SESSION['user'];

// Ambil data pengguna yang login
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $user_email);
$stmt->execute();
$result = $stmt->get_result();
$data_user = $result->fetch_assoc();
$user_id = $data_user['id'];
$stmt->close();

// Proses transfer PIN
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $receiver_email = trim($_POST['receiver_email']);
    $code = trim($_POST['code']);

    // Cek apakah penerima ada
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $receiver_email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $receiver_data = $result->fetch_assoc();
        $receiver_id = $receiver_data['id'];
        $stmt->close();

        // Cek apakah user memiliki PIN yang belum digunakan
        $stmt = $conn->prepare("SELECT id FROM pins WHERE user_id = ? AND code = ? AND status = 'unused'");
        $stmt->bind_param("is", $user_id, $code);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $stmt->close();

            // Lakukan transfer PIN
            $stmt = $conn->prepare("UPDATE pins SET user_id = ?, status = 'transferred' WHERE pin_code = ?");
            $stmt->bind_param("is", $receiver_id, $code);
            $stmt->execute();
            $stmt->close();

            // Catat di tabel `pin_transfers`
            $stmt = $conn->prepare("INSERT INTO pin_transfers (sender_id, receiver_id, code, transfer_date) VALUES (?, ?, ?, NOW())");
            $stmt->bind_param("iis", $user_id, $receiver_id, $code);
            $stmt->execute();
            $stmt->close();

            echo "<script>alert('PIN berhasil ditransfer!'); window.location='transfer_pin.php';</script>";
        } else {
            echo "<script>alert('PIN tidak valid atau sudah digunakan!');</script>";
        }
    } else {
        echo "<script>alert('Penerima tidak ditemukan!');</script>";
    }
}
?>

<?php include('includes/header.php'); ?>
<a href="dashboard.php" class="btn btn-secondary">← Kembali ke Dashboard</a>
<div class="container mt-5">
    <h2 class="text-center">Transfer PIN</h2>
    <form method="POST" class="mt-4">
        <div class="mb-3">
            <label class="form-label">Email Penerima</label>
            <input type="email" name="receiver_email" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Kode PIN</label>
            <input type="text" name="code" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Transfer PIN</button>
    </form>
</div>

<?php include('includes/footer.php'); ?>
