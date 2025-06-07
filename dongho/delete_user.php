<?php
include 'db_connect.php'; // Kết nối cơ sở dữ liệu

// Kiểm tra nếu có `id` trên URL
if (isset($_GET['id'])) {
    $user_id = $_GET['id'];

    // Kiểm tra vai trò của người dùng trước khi xóa (không cho phép xóa Admin)
    $check_sql = "SELECT vaitro FROM users WHERE id = :user_id";
    $stmt = $conn->prepare($check_sql);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $check_row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($check_row['vaitro'] === 'admin') {
        // Nếu vai trò là Admin, không cho phép xóa
        echo "<script>
                alert('Không thể xóa tài khoản Admin.');
                window.location.href = 'view_users.php';
              </script>";
    } else {
        // Bắt đầu giao dịch
        $conn->beginTransaction();

        try {
            // Xóa các cart items của người dùng trước
            $delete_cart_sql = "DELETE FROM cart WHERE user_id = :user_id";
            $delete_cart_stmt = $conn->prepare($delete_cart_sql);
            $delete_cart_stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $delete_cart_stmt->execute();

            // Xóa người dùng
            $delete_user_sql = "DELETE FROM users WHERE id = :user_id";
            $delete_user_stmt = $conn->prepare($delete_user_sql);
            $delete_user_stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $delete_user_stmt->execute();

            // Commit giao dịch
            $conn->commit();

            header("Location: view_users.php?status=deleted"); // Chuyển hướng về danh sách người dùng
            exit();
        } catch (Exception $e) {
            // Nếu có lỗi, rollback giao dịch
            $conn->rollBack();
            echo "Lỗi khi xóa người dùng: " . $e->getMessage();
        }
    }
} else {
    echo "ID người dùng không hợp lệ.";
}

$conn = null; // Đóng kết nối
?>
