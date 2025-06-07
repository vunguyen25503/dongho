<?php
include('db_connect.php'); // Include the database connection

// Query to fetch brand data
$sql = "SELECT brand_id, brand_name, logo_brands, brand_description, created_at FROM brands";
$result = $conn->query($sql);

// Fetch the results into an array
$brands = $result->fetchAll(PDO::FETCH_ASSOC);

// Handle successful deletion status
if (isset($_GET['status']) && $_GET['status'] == 'deleted') {
    echo "<div class='alert alert-success'>Thương hiệu đã được xóa thành công.</div>";
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

            <!--logo start-->
            <a href="index.php" class="logo">Nice <span class="lite">Admin</span></a>
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
        <!--sidebar end-->
        
        <!--main content start-->
       <section id="main-content">
    <section class="wrapper">
        <div class="row">
            <div class="col-lg-12">
                <h3 class="page-header"><i class="fa fa-table"></i> Danh sách thương hiệu</h3>
                <ol class="breadcrumb">
                    <li><i class="fa fa-home"></i><a href="index.php">Home</a></li>
                    <li><i class="fa fa-table"></i>Quản lý thương hiệu</li>
                    <li><i class="fa fa-th-list"></i>Danh sách thương hiệu</li>
                </ol>
            </div>
        </div>
        <!-- page start -->
        <div class="row">
            <?php
            include('db_connect.php'); // Kết nối cơ sở dữ liệu

            // Truy vấn danh sách thương hiệu
            $sql = "SELECT brand_id, brand_name, brand_description, logo_brands, created_at FROM brands";
            $result = $conn->query($sql);

            if (isset($_GET['status']) && $_GET['status'] == 'deleted') {
                echo "<div class='alert alert-success'>Thương hiệu đã được xóa thành công.</div>";
            }
            ?>
            <section class="panel">
                <header class="panel-heading">
                    Danh sách thương hiệu
                </header>
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Logo thương hiệu</th>
                            <th>Tên thương hiệu</th>
                            <th>Mô tả thương hiệu</th>
                            <th>Ngày tạo</th>
                            <th>Chức năng</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($result->rowCount() > 0) {
                            while ($brand = $result->fetch(PDO::FETCH_ASSOC)) {
                                // Xử lý logo URL
                                $logo_url = $brand["logo_brands"];
                                if (strpos($logo_url, 'http://') === false && strpos($logo_url, 'https://') === false) {
                                    $logo_url = "http://localhost:8080/dongho/" . $logo_url;
                                }

                                echo "<tr>
                                    <td>" . htmlspecialchars($brand['brand_id']) . "</td>
                                    <td><img src='" . htmlspecialchars($logo_url) . "' alt='Brand Logo' class='brand-image' width='50' height='50'></td>
                                    <td>" . htmlspecialchars($brand['brand_name']) . "</td>
                                    <td>" . htmlspecialchars($brand['brand_description']) . "</td>
                                    <td>" . htmlspecialchars($brand['created_at']) . "</td>
                                    <td>
                                        <a href='edit_brand.php?id=" . htmlspecialchars($brand['brand_id']) . "' class='btn btn-warning btn-sm'>Sửa</a>
                                        <a href='delete_brand.php?id=" . htmlspecialchars($brand['brand_id']) . "' class='btn btn-danger btn-sm' onclick=\"return confirm('Bạn có chắc chắn muốn xóa thương hiệu này?');\">Xóa</a>
                                    </td>
                                </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='6'>Không có dữ liệu</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </section>
        </div>
        <!-- page end -->
    </section>
</section>

    <!-- container section end -->

    <!-- JavaScript Libraries -->
    <script src="js/jquery.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <!-- nicescroll -->
    <script src="js/jquery.scrollTo.min.js"></script>
    <script src="js/jquery.nicescroll.js" type="text/javascript"></script>
    <!-- custom script for all page -->
    <script src="js/scripts.js"></script>
</body>

</html>
