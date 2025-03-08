<?php
session_start();
include('config/db.php');

// Pastikan user sudah login
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

// Ambil data pengguna yang login
$user_email = $_SESSION['user'];
$stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
$stmt->bind_param("s", $user_email);
$stmt->execute();
$result = $stmt->get_result();
$data_user = $result->fetch_assoc();
$stmt->close();

// Ambil daftar PIN user
$stmt = $conn->prepare("SELECT id, code, status FROM pins WHERE user_id = ? AND status = 'unused'");
$stmt->bind_param("i", $data_user['id']);
$stmt->execute();
$pins = $stmt->get_result();
$stmt->close();

// Proses transfer PIN ke member lain dengan verifikasi password
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['transfer_pins'])) {
    $recipient_email = $_POST['recipient_email'];
    $pin_amount = intval($_POST['pin_amount']);
    $password_input = $_POST['password'];

    // Verifikasi password pengguna
    if (password_verify($password_input, $data_user['password'])) {
        // Cek apakah user tujuan ada
        $query_recipient = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $query_recipient->bind_param("s", $recipient_email);
        $query_recipient->execute();
        $recipient_result = $query_recipient->get_result();

        if ($recipient_result->num_rows > 0) {
            $recipient_data = $recipient_result->fetch_assoc();
            $recipient_id = $recipient_data['id'];

            // Ambil PIN yang akan ditransfer
            $pin_query = $conn->query("SELECT id FROM pins WHERE user_id = '" . $data_user['id'] . "' AND status = 'unused' LIMIT $pin_amount");
            $pins_to_transfer = [];

            while ($pin_row = $pin_query->fetch_assoc()) {
                $pins_to_transfer[] = $pin_row['id'];
            }

            if (count($pins_to_transfer) == $pin_amount) {
                // Transfer PIN ke member tujuan
                $pin_ids = implode(",", $pins_to_transfer);
                $conn->query("UPDATE pins SET user_id = '$recipient_id' WHERE id IN ($pin_ids)");

                echo "<script>alert('$pin_amount PIN berhasil dikirim ke $recipient_email!'); window.location='pin.php';</script>";
            } else {
                echo "<script>alert('Jumlah PIN yang tersedia tidak cukup!');</script>";
            }
        } else {
            echo "<script>alert('User tidak ditemukan!');</script>";
        }
    } else {
        echo "<script>alert('Password salah!');</script>";
    }
}
?>

<?php include('includes/header.php'); ?>

<div class="container mt-5">
    <a href="dashboard.php" class="btn btn-secondary">← Kembali ke Dashboard</a>
    <h2 class="text-center">Pin Saya</h2>

    <table class="table table-bordered mt-4">
        <thead>
            <tr>
                <th>#</th>
                <th>Kode Pin</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            if ($pins->num_rows > 0) {
                $no = 1;
                while ($pin = $pins->fetch_assoc()) { 
                    $pin_code = htmlspecialchars($pin['code']);
                    ?>
                    <tr>
                        <td><?php echo $no++; ?></td>
                        <td><?php echo $pin_code; ?></td>
                        <td><span class="badge bg-warning">Belum Digunakan</span></td>
                        <td>
                            <button class="btn btn-primary" onclick="openRegisterModal('<?php echo $pin_code; ?>')">
                                Gunakan Pin
                            </button>
                        </td>
                    </tr>
            <?php }
            } else { ?>
                <tr>
                    <td colspan="4" class="text-center">Anda belum memiliki PIN.</td>
                </tr>
            <?php } ?>
        </tbody>
    </table>

    <!-- Form Transfer PIN -->
    <h3 class="mt-5">Transfer PIN ke Member</h3>
    <form method="POST">
        <div class="mb-3">
            <label class="form-label">Email Tujuan</label>
            <input type="email" name="recipient_email" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Jumlah PIN yang Dikirim</label>
            <input type="number" name="pin_amount" class="form-control" min="1" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Masukkan Password Anda</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <button type="submit" name="transfer_pins" class="btn btn-primary w-100">Kirim PIN</button>
    </form>
</div>

<script>
function openRegisterModal(pinCode) {
    document.getElementById('modal_pin_code').value = pinCode;
    var registerModal = new bootstrap.Modal(document.getElementById('registerModal'));
    registerModal.show();
}
</script>

<!-- Modal Pendaftaran Member Baru -->
<div class="modal fade" id="registerModal" tabindex="-1" aria-labelledby="registerModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="registerModalLabel">Pendaftaran Member Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="POST" action="register_downline.php">
                    <input type="hidden" name="pin_code" id="modal_pin_code">
                    <div class="mb-3">
                        <label class="form-label">Sponsor</label>
                        <input type="text" name="sponsor" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="position" class="form-label">Posisi Penempatan</label>
                        <select name="position" id="position" class="form-control" required>
                            <option value="left">Kiri</option>
                            <option value="right">Kanan</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="name" class="form-label">Nama</label>
                        <input type="text" name="name" id="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" name="email" id="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" name="password" id="password" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-success w-100">Daftar</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include('includes/footer.php'); ?>
