<!DOCTYPE html>
<html lang="en">
 <style>
       /* Định dạng toàn bộ wrapper */
.wrapper {
    padding: 20px;
    background-color: #f9f9f9;
    font-family: Arial, sans-serif;
}

/* Tiêu đề chính */
h1 {
    text-align: center;
    font-size: 2rem;
    color: #333;
    margin-bottom: 20px;
}

/* Tiêu đề phụ */
h2 {
    color: #555;
    font-size: 1.5rem;
    margin-top: 30px;
    margin-bottom: 15px;
    border-bottom: 2px solid #ddd;
    padding-bottom: 5px;
}

/* Định dạng các sản phẩm */
.product {
    display: flex;
    align-items: center;
    gap: 15px;
    background-color: #fff;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 15px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

/* Ảnh sản phẩm */
.product img {
    border-radius: 8px;
    border: 1px solid #ccc;
    max-width: 100px;
    max-height: 100px;
    object-fit: cover;
}

/* Thông tin sản phẩm */
.product strong {
    font-weight: bold;
    color: #333;
}

.product div {
    display: flex;
    flex-direction: column;
}

/* Thông báo khi không có dữ liệu */
p {
    color: #777;
    font-style: italic;
    text-align: center;
    margin-top: 15px;
}

/* Responsive - đảm bảo hiển thị tốt trên di động */
@media (max-width: 768px) {
    .product {
        flex-direction: column;
        text-align: center;
    }

    .product img {
        margin: 0 auto;
    }
}

    </style>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Creative - Bootstrap 3 Responsive Admin Template">
    <meta name="author" content="GeeksLabs">
    <meta name="keyword" content="Creative, Dashboard, Admin, Template, Theme, Bootstrap, Responsive, Retina, Minimal">
    <link rel="shortcut icon" href="img/favicon.png">

    <title>Danh sách thương hiệu | Admin</title>

    <!-- Bootstrap CSS -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <!-- bootstrap theme -->
    <link href="css/bootstrap-theme.css" rel="stylesheet">
    <!--external css-->
    <!-- font icon -->
    <link href="css/elegant-icons-style.css" rel="stylesheet" />
    <link href="css/font-awesome.min.css" rel="stylesheet" />
    <!-- Custom styles -->
    <link href="css/style.css" rel="stylesheet">
    <link href="css/style-responsive.css" rel="stylesheet" />
</head>

<body>
    <!-- container section start -->
    <section id="container" class="">
        <!--header start-->
        <header class="header dark-bg">
            <div class="toggle-nav">
                <div class="icon-reorder tooltips" data-original-title="Toggle Navigation" data-placement="bottom"><i class="icon_menu"></i></div>
            </div>
            <a href="index.html" class="logo">Nice <span class="lite">Admin</span></a>
        </header>
        <!--header end-->

        <!--sidebar start-->
         <aside>
            <div id="sidebar" class="nav-collapse">
                <!-- sidebar menu start-->
                <ul class="sidebar-menu">
                    <li class="active">
                        <a href="index.html">
                            <i class="icon_house_alt"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>

                    <li class="sub-menu">
                        <a href="javascript:;">
                            <i class="icon_cart_alt"></i>
                            <span>Quản lý đồng  hồ</span>
                            <span class="menu-arrow arrow_carrot-right"></span>
                        </a>
                        <ul class="sub">
                            <li><a href="add_product.php">Thêm mới sản phẩm</a></li>
                            <li><a href="view_products.php">Danh sách sản phẩm</a></li>
                        </ul>
                    </li>

                    <li class="sub-menu">
                        <a href="javascript:;">
                            <i class="icon_documents_alt"></i>
                            <span>Quản lý Brands</span>
                            <span class="menu-arrow arrow_carrot-right"></span>
                        </a>
                        <ul class="sub">
                            <li><a href="add_brands.php">Thêm mới thương hiệu</a></li>
                            <li><a href="view_brand.php">Danh sách thương hiệu</a></li>
                        </ul>
                    </li>

                    <li class="sub-menu">
                        <a href="javascript:;">
                            <i class="icon_profile"></i>
                            <span>Quản lý người dùng</span>
                            <span class="menu-arrow arrow_carrot-right"></span>
                        </a>
                        <ul class="sub">
                            <li><a href="add_user.php">Thêm mới người dùng</a></li>
                            <li><a href="view_users.php">Danh sách người dùng</a></li>
                        </ul>
                    </li>

                    <li class="sub-menu">
                        <a href="javascript:;">
                            <i class="icon_mail_alt"></i>
                            <span>Quản lý liên hệ</span>
                            <span class="menu-arrow arrow_carrot-right"></span>
                        </a>
                        <ul class="sub">
                            <li><a href="view_feedback.php">Xem liên hệ</a></li>
                        </ul>
                    </li>

                    <li class="sub-menu">
                        <a href="javascript:;">
                            <i class="icon_profile"></i>
                            <span>Quản lý Voucher</span>
                            <span class="menu-arrow arrow_carrot-right"></span>
                        </a>
                        <ul class="sub">
                            <li><a href="add_voucher.php">Thêm mới Voucher</a></li>
                            <li><a href="view_voucher.php">Danh sách Voucher</a></li>
                        </ul>
                    </li>
                    <li class="sub-menu">
                        <a href="javascript:;">
                            <i class="icon_profile"></i>
                            <span>Quản lý giỏ hàng</span>
                            <span class="menu-arrow arrow_carrot-right"></span>
                        </a>
                        <ul class="sub">
                            
                            <li><a href="view_cart.php">Danh sách giỏ hàng</a></li>
                        </ul>
                    </li>
                     <li class="sub-menu">
                        <a href="javascript:;">
                            <i class="icon_mail_alt"></i>
                            <span>Quản lý Khách hàng</span>
                            <span class="menu-arrow arrow_carrot-right"></span>
                        </a>
                        <ul class="sub">
                            <li><a href="view_khachhang.php">Thông tin về khách hàng</a></li>
                            <li><a href="view_donhang.php">Thông tin về đơn hàng</a></li>
                        </ul>
                    </li>
                    <li class="sub-menu">
                        <a href="javascript:;">
                            <i class="icon_mail_alt"></i>
                            <span>Báo cáo thống kê</span>
                            <span class="menu-arrow arrow_carrot-right"></span>
                        </a>
                        <ul class="sub">
                            <li><a href="baocaokh.php">thống kê về khách hàng</a></li>
                            <li><a href="baocaosp.php">thống kê về sản phẩm</a></li>
                        </ul>
                    </li>
                    
                </ul>
                <!-- sidebar menu end-->
            </div>
        </aside>
        <!--sidebar end-->
        
        <!--main content start-->
        <section id="main-content">
            <section class="wrapper">
                <h1>Báo cáo Sản phẩm</h1>

                <?php
// Gọi file kết nối cơ sở dữ liệu
include 'db_connect.php';

// Truy vấn tìm sản phẩm được mua nhiều nhất
$mostPurchasedQuery = "
    SELECT p.code, p.name, p.image, SUM(c.quantity) as total_quantity
    FROM products p
    JOIN cart c ON p.id = c.product_id
    GROUP BY p.id
    ORDER BY total_quantity DESC
    LIMIT 1;
";

$mostPurchasedResult = $conn->query($mostPurchasedQuery);

// Hiển thị sản phẩm được mua nhiều nhất
echo "<h2>Sản phẩm được mua nhiều nhất</h2>";
if ($mostPurchasedResult->rowCount() > 0) {  // Thay num_rows bằng rowCount()
    while ($row = $mostPurchasedResult->fetch(PDO::FETCH_ASSOC)) {
        echo "<div class='product'>";
        echo "<strong>Mã sản phẩm:</strong> " . $row["code"] . "<br>";
        echo "<strong>Tên sản phẩm:</strong> " . $row["name"] . "<br>";
        echo "<img src='" . $row["image"] . "' alt='" . $row["name"] . "' style='max-width:100px;'><br>";
        echo "</div>";
    }
} else {
    echo "<p>Không tìm thấy sản phẩm đã mua.</p>";
}

// Truy vấn tìm các sản phẩm hết hàng
$outOfStockQuery = "SELECT code, name, image FROM products WHERE soluong = 0";
$outOfStockResult = $conn->query($outOfStockQuery);

// Hiển thị sản phẩm hết hàng
echo "<h2>Sản phẩm hết hàng</h2>";
if ($outOfStockResult->rowCount() > 0) {  // Thay num_rows bằng rowCount()
    while ($row = $outOfStockResult->fetch(PDO::FETCH_ASSOC)) {
        echo "<div class='product'>";
        echo "<strong>Mã sản phẩm:</strong> " . $row["code"] . "<br>";
        echo "<strong>Tên sản phẩm:</strong> " . $row["name"] . "<br>";
        echo "<img src='" . $row["image"] . "' alt='" . $row["name"] . "' style='max-width:100px;'><br>";
        echo "</div>";
    }
} else {
    echo "<p>Tất cả sản phẩm đều còn hàng.</p>";
}

$conn = null;
?>

            </section>
        </section>
        <!--main content end-->
    </section>
    <!-- container section end -->

    <!-- JavaScript Libraries -->
    <script src="js/jquery.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/jquery.scrollTo.min.js"></script>
    <script src="js/jquery.nicescroll.js" type="text/javascript"></script>
    <script src="js/scripts.js"></script>
</body>

</html>
