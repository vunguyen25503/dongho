<?php
include 'db_connect.php';

// Kiểm tra nếu có `id` trên URL
if (isset($_GET['id'])) {
    $product_id = $_GET['id'];

    // Truy vấn để lấy thông tin sản phẩm (để xóa ảnh cũ nếu có)
    $sql = "SELECT image FROM products WHERE id = :product_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
    $stmt->execute();

    // Kiểm tra nếu có sản phẩm
    if ($stmt->rowCount() > 0) {
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        // Xóa ảnh cũ nếu tồn tại
        if (!empty($product['image']) && file_exists("../" . $product['image'])) {
            unlink("../" . $product['image']);
        }

        // Xóa sản phẩm đồng hồ từ cơ sở dữ liệu
        $delete_sql = "DELETE FROM products WHERE id = :product_id";
        $delete_stmt = $conn->prepare($delete_sql);
        $delete_stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);

        if ($delete_stmt->execute()) {
            header("Location: view_products.php?status=deleted"); // Chuyển hướng về danh sách sản phẩm
            exit();
        } else {
            echo "Lỗi khi xóa sản phẩm: " . $conn->errorInfo()[2];
        }
    } else {
        echo "Không tìm thấy sản phẩm đồng hồ.";
    }
} else {
    echo "ID sản phẩm không hợp lệ.";
}

$conn->close();
?>
