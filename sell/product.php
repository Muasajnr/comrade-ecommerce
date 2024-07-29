<?php
session_start();

// Database connection details
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

// Get product id from query string
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch product details from database
$sql = "SELECT id, product_name, price, upload_date, user_name, image_path, description FROM products WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

// Handle order submission
if (isset($_POST['order'])) {
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
        $quantity = intval($_POST['quantity']);
        $user_name = $_POST['user_name'];
        $order_date = date('Y-m-d');
        $payment_method = $_POST['payment_method'];
        $address = $_POST['address'];
        $city = $_POST['city'];
        $state = $_POST['state'];
        $zip_code = $_POST['zip_code'];
        $contact_number = $_POST['contact_number'];

        // Insert order into database
        $order_sql = "INSERT INTO orders (user_id, product_id, product_name, price, quantity, order_date, user_name, payment_method, address, city, state, zip_code, contact_number) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $order_stmt = $conn->prepare($order_sql);
        $order_stmt->bind_param("iisdsssssssss", $user_id, $product_id, $product['product_name'], $product['price'], $quantity, $order_date, $user_name, $payment_method, $address, $city, $state, $zip_code, $contact_number);
        if ($order_stmt->execute()) {
            echo '<p>Order placed successfully!</p>';
        } else {
            echo '<p>Error placing order. Please try again.</p>';
        }
        $order_stmt->close();
    } else {
        echo '<p>Please log in to place an order.</p>';
    }
}

// Logout process
if (isset($_POST['logout'])) {
    session_destroy();
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Product Details | Comrade</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="" name="keywords">
    <meta content="" name="description">

    <!-- Favicon -->
    <link href="img/favicon.ico" rel="icon">

    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Heebo:wght@400;500;600&family=Inter:wght@700;800&display=swap" rel="stylesheet">

    <!-- Icon Font Stylesheet -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Customized Bootstrap Stylesheet -->
    <link href="css/bootstrap.min.css" rel="stylesheet">

    <!-- Template Stylesheet -->
    <link href="css/style.css" rel="stylesheet">

    <style>
        /* Popup styles */
        .checkout-popup {
            display: none; /* Hidden by default */
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.5); /* Black background with opacity */
        }

        .checkout-popup-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 600px;
            border-radius: 8px;
        }

        .close-popup {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close-popup:hover,
        .close-popup:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
    </style>
</head>

<body>
    <div class="container-xxl bg-white p-0">
        <!-- Navbar Start -->
        <div class="container-fluid nav-bar bg-transparent">
            <nav class="navbar navbar-expand-lg bg-white navbar-light py-0 px-4">
                <a href="index.php" class="navbar-brand d-flex align-items-center text-center">
                    <div class="icon p-2 me-2">
                        <img class="img-fluid" src="img/icon-deal.png" alt="Icon" style="width: 30px; height: 30px;">
                    </div>
                    <h1 class="m-0 text-primary">Comrade Ecommerce</h1>
                </a>
                <button type="button" class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#navbarCollapse">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarCollapse">
                    <div class="navbar-nav ms-auto">
                        <a href="index.php" class="nav-item nav-link active">Home</a>
                        <a href="addproduct.php" class="nav-item nav-link">Add Product</a>
                        <a href="dashboard.php" class="nav-item nav-link">Dashboard</a>
                    </div>
                    <a href="logout.php" class="btn btn-primary px-3 d-none d-lg-flex">Log Out</a>
                </div>
            </nav>
        </div>
        <!-- Navbar End -->

        <!-- Product Details Start -->
        <div class="container py-5">
            <div class="row">
                <div class="col-lg-6">
                    <?php
                    // Check if image_path is available
                    if (!empty($product['image_path'])) {
                        // Split the image_path string into an array
                        $images = explode(',', $product['image_path']);
                        foreach ($images as $image) {
                            // Construct the image path
                            $image_path = '../sell/' . htmlspecialchars($image);

                            // Check if the file exists and display image
                            if (file_exists($image_path)) {
                                echo '<img class="img-fluid mb-3" src="' . $image_path . '" alt="' . htmlspecialchars($product['product_name']) . '">';
                            } else {
                                echo '<p>Image not found: ' . htmlspecialchars($image) . '</p>';
                            }
                        }
                    } else {
                        echo '<p>No images available.</p>';
                    }
                    ?>
                </div>
                <div class="col-lg-6">
                    <h1 class="mb-4"><?php echo htmlspecialchars($product['product_name']); ?></h1>
                    <h2>Price: Ksh <?php echo htmlspecialchars($product['price']); ?></h2>
                    <p><?php echo htmlspecialchars($product['description']); ?></p>
                    <p>Uploaded by: <?php echo htmlspecialchars($product['user_name']); ?></p>
                    <p>Upload Date: <?php echo htmlspecialchars($product['upload_date']); ?></p>

                    <!-- Order Button -->
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <button class="btn btn-primary" onclick="openCheckoutPopup()">Order Now</button>
                    <?php else: ?>
                        <p>Please <a href="login.php">log in</a> to place an order.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <!-- Product Details End -->

        <!-- Checkout Popup Start -->
        <div id="checkoutPopup" class="checkout-popup">
            <div class="checkout-popup-content">
                <span class="close-popup">&times;</span>
                <h2>Checkout</h2>
                <form id="checkoutForm" action="" method="post">
                    <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($product_id); ?>">
                    <div class="mb-3">
                        <label for="user_name" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="user_name" name="user_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="quantity" class="form-label">Quantity</label>
                        <input type="number" class="form-control" id="quantity" name="quantity" required>
                    </div>
                    <div class="mb-3">
                        <label for="payment_method" class="form-label">Payment Method</label>
                        <select class="form-select" id="payment_method" name="payment_method" required>
                            <option value="Credit Card">Mpesa</option>
                            <option value="Credit Card">Credit Card</option>
                            <option value="PayPal">PayPal</option>
                            <option value="Bank Transfer">Bank Transfer</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="address" class="form-label">Street Address</label>
                        <input type="text" class="form-control" id="address" name="address" required>
                    </div>
                    <div class="mb-3">
                        <label for="city" class="form-label">City</label>
                        <input type="text" class="form-control" id="city" name="city" required>
                    </div>
                    <div class="mb-3">
                        <label for="state" class="form-label">State</label>
                        <input type="text" class="form-control" id="state" name="state" required>
                    </div>
                    <div class="mb-3">
                        <label for="zip_code" class="form-label">Zip Code</label>
                        <input type="text" class="form-control" id="zip_code" name="zip_code" required>
                    </div>
                    <div class="mb-3">
                        <label for="contact_number" class="form-label">Contact Number</label>
                        <input type="text" class="form-control" id="contact_number" name="contact_number" required>
                    </div>
                    <button type="submit" name="order" class="btn btn-primary">Place Order</button>
                </form>
            </div>
        </div>
        <!-- Checkout Popup End -->

        <!-- Footer Start -->
        <!-- (Include your footer code here) -->
        <!-- Footer End -->

        <!-- Back to Top -->
        <a href="#" class="btn btn-lg btn-primary btn-lg-square back-to-top"><i class="bi bi-arrow-up"></i></a>
    </div>

    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="lib/wow/wow.min.js"></script>
    <script src="lib/easing/easing.min.js"></script>
    <script src="lib/waypoints/waypoints.min.js"></script>
    <script src="lib/owlcarousel/owl.carousel.min.js"></script>

    <!-- Template Javascript -->
    <script src="js/main.js"></script>

    <script>
        // JavaScript to handle popup display
        function openCheckoutPopup() {
            document.getElementById("checkoutPopup").style.display = "block";
        }

        // JavaScript to handle closing the popup
        document.querySelector(".close-popup").onclick = function() {
            document.getElementById("checkoutPopup").style.display = "none";
        }

        window.onclick = function(event) {
            if (event.target == document.getElementById("checkoutPopup")) {
                document.getElementById("checkoutPopup").style.display = "none";
            }
        }
    </script>
</body>
</html>

<?php
$conn->close();
?>
