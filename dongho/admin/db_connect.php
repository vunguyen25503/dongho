<?php
// db_connect.php
$servername = "localhost";  // Máy chủ cơ sở dữ liệu
$username = "root";         // Tên người dùng
$password = "";             // Mật khẩu (rỗng cho người dùng root)
$dbname = "dongho";         // Tên cơ sở dữ liệu

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Kết nối thất bại: " . $e->getMessage();
}
?>
