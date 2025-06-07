<?php
session_start();
include 'db_connect.php';

// Lấy giá trị của gift_fee từ yêu cầu AJAX
$gift_fee = isset($_POST['gift_fee']) ? $_POST['gift_fee'] : 0;

$totalPrice = 0;

// Tính tổng tiền của giỏ hàng
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];

    $stmt = $conn->prepare("
        SELECT 
            c.quantity, 
            p.price, p.discount
        FROM cart c
        JOIN products p ON c.product_id = p.id
        WHERE c.user_id = ?
    ");
    $stmt->execute([$user_id]);
    $cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($cartItems as $item) {
        // Áp dụng giảm giá cho từng sản phẩm
        $discountedPrice = $item['price'] - ($item['price'] * ($item['discount'] / 100));
        $totalPrice += $discountedPrice * $item['quantity'];
    }
}

// Tính tổng tiền với mã giảm giá và phí quà tặng
$discountPercent = $_SESSION['discount_percent'] ?? 0;
$finalTotal = $totalPrice * (1 - $discountPercent / 100) + $gift_fee;

// Định dạng tổng tiền
$totalPriceFormatted = number_format($totalPrice, 0, ',', '.');
$finalTotalFormatted = number_format($finalTotal, 0, ',', '.');

// Trả về kết quả cho AJAX
echo json_encode([
    'totalPriceFormatted' => $totalPriceFormatted,
    'finalTotalFormatted' => $finalTotalFormatted
]);
?>
