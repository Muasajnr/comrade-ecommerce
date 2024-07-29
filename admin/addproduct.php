<?php
session_start();


$servername = "localhost";
$username = "root";
$password = "";
$dbname = "comrade";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $product_name = $_POST['name'];
    $price = $_POST['price'];
    $description = $_POST['description'];
    $user_id = $_SESSION['user_id'];
    $user_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Admin';
    $uploaded_date = date("Y-m-d H:i:s");

    // Handle file upload
    $image_paths = [];
    $target_dir = "uploads/";

    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0755, true); // Create directory if it doesn't exist
    }

    if (isset($_FILES['images'])) {
        foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
            $target_file = $target_dir . basename(preg_replace('/[^a-zA-Z0-9\._-]/', '_', $_FILES['images']['name'][$key]));
            if (move_uploaded_file($tmp_name, $target_file)) {
                $image_paths[] = $target_file;
            } else {
                echo "Error uploading file: " . $_FILES['images']['name'][$key];
            }
        }
    }

    $image_paths_str = implode(',', $image_paths);

    // Insert product into the database
    $sql = "INSERT INTO products (user_id, user_name, product_name, price, description, uploaded_date, image_path) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issdsss", $user_id, $user_name, $product_name, $price, $description, $uploaded_date, $image_paths_str);

    if ($stmt->execute()) {
        echo "New product uploaded successfully";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}
$conn->close();
?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>New Product | Comrade</title>
    <!-- Favicon -->
    <link href="img/favicon.ico" rel="icon">

    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Heebo:wght@400;500;600&family=Inter:wght@700;800&display=swap" rel="stylesheet">

    <!-- Icon Font Stylesheet -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Libraries Stylesheet -->
    <link href="../sell/lib/animate/animate.min.css" rel="stylesheet">
    <link href="../sell/lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">

    <!-- Customized Bootstrap Stylesheet -->
    <link href="../sell/css/bootstrap.min.css" rel="stylesheet">

    <!-- Template Stylesheet -->
    <link href="../sell/css/style.css" rel="stylesheet">
</head>

<body>
    <div class="container">
        <div class="page-title">
            <h3>New Product</h3>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">Add Product</div>
                    <div class="card-body">
                        <h5 class="card-title">Fill all fields and save to add a product</h5>
                        <form class="needs-validation" novalidate accept-charset="utf-8" enctype="multipart/form-data" action="addproduct.php" method="post">
                            <div class="row g-2">
                                <div class="mb-3 col-md-6">
                                    <label for="name" class="form-label">Name</label>
                                    <input type="text" class="form-control" name="name" placeholder="Product Name" required>
                                    <small class="form-text text-muted">Enter product name.</small>
                                    <div class="valid-feedback">Looks good!</div>
                                    <div class="invalid-feedback">Please enter Product Name.</div>
                                </div>
                                <div class="mb-3 col-md-6">
                                    <label for="price" class="form-label">Price</label>
                                    <input type="text" class="form-control" name="price" placeholder="Price" required>
                                    <small class="form-text text-muted">Product Price.</small>
                                    <div class="valid-feedback">Looks good!</div>
                                    <div class="invalid-feedback">Please enter product price.</div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" name="description" placeholder="Product Description" required style="height: 100px;"></textarea>
                                <div class="valid-feedback">Looks good!</div>
                                <div class="invalid-feedback">Please enter product description.</div>
                            </div>
                            <div class="row g-2">
                                <div class="mb-3 col-md-6">
                                    <label for="images" class="form-label">Images</label>
                                    <input type="file" class="form-control" name="images[]" placeholder="Attach Images" required multiple>
                                    <div class="valid-feedback">Looks good!</div>
                                    <div class="invalid-feedback">Please attach product images.</div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="" id="check1" required>
                                    <label class="form-check-label" for="check1">Check me out</label>
                                    <div class="invalid-feedback">You must agree before submitting.</div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="../sell/assets/vendor/jquery/jquery.min.js"></script>
    <script src="../sell/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../sell/assets/js/form-validator.js"></script>
    <script src="../sell/assets/js/script.js"></script>
</body>

</html>
