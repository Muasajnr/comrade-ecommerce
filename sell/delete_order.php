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
    echo '<p>Please <a href="login.php">log in</a> to delete your orders.</p>';
    exit();
}

$user_id = $_SESSION['user_id'];

// Get order ID from query string
$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Delete order from database
$sql = "DELETE FROM orders WHERE id = ? AND user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $order_id, $user_id);

if ($stmt->execute()) {
    echo '<p>Order deleted successfully!</p>';
} else {
    echo '<p>Error deleting order. Please try again.</p>';
}
$stmt->close();

header("Location: my_orders.php"); // Redirect to orders page
exit();

$conn->close();
?>
