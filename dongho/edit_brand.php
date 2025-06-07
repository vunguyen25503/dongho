<?php
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_brand'])) {
    $brand_id = $_POST['brand_id'];
    $brand_name = htmlspecialchars(trim($_POST['brand_name']));
    $brand_description = htmlspecialchars(trim($_POST['brand_description']));
    $logo_brand = '';  // Default value if no logo is uploaded

    // Process file upload for logo
    if (isset($_FILES['logo_brands']) && $_FILES['logo_brands']['error'] == 0) {
        $logo_tmp = $_FILES['logo_brands']['tmp_name'];
        $logo_name = $_FILES['logo_brands']['name'];
        $logo_extension = pathinfo($logo_name, PATHINFO_EXTENSION);
        $logo_new_name = "logo_" . time() . "." . $logo_extension;

        // Validate image file extension
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array(strtolower($logo_extension), $allowed_extensions)) {
            $logo_path = "../uploads/logos/" . $logo_new_name;
            move_uploaded_file($logo_tmp, $logo_path);
            $logo_brand = "uploads/logos/" . $logo_new_name;

            // Optionally delete the old logo if necessary
            if (isset($brand['logo_brands']) && !empty($brand['logo_brands'])) {
                $old_logo = "../uploads/" . $brand['logo_brands'];
                if (file_exists($old_logo)) {
                    unlink($old_logo);
                }
            }
        } else {
            echo "Logo không hợp lệ. Chỉ hỗ trợ JPG, PNG, GIF.";
            exit();
        }
    } else {
        // If no new logo is uploaded, retain the existing logo
        $logo_brand = $brand['logo_brands'];
    }

    try {
        $stmt = $conn->prepare("UPDATE brands SET brand_name = :brand_name, brand_description = :brand_description, logo_brands = :logo_brands WHERE brand_id = :brand_id");
        $stmt->bindParam(':brand_id', $brand_id);
        $stmt->bindParam(':brand_name', $brand_name);
        $stmt->bindParam(':brand_description', $brand_description);
        $stmt->bindParam(':logo_brands', $logo_brand);
        $stmt->execute();

        echo "Sửa thương hiệu thành công!";
        header('Location: view_brand.php'); // Redirect after successful update
    } catch (PDOException $e) {
        echo "Lỗi: " . $e->getMessage();
    }
}

// Fetch brand details if the brand ID is set in the URL
if (isset($_GET['id'])) {
    $brand_id = $_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM brands WHERE brand_id = :brand_id");
    $stmt->bindParam(':brand_id', $brand_id);
    $stmt->execute();
    $brand = $stmt->fetch();
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
        <!--main content start-->
      <!--main content start-->
<section id="main-content">
    <section class="wrapper">
        <div class="row">
            <div class="col-lg-12">
                <h3 class="page-header">Chỉnh sửa thương hiệu</h3>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <section class="panel">
                    <header class="panel-heading">Chỉnh sửa thương hiệu</header>
                    <div class="panel-body">
                        <form action="edit_brand.php?id=<?php echo htmlspecialchars($brand_id); ?>" method="POST" enctype="multipart/form-data">
                            <!-- Brand Name -->
                            <div class="form-group">
                                <label for="brand_name">Tên thương hiệu</label>
                                <input type="text" class="form-control" id="brand_name" name="brand_name" value="<?php echo htmlspecialchars($brand['brand_name']); ?>" required>
                            </div>

                            <!-- Brand Description -->
                            <div class="form-group">
                                <label for="brand_description">Mô tả thương hiệu</label>
                                <textarea class="form-control" id="brand_description" name="brand_description" rows="3" required><?php echo htmlspecialchars($brand['brand_description']); ?></textarea>
                            </div>

                            <!-- Logo Upload or URL Input -->
                            <div class="form-group">
                                <label for="logo_brands">Logo thương hiệu</label>
                                <input type="file" class="form-control" id="logo_brands" name="logo_brands" accept="image/*">
                                <input type="text" class="form-control" name="logo_url" placeholder="Hoặc nhập URL của logo">
                                
                                <!-- Display Current Logo -->
                                <?php if (isset($brand['logo_brands']) && !empty($brand['logo_brands'])): ?>
                                    <p>Logo hiện tại:</p>
                                    <img src="../uploads/<?php echo htmlspecialchars($brand['logo_brands']); ?>" alt="Current logo" width="100">
                                <?php else: ?>
                                    <p>Không có logo thương hiệu.</p>
                                <?php endif; ?>
                            </div>

                            <!-- Hidden Brand ID -->
                            <input type="hidden" name="brand_id" value="<?php echo htmlspecialchars($brand['brand_id']); ?>">

                            <!-- Submit Button -->
                            <button type="submit" name="edit_brand" class="btn btn-primary">Lưu thay đổi</button>
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
