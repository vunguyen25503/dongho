<?php
include 'db_connect.php'; // Kết nối cơ sở dữ liệu

// Kiểm tra nếu có `id` trên URL
if (isset($_GET['id'])) {
    $brand_id = $_GET['id'];

    // Truy vấn để lấy thông tin thương hiệu (để xóa logo cũ nếu có)
    $sql = "SELECT logo_brands FROM brands WHERE brand_id = :brand_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':brand_id', $brand_id, PDO::PARAM_INT);
    $stmt->execute();

    // Kiểm tra nếu có thương hiệu
    if ($stmt->rowCount() > 0) {
        $brand = $stmt->fetch(PDO::FETCH_ASSOC);

        // Xóa logo cũ nếu tồn tại
        if (!empty($brand['logo_brands']) && file_exists("../" . $brand['logo_brands'])) {
            unlink("../" . $brand['logo_brands']);
        }

        // Xóa thương hiệu khỏi cơ sở dữ liệu
        $delete_sql = "DELETE FROM brands WHERE brand_id = :brand_id";
        $delete_stmt = $conn->prepare($delete_sql);
        $delete_stmt->bindParam(':brand_id', $brand_id, PDO::PARAM_INT);

        if ($delete_stmt->execute()) {
            header("Location: view_brand.php?status=deleted"); // Chuyển hướng về danh sách thương hiệu
            exit();
        } else {







            echo "Lỗi khi xóa thương hiệu: " . $conn->errorInfo()[2];
        }
    } else {
        echo "Không tìm thấy thương hiệu.";
    }
} else {
    echo "ID thương hiệu không hợp lệ.";
}

$conn->close();
?>
