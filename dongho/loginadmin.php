<?php
session_start();
include 'db_connect.php'; // Kết nối cơ sở dữ liệu

$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Truy vấn thông tin người dùng
    $query = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $query->execute([$username]);
    $user = $query->fetch(PDO::FETCH_ASSOC);

    // Kiểm tra mật khẩu
    if ($user && password_verify($password, $user['password'])) {
        // Lưu thông tin người dùng vào session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['vaitro'] = $user['vaitro'];  // Lưu vai trò vào session

        // Kiểm tra vai trò và điều hướng tương ứng
        if ($user['vaitro'] === 'admin') {
            header("Location: index.php");  // Chuyển hướng tới trang admin nếu là admin
        } else {
            header("Location: dongho.php");  // Chuyển hướng tới trang chính nếu không phải admin
        }

        exit;
    } else {
        $error = "Tên đăng nhập hoặc mật khẩu không đúng!";
    }
}
?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Creative - Bootstrap 3 Responsive Admin Template">
    <meta name="author" content="GeeksLabs">
    <meta name="keyword" content="Creative, Dashboard, Admin, Template, Theme, Bootstrap, Responsive, Retina, Minimal">
    <link rel="shortcut icon" href="img/favicon.png">

    <title>Login</title>

    <!-- Bootstrap CSS -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <!-- bootstrap theme -->
    <link href="css/bootstrap-theme.css" rel="stylesheet">
    <!--external css-->
    <!-- font icon -->
    <link href="css/elegant-icons-style.css" rel="stylesheet" />
    <link href="css/font-awesome.css" rel="stylesheet" />
    <!-- Custom styles -->
    <link href="css/style.css" rel="stylesheet">
    <link href="css/style-responsive.css" rel="stylesheet" />

</head>

<body class="login-img3-body">
    <div class="container">
        <form class="login-form" action="login.php" method="POST">
            <div class="login-wrap">
                <p class="login-img"><i class="icon_lock_alt"></i></p>

                <div class="input-group">
                    <span class="input-group-addon"><i class="icon_profile"></i></span>
                    <input type="text" name="username" class="form-control" placeholder="Username" required autofocus>
                </div>

                <div class="input-group">
                    <span class="input-group-addon"><i class="icon_key_alt"></i></span>
                    <input type="password" name="password" class="form-control" placeholder="Password" required>
                </div>

                <label class="checkbox">
                    <input type="checkbox" name="remember-me" value="remember-me"> Remember me
                    <span class="pull-right"><a href="#"> Forgot Password?</a></span>
                </label>

                <button class="btn btn-primary btn-lg btn-block" type="submit">Login</button>

                <!-- Hiển thị thông báo lỗi nếu có -->
                <?php if (!empty($error)): ?>
                <p style="color:red;"><?= htmlspecialchars($error) ?></p>
                <?php endif; ?>

            </div>
        </form>
    </div>
</body>

</html>
