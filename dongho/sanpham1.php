<?php
session_start();
include 'db_connect.php'; // Kết nối cơ sở dữ liệu

// Initialize filtering and sorting
$brand = isset($_GET['brand']) ? $_GET['brand'] : 'all';
$category = isset($_GET['category']) ? $_GET['category'] : 'all';
$availability = isset($_GET['availability']) ? $_GET['availability'] : 'all';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'id';
$search_code = isset($_GET['search_code']) ? $_GET['search_code'] : '';
$search_name = isset($_GET['search_name']) ? $_GET['search_name'] : '';
$min_price = isset($_GET['min_price']) ? $_GET['min_price'] : '';
$max_price = isset($_GET['max_price']) ? $_GET['max_price'] : '';

// SQL base query to select products
$query = "SELECT * FROM products";
$params = [];

// Apply filters based on user selection
$conditions = [];
if (!empty($search_code)) {
    $conditions[] = "code LIKE ?";
    $params[] = '%' . $search_code . '%';
}
if (!empty($search_name)) {
    $conditions[] = "name LIKE ?";
    $params[] = '%' . $search_name . '%';
}
if (!empty($min_price)) {
    $conditions[] = "price >= ?";
    $params[] = $min_price;
}
if (!empty($max_price)) {
    $conditions[] = "price <= ?";
    $params[] = $max_price;
}
if ($brand !== 'all') {
    $conditions[] = "Thuong_hieu = ?";
    $params[] = $brand;
}
if ($availability === 'in_stock') {
    $conditions[] = "soluong > 0";
} elseif ($availability === 'out_of_stock') {
    $conditions[] = "soluong = 0";
}
if (isset($_GET['discount'])) {
    $conditions[] = "discount > 0";
}

// Combine conditions into the query
if (count($conditions) > 0) {
    $query .= " WHERE " . implode(" AND ", $conditions);
}

// Apply sorting based on user selection
if ($sort === 'price_asc') {
    $query .= " ORDER BY price ASC";
} elseif ($sort === 'price_desc') {
    $query .= " ORDER BY price DESC";
} else {
    $query .= " ORDER BY id ASC";
}


 // Database connection
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


// Execute the query with parameters
$stmt = $conn->prepare($query);
$stmt->execute($params);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sản phẩm - Bán Đồng Hồ</title>
    <style>
    body {
    font-family: Arial, sans-serif;
    margin: 0;
    padding: 0;
    color: #333;
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

.cart-icon {
    display: flex;
    align-items: center;
    color: #fff;
    text-decoration: none;
    transition: background-color 0.3s ease, transform 0.2s ease;
    padding: 8px 10px;
    border-radius: 5px;
    font-weight: bold;
}

.cart-icon img {
    width: 24px;
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

.cart-dropdown {
    position: relative;
    display: inline-block;
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
    font-weight: bold;
    transition: background-color 0.3s;
}

.top-menu ul li a:hover {
    background-color: #f60;
}

.dropdown-container {
    display: flex;
    justify-content: flex-end;
    align-items: center;
    font-size: 18px;
    font-weight: bold;
    margin-bottom: 10px;
    width: 100%;
}

.dropdown {
    position: relative;
    display: inline-block;
}

.dropdown-button {
    background: none;
    color: #333;
    border: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    font-size: 16px;
}

.dropdown-button:hover {
    color: #f60;
}

.dropdown-content {
    display: none;
    position: absolute;
    right: 0;
    background-color: #fff;
    box-shadow: 0px 8px 16px rgba(0, 0, 0, 0.2);
    border-radius: 5px;
    z-index: 1;
    width: 160px;
    padding: 10px 0;
}

.dropdown-content a {
    color: #333;
    padding: 10px 15px;
    text-decoration: none;
    display: block;
    font-size: 16px;
}

.dropdown-content a:hover {
    background-color: #f5f5f5;
    color: #f60;
}

.dropdown:hover .dropdown-content {
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

/* Filter Bar Styling */
.filter-bar {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    background-color: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
    margin-bottom: 20px;
    border: 1px solid #ddd;
    gap: 10px; /* Thêm khoảng cách giữa các phần tử */
}

.filter-bar .filter-section {
    display: flex;
    flex-direction: row; /* Chuyển thành chiều ngang */
    align-items: center; /* Căn giữa các phần tử theo chiều dọc */
    gap: 10px; /* Khoảng cách giữa các thành phần trong cùng một nhóm */
    flex: 1;
    max-width: none; /* Bỏ giới hạn chiều rộng */
}

.filter-bar .filter-section label {
    font-size: 14px;
    margin-right: 10px; /* Khoảng cách giữa nhãn và input */
}

.filter-bar .filter-section input[type="text"],
.filter-bar .filter-section input[type="number"],
.filter-bar .filter-section select {
    width: auto; /* Để kích thước input linh hoạt */
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 4px;
    font-size: 14px;
}

.filter-bar .apply-button,
.filter-bar .reset-button {
    padding: 10px 20px;
    font-size: 14px;
    font-weight: bold;
    background-color: #333;
    color: #fff;
    border-radius: 4px;
    text-align: center;
    cursor: pointer;
    text-decoration: none;
    transition: background-color 0.3s;
}

.filter-bar .apply-button:hover,
.filter-bar .reset-button:hover {
    background-color: #f60;
}


/* Product Grid Styling */
.product-list {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            margin-top: 30px;
        }

.product-item {
    position: relative; /* Để định vị chính xác .discount-badge */
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
.product-image-container {
    position: relative; /* Để chứa .discount-badge */
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
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

        




    </style>
</head>
<body>

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

<nav class="top-menu">
    <ul>
        <li><a href="dongho.php">Trang chủ</a></li>
        <li><a href="sanpham.php">Sản phẩm</a></li>
        <li><a href="cart.php">Giỏ hàng</a></li>
        <li><a href="contact.php">Liên hệ</a></li>
    </ul>
</nav>

<div class="product-page">
    <!-- Sidebar Filter -->
    <aside class="filter-bar">
        <form method="GET" action="sanpham.php">
            <!-- Search by Product Code and Name -->
            <div class="filter-section">
                <label class="filter-title">Tìm kiếm sản phẩm</label>
                <input type="text" name="search_code" placeholder="Mã sản phẩm" value="<?php echo htmlspecialchars($search_code); ?>">
                <input type="text" name="search_name" placeholder="Tên sản phẩm" value="<?php echo htmlspecialchars($search_name); ?>">
            </div>

            <!-- Price Range Filter -->
            <div class="filter-section">
                <label class="filter-title">Khoảng giá</label>
                <input type="number" name="min_price" placeholder="Giá từ" value="<?php echo htmlspecialchars($min_price); ?>">
                <input type="number" name="max_price" placeholder="Giá đến" value="<?php echo htmlspecialchars($max_price); ?>">
            </div>


            <!-- Brand Filter -->
            <div class="filter-section">
                <label class="filter-title">Thương hiệu</label>
                <label><input type="radio" name="brand" value="all" <?php if ($brand === 'all') echo 'checked'; ?>> Tất cả</label>
                <label><input type="radio" name="brand" value="Rolex" <?php if ($brand === 'Rolex') echo 'checked'; ?>> Rolex</label>
                <label><input type="radio" name="brand" value="Gucci" <?php if ($brand === 'Gucci') echo 'checked'; ?>> Gucci</label>
                <label><input type="radio" name="brand" value="Casio" <?php if ($brand === 'Casio') echo 'checked'; ?>> Casio</label>
                <label><input type="radio" name="brand" value="Patek Philippe" <?php if ($brand === 'Patek Philippe') echo 'checked'; ?>> Patek Philippe</label>
                <label><input type="radio" name="brand" value="Longines" <?php if ($brand === 'Longines') echo 'checked'; ?>> Longines</label>
                <label><input type="radio" name="brand" value="Bering" <?php if ($brand === 'Bering') echo 'checked'; ?>> Bering</label>

            </div>

            <!-- Discount Filter -->
            <div class="filter-section">
                <label><input type="checkbox" name="discount" <?php if (isset($_GET['discount'])) echo 'checked'; ?>> Có giảm giá</label>
            </div>

            <!-- Availability Filter -->
            <div class="filter-section">
                <label class="filter-title">Tình trạng hàng</label>
                <label><input type="radio" name="availability" value="all" <?php if ($availability === 'all') echo 'checked'; ?>> Tất cả</label>
                <label><input type="radio" name="availability" value="in_stock" <?php if ($availability === 'in_stock') echo 'checked'; ?>> Còn hàng</label>
                <label><input type="radio" name="availability" value="out_of_stock" <?php if ($availability === 'out_of_stock') echo 'checked'; ?>> Hết hàng</label>
            </div>

            <!-- Sort By Options -->
            <div class="filter-section">
                <label class="filter-title">Sắp xếp theo</label>
                <label><input type="radio" name="sort" value="price_asc" <?php if ($sort === 'price_asc') echo 'checked'; ?>> Giá tăng dần</label>
                <label><input type="radio" name="sort" value="price_desc" <?php if ($sort === 'price_desc') echo 'checked'; ?>> Giá giảm dần</label>
                <label><input type="radio" name="sort" value="alphabetical" <?php if ($sort === 'alphabetical') echo 'checked'; ?>> Tên A-Z</label>
            </div>

            <!-- Apply and Reset Buttons -->
            <button type="submit" class="apply-button">ÁP DỤNG</button>
            <a href="sanpham.php" class="reset-button">Reset</a>
        </form>
    </aside>

    <div style="flex: 1;">
        

        <div class="dropdown-container">
            <div class="dropdown">
                <button class="dropdown-button">Menu <span>⏷</span></button>
                <div class="dropdown-content">
                    <a href="sanpham.php?sort=price_asc">Giá thấp đến cao</a>
                    <a href="sanpham.php?sort=price_desc">Giá cao đến thấp</a>
                    <a href="sanpham.php?sort=alphabetical">Tên A-Z</a>
                    <a href="sanpham.php?discount=1">Giảm giá nhiều</a>
                </div>
            </div>
        </div>

        <!-- Products Display -->
   <div class="products">
    <?php if (empty($products)): ?>
        <p class="no-products-message">Không có đồng hồ theo yêu cầu tìm kiếm</p>
    <?php else: ?>
        <?php foreach ($products as $product): ?>
            <a href="product_detail.php?id=<?php echo $product['id']; ?>" class="product-item">
                <div class="product-image-container">
                    <?php if ($product['discount'] > 0): ?>
                        <div class="discount-badge"><?php echo $product['discount'] . '% OFF'; ?></div>
                    <?php endif; ?>
                    <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                </div>
                <h3><?php echo htmlspecialchars($product['name']); ?></h3>

                <?php 
                if ($product['discount'] > 0): 
                    $original_price = $product['price'];
                    $discounted_price = $original_price * (1 - $product['discount'] / 100);
                ?>
                    <p class="new-price"><?php echo number_format($discounted_price, 0, ',', '.'); ?> VND</p>
                    <p class="old-price"><?php echo number_format($original_price, 0, ',', '.'); ?> VND</p>
                <?php else: ?>
                    <p class="price"><?php echo number_format($product['price'], 0, ',', '.'); ?> VND</p>
                <?php endif; ?>
            </a>
        <?php endforeach; ?>
    <?php endif; ?>
</div>




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
