<?php
session_start();
include('config/db.php');
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
}

$user = $_SESSION['user'];

// Ambil data anggota yang sedang login
$query = "SELECT * FROM users WHERE email = '$user'";
$result = $conn->query($query);
$data = $result->fetch_assoc();

// Hitung jumlah downline
$referral_id = $data['id']; // ID anggota yang login
$query_downline = "SELECT COUNT(*) AS total_downline FROM users WHERE referral_id = '$referral_id'";
$result_downline = $conn->query($query_downline);
$total_downline = $result_downline->fetch_assoc()['total_downline'];
?>
<?php include('includes/header.php'); ?>
<div class="container mt-5">
    <h2>Jaringan Anda</h2>
    <canvas id="downlineChart" width="400" height="200"></canvas>
</div>

<script>
    // Data untuk grafik
    const data = {
        labels: ['Total Downline'],
        datasets: [{
            label: 'Jumlah Downline',
            data: [<?php echo $total_downline; ?>],
            backgroundColor: ['rgba(75, 192, 192, 0.2)'],
            borderColor: ['rgba(75, 192, 192, 1)'],
            borderWidth: 1
        }]
    };

    // Konfigurasi grafik
    const config = {
        type: 'bar',
        data: data,
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    };

    // Render grafik menggunakan Chart.js
    const downlineChart = new Chart(
        document.getElementById('downlineChart'),
        config
    );
</script>
<?php include('includes/footer.php'); ?>
