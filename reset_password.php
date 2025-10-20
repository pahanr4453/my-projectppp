<?php
$servername = "localhost";
$username = "root";
$password = "0715";
$dbname = "MyDb";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $user = trim($_POST["username"]);
    $new_pass = $_POST["new_password"];

    $sql = "UPDATE student SET password = ? WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $new_pass, $user);
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo "<script>
              alert('Password successfully updated.');
              window.location.href = 'login.html';
            </script>";
        } else {
            echo "<script>
              alert('Username not found.');
              window.location.href = 'forgot_password.html';
            </script>";
        }
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}
$conn->close();
?>
