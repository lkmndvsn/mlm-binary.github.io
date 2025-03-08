<?php
session_start();
include('config/db.php');

// Pastikan admin sudah login
if (!isset($_SESSION['admin'])) {
    header('Location: admin-login.php');
    exit;
}

// Proses hapus anggota
if (isset($_GET['delete'])) {
    $user_id = intval($_GET['delete']);
    $conn->query("DELETE FROM users WHERE id = $user_id");
    echo "<script>alert('Anggota berhasil dihapus!'); window.location='admin-members.php';</script>";
}

// Ambil daftar semua anggota
$search = "";
if (isset($_GET['search'])) {
    $search = $_GET['search'];
    $query = "SELECT * FROM users WHERE name LIKE '%$search%' OR email LIKE '%$search%' ORDER BY id DESC";
} else {
    $query = "SELECT * FROM users ORDER BY id DESC";
}
$result = $conn->query($query);

?>

<?php include('includes/header.php'); ?>
<div class="container mt-3">
    <a href="admin-dashboard.php" class="btn btn-secondary">
        ← Kembali ke Dashboard
    </a>
</div>
<div class="container mt-5">
    <h2 class="text-center">Kelola Anggota</h2>

    <!-- Form pencarian -->
    <form class="d-flex mb-4" method="GET">
        <input class="form-control me-2" type="text" name="search" placeholder="Cari Nama atau Email..." value="<?php echo htmlspecialchars($search); ?>">
        <button class="btn btn-primary" type="submit">Cari</button>
    </form>

    <table class="table table-bordered">
        <thead class="table-dark">
            <tr>
                <th>#</th>
                <th>Nama</th>
                <th>Email</th>
                <th>Tanggal Bergabung</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0) {
                $no = 1;
                while ($row = $result->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo $no++; ?></td>
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td><?php echo date('d-m-Y', strtotime($row['created_at'])); ?></td>
                        <td>
                            <a href="edit-member.php?id=<?php echo $row['id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                            <a href="admin-members.php?delete=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus anggota ini?')">Hapus</a>
                        </td>
                    </tr>
            <?php }
            } else { ?>
                <tr>
                    <td colspan="5" class="text-center">Tidak ada anggota ditemukan.</td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

<?php include('includes/footer.php'); ?>
