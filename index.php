<?php
include 'db.php';

// Ambil data trades
$sql = "SELECT trades.*, pairs.name AS pair_name 
        FROM trades 
        JOIN pairs ON trades.pair_id = pairs.id 
        ORDER BY trade_date DESC";
$result = $conn->query($sql);

// Ambil saldo
$balance_query = $conn->query("SELECT total_balance FROM balance LIMIT 1");
$balance = $balance_query->fetch_assoc()['total_balance'];

// Hitung total win/lose bulan ini
$current_month = date('Y-m');
$summary_sql = "SELECT result, SUM(amount_usd) AS total 
                FROM trades 
                WHERE DATE_FORMAT(trade_date, '%Y-%m') = '$current_month'
                GROUP BY result";
$summary_result = $conn->query($summary_sql);

$win_total = 0;
$lose_total = 0;
while ($row = $summary_result->fetch_assoc()) {
    if ($row['result'] == 'win') {
        $win_total = $row['total'];
    } else {
        $lose_total = $row['total'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Daily Trading Journal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-4">
    <h1 class="text-center mb-4">📈 Daily Trading Journal</h1>

    <!-- Card Balance & Summary -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card text-white bg-primary shadow">
                <div class="card-body">
                    <h5 class="card-title">Balance</h5>
                    <p class="card-text fs-4">$<?= number_format($balance, 2) ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-success shadow">
                <div class="card-body">
                    <h5 class="card-title">Total Win (<?= date('F Y') ?>)</h5>
                    <p class="card-text fs-4">$<?= number_format($win_total, 2) ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-danger shadow">
                <div class="card-body">
                    <h5 class="card-title">Total Lose (<?= date('F Y') ?>)</h5>
                    <p class="card-text fs-4">$<?= number_format($lose_total, 2) ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Table Trading Journal -->
    <div class="card shadow">
        <div class="card-header d-flex justify-content-between">
            <h5 class="mb-0">Trading History</h5>
            <a href="add.php" class="btn btn-primary btn-sm">+ Add Trade</a>
        </div>
        <div class="card-body">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Pair</th>
                        <th>Position</th>
                        <th>Result</th>
                        <th>Amount (USD)</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['id'] ?></td>
                            <td><?= $row['pair_name'] ?></td>
                            <td>
                                <span class="badge <?= $row['position']=='long' ? 'bg-primary' : 'bg-warning' ?>">
                                    <?= ucfirst($row['position']) ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge <?= $row['result']=='win' ? 'bg-success' : 'bg-danger' ?>">
                                    <?= ucfirst($row['result']) ?>
                                </span>
                            </td>
                            <td>$<?= number_format($row['amount_usd'], 2) ?></td>
                            <td><?= $row['trade_date'] ?></td>
                            <td>
                                <a href="edit.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                                <a href="delete.php?id=<?= $row['id'] ?>" 
                                   class="btn btn-sm btn-danger"
                                   onclick="return confirm('Yakin mau hapus trade ini?')">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="7" class="text-center">No trades recorded yet.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>
