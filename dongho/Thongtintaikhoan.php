<?php
    session_start();
include 'db_connect.php'; // Kết nối cơ sở dữ liệu
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

// Retrieve cart items if user is logged in
if ($user_id) {
    $stmt = $conn->prepare("
        SELECT 
            c.quantity, 
            p.id AS product_id, p.name, p.price, p.image, p.discount 
        FROM cart c
        JOIN products p ON c.product_id = p.id
        WHERE c.user_id = ?
    ");
    $stmt->execute([$user_id]);
    $cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $cartItems = [];
}

// Handle item removal
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_item'])) {
    $product_id = $_POST['product_id'];
    $stmt = $conn->prepare("DELETE FROM cart WHERE product_id = ? AND user_id = ?");
    $stmt->execute([$product_id, $user_id]);
    header("Location: dongho.php"); // Redirect to refresh cart
    exit;
}

try {
    // Kiểm tra xem URL có truyền ID không
    if (isset($_GET['id'])) {
        $id = $_GET['id'];

        // Lấy thông tin người dùng hiện tại từ CSDL
        $stmt = $conn->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $user = $stmt->fetch();

        // Kiểm tra nếu người dùng tồn tại
        if (!$user) {
            echo "Người dùng không tồn tại.";
            exit;
        }

        // Cập nhật thông tin người dùng khi form được submit
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Lấy dữ liệu từ form
            $username = $_POST['username'];
            $email = $_POST['email'];
            $phone = $_POST['phone'];
            $address = $_POST['address'];

            // Thực hiện câu truy vấn UPDATE để lưu dữ liệu mới
            $stmt = $conn->prepare("UPDATE users SET 
                                    username = :username, 
                                    email = :email, 
                                    phone = :phone, 
                                    address = :address 
                                    WHERE id = :id");

            // Gán giá trị các biến vào prepared statement
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':phone', $phone);
            $stmt->bindParam(':address', $address);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);

            // Thực thi câu truy vấn
            $stmt->execute();

            // Điều hướng quay lại trang quản lý sau khi cập nhật thành công
            header("Location: dongho.php");
            exit;
        }
    } else {
        echo "ID người dùng không hợp lệ.";
        exit;
    }
} catch (PDOException $e) {
    echo "Lỗi: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Đăng nhập</title>
    <style>
        /* General Styling */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        header .banner {
            background: #333;
            color: #fff;
            text-align: center;
            padding: 20px 0;
            position: relative;
        }

        .logo {
            width: 55px;
            height: auto;
        }

        .logo-caption {
            font-size: 14px;
            color: #fff;
            font-weight: bold;
            margin-top: 5px;
        }

        /* Top-right menu for login, register, cart, or user dropdown */
        .auth-menu {
            position: absolute;
            top: 10px;
            right: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .auth-menu a, .user-info {
            color: #fff;
            text-decoration: none;
            padding: 5px 10px;
            background-color: #333;
            border-radius: 5px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 5px;
            transition: background-color 0.3s ease;
        }

        .auth-menu a:hover, .user-info:hover {
            background-color: #f60;
        }

        /* General Styling for the Cart Icon */
.cart-icon {
    display: flex;
    align-items: center;
    color: #fff;
    text-decoration: none;
    transition: background-color 0.3s ease, transform 0.2s ease;
    padding: 8px 10px; /* Adjust padding for better alignment */
    border-radius: 5px;
    font-weight: bold;
}

/* Styling the Default and Hover Icons */
.cart-icon img {
    width: 24px; /* Adjust icon size */
    height: auto;
    margin-left: 5px;
}

.cart-icon:hover {
    background-color: #f60;
    color: #fff;
}

.cart-icon .default-icon {
    display: block;
}

.cart-icon .hover-icon {
    display: none;
}

.cart-icon:hover .default-icon {
    display: none;
}

.cart-icon:hover .hover-icon {
    display: block;
}

        .user-dropdown {
            position: relative;
            display: inline-block;
        }

        .user-dropdown-content {
            display: none;
            position: absolute;
            background-color: #444;
            min-width: 120px;
            right: 0;
            box-shadow: 0px 8px 16px rgba(0, 0, 0, 0.2);
            z-index: 1;
        }

        .user-dropdown-content a {
            color: white;
            padding: 12px 16px;
            text-decoration: none;
            display: block;
            text-align: left;
        }

        .user-dropdown-content a:hover {
            background-color: #555;
        }

        .user-dropdown:hover .user-dropdown-content {
            display: block;
        }

        /* Top Menu */
        .top-menu ul {
            list-style: none;
            display: flex;
            justify-content: center;
            background-color: #444;
            margin: 0;
            padding: 0;
            position: relative;
        }

        .top-menu ul li {
            margin: 0 10px;
        }

        .top-menu ul li a {
            color: #fff;
            text-decoration: none;
            padding: 10px 20px;
            font-size: 16px;
            font-weight: bold;
            display: inline-block;
            transition: color 0.3s, background-color 0.3s;
        }

        .top-menu ul li a:hover {
            background-color: #f60;
            color: #333;
            border-radius: 5px;
        }

        /* Login Form Styling */
        .login-container {
            max-width: 500px;
            margin: 50px auto;
            padding: 20px;
            border: 1px solid #ddd;
            background-color: #f9f9f9;
            border-radius: 5px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }

        .login-container h2 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }

        .login-form label {
            display: block;
            margin-bottom: 5px;
            color: #333;
            font-weight: bold;
        }

        .login-form input {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 3px;
            box-sizing: border-box;
        }

        .login-btn {
            width: 100%;
            padding: 10px;
            background-color: #f60;
            color: #fff;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
        }

        .login-btn:hover {
            background-color: #e55a00;
        }

        /* Error message */
        .error-message {
            color: red;
            text-align: center;
            margin-bottom: 15px;
        }

        .footer {
            background-color: #f9f9f9;
         
            color: #333;
            font-size: 14px;
        }

        .footer-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            max-width: 1200px;
            margin: 0 auto;
        }

        .footer-column {
            width: 22%;
            margin-bottom: 20px;
        }

        .footer-column h3 {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 10px;
            color: #000;
        }

        .footer-column ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .footer-column ul li {
            margin-bottom: 8px;
        }

        .footer-column ul li a {
            text-decoration: none;
            color: #333;
            font-size: 14px;
        }

        .footer-column ul li a:hover {
            color: #007bff;
            text-decoration: underline;
        }

        .footer-logos {
            margin-top: 10px;
        }

        .footer-logos img {
            width: 100px;
            margin-right: 10px;
        }

        .footer-bottom {
            text-align: center;
            border-top: 1px solid #ddd;
            padding-top: 20px;
            margin-top: 20px;
            font-size: 14px;
            color: #666;
        }

        .footer-bottom {
            text-align: center;
            margin-top: 20px;
            font-size: 12px;
            color: #777;
        }
        main {
    max-width: 600px;
    margin: 30px auto;
    padding: 20px;
    background-color: #fff;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

main h2 {
    text-align: center;
    color: #333;
    font-size: 24px;
    margin-bottom: 20px;
    font-weight: bold;
}

main form {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

main label {
    font-size: 16px;
    color: #555;
    font-weight: 600;
    margin-bottom: 5px;
}

main input[type="text"],
main input[type="email"],
main input[type="password"] {
    width: 100%;
    padding: 12px;
    font-size: 16px;
    border: 1px solid #ddd;
    border-radius: 5px;
    box-sizing: border-box;
    transition: border-color 0.3s ease;
}

main input[type="text"]:focus,
main input[type="email"]:focus,
main input[type="password"]:focus {
    border-color: #f60;
    outline: none;
}

main button[type="submit"] {
    padding: 12px;
    background-color: #f60;
    color: #fff;
    border: none;
    border-radius: 5px;
    font-size: 18px;
    cursor: pointer;
    font-weight: bold;
    transition: background-color 0.3s ease;
}

main button[type="submit"]:hover {
    background-color: #e55a00;
}

main .button {
    display: inline-block;
    padding: 10px 15px;
    font-size: 16px;
    color: #333;
    text-decoration: none;
    border: 1px solid #ddd;
    border-radius: 5px;
    text-align: center;
    transition: background-color 0.3s ease, color 0.3s ease;
    margin-top: 10px;
}

main .button:hover {
    background-color: #f5f5f5;
    color: #f60;
}

main a.button {
    text-decoration: none;
    color: #333;
}

    </style>
</head>
<body>

<!-- Banner -->
<header>
    <div class="banner">
        <a href="dongho.php">
            <img src="https://play-lh.googleusercontent.com/ysE2vm-N3xxHc7nQa5v0wsk895_lI8diEGlC27QtWbJcVG7MQ2FfMcZ0qEqgy20dJw" alt="Logo" class="logo">
        </a>
        <p class="logo-caption">VUA ĐỒNG HỒ</p>
        
        <div class="auth-menu">
            <?php if (isset($_SESSION['username'])): ?>
                <div class="user-dropdown">
                    <span class="user-info"><?php echo htmlspecialchars($_SESSION['username']); ?> ▼</span>
                    <div class="user-dropdown-content">
                        <a href="logout.php">Đăng xuất</a>
                    </div>
                </div>
            <?php else: ?>
                <a href="login.php">Đăng nhập</a>
                <a href="register.php">Đăng ký</a>
            <?php endif; ?>
            
            <a href="cart.php" class="cart-icon">
                Giỏ Hàng 
                <img src="https://encrypted-tbn2.gstatic.com/images?q=tbn:ANd9GcQ5BzqJMkEtB_00QrlN-RHiusSV8a4Pnij46sWTwc6Wo_HIqFEx" class="default-icon" alt="Cart Icon">
                <img src="https://vuahanghieu.com/assets/images/device-cart-bag.png" class="hover-icon" alt="Cart Hover Icon">
            </a>
        </div>
    </div>
</header>


<nav class="top-menu">
    <ul>
        <li><a href="dongho.php">Trang chủ</a></li>
        <li><a href="sanpham.php">Sản phẩm</a></li>
        <li><a href="cart.php">Giỏ hàng</a></li>
        <li><a href="contact.php">Liên hệ</a></li>
    </ul>
</nav>

  <main>
        <h2>Chỉnh sửa thông tin người dùng</h2>
        <form action="" method="POST">
            <label for="username">Tên đăng nhập:</label>
            <input type="text" name="username" id="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>

            <label for="email">Email:</label>
            <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>

            <label for="phone">Số điện thoại:</label>
            <input type="text" name="phone" id="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" required>

            <label for="address">Địa chỉ:</label>
            <input type="text" name="address" id="address" value="<?php echo htmlspecialchars($user['address']); ?>" required>

            <button type="submit">Lưu thay đổi</button>
            <a href="view_user.php" class="button">Hủy</a>
        </form>
    </main>

<!-- Footer -->
<!-- Footer -->
 <footer class="footer">
        <div class="footer-container">
            <div class="footer-column">
                <h3>THÔNG TIN</h3>
                <ul>
                    <li><a href="#">Giới thiệu về Vua Đồng Hồ Việt Nam</a></li>
                    <li><a href="#">Quy chế hoạt động</a></li>
                    <li><a href="#">Hợp tác với Vua Đồng Hồ</a></li>
                    <li><a href="#">Chương trình Affiliate - Cộng tác viên</a></li>
                    <li><a href="#">Phụ trách nội dung</a></li>
                    <li><a href="#">Tuyển dụng</a></li>
                    <li><a href="#">Liên hệ</a></li>
                </ul>
                <div class="footer-logos">
                    <img src="https://www.locklizard.com/wp-content/uploads/2023/04/dmca-protected.png" alt="DMCA Logo">
                    <img src="https://awery.aero/uploads/5693781eeaf2534102203.JPEG" alt="TrustLock Verified">
                </div>
            </div>

            <div class="footer-column">
                <h3>CÂU HỎI THƯỜNG GẶP</h3>
                <ul>
                    <li><a href="#">Hướng dẫn cách tra cứu mã đơn hàng</a></li>
                    <li><a href="#">Sản phẩm cần đổi hết hàng?</a></li>
                    <li><a href="#">Nếu không có hóa đơn, tôi có thể trả lại không?</a></li>
                    <li><a href="#">Đơn hàng bị thiếu sản phẩm?</a></li>
                    <li><a href="#">Sản phẩm không giống với ảnh?</a></li>
                    <li><a href="#">Tại sao đơn hàng bị hủy?</a></li>
                    <li><a href="#">Nếu gặp vấn đề, tôi cần liên hệ với ai?</a></li>
                </ul>
            </div>

            <div class="footer-column">
                <h3>CHĂM SÓC KHÁCH HÀNG</h3>
                <ul>
                    <li><a href="#">Chính sách bán hàng</a></li>
                    <li><a href="#">Chính sách bảo hành</a></li>
                    <li><a href="#">Chính sách giao hàng</a></li>
                    <li><a href="#">Chính sách đổi trả và hoàn tiền</a></li>
                    <li><a href="#">Chính sách thanh toán</a></li>
                    <li><a href="#">Chính sách bảo mật</a></li>
                    <li><a href="#">Cơ chế giải quyết tranh chấp</a></li>
                </ul>
            </div>

            <div class="footer-column">
                <h3>DỊCH VỤ KHÁCH HÀNG</h3>
                <ul>
                    <li><strong>Tên doanh nghiệp:</strong> Công Ty Cổ Phần Thương Mại Vua Đồng Hồ</li>
                    <li><strong>Hotline:</strong> 012.345.6789</li>
                    <li><strong>Tổng đài:</strong> 1900 12345</li>
                    <li><strong>Email:</strong> cskh@vuadongho.com</li>
                    <li><strong>Văn phòng:</strong> Tầng 4, Tòa nhà HA9, Uneti</li>
                    <li><strong>Mã số thuế:</strong> 00000000</li>
                </ul>
            </div>
        </div>

        <div class="footer-bottom">
            &copy; 2024 VUA ĐỒNG HỒ. ĐIỀU KHOẢN | CHÍNH SÁCH BẢO MẬT
            <p>Vũ Phi Long - 26/10/2003 | Nguyễn Minh Vũ - 25/05/2003 | Phạm Xuân Quý - 06/02/2003</p>
        </div>
    </footer>
    <script>
    // JavaScript for Carousel Functionality
    const slides = document.querySelectorAll('.carousel-slide');
    const totalSlides = slides.length;
    let currentIndex = 0;
    let carouselInterval;

    function showSlide(index) {
        const slidesContainer = document.querySelector('.carousel-slides');
        slidesContainer.style.transform = `translateX(-${index * 100}%)`;
    }

    function nextSlide() {
        currentIndex = (currentIndex + 1) % totalSlides;
        showSlide(currentIndex);
    }

    function prevSlide() {
        currentIndex = (currentIndex - 1 + totalSlides) % totalSlides;
        showSlide(currentIndex);
    }

    // Auto-slide function
    function startAutoSlide() {
        carouselInterval = setInterval(nextSlide, 4500);
    }

    // Stop auto-slide on manual navigation
    function stopAutoSlide() {
        clearInterval(carouselInterval);
    }

    // Event listeners for navigation arrows
    document.getElementById('next').addEventListener('click', () => {
        stopAutoSlide();
        nextSlide();
        startAutoSlide();
    });

    document.getElementById('prev').addEventListener('click', () => {
        stopAutoSlide();
        prevSlide();
        startAutoSlide();
    });

    // Start the carousel auto-slide on page load
    startAutoSlide();
</script>

</body>
</html>

