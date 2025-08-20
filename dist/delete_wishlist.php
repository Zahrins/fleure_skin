<?php

require_once 'koneksi.php';
$conn = getConnection();

$id = $_GET['id'] ?? 0;

if ($id) {
    $id = intval($id);
    $sql = "DELETE FROM wishlist_product WHERE id = $id";

    if ($conn->query($sql)) {
        $_SESSION['success'] = "data deleted successfully";
    } else {
        $_SESSION['error'] = "failed to delete data: " . $conn->error;
    }
}

header("Location: products.php");
exit;

?>
