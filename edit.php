<?php
include 'db.php';

// Ambil data trade berdasarkan ID
if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$id = $_GET['id'];
$trade = $conn->query("SELECT * FROM trades WHERE id = $id")->fetch_assoc();

// Ambil pair untuk dropdown
$pairs = $conn->query("SELECT * FROM pairs ORDER BY name ASC");

// Proses update data
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $pair_id = $_POST['pair_id'];
    $position = $_POST['position'];
    $result = $_POST['result'];
    $amount_usd = $_POST['amount_usd'];
    $trade_date = $_POST['trade_date'];

    // Hitung ulang balance (kembalikan balance lama dulu)
    if ($trade['result'] == "win") {
        $conn->query("UPDATE balance SET total_balance = total_balance - {$trade['amount_usd']} WHERE id=1");
    } else {
        $conn->query("UPDATE balance SET total_balance = total_balance + {$trade['amount_usd']} WHERE id=1");
    }

    // Update data trade
    $sql = "UPDATE trades SET 
                pair_id='$pair_id',
                position='$position',
                result='$result',
                amount_usd='$amount_usd',
                trade_date='$trade_date'
            WHERE id=$id";

    if ($conn->query($sql) === TRUE) {
        // Update balance sesuai result baru
        if ($result == "win") {
            $conn->query("UPDATE balance SET total_balance = total_balance + $amount_usd WHERE id=1");
        } else {
            $conn->query("UPDATE balance SET total_balance = total_balance - $amount_usd WHERE id=1");
        }

        header("Location: index.php?msg=Trade updated successfully");
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
    <title>Edit Trade - Daily Trading Journal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-4">
    <h2 class="mb-4">✏️ Edit Trade</h2>

    <div class="card shadow">
        <div class="card-body">
            <form method="POST">
                <!-- Pair -->
                <div class="mb-3">
                    <label class="form-label">Pair</label>
                    <select class="form-select" name="pair_id" required>
                        <?php while($row = $pairs->fetch_assoc()): ?>
                            <option value="<?= $row['id'] ?>" <?= $trade['pair_id']==$row['id'] ? 'selected' : '' ?>>
                                <?= $row['name'] ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <!-- Position -->
                <div class="mb-3">
                    <label class="form-label">Position</label>
                    <select class="form-select" name="position" required>
                        <option value="long" <?= $trade['position']=='long' ? 'selected' : '' ?>>Long</option>
                        <option value="short" <?= $trade['position']=='short' ? 'selected' : '' ?>>Short</option>
                    </select>
                </div>

                <!-- Result -->
                <div class="mb-3">
                    <label class="form-label">Result</label>
                    <select class="form-select" name="result" required>
                        <option value="win" <?= $trade['result']=='win' ? 'selected' : '' ?>>Win</option>
                        <option value="lose" <?= $trade['result']=='lose' ? 'selected' : '' ?>>Lose</option>
                    </select>
                </div>

                <!-- Amount -->
                <div class="mb-3">
                    <label class="form-label">Amount (USD)</label>
                    <input type="number" step="0.01" class="form-control" name="amount_usd" 
                           value="<?= $trade['amount_usd'] ?>" required>
                </div>

                <!-- Date -->
                <div class="mb-3">
                    <label class="form-label">Trade Date</label>
                    <input type="date" class="form-control" name="trade_date" 
                           value="<?= $trade['trade_date'] ?>" required>
                </div>

                <!-- Buttons -->
                <button type="submit" class="btn btn-warning">Update Trade</button>
                <a href="index.php" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>

</body>
</html>
