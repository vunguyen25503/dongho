<?php
session_start();
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
$error_message = '';
$success_message = '';
$discountPercent = 0;
$totalPrice = 0;
$gift = isset($_SESSION['gift']) && $_SESSION['gift'] ? 30000 : 0;
include 'db_connect.php'; // Kết nối tới cơ sở dữ liệu
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT vaitro FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $userRole = $stmt->fetch(PDO::FETCH_ASSOC)['vaitro'];
}
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

// Khởi tạo biến


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Áp dụng mã giảm giá
    if (isset($_POST['apply_discount'])) {
        $discount_code = strtoupper(trim($_POST['discount_code']));
        
        // Kiểm tra mã giảm giá trong cơ sở dữ liệu
        $stmt = $conn->prepare("SELECT * FROM discount_codes WHERE code = ? AND start_date <= NOW() AND end_date >= NOW()");
        $stmt->execute([$discount_code]);
        $discount = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($discount) {
            // Lưu thông tin giảm giá vào session
            $_SESSION['discount_code'] = $discount['code'];
            $_SESSION['discount_percent'] = $discount['discount_percent'];
        } else {
            // Mã giảm giá không hợp lệ
            $_SESSION['discount_code'] = '';  // Xóa mã giảm giá cũ
            $_SESSION['discount_percent'] = 0;  // Không có giảm giá
        }
    }

    // Kiểm tra xem có chọn quà tặng không
    if (isset($_POST['gift'])) {
        $_SESSION['gift'] = true;  // Lưu thông tin quà tặng vào session
    } else {
        $_SESSION['gift'] = false;
    }
}
 // Xử lý thêm sản phẩm vào giỏ hàng
   if (isset($_POST['product_id']) && $user_id !== null) {
    $product_id = $_POST['product_id'];
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

    // Lấy thông tin sản phẩm và tên sản phẩm
    $stmt = $conn->prepare("SELECT soluong, name FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    // Lấy tên người dùng
    $user_stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
    $user_stmt->execute([$user_id]);
    $user = $user_stmt->fetch(PDO::FETCH_ASSOC);

    if ($product && $quantity > $product['soluong']) {
        $error_message = "Bạn không thể mua quá số lượng có sẵn.";
    } else {
        if (isset($_POST['update'])) {
            $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE product_id = ? AND user_id = ?");
            $stmt->execute([$quantity, $product_id, $user_id]);
        } elseif (isset($_POST['remove'])) {
            $stmt = $conn->prepare("DELETE FROM cart WHERE product_id = ? AND user_id = ?");
            $stmt->execute([$product_id, $user_id]);
        } else {
            $stmt = $conn->prepare("SELECT * FROM cart WHERE product_id = ? AND user_id = ?");
            $stmt->execute([$product_id, $user_id]);
            $cart_item = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($cart_item) {
                $stmt = $conn->prepare("UPDATE cart SET quantity = quantity + ? WHERE cart_id = ?");
                $stmt->execute([$quantity, $cart_item['cart_id']]);
            } else {
                // Thêm sản phẩm mới vào giỏ hàng với tên sản phẩm và tên người dùng
                $stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, TenSp, ten_user, quantity) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$user_id, $product_id, $product['name'], $user['username'], $quantity]);
            }
        }
    }
}



// Xử lý đặt hàng
if (isset($_POST['order'])) {
    // Lấy thông tin đơn hàng từ form
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $city = $_POST['city'];
    $district = $_POST['district'];
    $ward = $_POST['ward'];
    $address = $_POST['address'];
    $email = $_POST['email'];
    $notes = $_POST['notes'];
    $gift = isset($_POST['gift']) ? 30000 : 0;  // Phí quà tặng

    // Tính tổng tiền của giỏ hàng
    if ($user_id !== null) {
        $stmt = $conn->prepare("
            SELECT 
                c.quantity, 
                p.price, p.discount, p.id as product_id, p.code as code_product
            FROM cart c
            JOIN products p ON c.product_id = p.id
            WHERE c.user_id = ?
        ");
        $stmt->execute([$user_id]);
        $cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $totalPrice = 0;
        $orderItems = [];  // Mảng chứa thông tin sản phẩm trong đơn hàng
        foreach ($cartItems as $item) {
            // Áp dụng giảm giá sản phẩm
            $discountedPrice = $item['price'] - ($item['price'] * ($item['discount'] / 100));
            $totalPrice += $discountedPrice * $item['quantity'];

            // Thêm thông tin sản phẩm vào mảng orderItems
            $orderItems[] = [
                'product_id' => $item['product_id'],
                'code_product' => $item['code_product'],
                'soluong' => $item['quantity']
            ];

            // Cập nhật số lượng sản phẩm trong bảng products
            $newQuantity = $item['quantity'];

            // Giảm số lượng sản phẩm trong bảng products
            $updateProductStmt = $conn->prepare("
                UPDATE products 
                SET soluong = soluong - ?
                WHERE id = ?
            ");
            $updateProductStmt->execute([$newQuantity, $item['product_id']]);
        }
    }
  // Tính tổng tiền sau khi áp dụng giảm giá và phí quà tặng
    $finalTotal = $totalPrice * (1 - ($_SESSION['discount_percent'] ?? 0) / 100) + ($gift);

    // Thêm đơn hàng vào cơ sở dữ liệu (Bảng nguoinhan)
    $stmt = $conn->prepare("INSERT INTO nguoinhan (ho_va_ten, so_dien_thoai, tinh_thanh_pho, quan_huyen, phuong_xa, dia_chi, email, ghi_chu_giao_hang, total_price, tongtien) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$name, $phone, $city, $district, $ward, $address, $email, $notes, $gift, $finalTotal]);

    // Lấy id của đơn hàng vừa được thêm
    $orderId = $conn->lastInsertId();

    // Thêm các sản phẩm vào bảng nguoinhan_product (nếu cần thiết)
    foreach ($orderItems as $item) {
        $stmt = $conn->prepare("INSERT INTO nguoinhan_product (order_id, product_id, code_product, soluong) VALUES (?, ?, ?, ?)");
        $stmt->execute([$orderId, $item['product_id'], $item['code_product'], $item['soluong']]);
    }

    // Reset giỏ hàng trong cơ sở dữ liệu
    $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
    $stmt->execute([$user_id]);

    // Reset giỏ hàng trong session
    unset($_SESSION['cart']);

    // Reset mã giảm giá và quà tặng sau khi đặt hàng thành công
    unset($_SESSION['discount_code']);
    unset($_SESSION['discount_percent']);

    $success_message = "Đặt hàng thành công! Giỏ hàng đã được làm mới.";
}

// Lấy sản phẩm trong giỏ hàng
if ($user_id !== null) {
    $stmt = $conn->prepare("
        SELECT 
            c.cart_id, c.quantity, 
            p.id as product_id, p.name, p.price, p.image, p.soluong as available_stock, p.discount
        FROM cart c
        JOIN products p ON c.product_id = p.id
        WHERE c.user_id = ?
    ");
    $stmt->execute([$user_id]);
    $cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Tính tổng tiền của giỏ hàng
    foreach ($cartItems as $item) {
        $discountedPrice = $item['price'] - ($item['price'] * ($item['discount'] / 100));
        $totalPrice += $discountedPrice * $item['quantity'];
    }
} else {
    $cartItems = [];
}

// Tính tổng tiền sau khi áp dụng giảm giá và phí gói quà

$displayTotal = $totalPrice * (1 - ($_SESSION['discount_percent'] ?? 0) / 100) + $gift;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Giỏ hàng của bạn - Bán Đồng Hồ</title>
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
        font-weight: bold;
        transition: background-color 0.3s;
    }

    .top-menu ul li a:hover {
        background-color: #f60;
    }

    .cart-container {
        max-width: 1200px;
        margin: 20px auto;
        padding: 20px;
        background-color: #fff;
        border-radius: 8px;
        box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
    }

    .cart-item {
        display: flex;
        align-items: center;
        border-bottom: 1px solid #ddd;
        padding: 10px 0;
    }

    .cart-item img {
        width: 80px;
        height: auto;
        margin-right: 20px;
        border-radius: 5px;
    }

    .item-details {
        flex: 2;
        display: flex;
        flex-direction: column;
    }

    .item-details h2 {
        margin: 0;
        font-size: 18px;
        color: #333;
    }

    .item-details p {
        margin: 5px 0;
        font-size: 16px;
        color: #777;
    }

    .quantity {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .quantity input[type="number"] {
        width: 60px;
        padding: 5px;
        text-align: center;
        border: 1px solid #ddd;
        border-radius: 4px;
        margin-right: 10px;
    }

    .stock-info {
        background-color: #4CAF50;
        color: #fff;
        padding: 5px 10px;
        margin-left: 10px;
        border: none;
        border-radius: 5px;
        font-size: 14px;
        font-weight: bold;
        display: inline-block;
        text-align: center;
        transition: background-color 0.3s;
    }

    .stock-info.out-of-stock {
        background-color: red;
    }

    .update-btn {
        background-color: #4CAF50;
        color: #fff;
        padding: 5px 10px;
        margin-left: 10px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-size: 14px;
        font-weight: bold;
        transition: background-color 0.3s;
    }

    .update-btn:hover {
        background-color: #45a049;
    }

    .price {
        flex: 1;
        text-align: right;
        font-size: 16px;
        color: #333;
        margin-right: 20px;
    }

    .remove-btn {
        background-color: transparent;
        border: none;
        cursor: pointer;
        margin-left: 10px;
    }

    .remove-btn img {
        width: 50px;
        height: 50px;
    }

    .total-price {
        text-align: right;
        font-size: 20px;
        font-weight: bold;
        color: #333;
        margin-top: 20px;
        padding-top: 10px;
        border-top: 2px solid #ddd;
    }

    .error-message {
        color: red;
        text-align: center;
        font-weight: bold;
        margin-bottom: 20px;
    }

    .empty-cart {
        text-align: center;
        padding: 50px 20px;
        color: #555;
    }

    .empty-cart h2 {
        font-size: 24px;
        color: #f60;
    }

    .empty-cart a {
        font-size: 18px;
        color: #4CAF50;
        text-decoration: none;
    }

    /* Chỉnh sửa phần tiêu đề */
    .risk-free-shopping .pageTitle {
        font-size: 28px;
        font-weight: bold;
        text-align: center;
        margin-bottom: 20px;
    }

    /* Chỉnh sửa phần các box trong khu vực risk-free */
    .risk-free-content {
        display: flex;
        justify-content: center;
        gap: 15px;
        padding: 20px;
        border-radius: 10px;
       
    }

    .risk-box {
        text-align: center;
        width: 200px;
    }

    .risk-box img {
        margin-bottom: 10px;
    }

    .risk-title {
        font-size: 18px;
        font-weight: bold;
        color: #333;
        margin: 10px 0;
    }

    .risk-box p {
        color: #666;
        font-size: 14px;
    }


    /* Additional Styles for Checkout Form and Right Column */
    .container {
        display: flex;
        gap: 20px;
        max-width: 1000px;
        margin: 20px auto;
        padding: 20px;
        background-color: #ffffff;
        border: 1px solid #e0e0e0;
        border-radius: 5px;
    }

    /* Left Column Styling */
    .left-column {
        width: 60%;
    }

    h2 {
        font-size: 20px;
        margin-bottom: 15px;
    }

    .form-group {
        margin-bottom: 15px;
    }

    label {
        display: block;
        margin-bottom: 5px;
        font-weight: bold;
    }

    input[type="text"],
    input[type="tel"],
    input[type="email"],
    select {
        width: 100%;
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 5px;
        font-size: 14px;
    }

    .order-button {
        width: 100%;
        padding: 12px;
        background-color: black;
        color: white;
        border: none;
        border-radius: 5px;
        font-size: 16px;
        font-weight: bold;
        cursor: pointer;
    }

    .order-button:hover {
        background-color: #333;
    }

    .gift-info {
        display: flex;
        align-items: center;
        font-size: 14px;
    }

    .gift-info input {
        margin-right: 5px;
    }

    /* Right Column Styling */
    .right-column {
        width: 40%;
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .payment-method, .discount-code, .summary {
        padding: 15px;
        border: 1px solid #e0e0e0;
        border-radius: 5px;
    }

    .payment-method h3,
    .discount-code h3,
    .summary h3 {
        font-size: 16px;
        font-weight: bold;
        margin-bottom: 10px;
    }

    .summary p, .summary .total-price {
        display: flex;
        justify-content: space-between;
        margin: 5px 0;
    }

    .summary .total-price {
        font-weight: bold;
        color: red;
    }

    .discount-code input[type="text"] {
        width: calc(100% - 80px);
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 5px;
        font-size: 14px;
    }

    .apply-btn {
        width: 70px;
        padding: 10px;
        background-color: #333;
        color: #fff;
        font-weight: bold;
        border: none;
        border-radius: 5px;
        cursor: pointer;
    }

    .checkout-buttons {
        display: flex;
        gap: 10px;
    }

    .checkout-buttons button {
        width: 100%;
        padding: 15px;
        font-size: 14px;
        font-weight: bold;
        border: 1px solid #000;
        cursor: pointer;
        border-radius: 5px;
    }

    .checkout-buttons .continue-btn {
        background-color: #fff;
        color: #000;
    }

    .checkout-buttons .order-btn {
        background-color: #000;
        color: #fff;
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

</style>
    <script>
        function applyDiscountCode() {
            const discountCode = document.getElementById("discount_code").value;
            document.getElementById("discount_form").submit();
        }
    </script>
</head>
<body>

<!-- Banner -->
<header>
    <div class="banner">
        <!-- Logo image with caption underneath -->
        <img src="https://play-lh.googleusercontent.com/ysE2vm-N3xxHc7nQa5v0wsk895_lI8diEGlC27QtWbJcVG7MQ2FfMcZ0qEqgy20dJw" alt="Logo" class="logo">
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

<div class="cart-container">
    <?php if ($error_message): ?>
        <div class="error-message"><?php echo $error_message; ?></div>
    <?php elseif ($success_message): ?>
        <div class="success-message"><?php echo $success_message; ?></div>
    <?php endif; ?>

    <?php if (empty($cartItems)): ?>
        <div class="empty-cart">
            <h2>Giỏ hàng của bạn đang rỗng!</h2>
            <a href="sanpham.php">Xem sản phẩm</a>
        </div>
    <?php else: ?>
        <?php foreach ($cartItems as $item): ?>
            <div class="cart-item">
                <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                <div class="item-details">
                    <h2><?php echo htmlspecialchars($item['name']); ?></h2>
                    <p>Giá gốc: <?php echo number_format($item['price'], 0, ',', '.'); ?> VND</p>
                    <?php if ($item['discount'] > 0): ?>
                        <p>Giảm giá: <?php echo $item['discount']; ?>%</p>
                    <?php endif; ?>
                    <p>Giá sau giảm: <?php 
                        $discountedPrice = $item['price'] - ($item['price'] * ($item['discount'] / 100)); 
                        echo number_format($discountedPrice, 0, ',', '.'); 
                    ?> VND</p>
                </div>
                <form method="post" class="quantity" oninput="updateStockDisplay(<?php echo $item['product_id']; ?>, <?php echo $item['available_stock']; ?>)">
                    <input type="hidden" name="product_id" value="<?php echo $item['product_id']; ?>">
                    <input type="number" name="quantity" id="quantity_<?php echo $item['product_id']; ?>" value="<?php echo $item['quantity']; ?>" min="1">
                    <?php
                    // Kiểm tra nếu số lượng giỏ hàng vượt quá số lượng có sẵn
                    $remainingStock = $item['available_stock'] - $item['quantity'];
                    if ($remainingStock < 0) {
                        $remainingStock = 0;
                    }
                    ?>
                    <button type="submit" name="update" class="update-btn">Cập nhật</button>
                    <div class="stock-info" id="stock_<?php echo $item['product_id']; ?>">
                        <?php if ($remainingStock <= 0): ?>
                            Hết hàng
                        <?php else: ?>
                            Số lượng còn lại: <?php echo $remainingStock; ?>
                        <?php endif; ?>
                    </div>
                </form>
                <div class="price">
                    Tổng: <?php 
                        $totalItemPrice = $discountedPrice * $item['quantity'];
                        echo number_format($totalItemPrice, 0, ',', '.'); 
                    ?> VND
                </div>
                <form method="post">
                    <input type="hidden" name="product_id" value="<?php echo $item['product_id']; ?>">
                    <button type="submit" name="remove" class="remove-btn">
<img src="https://static.vecteezy.com/system/resources/thumbnails/003/241/364/small/trash-bin-icon-line-vector.jpg" alt="Xóa">
                    </button>
                </form>
            </div>
        <?php endforeach; ?>
       <div class="total-price">
    Tổng tiền: <?php echo number_format($displayTotal, 0, ',', '.'); ?> VND
</div>
    <?php endif; ?>
</div>

<div class="container">
    <div class="left-column">
        <h2>Thông Tin Nhận Hàng</h2>
        <form method="post" id="order_form">
            <div class="form-group">
                <label for="name">Họ và tên *</label>
                <input type="text" id="name" name="name" required>
            </div>
            <div class="form-group">
                <label for="phone">Số điện thoại *</label>
                <input type="tel" id="phone" name="phone" required>
            </div>
            <div class="form-group">
                <label for="city">Tỉnh / Thành phố *</label>
                <input type="text" id="city" name="city" required>
            </div>
            <div class="form-group">
                <label for="district">Quận / Huyện *</label>
                <input type="text" id="district" name="district" required>
            </div>
            <div class="form-group">
                <label for="ward">Phường / Xã *</label>
                <input type="text" id="ward" name="ward" required>
            </div>
            <div class="form-group">
                <label for="address">Địa chỉ *</label>
                <input type="text" id="address" name="address" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email">
            </div>
            <div class="form-group">
                <label for="notes">Ghi chú giao hàng</label>
                <select id="notes" name="notes">
                    <option value="business-hours">Giao trong giờ hành chính</option>
                    <option value="anytime">Giao bất cứ lúc nào</option>
                </select>
            </div>
             <div class="form-group">
    <label class="gift-info">
        <input type="checkbox" name="gift" id="gift_checkbox">
        Gửi quà tặng đến bạn bè, người thân (30.000đ bao gồm phí gói quà và thiệp)
    </label>
</div>

            <button type="submit" name="order" class="order-button">Đặt Hàng</button>
        </form>
    </div>

    <div class="right-column">
        <div class="payment-method">
            <h3>Phương Thức Thanh Toán</h3>
            <input type="radio" id="cash_on_delivery" name="payment_method" checked>
            <label for="cash_on_delivery">Thanh toán khi nhận hàng</label>
            <p>Quý khách sẽ thanh toán bằng tiền mặt khi nhận hàng.</p>
        </div>

     <form method="post" id="discount_form">
    <div class="discount-code">
        <h3>Mã Giảm Giá</h3>
        <div style="display: flex;">
            <input type="text" id="discount_code" name="discount_code" placeholder="Nhập mã khuyến mãi" value="<?php echo isset($_SESSION['discount_code']) ? $_SESSION['discount_code'] : ''; ?>">
            <button type="submit" name="apply_discount" class="apply-btn">ÁP DỤNG</button>
        </div>
    
    </div>
</form>

<?php if (empty($success_message)): ?>
<!-- Phần hiển thị tóm tắt đơn hàng -->
<div class="summary">
    <h3>Tóm Tắt Đơn Hàng</h3>
    <p><span>Tạm tính:</span><span id="total_price"><?php echo number_format($totalPrice, 0, ',', '.'); ?> ₫</span></p>
    <p id="gift_fee" style="display: none;">
        <span>Phí gói quà:</span><span>30.000 ₫</span>
    </p>
    <p class="total-price">
        <span>Tiền phải trả:</span><span id="final_price"><?php echo number_format($displayTotal, 0, ',', '.'); ?> ₫</span>
    </p>
    <p>(Giá này đã bao gồm thuế GTGT, phí đóng gói, phí vận chuyển và các chi phí phát sinh khác)</p>
</div>
<?php endif; ?>

        <div class="checkout-buttons">
            <button type="button" class="continue-btn" onclick="window.location.href='sanpham.php'">TIẾP TỤC MUA SẮM</button>
        </div>
    </div>
</div>

<div class="risk-free-shopping">
    <h5 class="pageTitle">An tâm khi mua sắm tại Vua Đồng Hồ</h5>
    <div class="risk-free-content">
        <div class="risk-box">
            <img src="https://vuahanghieu.com/assets/images/mien-phi-van-chuyen.svg" height="60" width="94" alt="Miễn phí vận chuyển">
            <h6 class="risk-title">Miễn phí vận chuyển</h6>
            <p>Miễn phí vận chuyển các đơn nội thành<br>Hà Nội và Hồ Chí Minh từ 500K</p>
        </div>
        <div class="risk-box">
            <img src="https://vuahanghieu.com/assets/images/doi-tra.svg" height="60" width="60" alt="Đổi trả đơn giản">
            <h6 class="risk-title">Đổi trả đơn giản</h6>
            <p>Đổi trả và hoàn tiền trong vòng 5 ngày.<br>Với bất kỳ sản phẩm lỗi, không đúng mô tả</p>
        </div>
        <div class="risk-box">
            <img src="https://vuahanghieu.com/assets/images/san-pham-chinh-hang.svg" height="60" width="47" alt="Sản phẩm chính hãng">
            <h6 class="risk-title">Sản phẩm chính hãng</h6>
            <p>Bảo đảm sản phẩm chính hãng 100%.<br>Hoàn tiền 150% nếu phát hiện hàng giả</p>
        </div>
        <div class="risk-box">
            <img src="https://vuahanghieu.com/assets/images/bao-mat-thong-tin.svg" height="60" width="86" alt="Bảo mật thông tin">
            <h6 class="risk-title">Bảo mật thông tin</h6>
            <p>100% thông tin cá nhân và giao dịch của bạn được bảo vệ an toàn</p>
        </div>
    </div>
</div>
<script>
        document.getElementById('gift_checkbox').addEventListener('change', function() {
            var giftChecked = this.checked; // Kiểm tra checkbox đã được chọn hay chưa

            // Gửi AJAX request để tính lại giá
            var xhr = new XMLHttpRequest();
            xhr.open("POST", "update_price.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            
            var giftFee = giftChecked ? 30000 : 0; // Phí quà tặng, nếu chọn thì là 30.000đ, nếu không thì là 0

            xhr.onreadystatechange = function() {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    // Nhận kết quả từ server
                    var response = JSON.parse(xhr.responseText);
                    
                    // Cập nhật lại giá tiền trên web
                    document.getElementById('total_price').textContent = response.totalPriceFormatted + ' ₫';
                    document.getElementById('final_price').textContent = response.finalTotalFormatted + ' ₫';

                    // Hiển thị hoặc ẩn phí gói quà
                    if (giftChecked) {
                        document.getElementById('gift_fee').style.display = 'block';
                    } else {
                        document.getElementById('gift_fee').style.display = 'none';
                    }
                }
            };

            // Gửi dữ liệu AJAX
            xhr.send("gift_fee=" + giftFee);
        });
    </script>
</body>
</html>

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
