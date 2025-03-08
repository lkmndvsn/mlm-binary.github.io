<?php
session_start();
include('config/db.php');

// Pastikan admin sudah login
if (!isset($_SESSION['admin'])) {
    header('Location: admin-login.php');
    exit;
}

// Ambil semua transaksi dari database
$query = "SELECT transactions.id, users.name, users.email, transactions.amount, transactions.transaction_type, transactions.status, transactions.created_at 
          FROM transactions 
          JOIN users ON transactions.user_id = users.id 
          ORDER BY transactions.created_at DESC";
$result = $conn->query($query);
?>

<?php include('includes/header.php'); ?>
<a href="admin-dashboard.php" class="btn btn-secondary">
        ← Kembali ke Dashboard
    </a>
<div class="container mt-5">
    <h2 class="text-center">Lihat Transaksi</h2>

    <table class="table table-bordered">
        <thead class="table-dark">
            <tr>
                <th>#</th>
                <th>Nama</th>
                <th>Email</th>
                <th>Jumlah</th>
                <th>Jenis Transaksi</th>
                <th>Status</th>
                <th>Tanggal</th>
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
                        <td>Rp <?php echo number_format($row['amount'], 2, ',', '.'); ?></td>
                        <td>
                            <?php
                            $type_badge = [
                                'deposit' => 'success',
                                'withdrawal' => 'danger',
                                'purchase' => 'primary'
                            ];
                            ?>
                            <span class="badge bg-<?php echo $type_badge[$row['transaction_type']]; ?>">
                                <?php echo ucfirst($row['transaction_type']); ?>
                            </span>
                        </td>
                        <td>
                            <?php
                            $status_badge = [
                                'pending' => 'warning',
                                'completed' => 'success',
                                'failed' => 'danger'
                            ];
                            ?>
                            <span class="badge bg-<?php echo $status_badge[$row['status']]; ?>">
                                <?php echo ucfirst($row['status']); ?>
                            </span>
                        </td>
                        <td><?php echo date('d-m-Y H:i', strtotime($row['created_at'])); ?></td>
                    </tr>
            <?php }
            } else { ?>
                <tr>
                    <td colspan="7" class="text-center">Tidak ada transaksi ditemukan.</td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

<?php include('includes/footer.php'); ?>
