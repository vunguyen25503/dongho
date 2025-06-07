<?php
session_start();
include 'db_connect.php'; // Kết nối cơ sở dữ liệu
$message = "";

// Kiểm tra người dùng đã đăng nhập
if (!isset($_SESSION['user_id'])) {
    $message = "Vui lòng đăng nhập để xem thông tin liên hệ.";
    $contactInfo = null;
    $cartItems = [];
} else {
    $userId = $_SESSION['user_id'];

    // Lấy thông tin giỏ hàng
    $stmt = $conn->prepare("
        SELECT 
            c.quantity, 
            p.id AS product_id, p.name, p.price, p.image, p.discount 
        FROM cart c
        JOIN products p ON c.product_id = p.id
        WHERE c.user_id = ?
    ");
    $stmt->execute([$userId]);
    $cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Lấy thông tin liên hệ từ bảng contacts
    $stmt = $conn->prepare("
        SELECT Ten AS name, email, sdt AS phone
        FROM contacts 
        WHERE user_id = ?
        ORDER BY created_at DESC 
        LIMIT 1
    ");
    $stmt->execute([$userId]);
    $contactInfo = $stmt->fetch(PDO::FETCH_ASSOC);

    // Nếu không có thông tin nào, thiết lập giá trị mặc định
    if (!$contactInfo) {
        $message = "Bạn chưa có thông tin liên hệ nào.";
        $contactInfo = [
            'name' => '',
            'email' => '',
            'phone' => '',
            'subject' => '',
            'message' => '',
        ];
    }

    // Xử lý form liên hệ khi được gửi
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_contact'])) {
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone']);
        $subject = trim($_POST['subject']);
        $content = trim($_POST['content']);

        // Xác thực dữ liệu nhập
        if (empty($name) || empty($email) || empty($phone) || empty($subject) || empty($content)) {
            $message = "Vui lòng điền đầy đủ tất cả thông tin.";
        } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = "Email không hợp lệ.";
        } else if (!preg_match('/^\d{10,11}$/', $phone)) {
            $message = "Số điện thoại không hợp lệ.";
        } else {
            // Lấy thông tin người dùng từ CSDL
            $query = $conn->prepare("SELECT * FROM users WHERE id = ?");
            $query->execute([$userId]);
            $user = $query->fetch(PDO::FETCH_ASSOC);

            // Kiểm tra email và số điện thoại
            if ($user && $user['email'] == $email && $user['phone'] == $phone) {
                // Kiểm tra xem nội dung đã gửi chưa
                $checkContentQuery = $conn->prepare("SELECT * FROM contacts WHERE user_id = ? AND message = ?");
                $checkContentQuery->execute([$userId, $content]);
                $existingMessage = $checkContentQuery->fetch(PDO::FETCH_ASSOC);

                if ($existingMessage) {
                    $message = "Nội dung này bạn đã gửi rồi.";
                } else {
                    // Thêm liên hệ mới vào CSDL
                    $insertQuery = $conn->prepare("INSERT INTO contacts (user_id, Ten, email, sdt, Subject, message) VALUES (?, ?, ?, ?, ?, ?)");
                    $insertQuery->execute([$userId, $name, $email, $phone, $subject, $content]);
                    $message = "Liên hệ của bạn đã được gửi thành công!";
                }
            } else {
                $message = "Email hoặc số điện thoại không trùng với tài khoản của bạn.";
            }
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Liên hệ</title>
    <style>
        /* Giữ nguyên các kiểu CSS */
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

        .top-menu ul {
            list-style: none;
            display: flex;
            justify-content: center;
            background-color: #444;
            margin: 0;
            padding: 0;
        }

        .top-menu ul li {
            margin: 0 10px;
        }

        .top-menu ul li a {
            color: #fff;
            text-decoration: none;
            padding: 10px 20px;
            display: block;
            font-size: 16px;
            font-weight: bold;
            transition: background-color 0.3s;
        }

        .top-menu ul li a:hover {
            background-color: #f60;
            color: #333;
            border-radius: 5px;
        }

        .contact-container { max-width: 600px; margin: 20px auto; padding: 20px; border: 1px solid #ddd; background-color: #f9f9f9; border-radius: 5px; box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1); }
        .contact-container h2 { text-align: center; color: #333; margin-bottom: 20px; }
        .contact-form label { display: block; margin-bottom: 5px; color: #333; font-weight: bold; }
        .contact-form input, .contact-form textarea { width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ddd; border-radius: 3px; box-sizing: border-box; }
        .contact-form textarea { resize: vertical; height: 100px; }
        .contact-btn { width: 100%; padding: 10px; background-color: #f60; color: #fff; border: none; border-radius: 3px; cursor: pointer; font-size: 16px; }
        .contact-btn:hover { background-color: #e55a00; }
        .notification { text-align: center; margin-bottom: 15px; color: red; }
        .notification.success { color: green; }
        
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

        .cart-dropdown-content {
            display: none;
            position: absolute;
            background-color: #fff;
            min-width: 300px;
            right: 0;
            box-shadow: 0px 8px 16px rgba(0, 0, 0, 0.2);
            z-index: 1;
            border: 1px solid #ddd;
            padding: 15px;
            text-align: left;
            color: #333;
        }

        .cart-dropdown-content .cart-item {
            display: flex;
            align-items: center;
            border-bottom: 1px solid #ddd;
            padding: 10px 0;
        }

        .cart-dropdown-content .cart-item img {
            width: 50px;
            height: auto;
            margin-right: 15px;
            border-radius: 5px;
        }

        .cart-dropdown-content .cart-item p {
            margin: 0;
            font-size: 16px;
        }

        .cart-dropdown:hover .cart-dropdown-content {
            display: block;
        }

        .cart-dropdown-content .view-cart-btn {
            background-color: #333;
            color: #fff;
            padding: 10px;
            text-align: center;
            display: block;
            border-radius: 5px;
            margin-top: 10px;
            text-decoration: none;
            font-weight: bold;
        }

        .cart-dropdown-content .view-cart-btn:hover {
            background-color: #f60;
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

        /* Additional styling for banner images */
        .ad-banner-middle {
            width: 100%;
            max-width: 1200px;
            margin: 20px auto;
            display: block;
            border-radius: 10px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
        }

        <!-- Footer -->
    </style>
</head>
<body>

<!-- Banner -->
<header>
    <div class="banner">
        <!-- Logo image with caption underneath -->
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
                         <a href="Thongtintaikhoan.php?id=<?php echo $_SESSION['user_id']; ?>">Thông tin cá nhân</a>

                    </div>
                </div>
            <?php else: ?>
                <a href="login.php">Đăng nhập</a>
                <a href="register.php">Đăng ký</a>
            <?php endif; ?>
            
            <!-- Cart dropdown with product image, quantity, and remove button -->
            <div class="cart-dropdown">
                <a href="#" class="cart-icon">
                    Giỏ Hàng (<?php echo count($cartItems); ?>)
                    <img src="https://encrypted-tbn2.gstatic.com/images?q=tbn:ANd9GcQ5BzqJMkEtB_00QrlN-RHiusSV8a4Pnij46sWTwc6Wo_HIqFEx" class="default-icon" alt="Cart Icon">
                    <img src="https://vuahanghieu.com/assets/images/device-cart-bag.png" class="hover-icon" alt="Cart Hover Icon">
                </a>
                <div class="cart-dropdown-content">
                    <?php if (count($cartItems) > 0): ?>
                        <?php foreach ($cartItems as $item): ?>
                            <div class="cart-item">
                                <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                                <div>
                                    <p><?php echo htmlspecialchars($item['name']); ?></p>
                                    <p>SL: <?php echo $item['quantity']; ?></p>
                                    <p style="color: #f60;">
                                        <?php 
                                        $discounted_price = $item['price'] * (1 - $item['discount'] / 100);
                                        echo number_format($discounted_price, 0, ',', '.') . " đ"; 
                                        ?>
                                    </p>
                                </div>
                                <!-- Remove item button -->
                                <form method="post" style="display: inline;">
                                    <input type="hidden" name="product_id" value="<?php echo $item['product_id']; ?>">
                                    <button type="submit" name="remove_item" class="remove-btn">Xóa</button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                        <a href="cart.php" class="view-cart-btn">Xem giỏ hàng</a>
                    <?php else: ?>
                        <p>Giỏ hàng của bạn đang rỗng!</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</header>

<!-- Top Menu -->
<nav class="top-menu">
    <ul>
        <li><a href="dongho.php">Trang chủ</a></li>
        <li><a href="sanpham.php">Sản phẩm</a></li>
        <li><a href="cart.php">Giỏ hàng</a></li>
        <li><a href="contact.php">Liên hệ</a></li>
    </ul>
</nav>

<main>
    <div class="contact-container">
        <h2>Liên hệ với chúng tôi</h2>
        <?php if ($message): ?>
            <p class="notification <?php echo ($message == "Liên hệ của bạn đã được gửi thành công!") ? 'success' : 'error'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </p>
        <?php endif; ?>

        <form action="contact.php" method="POST" class="contact-form">
            <label for="name">Họ và tên:</label>
            <input 
                type="text" 
                id="name" 
                name="name" 
                value="<?php echo htmlspecialchars($contactInfo['name'] ?? ''); ?>" 
                required>

            <label for="email">Email:</label>
            <input 
                type="email" 
                id="email" 
                name="email" 
                value="<?php echo htmlspecialchars($contactInfo['email'] ?? ''); ?>" 
                required>

            <label for="phone">Số điện thoại:</label>
            <input 
                type="tel" 
                id="phone" 
                name="phone" 
                value="<?php echo htmlspecialchars($contactInfo['phone'] ?? ''); ?>" 
                required>

            <label for="subject">Chủ đề:</label>
            <input 
                type="text" 
                id="subject" 
                name="subject" 
                value="<?php echo htmlspecialchars($contactInfo['subject'] ?? ''); ?>" 
                required>

            <label for="content">Nội dung:</label>
            <textarea 
                id="content" 
                name="content" 
                required><?php echo htmlspecialchars($contactInfo['message'] ?? ''); ?></textarea>

            <button type="submit" name="submit_contact" class="contact-btn">Gửi liên hệ</button>
        </form>
    </div>
</main>

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

</body>
</html>
