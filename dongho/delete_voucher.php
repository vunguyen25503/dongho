<?php
include 'db_connect.php'; // Kết nối cơ sở dữ liệu

// Kiểm tra nếu có `id` trên URL
if (isset($_GET['id'])) {
    $voucher_id = $_GET['id'];

    // Truy vấn để lấy thông tin voucher (nếu cần xóa dữ liệu liên quan)
    $sql = "SELECT * FROM discount_codes WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $voucher_id, PDO::PARAM_INT);
    $stmt->execute();

    // Kiểm tra nếu tồn tại voucher
    if ($stmt->rowCount() > 0) {
        // Xóa voucher khỏi cơ sở dữ liệu
        $delete_sql = "DELETE FROM discount_codes WHERE id = :id";
        $delete_stmt = $conn->prepare($delete_sql);
        $delete_stmt->bindParam(':id', $voucher_id, PDO::PARAM_INT);

        if ($delete_stmt->execute()) {
            header("Location: view_voucher.php?status=deleted"); // Chuyển hướng về danh sách voucher
            exit();
        } else {
            echo "Lỗi khi xóa voucher: " . $conn->errorInfo()[2];
        }
    } else {
        echo "Không tìm thấy voucher.";
    }
} else {
    echo "ID voucher không hợp lệ.";
}

$conn->close();
?>
