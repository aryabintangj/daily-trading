<?php
include 'db.php';

// ==========================
// Ambil bulan & tahun filter
// ==========================
$month = isset($_GET['month']) ? (int)$_GET['month'] : date('m');
$year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');
$selected_month = sprintf("%04d-%02d", $year, $month);

// ==========================
// Ambil data trades (filter per bulan)
// ==========================
$sql = "SELECT trades.*, pairs.name AS pair_name 
        FROM trades 
        JOIN pairs ON trades.pair_id = pairs.id 
        WHERE DATE_FORMAT(trade_date, '%Y-%m') = '$selected_month'
        ORDER BY trade_date DESC";
$result = $conn->query($sql);

// ==========================
// Ambil saldo
// ==========================
$balance_query = $conn->query("SELECT total_balance FROM balance LIMIT 1");
$balance = $balance_query->fetch_assoc()['total_balance'] ?? 0;

// ==========================
// Hitung total win/lose bulan ini
// ==========================
$summary_sql = "SELECT result, SUM(amount_usd) AS total 
                FROM trades 
                WHERE DATE_FORMAT(trade_date, '%Y-%m') = '$selected_month'
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

// ==========================
// Ambil PnL harian bulan ini
// ==========================
$pnl_sql = "SELECT trade_date, SUM(pnl_usd) AS daily_pnl
            FROM trades
            WHERE DATE_FORMAT(trade_date, '%Y-%m') = '$selected_month'
            GROUP BY trade_date
            ORDER BY trade_date ASC";
$pnl_result = $conn->query($pnl_sql);

$daily_pnl = [];
while ($row = $pnl_result->fetch_assoc()) {
    $daily_pnl[$row['trade_date']] = $row['daily_pnl'];
}

// ==========================
// Challenge progress (otomatis naik level)
// ==========================
$base_challenge = 1500;
$growth_rate = 0.25;
$total_challenges = 22;

$challenges = [];
for ($i = 1; $i <= $total_challenges; $i++) {
    $challenges[$i] = $base_challenge * pow(1 + $growth_rate, $i - 1);
}

$current_challenge = 1;
foreach ($challenges as $id => $target) {
    if ($balance >= $target) {
        $current_challenge = $id + 1;
    } else {
        $current_challenge = $id;
        break;
    }
}
if ($current_challenge > $total_challenges) {
    $current_challenge = $total_challenges;
}

$target_balance = $challenges[$current_challenge];
$previous_target = $current_challenge > 1 ? $challenges[$current_challenge - 1] : 0;
$progress = ($balance - $previous_target) / ($target_balance - $previous_target) * 100;
$progress = max(0, min(100, $progress));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Daily Trading Journal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { background: #f8f9fa; }
        .pnl-box {
            padding: 12px;
            border-radius: 10px;
            text-align: center;
            font-weight: bold;
            color: white;
            min-height: 70px;
        }
        .pnl-positive { background-color: #28a745; }
        .pnl-negative { background-color: #dc3545; }
        .pnl-neutral  { background-color: #6c757d; }

        /* Challenge Card */
        .challenge-card.completed { border-left: 6px solid #28a745; }
        .challenge-card.progressing { border-left: 6px solid #ffc107; }
        .challenge-progress .progress-bar { transition: width 1s ease-in-out; }

        /* Table hover */
        table.table-hover tbody tr:hover {
            background-color: #f1f5ff;
            cursor: pointer;
        }
    </style>
</head>
<body>

<div class="container mt-4">
    <h1 class="text-center mb-4">📈 Daily Trading Journal</h1>

    <!-- Filter Bulan & Tahun -->
    <form method="GET" class="mb-4 row g-2 justify-content-center">
        <div class="col-auto">
            <select name="month" class="form-select">
                <?php 
                for ($m = 1; $m <= 12; $m++) {
                    $selected = ($m == $month) ? "selected" : "";
                    echo "<option value='$m' $selected>".date('F', mktime(0,0,0,$m,1))."</option>";
                }
                ?>
            </select>
        </div>
        <div class="col-auto">
            <select name="year" class="form-select">
                <?php 
                $current_year = date('Y');
                for ($y = $current_year; $y >= $current_year-5; $y--) {
                    $selected = ($y == $year) ? "selected" : "";
                    echo "<option value='$y' $selected>$y</option>";
                }
                ?>
            </select>
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-primary">Filter</button>
        </div>
    </form>

    <!-- Card Balance & Summary -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card text-white bg-primary shadow">
                <div class="card-body">
                    <h5 class="card-title">💰 Balance</h5>
                    <p class="card-text fs-4">$<?= number_format($balance, 2) ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-success shadow">
                <div class="card-body">
                    <h5 class="card-title">✅ Total Win (<?= date('F Y', strtotime($selected_month.'-01')) ?>)</h5>
                    <p class="card-text fs-4">$<?= number_format($win_total, 2) ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-danger shadow">
                <div class="card-body">
                    <h5 class="card-title">❌ Total Lose (<?= date('F Y', strtotime($selected_month.'-01')) ?>)</h5>
                    <p class="card-text fs-4">$<?= number_format($lose_total, 2) ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Challenge Card -->
    <div class="card shadow mb-4 challenge-card <?= $progress >= 100 ? 'completed' : 'progressing' ?>">
        <div class="card-header">
            <h5 class="mb-0">🏆 Challenge <?= $current_challenge ?>: Target Balance $<?= number_format($target_balance, 2) ?></h5>
        </div>
        <div class="card-body">
            <p>Current Balance: <strong>$<?= number_format($balance, 2) ?></strong></p>
            <div class="progress challenge-progress mb-2">
                <div class="progress-bar bg-success" role="progressbar" 
                     style="width: <?= $progress ?>%;">
                     <?= round($progress, 1) ?>%
                </div>
            </div>
            <p class="challenge-status <?= $progress >= 100 ? 'text-success' : 'text-warning' ?>">
                <?= $progress >= 100 ? 'Completed ✅' : 'Progressing...' ?>
            </p>
        </div>
    </div>

    <!-- Daily PnL Line Chart -->
    <div class="card shadow mb-4">
        <div class="card-header">
            <h5 class="mb-0">📊 Daily PnL Chart</h5>
        </div>
        <div class="card-body">
            <canvas id="pnlChart" height="100"></canvas>
        </div>
    </div>

    <!-- Daily PnL Calendar -->
    <div class="card shadow mb-4">
        <div class="card-header">
            <h5 class="mb-0">📅 Daily PnL - <?= date('F Y', strtotime($selected_month.'-01')) ?></h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered text-center align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Minggu</th>
                            <th>Senin</th>
                            <th>Selasa</th>
                            <th>Rabu</th>
                            <th>Kamis</th>
                            <th>Jumat</th>
                            <th>Sabtu</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    $days_in_month = date('t', strtotime($selected_month.'-01'));
                    $first_day_week = date('w', strtotime($selected_month.'-01')); // 0=Sun,6=Sat
                    $row = [];

                    // isi kosong sebelum tanggal 1
                    for ($i=0; $i<$first_day_week; $i++) {
                        $row[] = "";
                    }

                    for ($d=1; $d <= $days_in_month; $d++) {
                        $date = sprintf("%s-%02d", $selected_month, $d);

                        if (!array_key_exists($date, $daily_pnl)) {
                            $pnl = null;
                            $class = "pnl-neutral";
                        } else {
                            $pnl = $daily_pnl[$date];
                            if ($pnl > 0) $class = "pnl-positive";
                            elseif ($pnl < 0) $class = "pnl-negative";
                            else $class = "pnl-neutral";
                        }

                        $box = "<div class='pnl-box $class'>$d<br>" . ($pnl !== null ? "$" . number_format($pnl, 2) : "-") . "</div>";
                        $row[] = $box;

                        if (count($row) == 7) {
                            echo "<tr>";
                            foreach ($row as $cell) {
                                echo "<td>$cell</td>";
                            }
                            echo "</tr>";
                            $row = [];
                        }
                    }

                    if (count($row) > 0) {
                        while (count($row) < 7) {
                            $row[] = "";
                        }
                        echo "<tr>";
                        foreach ($row as $cell) {
                            echo "<td>$cell</td>";
                        }
                        echo "</tr>";
                    }
                    ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Table Trading Journal -->
    <div class="card shadow">
        <div class="card-header d-flex justify-content-between">
            <h5 class="mb-0">📝 Trading History (<?= date('F Y', strtotime($selected_month.'-01')) ?>)</h5>
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
                        <th>PNL (USD)</th>
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
                            <td>$<?= number_format($row['pnl_usd'], 2) ?></td>
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
                    <tr><td colspan="8" class="text-center">No trades recorded for this month.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Script Chart.js -->
<script>
    const ctx = document.getElementById('pnlChart').getContext('2d');
    const pnlChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?= json_encode(array_keys($daily_pnl)) ?>,
            datasets: [{
                label: 'Daily PnL',
                data: <?= json_encode(array_values($daily_pnl)) ?>,
                borderColor: '#007bff',
                backgroundColor: 'rgba(0, 123, 255, 0.2)',
                tension: 0.3,
                fill: true,
                pointRadius: 5,
                pointHoverRadius: 7,
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: true }
            },
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
</script>

</body>
</html>
