<?php
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_discount_code'])) {
    $discount_id = $_POST['discount_id'];
    $code = htmlspecialchars(trim($_POST['code']));
    $discount_percent = htmlspecialchars(trim($_POST['discount_percent']));
    $description = htmlspecialchars(trim($_POST['description']));
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $status = isset($_POST['status']) ? 1 : 0;  // 1 for active, 0 for inactive

    try {
        $stmt = $conn->prepare("UPDATE discount_codes 
                                SET code = :code, discount_percent = :discount_percent, description = :description, 
                                    start_date = :start_date, end_date = :end_date, status = :status 
                                WHERE id = :discount_id");
        $stmt->bindParam(':discount_id', $discount_id);
        $stmt->bindParam(':code', $code);
        $stmt->bindParam(':discount_percent', $discount_percent);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':start_date', $start_date);
        $stmt->bindParam(':end_date', $end_date);
        $stmt->bindParam(':status', $status);
        $stmt->execute();

        echo "Sửa mã giảm giá thành công!";
        header('Location: view_voucher.php'); // Redirect after successful update
    } catch (PDOException $e) {
        echo "Lỗi: " . $e->getMessage();
    }
}

// Fetch discount code details if the discount ID is set in the URL
if (isset($_GET['id'])) {
    $discount_id = $_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM discount_codes WHERE id = :discount_id");
    $stmt->bindParam(':discount_id', $discount_id);
    $stmt->execute();
    $discount_code = $stmt->fetch();
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

    <title>Basic Table | Creative - Bootstrap 3 Responsive Admin Template</title>

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

    <!-- HTML5 shim and Respond.js IE8 support of HTML5 -->
    <!--[if lt IE 9]>
      <script src="js/html5shiv.js"></script>
      <script src="js/respond.min.js"></script>
      <script src="js/lte-ie7.js"></script>
    <![endif]-->

    <!-- =======================================================
      Theme Name: NiceAdmin
      Theme URL: https://bootstrapmade.com/nice-admin-bootstrap-admin-html-template/
      Author: BootstrapMade
      Author URL: https://bootstrapmade.com
    ======================================================= -->
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
      <!--main content start-->
<section id="main-content">
    <section class="wrapper">
        <div class="row">
            <div class="col-lg-12">
                <h3 class="page-header">Chỉnh sửa mã giảm giá</h3>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <section class="panel">
                    <header class="panel-heading">Chỉnh sửa mã giảm giá</header>
                    <div class="panel-body">
                        <form action="edit_voucher.php?id=<?php echo htmlspecialchars($discount_code['id']); ?>" method="POST">
                            <!-- Discount Code -->
                            <div class="form-group">
                                <label for="code">Mã giảm giá</label>
                                <input type="text" class="form-control" id="code" name="code" value="<?php echo htmlspecialchars($discount_code['code']); ?>" required>
                            </div>

                            <!-- Discount Percentage -->
                            <div class="form-group">
                                <label for="discount_percent">Phần trăm giảm</label>
                                <input type="number" class="form-control" id="discount_percent" name="discount_percent" value="<?php echo htmlspecialchars($discount_code['discount_percent']); ?>" min="0" max="100" required>
                            </div>

                            <!-- Description -->
                            <div class="form-group">
                                <label for="description">Mô tả mã giảm giá</label>
                                <textarea class="form-control" id="description" name="description" rows="3" required><?php echo htmlspecialchars($discount_code['description']); ?></textarea>
                            </div>

                            <!-- Start Date -->
                            <div class="form-group">
                                <label for="start_date">Ngày bắt đầu</label>
                                <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo htmlspecialchars($discount_code['start_date']); ?>" required>
                            </div>

                            <!-- End Date -->
                            <div class="form-group">
                                <label for="end_date">Ngày kết thúc</label>
                                <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo htmlspecialchars($discount_code['end_date']); ?>" required>
                            </div>

                            <!-- Status -->
                            <div class="form-group">
                                <label for="status">Trạng thái</label>
                                <select class="form-control" id="status" name="status" required>
                                    <option value="1" <?php echo $discount_code['status'] == 1 ? 'selected' : ''; ?>>Kích hoạt</option>
                                    <option value="0" <?php echo $discount_code['status'] == 0 ? 'selected' : ''; ?>>Không kích hoạt</option>
                                </select>
                            </div>

                            <!-- Hidden Discount Code ID -->
                            <input type="hidden" name="discount_id" value="<?php echo htmlspecialchars($discount_code['id']); ?>">

                            <!-- Submit Button -->
                            <button type="submit" name="edit_discount_code" class="btn btn-primary">Lưu thay đổi</button>
                        </form>
                    </div>
                </section>
            </div>
        </div>
    </section>
</section>





        <!--main content end-->
        <div class="text-right">
            <div class="credits">
                Designed by <a href="https://bootstrapmade.com/">BootstrapMade</a>
            </div>
        </div>
    </section>
      <script src="js/jquery.js"></script>
            <script src="js/jquery-ui-1.10.4.min.js"></script>
            <script src="js/jquery-1.8.3.min.js"></script>
            <script type="text/javascript" src="js/jquery-ui-1.9.2.custom.min.js"></script>
            <!-- bootstrap -->
            <script src="js/bootstrap.min.js"></script>
            <!-- nice scroll -->
            <script src="js/jquery.scrollTo.min.js"></script>
            <script src="js/jquery.nicescroll.js" type="text/javascript"></script>
            <!-- charts scripts -->
            <script src="assets/jquery-knob/js/jquery.knob.js"></script>
            <script src="js/jquery.sparkline.js" type="text/javascript"></script>
            <script src="assets/jquery-easy-pie-chart/jquery.easy-pie-chart.js"></script>
            <script src="js/owl.carousel.js"></script>
            <!-- jQuery full calendar -->
            <script src="js/fullcalendar.min.js"></script>
            <!-- Full Google Calendar - Calendar -->
            <script src="assets/fullcalendar/fullcalendar/fullcalendar.js"></script>
            <!--script for this page only-->
            <script src="js/calendar-custom.js"></script>
            <script src="js/jquery.rateit.min.js"></script>
            <!-- custom select -->
            <script src="js/jquery.customSelect.min.js"></script>
            <script src="assets/chart-master/Chart.js"></script>

            <!--custome script for all page-->
            <script src="js/scripts.js"></script>
            <!-- custom script for this page-->
            <script src="js/sparkline-chart.js"></script>
            <script src="js/easy-pie-chart.js"></script>
            <script src="js/jquery-jvectormap-1.2.2.min.js"></script>
            <script src="js/jquery-jvectormap-world-mill-en.js"></script>
            <script src="js/xcharts.min.js"></script>
            <script src="js/jquery.autosize.min.js"></script>
            <script src="js/jquery.placeholder.min.js"></script>
            <script src="js/gdp-data.js"></script>
            <script src="js/morris.min.js"></script>
            <script src="js/sparklines.js"></script>
            <script src="js/charts.js"></script>
            <script src="js/jquery.slimscroll.min.js"></script>
            <script>
            //knob
            $(function() {
                $(".knob").knob({
                    'draw': function() {
                        $(this.i).val(this.cv + '%')
                    }
                })
            });

            //carousel
            $(document).ready(function() {
                $("#owl-slider").owlCarousel({
                    navigation: true,
                    slideSpeed: 300,
                    paginationSpeed: 400,
                    singleItem: true

                });
            });

            //custom select box

            $(function() {
                $('select.styled').customSelect();
            });

            /* ---------- Map ---------- */
            $(function() {
                $('#map').vectorMap({
                    map: 'world_mill_en',
                    series: {
                        regions: [{
                            values: gdpData,
                            scale: ['#000', '#000'],
                            normalizeFunction: 'polynomial'
                        }]
                    },
                    backgroundColor: '#eef3f7',
                    onLabelShow: function(e, el, code) {
                        el.html(el.html() + ' (GDP - ' + gdpData[code] + ')');
                    }
                });
            });
            </script>


</body>

</html>
