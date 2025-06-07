<?php
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_category'])) {
    $category_id = $_POST['category_id'];
    $category_name = $_POST['category_name'];
    $category_description = $_POST['category_description'];

    try {
        $stmt = $conn->prepare("UPDATE watch_categories SET category_name = :category_name, category_description = :category_description WHERE category_id = :category_id");
        $stmt->bindParam(':category_id', $category_id);
        $stmt->bindParam(':category_name', $category_name);
        $stmt->bindParam(':category_description', $category_description);
        $stmt->execute();
        echo "Sửa loại đồng hồ thành công!";
        header('Location: manage_watch_categories.php'); // Quay lại trang quản lý sau khi sửa
    } catch (PDOException $e) {
        echo "Lỗi: " . $e->getMessage();
    }
}

// Lấy thông tin loại đồng hồ cần sửa
if (isset($_GET['id'])) {
    $category_id = $_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM watch_categories WHERE category_id = :category_id");
    $stmt->bindParam(':category_id', $category_id);
    $stmt->execute();
    $category = $stmt->fetch();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Sửa Loại Đồng Hồ</title>
    <style>
        /* Thiết lập chung cho trang */
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f7fc;
            color: #333;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .container {
            width: 100%;
            max-width: 600px;
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
            font-size: 28px;
            color: #333;
            font-weight: 600;
        }

        label {
            display: block;
            margin: 10px 0 5px;
            font-weight: 500;
            font-size: 16px;
            color: #555;
        }

        input[type="text"],
        textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            box-sizing: border-box;
        }

        input[type="text"]:focus,
        textarea:focus {
            border-color: #4CAF50;
            outline: none;
        }

        textarea {
            resize: vertical;
            min-height: 80px;
        }

        input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            padding: 15px 25px;
            border: none;
            border-radius: 5px;
            font-size: 18px;
            cursor: pointer;
            transition: background-color 0.3s;
            width: 100%;
        }

        input[type="submit"]:hover {
            background-color: #45a049;
        }

        input[type="submit"]:focus {
            outline: none;
        }

        /* Thêm sự kiện hover cho các option */
        select option:hover {
            background-color: #f0f0f0;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Sửa Loại Đồng Hồ</h2>
    <form action="edit_category.php" method="post">
        <input type="hidden" name="category_id" value="<?php echo $category['category_id']; ?>">
        <label for="category_name">Tên loại đồng hồ:</label>
        <input type="text" name="category_name" value="<?php echo $category['category_name']; ?>" required>

        <label for="category_description">Mô tả:</label>
        <textarea name="category_description" required><?php echo $category['category_description']; ?></textarea>

        <input type="submit" name="edit_category" value="Lưu Thay Đổi">
    </form>
</div>

</body>
</html>

<?php
// Đóng kết nối
$conn = null;
?>
