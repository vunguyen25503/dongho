<?php
session_start(); // Ensure session is started before any output
include 'db_connect.php'; // Database connection
// Check if the user is logged in and retrieve their role
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT vaitro FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $userRole = $stmt->fetch(PDO::FETCH_ASSOC)['vaitro'];
}

// Check if user is logged in
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Trang chủ - Bán Đồng Hồ</title>
     <style> 
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

        .left-menu {
            float: left;
            width: 200px;
            padding: 20px;
        }

        .main {
            margin-left: auto;
            padding: 20px;
            flex: 1;
        }

        .product-list {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            margin-top: 30px;
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
        }

        .product-item p {
            margin: 10px 0;
            font-size: 18px;
            color: #f60;
        }

        .featured-image {
            width: 100%;
            max-width: 2000px;
            height: auto;
            display: block;
            margin: -20px auto 0;
            border-radius: 10px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
        }

        .old-price {
            font-size: 16px;
            color: #999;
            text-decoration: line-through;
            margin-bottom: 5px;
        }

        .new-price {
            font-size: 18px;
            color: #e67e22;
            font-weight: bold;
            margin-bottom: 5px;
        }

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
            z-index: 1;
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

        @media (max-width: 768px) {
            .footer-column {
                width: 100%;
            }

            .footer-container {
                flex-direction: column;
                align-items: center;
            }

            .footer-logos img {
                width: 80px;
            }
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
          /* Banner Carousel Styling */
        .carousel-container {
            position: relative;
            max-width: 1200px;
            margin: 20px auto;
            overflow: hidden;
            border-radius: 10px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
        }

        .carousel-slides {
            display: flex;
            transition: transform 0.5s ease;
        }

        .carousel-slide {
            min-width: 100%;
            box-sizing: border-box;
        }

        .carousel-slide img {
            width: 100%;
            display: block;
        }

        .carousel-nav {
            position: absolute;
            top: 50%;
            width: 100%;
            display: flex;
            justify-content: space-between;
            transform: translateY(-50%);
        }

        .carousel-arrow {
            background-color: rgba(0, 0, 0, 0.5);
            color: #fff;
            padding: 10px;
            cursor: pointer;
            border-radius: 50%;
            font-size: 18px;
            user-select: none;
        }
        
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
                <?php if (isset($userRole) && $userRole === 'admin'): ?>
                    <a href="index.php">Quản lý</a>
                <?php endif; ?>
                <a href="logout.php">Đăng xuất</a>
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


<!-- Main Content -->
<main class="main">
    <img src="https://mauweb.monamedia.net/luxshopping/wp-content/uploads/2018/11/banner11.jpg" 
         srcset="https://mauweb.monamedia.net/luxshopping/wp-content/uploads/2018/11/banner11-1024x512.jpg 1024w, 
                 https://mauweb.monamedia.net/luxshopping/wp-content/uploads/2018/11/banner11-600x300.jpg 600w, 
                 https://mauweb.monamedia.net/luxshopping/wp-content/uploads/2018/11/banner11.jpg 2000w" 
         sizes="(max-width: 2000px) 100vw, 2000px" 
         alt="Banner Đồng Hồ" 
         class="featured-image">

<!-- Featured Products Section -->
    <section class="product-list">
        <h2 style="text-align:center; width:100%;">Sản phẩm nổi bật</h2>
        <?php
        // Query to fetch the top 5 highest-priced products
        $query = $conn->query("SELECT * FROM products ORDER BY price DESC LIMIT 5");
        $products = $query->fetchAll(PDO::FETCH_ASSOC);
        foreach ($products as $product): ?>
            <a href="product_detail.php?id=<?php echo $product['id']; ?>" class="product-item">
                <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                <p>Giá: <?php echo number_format($product['price'], 0, ',', '.'); ?> VND</p>
            </a>
        <?php endforeach; ?>
    </section>

    <!-- Men's Watches Section -->
    <section class="product-list">
        <h2 style="text-align:center; width:100%;">Đồng Hồ Nam Bán Chạy</h2>
        <?php
        // Query to fetch men's watches priced under 3,500,000 VND
        $query_men_high = $conn->query("SELECT * FROM products WHERE category = 'nam' AND price <= 3500000 LIMIT 5");
        $products_men_high = $query_men_high->fetchAll(PDO::FETCH_ASSOC);
        foreach ($products_men_high as $product): ?>
            <a href="product_detail.php?id=<?php echo $product['id']; ?>" class="product-item">
                <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                <p>Giá: <?php echo number_format($product['price'], 0, ',', '.'); ?> VND</p>
            </a>
        <?php endforeach; ?>
    </section>

   <!-- Carousel Container -->
<div class="carousel-container">
    <div class="carousel-slides">
        <!-- Banner 1: Rolex -->
        <div class="carousel-slide">
            <a href="sanpham.php?brand=Rolex"><img src="https://cdn.tgdd.vn/News/1515264/top-20-logo-dong-ho-dep-tim-hieu-y-nghia-cua-cac-logo-1-800x450.jpg" alt="Banner 1"></a>
        </div>

        <!-- Banner 2: Patek Philippe -->
        <div class="carousel-slide">
            <a href="sanpham.php?brand=Patek%20Philippe"><img src="https://ik.imagekit.io/travelsoftwares/blog/wp-content/uploads/2022/09/patek-philippe-watches.jpg" alt="Banner 2"></a>
        </div>

        <!-- Banner 3: Longines -->
        <div class="carousel-slide">
            <a href="sanpham.php?brand=Longines"><img src="https://feeltime.com.ua/files/resized/blog/longines-record.800x800.png" alt="Banner 3"></a>
        </div>

        <!-- Banner 4: Casio -->
       <div class="carousel-slide">
            <a href="sanpham.php?brand=Casio"><img src="https://cdn.tgdd.vn/Products/Images/7264/289338/casio-a168wer-2adf-unisex-260323-042416.jpg" alt="Banner 3"></a>
        </div>
    </div>
    <div class="carousel-nav">
        <span class="carousel-arrow" id="prev">❮</span>
        <span class="carousel-arrow" id="next">❯</span>
    </div>
</div>


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
    <!-- Women's Watches Section -->
    <section class="product-list">
        <h2 style="text-align:center; width:100%;">Đồng Hồ Nữ Bán Chạy</h2>
        <?php
        // Query to fetch women's watches priced under 3,500,000 VND
        $query_women_high = $conn->query("SELECT * FROM products WHERE category = 'nữ' AND price <= 3500000 LIMIT 5");
        $products_women_high = $query_women_high->fetchAll(PDO::FETCH_ASSOC);
        foreach ($products_women_high as $product): ?>
            <a href="product_detail.php?id=<?php echo $product['id']; ?>" class="product-item">
                <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                <p>Giá: <?php echo number_format($product['price'], 0, ',', '.'); ?> VND</p>
            </a>
        <?php endforeach; ?>
    </section>

    <!-- Top Discount Products Section -->
    <section class="product-list">
        <h2 style="text-align:center; width:100%;">Sản Phẩm Giảm Giá Nhiều Nhất</h2>
        <?php
        // Query to fetch top 5 products with the highest discount
        $query_discount = $conn->query("SELECT * FROM products ORDER BY discount DESC LIMIT 5");
        $products_discount = $query_discount->fetchAll(PDO::FETCH_ASSOC);
        foreach ($products_discount as $product): ?>
            <a href="product_detail.php?id=<?php echo $product['id']; ?>" class="product-item" style="position: relative;">
                <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                <p>
                    Giá: 
                    <?php 
                    // Tính giá sau khi giảm
                    $discounted_price = $product['price'] * (1 - $product['discount'] / 100);
                    echo number_format($discounted_price, 0, ',', '.') . " VND"; 
                    ?>
                </p>
                <p style="text-decoration: line-through; color: #888;">
                    Giá gốc: <?php echo number_format($product['price'], 0, ',', '.'); ?> VND
                </p>
                <!-- Display discount percentage in the top-right corner -->
                <span style="position: absolute; top: 5px; right: 5px; background-color: #f60; color: #fff; padding: 5px 10px; border-radius: 5px; font-weight: bold;">
                    <?php echo htmlspecialchars($product['discount']); ?>% OFF 
                </span>
            </a>
        <?php endforeach; ?>
    </section>

</main>

<!-- Footer -->
<!-- Footer -->
 <footer class="footer">
        <div class="footer-container">
            <div class="footer-column">
                <h3>THÔNG TIN</h3>
                <ul>
                    <li><a href="gioithieu.php">Giới thiệu về Vua Đồng Hồ Việt Nam</a></li>
                    <li><a href="quychehoatdong.php">Quy chế hoạt động</a></li>
                    <li><a href="hoptac.php">Hợp tác với Vua Đồng Hồ</a></li>
                    <li><a href="chuongtrinhAffiliate.php">Chương trình Affiliate - Cộng tác viên</a></li>
                    <li><a href="phutrachnoidung.php">Phụ trách nội dung</a></li>
                    <li><a href="#">Tuyển dụng</a></li>
                    <li><a href="lienhe.php">Liên hệ</a></li>
                </ul>
                <div class="footer-logos">
                    <img src="https://www.locklizard.com/wp-content/uploads/2023/04/dmca-protected.png" alt="DMCA Logo">
                    <img src="https://awery.aero/uploads/5693781eeaf2534102203.JPEG" alt="TrustLock Verified">
                </div>
            </div>

            <div class="footer-column">
                <h3>CÂU HỎI THƯỜNG GẶP</h3>
                <ul>
                    <li><a href="tracuudonhang.php">Hướng dẫn cách tra cứu mã đơn hàng</a></li>
                    <li><a href="huongdandoitra.php">Sản phẩm cần đổi hết hàng?</a></li>
                    <li><a href="khongcohoadon.php">Nếu không có hóa đơn, tôi có thể trả lại không?</a></li>
                    <li><a href="thieusanpham.php">Đơn hàng bị thiếu sản phẩm?</a></li>
                    <li><a href="sanphamkgiong.php">Sản phẩm không giống với ảnh?</a></li>
                    <li><a href="huydonhang.php">Tại sao đơn hàng bị hủy?</a></li>
                    <li><a href="lienhevande.php">Nếu gặp vấn đề, tôi cần liên hệ với ai?</a></li>
                </ul>
            </div>

            <div class="footer-column">
                <h3>CHĂM SÓC KHÁCH HÀNG</h3>
                <ul>
                    <li><a href="chinhsachbanhang.php">Chính sách bán hàng</a></li>
                    <li><a href="chinhsachbaohanh.php">Chính sách bảo hành</a></li>
                    <li><a href="chinhsachgiaohang.php">Chính sách giao hàng</a></li>
                    <li><a href="chinhsachdoitra.php">Chính sách đổi trả và hoàn tiền</a></li>
                    <li><a href="chinhsachthanhtoan.php">Chính sách thanh toán</a></li>
                    <li><a href="chinhsachbaomat.php">Chính sách bảo mật</a></li>
                    <li><a href="chinhsachtranhchap.php">Cơ chế giải quyết tranh chấp</a></li>
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
