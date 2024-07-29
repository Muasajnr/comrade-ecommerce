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

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo '<p>Please <a href="login.php">log in</a> to view your orders.</p>';
    exit();
}

// Fetch user information from session
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Fetch orders where the product name matches and the user name in the products table matches the current user
$sql = "SELECT o.id, o.product_name, p.price, o.quantity, o.order_date, p.user_name, o.status
        FROM orders o 
        INNER JOIN products p ON o.product_name = p.product_name 
        WHERE p.user_name = ?
        ORDER BY o.order_date DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$orders = $result->fetch_all(MYSQLI_ASSOC);

// Calculate total income where status is 'delivered'
$income_sql = "SELECT SUM(p.price * o.quantity) AS total_sales 
               FROM orders o 
               INNER JOIN products p ON o.product_name = p.product_name 
               WHERE p.user_name = ? AND o.status = 'delivered'";
$income_stmt = $conn->prepare($income_sql);
$income_stmt->bind_param("s", $username);
$income_stmt->execute();
$income_result = $income_stmt->get_result();
$total_sales_row = $income_result->fetch_assoc();
$total_sales = $total_sales_row['total_sales'] ?? 0; // Set to 0 if null

// Handle delete request
if (isset($_GET['delete'])) {
    $delete_id = intval($_GET['delete']);
    $delete_sql = "DELETE FROM orders WHERE id = ? AND user_id = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("ii", $delete_id, $user_id);
    if ($delete_stmt->execute()) {
        echo "<p>Order deleted successfully.</p>";
    } else {
        echo "<p>Failed to delete order.</p>";
    }
    header("Location: total-income.php"); // Redirect to avoid form resubmission
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>My Income | Comrade</title>
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
                        <a href="index.php" class="nav-item nav-link">Home</a>
                        <a href="dashboard.php" class="nav-item nav-link">Dashboard</a>
                    </div>
                    <a href="logout.php" class="btn btn-primary px-3 d-none d-lg-flex">Log Out</a>
                </div>
            </nav>
        </div>
        <!-- Navbar End -->
        <!-- My Orders Start -->
        <div class="container py-5">
            <h2 class="mb-4">My Income</h2>

            <?php if (!empty($orders)): ?>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Product Name</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Order Date</th>
                            <th>Ordered By</th>
                            <th>Total Income</th>
                            <th>Username</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($order['id']); ?></td>
                                <td><?php echo htmlspecialchars($order['product_name']); ?></td>
                                <td>$<?php echo htmlspecialchars($order['price']); ?></td>
                                <td><?php echo htmlspecialchars($order['quantity']); ?></td>
                                <td><?php echo htmlspecialchars($order['order_date']); ?></td>
                                <td><?php echo htmlspecialchars($order['user_name']); ?></td>
                                <td>Ksh <?php echo htmlspecialchars($order['price'] * $order['quantity']); ?></td>
                                <td><?php echo htmlspecialchars($order['user_name']); ?></td>
                                <td><?php echo htmlspecialchars($order['status']); ?></td>
                                <td>
                                    <a href="?delete=<?php echo htmlspecialchars($order['id']); ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this order?')">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <p><strong>Total Sales (Delivered Orders): Ksh <?php echo number_format($total_sales, 2); ?></strong></p>
            <?php else: ?>
                <p>You have no orders yet.</p>
            <?php endif; ?>
        </div>
        <!-- My Orders End -->

        <!-- Footer Start -->
        <!-- Your footer content -->
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
</body>
</html>

<?php
$conn->close();
?>
