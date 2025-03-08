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

// Simpan user_id ke dalam session jika belum ada
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = $data_user['id'];
}

// Fungsi untuk mengecek apakah user memiliki pin/tiket
function hasValidPin($userId, $conn) {
    $stmt = $conn->prepare("SELECT * FROM pins WHERE user_id = ? AND status = 'unused' LIMIT 1");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->num_rows > 0;
}

// Fungsi untuk membangun pohon jaringan downline
function getDownlineTree($parentId, $conn, $level = 1, $maxLevel = 5) {
    if ($level > $maxLevel) return;
    
    $stmt = $conn->prepare("SELECT * FROM users WHERE referral_id = ? LIMIT 2");
    $stmt->bind_param("i", $parentId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    echo '<ul class="tree-level">';
    
    $childCount = 0;
    while ($row = $result->fetch_assoc()) {
        $childCount++;
        echo '<li class="tree-node">';
        echo '<div class="user-box">';
        echo '<i class="bi bi-person-circle user-icon"></i><br>';
        echo '<strong>' . htmlspecialchars($row['name']) . '</strong><br><span class="text-muted">' . htmlspecialchars($row['email']) . '</span>';
        getDownlineTree($row['id'], $conn, $level + 1, $maxLevel);
        echo '</div>';
        echo '</li>';
    }
    
    while ($childCount < 2) {
        echo '<li class="tree-node">';
        echo '<div class="user-box">';
        if (isset($_SESSION['user_id']) && hasValidPin($_SESSION['user_id'], $conn)) {
            echo '<button class="btn btn-primary" onclick="openRegisterModal(' . $parentId . ')">+ Tambah Member</button>';
        } else {
            echo '<button class="btn btn-secondary" disabled>Pin Tidak Tersedia</button>';
        }
        echo '</div>';
        echo '</li>';
        $childCount++;
    }
    
    echo '</ul>';
    $stmt->close();
}
?>

<?php include('includes/header.php'); ?>

<div class="container mt-3">
    <a href="dashboard.php" class="btn btn-secondary">← Kembali ke Dashboard</a>
</div>

<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

<style>
.tree {
    text-align: center;
    display: flex;
    flex-direction: column;
    align-items: center;
}
.tree-level {
    display: flex;
    justify-content: center;
    list-style: none;
    padding: 0;
}
.tree-node {
    position: relative;
    margin: 10px;
    text-align: center;
}
.user-box {
    background: #f8f9fa;
    padding: 10px;
    border-radius: 10px;
    display: inline-block;
    min-width: 150px;
}
.user-icon {
    font-size: 24px;
    color: #007acc;
}
</style>

<div class="container mt-5">
    <h2 class="text-center">Pohon Jaringan Downline</h2>
    <div class="tree text-center mt-4">
        <ul class="tree-level">
            <li class="tree-node">
                <div class="user-box">
                    <i class="bi bi-person-circle user-icon"></i><br>
                    <strong><?php echo htmlspecialchars($data_user['name']); ?></strong><br>
                    <span class="text-muted"><?php echo htmlspecialchars($data_user['email']); ?></span>
                </div>
                <?php getDownlineTree($data_user['id'], $conn); ?>
            </li>
        </ul>
    </div>
</div>

<script>
function openRegisterModal(referralId) {
    document.getElementById('modal_referral_id').value = referralId;
    var registerModal = new bootstrap.Modal(document.getElementById('registerModal'));
    registerModal.show();
}
</script>

<!-- Modal Pendaftaran Downline -->
<div class="modal fade" id="registerModal" tabindex="-1" aria-labelledby="registerModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="registerModalLabel">Pendaftaran Downline</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="POST" action="register_downline.php">
                    <input type="hidden" name="referral_id" id="modal_referral_id">
                    <div class="mb-3">
                        <label for="name" class="form-label">Nama</label>
                        <input type="text" name="name" id="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" name="email" id="email" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-success w-100">Daftar</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<?php include('includes/footer.php'); ?>
