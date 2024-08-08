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
    echo '<p>Please <a href="login.php">log in</a> to edit your orders.</p>';
    exit();
}

$user_id = $_SESSION['user_id'];

// Get order ID from query string
$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch order details from database
$sql = "SELECT * FROM orders WHERE id = ? AND user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();
$stmt->close();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $status = htmlspecialchars($_POST['status']);

    // Update order in the database
    $update_sql = "UPDATE orders SET status = ? WHERE id = ? AND user_id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("sii", $status, $order_id, $user_id);

    if ($update_stmt->execute()) {
        echo '<p>Order updated successfully!</p>';
        header("Location: my_orders.php"); // Redirect to orders page
        exit();
    } else {
        echo '<p>Error updating order. Please try again.</p>';
    }
    $update_stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Edit Order | Comrade</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="" name="keywords">
    <meta content="" name="description">
    <!-- Add your CSS and other necessary includes here -->
</head>
<body>
    <div class="container">
        <h2>Edit Order</h2>
        <form action="" method="post">
            <div class="mb-3">
                <label for="status" class="form-label">Status</label>
                <input type="text" class="form-control" id="status" name="status" value="<?php echo htmlspecialchars($order['status']); ?>" required>
            </div>
            <button type="submit" class="btn btn-primary">Update Order</button>
        </form>
        <a href="my_orders.php" class="btn btn-secondary mt-3">Back to Orders</a>
    </div>
</body>
</html>

<?php
$conn->close();
?>
