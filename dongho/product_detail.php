<?php
session_start();
include 'db_connect.php';
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

// Lấy ID sản phẩm từ URL
$product_id = isset($_GET['id']) ? $_GET['id'] : null;

if ($product_id) {
    // Truy vấn chi tiết sản phẩm
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        echo "<p>Sản phẩm không tồn tại!</p>";
        exit;
    }

    // Lấy các sản phẩm đồng hãng
    $stmt_related = $conn->prepare("SELECT * FROM products WHERE Thuong_hieu = ? AND id != ? LIMIT 5");
    $stmt_related->execute([$product['Thuong_hieu'], $product_id]);
    $related_products = $stmt_related->fetchAll(PDO::FETCH_ASSOC);

} else {
    echo "<p>ID sản phẩm không hợp lệ!</p>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Chi tiết sản phẩm</title>
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

        /* Cart Dropdown Styling */
        .cart-dropdown {
            position: relative;
            display: inline-block;
        }

        .cart-icon {
            display: flex;
            align-items: center;
            color: #fff;
            text-decoration: none;
            padding: 8px 10px;
            border-radius: 5px;
            font-weight: bold;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }

        .cart-icon .default-icon {
            display: block;
            width: 20px;
            margin-left: 5px;
        }

        .cart-icon .hover-icon {
            display: none;
            width: 20px;
            margin-left: 5px;
        }

        /* Show hover icon on hover */
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

        /* Product Container */
        .product-container {
            max-width: 1000px;
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            display: flex;
            gap: 20px;
        }

        .product-image {
            max-width: 400px;
            width: 100%;
            border-radius: 8px;
        }

        .product-info {
            flex: 1;
        }

        .product-title {
            font-size: 28px;
            font-weight: bold;
        }

        .old-price {
            font-size: 16px;
            color: #888; /* Gray color */
            position: relative;
            text-decoration: line-through; /* Line-through for original price */
        }

        .new-price {
            font-size: 24px;
            color: #e67e22;
            font-weight: bold;
            margin-top: 0;
        }

        .product-stock {
            font-size: 16px;
            color: #555;
        }

        .product-details {
            list-style: none;
            padding: 0;
        }

        .product-details li {
            margin-bottom: 10px;
        }

        .add-to-cart-btn {
            background-color: #e6b800;
            color: #fff;
            padding: 12px 24px;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            font-size: 16px;
        }

        .add-to-cart-btn[disabled] {
            background-color: gray;
            cursor: not-allowed;
        }

        /* Product Description */
        .product-description {
            font-size: 18px;
            line-height: 1.8;
            color: #333;
            margin-top: 40px;
            padding: 40px;
            background-color: #f0f5ff;
            border-radius: 12px;
            box-shadow: 0px 6px 15px rgba(0, 0, 0, 0.1);
            border-left: 6px solid #e6b800;
            position: relative;
        }

        .product-description h2 {
            font-size: 28px;
            font-weight: bold;
            color: #333;
            border-bottom: 3px solid #e6b800;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        /* Related Products Section */
        .related-products {
            margin-top: 40px;
        }

        .related-products h2 {
            font-size: 28px;
            font-weight: bold;
margin-bottom: 20px;
            text-align: center;
        }

        .product-list {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 20px;
        }

        .product-item {
            padding: 10px;
            margin: 10px;
            text-align: center;
            width: 230px;
            display: inline-block;
            vertical-align: top;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            background-color: #fff;
            position: relative;
        }

        .product-item:hover {
            transform: scale(1.05);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        }

        .product-item img {
            width: 100%;
            height: auto;
            border-radius: 5px;
        }

        .product-item a {
            text-decoration: none; /* Remove underline for the entire link */
        }

        .product-item h3 {
            margin: 10px 0;
            font-size: 20px;
            font-weight: bold;
            color: #333;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
            text-align: left;
            text-decoration: none; /* Ensure no underline */
        }

        .product-item p {
            margin: 10px 0;
            font-size: 18px;
            color: #f60;
            text-decoration: none; /* Remove underline for price */
        }

        /* Discount badge styling */
        .discount-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background-color: #e74c3c;
            color: #fff;
            font-size: 14px;
            font-weight: bold;
            padding: 5px 10px;
            border-radius: 5px;
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
    </style>
</head>
<body>


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

<nav class="top-menu">
    <ul>
        <li><a href="dongho.php">Trang chủ</a></li>
        <li><a href="sanpham.php">Sản phẩm</a></li>
        <li><a href="cart.php">Giỏ hàng</a></li>
        <li><a href="contact.php">Liên hệ</a></li>
    </ul>
</nav>

<main>
    <div class="product-container">
        <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="product-image">
        <div class="product-info">
            <h1 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h1>

            <?php 
            if ($product['discount'] > 0): 
                $original_price = $product['price'];
                $discounted_price = $original_price * (1 - $product['discount'] / 100);
                $discount_percent = $product['discount'];
            ?>
                <p class="old-price"><?php echo number_format($original_price, 0, ',', '.'); ?> VND</p>
                <p class="new-price"><?php echo number_format($discounted_price, 0, ',', '.'); ?> VND</p>
                <p class="discount-percent">Giảm giá: <?php echo $discount_percent; ?>%</p>
            <?php else: ?>
                <p class="product-price"><?php echo number_format($product['price'], 0, ',', '.'); ?> VND</p>
            <?php endif; ?>

            <p class="product-stock"><?php echo ($product['soluong'] > 0) ? "Số lượng còn lại: " . $product['soluong'] : "Sản phẩm hết hàng"; ?></p>
            <ul class="product-details">
                <li>Mã sản phẩm: <?php echo htmlspecialchars($product['code']); ?></li>
                <li>Sản phẩm nhập khẩu chính hãng.</li>
                <li>Giao hàng miễn phí toàn quốc.</li>
                <li>Thanh toán khi nhận hàng.</li>
                <li>Bảo hành 2 năm tại công ty.</li>
            </ul>

            <form action="cart.php" method="post">
                <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                <?php if ($product['soluong'] > 0): ?>
                    <button type="submit" class="add-to-cart-btn">Thêm vào giỏ hàng</button>
                <?php else: ?>
                    <button type="button" class="add-to-cart-btn" disabled>Sản phẩm hết hàng</button>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <div class="product-description">
        <h2>Mô tả</h2>
<p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
    </div>

    <?php if (count($related_products) > 0): ?>
        <div class="related-products">
            <h2>Sản phẩm đồng hãng</h2>
            <div class="product-list">
                <?php foreach ($related_products as $related_product): ?>
                    <div class="product-item">
                        <a href="product_detail.php?id=<?php echo $related_product['id']; ?>">
                            <?php if ($related_product['discount'] > 0): ?>
                                <span class="discount-badge"><?php echo $related_product['discount']; ?>% OFF</span>
                            <?php endif; ?>
                            <img src="<?php echo htmlspecialchars($related_product['image']); ?>" alt="<?php echo htmlspecialchars($related_product['name']); ?>">
                            <h3><?php echo htmlspecialchars($related_product['name']); ?></h3>
                            <?php if ($related_product['discount'] > 0): ?>
                                <p style="color: #f60;">Giá: <?php echo number_format($related_product['price'] * (1 - $related_product['discount'] / 100), 0, ',', '.'); ?> VND</p>
                                <p style="text-decoration: line-through; color: #888;" class="old-price">Giá gốc: <?php echo number_format($related_product['price'], 0, ',', '.'); ?> VND</p>
                            <?php else: ?>
                                <p><?php echo number_format($related_product['price'], 0, ',', '.'); ?> VND</p>
                            <?php endif; ?>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
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