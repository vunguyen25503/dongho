<?php
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_product'])) {
    $product_id = $_POST['id'];
    $product_name = htmlspecialchars(trim($_POST['name']));
    $product_code = htmlspecialchars(trim($_POST['code']));
    $price = floatval($_POST['price']);
    $Thuong_hieu = htmlspecialchars(trim($_POST['Thuong_hieu']));
    $category = htmlspecialchars(trim($_POST['category']));
    $description = htmlspecialchars(trim($_POST['description']));
    $detail = htmlspecialchars(trim($_POST['detail']));
    $soluong = intval($_POST['soluong']);
    $discount = floatval($_POST['discount']);
    $image = ''; // Default value if no image is uploaded
    $brands_stmt = $conn->prepare("SELECT brand_id, brand_name FROM brands");
    $brands_stmt->execute();
    $brands = $brands_stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetching categories
    $categories_stmt = $conn->prepare("SELECT category_id, category_name FROM watch_categories");
    $categories_stmt->execute();
    $categories = $categories_stmt->fetchAll(PDO::FETCH_ASSOC);

    // If a URL for the image is provided, use it
    if (!empty($_POST['image_url'])) {
        $image = htmlspecialchars(trim($_POST['image_url']));
    } else {
        // Process file upload for product image
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $image_tmp = $_FILES['image']['tmp_name'];
            $image_name = $_FILES['image']['name'];
            $image_extension = pathinfo($image_name, PATHINFO_EXTENSION);
            $image_new_name = "product_" . time() . "." . $image_extension;

            // Validate image file extension
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
            if (in_array(strtolower($image_extension), $allowed_extensions)) {
                $image_path = "../uploads/products/" . $image_new_name;
                move_uploaded_file($image_tmp, $image_path);
                $image = "uploads/products/" . $image_new_name;

                // Optionally delete the old image if necessary
                if (isset($product['image']) && !empty($product['image'])) {
                    $old_image = "../uploads/" . $product['image'];
                    if (file_exists($old_image)) {
                        unlink($old_image);
                    }
                }
            } else {
                echo "Hình ảnh không hợp lệ. Chỉ hỗ trợ JPG, PNG, GIF.";
                exit();
            }
        } else {
            // If no new image is uploaded, retain the existing image
            $image = $product['image'];
        }
    }

    try {
        $stmt = $conn->prepare("
            UPDATE products 
            SET 
                name = :name, 
                code = :code, 
                price = :price, 
                Thuong_hieu = :Thuong_hieu, 
                category = :category, 
                description = :description, 
                image = :image, 
                detail = :detail, 
                soluong = :soluong, 
                discount = :discount
            WHERE id = :id
        ");
        $stmt->bindParam(':id', $product_id);
        $stmt->bindParam(':name', $product_name);
        $stmt->bindParam(':code', $product_code);
        $stmt->bindParam(':price', $price);
        $stmt->bindParam(':Thuong_hieu', $Thuong_hieu);
        $stmt->bindParam(':category', $category);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':image', $image);
        $stmt->bindParam(':detail', $detail);
        $stmt->bindParam(':soluong', $soluong);
        $stmt->bindParam(':discount', $discount);
        $stmt->execute();

        echo "Sửa sản phẩm thành công!";
        header('Location: view_products.php'); // Redirect after successful update
    } catch (PDOException $e) {
        echo "Lỗi: " . $e->getMessage();
    }
}

// Fetch product details if the product ID is set in the URL
if (isset($_GET['id'])) {
    $product_id = $_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = :id");
    $stmt->bindParam(':id', $product_id);
    $stmt->execute();
    $product = $stmt->fetch();

    // Fetching all brands and categories for the form
    $brands_stmt = $conn->prepare("SELECT brand_id, brand_name FROM brands");
    $brands_stmt->execute();
    $brands = $brands_stmt->fetchAll(PDO::FETCH_ASSOC);

    $categories_stmt = $conn->prepare("SELECT category_id, category_name FROM watch_categories");
    $categories_stmt->execute();
    $categories = $categories_stmt->fetchAll(PDO::FETCH_ASSOC);
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
  <!--main content start-->
     

<section id="main-content">
    <section class="wrapper">
        <div class="row">
            <div class="col-lg-12">
                <h3 class="page-header"><i class="fa fa-edit"></i> Chỉnh sửa sản phẩm</h3>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <section class="panel">
                    <header class="panel-heading">Chỉnh sửa sản phẩm</header>
                    <div class="panel-body">
                        <form class="form-horizontal" action="edit_product.php" method="POST" enctype="multipart/form-data">
                            <!-- Tên sản phẩm -->
                            <div class="form-group">
                                <label class="col-sm-2 control-label">Tên sản phẩm</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required>
                                </div>
                            </div>

                            <!-- Mã sản phẩm -->
                            <div class="form-group">
                                <label class="col-sm-2 control-label">Mã sản phẩm</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" name="code" value="<?php echo htmlspecialchars($product['code']); ?>" required>
                                </div>
                            </div>

                            <!-- Giá -->
                            <div class="form-group">
                                <label class="col-sm-2 control-label">Giá</label>
                                <div class="col-sm-10">
                                    <input type="number" class="form-control" name="price" value="<?php echo htmlspecialchars($product['price']); ?>" required>
                                </div>
                            </div>

                            <!-- Thương hiệu -->
                            <div class="form-group">
                                <label for="Thuong_hieu" class="col-sm-2 control-label">Thương hiệu:</label>
                                <div class="col-sm-10">
                                    <select name="Thuong_hieu" required>
                                        <?php foreach ($brands as $brand): ?>
                                            <option value="<?php echo $brand['brand_id']; ?>" <?php echo $product['Thuong_hieu'] == $brand['brand_id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($brand['brand_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <!-- Danh mục -->
                            <div class="form-group">
                                <label for="category" class="col-sm-2 control-label">Danh mục:</label>
                                <div class="col-sm-10">
                                    <select name="category" required>
                                        <?php foreach ($categories as $category): ?>
                                            <option value="<?php echo $category['category_id']; ?>" <?php echo $product['category'] == $category['category_id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($category['category_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <!-- Mô tả -->
                            <div class="form-group">
                                <label class="col-sm-2 control-label">Mô tả</label>
                                <div class="col-sm-10">
                                    <textarea class="form-control" name="description"><?php echo htmlspecialchars($product['description']); ?></textarea>
                                </div>
                            </div>

                            <!-- Chi tiết -->
                            <div class="form-group">
                                <label class="col-sm-2 control-label">Chi tiết</label>
                                <div class="col-sm-10">
                                    <textarea class="form-control" name="detail"><?php echo htmlspecialchars($product['detail']); ?></textarea>
                                </div>
                            </div>

                            <!-- Số lượng tồn kho -->
                            <div class="form-group">
                                <label class="col-sm-2 control-label">Số lượng tồn kho</label>
                                <div class="col-sm-10">
                                    <input type="number" class="form-control" name="soluong" value="<?php echo htmlspecialchars($product['soluong']); ?>" required>
                                </div>
                            </div>

                            <!-- Giảm giá -->
                            <div class="form-group">
                                <label class="col-sm-2 control-label">Giảm giá (%)</label>
                                <div class="col-sm-10">
                                    <input type="number" class="form-control" name="discount" value="<?php echo htmlspecialchars($product['discount']); ?>">
                                </div>
                            </div>

                            <!-- Ảnh sản phẩm -->
                           <div class="form-group">
    <label class="col-sm-2 control-label">URL ảnh sản phẩm</label>
    <div class="col-sm-10">
        <input type="text" class="form-control" name="image_url" value="<?php echo htmlspecialchars($product['image']); ?>" placeholder="Nhập URL ảnh sản phẩm nếu có">
    </div>
</div>


                            <!-- Nút lưu thay đổi -->
                            <div class="form-group">
                                <div class="col-sm-offset-2 col-sm-10">
                                    <button type="submit" class="btn btn-primary" name="edit_product">Lưu thay đổi</button>
                                </div>
                            </div>

                            <!-- ID sản phẩm (ẩn) -->
                            <input type="hidden" name="id" value="<?php echo htmlspecialchars($product['id']); ?>">

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
