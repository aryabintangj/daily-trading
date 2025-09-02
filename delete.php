<?php
include 'db.php';

// Pastikan ada ID trade
if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$id = $_GET['id'];

// Ambil data trade dulu
$trade = $conn->query("SELECT * FROM trades WHERE id=$id")->fetch_assoc();

if ($trade) {
    // Koreksi balance
    if ($trade['result'] == "win") {
        $conn->query("UPDATE balance SET total_balance = total_balance - {$trade['amount_usd']} WHERE id=1");
    } else {
        $conn->query("UPDATE balance SET total_balance = total_balance + {$trade['amount_usd']} WHERE id=1");
    }

    // Hapus trade dari database
    $sql = "DELETE FROM trades WHERE id=$id";
    if ($conn->query($sql) === TRUE) {
        header("Location: index.php?msg=Trade deleted successfully");
        exit();
    } else {
        echo "Error: " . $conn->error;
    }
} else {
    header("Location: index.php?msg=Trade not found");
    exit();
}
?>
