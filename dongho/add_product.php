<?php
include 'db_connect.php'; // Kết nối cơ sở dữ liệu

// Lấy danh sách các loại đồng hồ từ cơ sở dữ liệu
$stmt_categories = $conn->prepare("SELECT * FROM watch_categories");
$stmt_categories->execute();
$categories_list = $stmt_categories->fetchAll();

// Lấy danh sách các thương hiệu từ cơ sở dữ liệu
$stmt_brands = $conn->prepare("SELECT * FROM brands");
$stmt_brands->execute();
$brands_list = $stmt_brands->fetchAll();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Lấy dữ liệu từ form
    $name = $_POST['name'];
    $code = $_POST['code'];
    $price = $_POST['price'];
    $brand_id = $_POST['brand_id'];
    $category_id = $_POST['category_id'];
    $description = $_POST['description'];
    $detail = $_POST['detail'];
    $soluong = $_POST['soluong'];
    $discount = $_POST['discount'];
    $image_url = null; // Biến lưu đường dẫn ảnh

    // Xử lý logo từ URL
    if (!empty($_POST['image_url'])) {
        if (filter_var($_POST['image_url'], FILTER_VALIDATE_URL)) {
            $image_url = $_POST['image_url']; // Lưu URL ảnh
        } else {
            echo "<div class='alert alert-danger'>URL ảnh không hợp lệ.</div>";
            exit;
        }
    } elseif (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        // Đường dẫn lưu ảnh
        $target_dir = "../uploads/"; // Đảm bảo thư mục này tồn tại và có quyền ghi
        $image_name = basename($_FILES["image"]["name"]);
        $target_file = $target_dir . $image_name;
        $image_path = "uploads/" . $image_name;

        // Kiểm tra loại file ảnh
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $valid_extensions = array("jpg", "jpeg", "png", "gif");

        if (in_array($imageFileType, $valid_extensions)) {
            // Di chuyển file vào thư mục uploads
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                $image_url = $image_path; // Lưu đường dẫn file ảnh
            } else {
                echo "<div class='alert alert-danger'>Lỗi khi tải lên ảnh.</div>";
                exit;
            }
        } else {
            echo "<div class='alert alert-danger'>Chỉ chấp nhận các định dạng ảnh: jpg, jpeg, png, gif.</div>";
            exit;
        }
    } else {
        echo "<div class='alert alert-danger'>Vui lòng nhập URL hoặc tải lên ảnh.</div>";
        exit;
    }

    // Thêm sản phẩm vào cơ sở dữ liệu
    $sql = "INSERT INTO Products (name, code, price, brand_id, category_id, description, detail, image, soluong, discount) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$name, $code, $price, $brand_id, $category_id, $description, $detail, $image_url, $soluong, $discount]);

    echo "<div class='alert alert-success'>Sản phẩm đã được thêm thành công.</div>";
    header('Location: view_products.php');
    
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

</head>

<body>
    <section id="container">
        <header class="header dark-bg">
            <a href="index.php" class="logo">Nice <span class="lite">Admin</span></a>
        </header>

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

       <section id="main-content">
    <section class="wrapper">
        <div class="row">
            <div class="col-lg-12">
                <h3 class="page-header"><i class="fa fa-table"></i> Thêm mới sản phẩm</h3>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <section class="panel">
                    <header class="panel-heading">Thêm sản phẩm mới</header>
                    <div class="panel-body">
                        <form class="form-horizontal" action="add_product.php" method="POST" enctype="multipart/form-data">
                            <!-- Tên sản phẩm -->
                            <div class="form-group">
                                <label class="col-sm-2 control-label">Tên sản phẩm</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" name="name" required>
                                </div>
                            </div>

                            <!-- Mã sản phẩm -->
                            <div class="form-group">
                                <label class="col-sm-2 control-label">Mã sản phẩm</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" name="code" required>
                                </div>
                            </div>

                            <!-- Giá sản phẩm -->
                            <div class="form-group">
                                <label class="col-sm-2 control-label">Giá</label>
                                <div class="col-sm-10">
                                    <input type="number" class="form-control" name="price" required>
                                </div>
                            </div>

                            <!-- Thương hiệu -->
                            <div class="form-group">
                                <label class="col-sm-2 control-label" for="brand">Thương hiệu</label>
                                <div class="col-sm-10">
                                    <select class="form-control" name="brand_id" required>
                                        <option value="">Chọn Thương hiệu</option>
                                        <?php foreach ($brands_list as $brand): ?>
                                            <option value="<?= $brand['brand_id'] ?>"><?= htmlspecialchars($brand['brand_name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <!-- Danh mục -->
                            <div class="form-group">
                                <label class="col-sm-2 control-label" for="category">Danh mục</label>
                                <div class="col-sm-10">
                                    <select class="form-control" name="category_id" required>
                                        <option value="">Chọn Loại sản phẩm</option>
                                        <?php foreach ($categories_list as $category): ?>
                                            <option value="<?= $category['category_id'] ?>"><?= htmlspecialchars($category['category_name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <!-- Mô tả sản phẩm -->
                            <div class="form-group">
                                <label class="col-sm-2 control-label">Mô tả</label>
                                <div class="col-sm-10">
                                    <textarea class="form-control" name="description"></textarea>
                                </div>
                            </div>

                            <!-- Chi tiết sản phẩm -->
                            <div class="form-group">
                                <label class="col-sm-2 control-label">Chi tiết</label>
                                <div class="col-sm-10">
                                    <textarea class="form-control" name="detail"></textarea>
                                </div>
                            </div>

                            <!-- Số lượng tồn kho -->
                            <div class="form-group">
                                <label class="col-sm-2 control-label">Số lượng tồn kho</label>
                                <div class="col-sm-10">
                                    <input type="number" class="form-control" name="soluong" required>
                                </div>
                            </div>

                            <!-- Giảm giá -->
                            <div class="form-group">
                                <label class="col-sm-2 control-label">Giảm giá (%)</label>
                                <div class="col-sm-10">
                                    <input type="number" class="form-control" name="discount">
                                </div>
                            </div>

                            <!-- URL Ảnh -->
                            <div class="form-group">
                                <label class="col-sm-2 control-label">URL Ảnh</label>
                                <div class="col-sm-10">
                                    <input type="url" class="form-control" name="image_url" placeholder="Nhập URL ảnh (tùy chọn)">
                                </div>
                            </div>

                            <!-- Tải lên Ảnh -->
                            <div class="form-group">
                                <label class="col-sm-2 control-label">Hoặc Tải lên Ảnh</label>
                                <div class="col-sm-10">
                                    <input type="file" class="form-control" name="image" accept="image/*">
                                </div>
                            </div>

                            <!-- Nút Submit -->
                            <div class="form-group">
                                <div class="col-sm-offset-2 col-sm-10">
                                    <button type="submit" class="btn btn-primary">Thêm mới</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </section>
            </div>
        </div>
    </section>
</section>


    <!-- Include necessary JS files -->
     <!-- javascripts -->
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
