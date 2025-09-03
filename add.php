<?php
include 'db.php';

// Ambil pair dari database untuk dropdown
$pairs = $conn->query("SELECT * FROM pairs ORDER BY name ASC");

// Proses form submit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $pair_id = $_POST['pair_id'];
    $position = $_POST['position'];
    $result = $_POST['result'];
    $amount_usd = $_POST['amount_usd'];
    $trade_date = $_POST['trade_date'];

    // Hitung pnl_usd
    $pnl_usd = ($result == "win") ? $amount_usd : -$amount_usd;

    // Simpan ke tabel trades
    $sql = "INSERT INTO trades (pair_id, position, result, amount_usd, pnl_usd, trade_date) 
            VALUES ('$pair_id', '$position', '$result', '$amount_usd', '$pnl_usd', '$trade_date')";
    if ($conn->query($sql) === TRUE) {
        // Update balance sesuai pnl
        $conn->query("UPDATE balance SET total_balance = total_balance + $pnl_usd WHERE id=1");

        header("Location: index.php?msg=Trade added successfully");
        exit();
    } else {
        echo "Error: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Trade - Daily Trading Journal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-4">
    <h2 class="mb-4">➕ Add New Trade</h2>

    <div class="card shadow">
        <div class="card-body">
            <form method="POST">
                <!-- Pair -->
                <div class="mb-3">
                    <label for="pair_id" class="form-label">Pair</label>
                    <select class="form-select" name="pair_id" required>
                        <option value="">-- Select Pair --</option>
                        <?php while($row = $pairs->fetch_assoc()): ?>
                            <option value="<?= $row['id'] ?>"><?= $row['name'] ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <!-- Position -->
                <div class="mb-3">
                    <label class="form-label">Position</label>
                    <select class="form-select" name="position" required>
                        <option value="">-- Select Position --</option>
                        <option value="long">Long</option>
                        <option value="short">Short</option>
                    </select>
                </div>

                <!-- Result -->
                <div class="mb-3">
                    <label class="form-label">Result</label>
                    <select class="form-select" name="result" required>
                        <option value="">-- Select Result --</option>
                        <option value="win">Win</option>
                        <option value="lose">Lose</option>
                    </select>
                </div>

                <!-- Amount -->
                <div class="mb-3">
                    <label class="form-label">Amount (USD)</label>
                    <input type="number" step="0.01" class="form-control" name="amount_usd" required>
                </div>

                <!-- Date -->
                <div class="mb-3">
                    <label class="form-label">Trade Date</label>
                    <input type="date" class="form-control" name="trade_date" required>
                </div>

                <!-- Buttons -->
                <button type="submit" class="btn btn-success">Save Trade</button>
                <a href="index.php" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>

</body>
</html>
