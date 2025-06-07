
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Creative - Bootstrap 3 Responsive Admin Template">
    <meta name="author" content="GeeksLabs">
    <meta name="keyword" content="Creative, Dashboard, Admin, Template, Theme, Bootstrap, Responsive, Retina, Minimal">
    <link rel="shortcut icon" href="img/favicon.png">

    <title>Danh sách đồng hồ | Admin</title>

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

            <!--logo start-->
            <a href="index.html" class="logo">Nice <span class="lite">Admin</span></a>
            <!--logo end-->
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
        <!--main content start-->
       <!-- main content start -->
<section id="main-content">
    <section class="wrapper">
        <div class="row">
            <div class="col-lg-12">
                <h3 class="page-header"><i class="fa fa-table"></i> Thông tin khách hàng</h3>
                <ol class="breadcrumb">
                    <li><i class="fa fa-home"></i><a href="index.html">Home</a></li>
                    <li><i class="fa fa-table"></i>Quản lý khách hàng</li>
                    <li><i class="fa fa-user"></i>Thông tin khách hàng</li>
                </ol>
            </div>
        </div>
        <!-- page start-->
        <div class="row">
            <?php
            // Gọi file kết nối cơ sở dữ liệu
            include 'db_connect.php';

            // Truy vấn tìm khách hàng có tổng tiền mua cao nhất
            $highestSpendingQuery = "
                SELECT u.id AS ma_kh, u.username AS ten, u.email, u.phone AS sdt, SUM(c.quantity * p.price) AS tongtien
                FROM users u
                JOIN cart c ON u.id = c.user_id
                JOIN products p ON c.product_id = p.id
                GROUP BY u.id
                ORDER BY tongtien DESC
                LIMIT 1;
            ";

            $highestSpendingResult = $conn->query($highestSpendingQuery);

            // Hiển thị khách hàng có tổng tiền mua cao nhất
            echo "<h2>Khách hàng có tổng tiền mua cao nhất</h2>";
            if ($highestSpendingResult->rowCount() > 0) {
                while ($row = $highestSpendingResult->fetch(PDO::FETCH_ASSOC)) {
                    echo "<div class='customer'>";
                    echo "<strong>Mã khách hàng:</strong> " . $row["ma_kh"] . "<br>";
                    echo "<strong>Tên khách hàng:</strong> " . $row["ten"] . "<br>";
                    echo "<strong>Email:</strong> " . $row["email"] . "<br>";
                    echo "<strong>Số điện thoại:</strong> " . $row["sdt"] . "<br>";
                    echo "<strong>Tổng tiền:</strong> " . number_format($row["tongtien"], 0, ',', '.') . " VND<br>";
                    echo "</div>";
                }
            } else {
                echo "<p>Không tìm thấy khách hàng.</p>";
            }

            // Truy vấn tìm khách hàng có lượt mua nhiều sản phẩm nhất
            $mostPurchasesQuery = "
                SELECT u.id AS ma_kh, u.username AS ten, u.email, u.phone AS sdt, SUM(c.quantity) AS soluong
                FROM users u
                JOIN cart c ON u.id = c.user_id
                GROUP BY u.id
                ORDER BY soluong DESC
                LIMIT 1;
            ";

            $mostPurchasesResult = $conn->query($mostPurchasesQuery);

            // Hiển thị khách hàng có lượt mua nhiều sản phẩm nhất
            echo "<h2>Khách hàng có lượt mua nhiều sản phẩm nhất</h2>";
            if ($mostPurchasesResult->rowCount() > 0) {
                while ($row = $mostPurchasesResult->fetch(PDO::FETCH_ASSOC)) {
                    echo "<div class='customer'>";
                    echo "<strong>Mã khách hàng:</strong> " . $row["ma_kh"] . "<br>";
                    echo "<strong>Tên khách hàng:</strong> " . $row["ten"] . "<br>";
                    echo "<strong>Email:</strong> " . $row["email"] . "<br>";
                    echo "<strong>Số điện thoại:</strong> " . $row["sdt"] . "<br>";
                    echo "<strong>Số lượng mua:</strong> " . $row["soluong"] . "<br>";
                    echo "</div>";
                }
            } else {
                echo "<p>Không tìm thấy khách hàng.</p>";
            }

            // Đóng kết nối
            $conn = null;
            ?>
        </div>
        <!-- page end-->
    </section>
</section>
<!-- main content end-->

    <!-- container section end -->
    <!-- javascripts -->
    <script src="js/jquery.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <!-- nicescroll -->
    <script src="js/jquery.scrollTo.min.js"></script>
    <script src="js/jquery.nicescroll.js" type="text/javascript"></script>
    <!--custome script for all page-->
    <script src="js/scripts.js"></script>

</body>

</html>
