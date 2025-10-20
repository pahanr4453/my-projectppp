<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role'] !== 'admin') {
    header("Location: login.html");
    exit;
}

$servername = "localhost";
$username = "root";
$password = "0715";
$dbname = "MyDb";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }
$conn->set_charset("utf8mb4");

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    $sql = "DELETE FROM student WHERE id = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        echo "<script>alert('Error preparing statement: " . $conn->error . "'); window.location.href='admin_dashboard.php';</script>";
    } else {
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            echo "<script>alert('Student deleted successfully!'); window.location.href='admin_dashboard.php';</script>";
        } else {
            echo "<script>alert('Error deleting student: " . $stmt->error . "'); window.location.href='admin_dashboard.php';</script>";
        }
        $stmt->close();
    }
} else {
    echo "<script>alert('No student ID provided for deletion.'); window.location.href='admin_dashboard.php';</script>";
}

$conn->close();
?>