<?php
session_start();
$conn = new mysqli("localhost", "root", "0715", "MyDb");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $input_username = trim($_POST['username']);
    $input_password = $_POST['password'];
    $sql = "SELECT id, username, password FROM student WHERE username = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die("Error preparing statement: " . $conn->error);
    }
    $stmt->bind_param("s", $input_username);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $db_username, $db_password);
        $stmt->fetch();
        if ($input_password === $db_password) {
            $_SESSION['user_id'] = $id;
            $_SESSION['username'] = $db_username;
            $_SESSION['loggedin'] = true;
            echo "<script>
                alert('Login Successful! Welcome, " . htmlspecialchars($db_username) . "');
                window.location.href = 'first.html';
            </script>";
        } else {
            echo "<script>
                alert('Incorrect username or password.');
                window.location.href = 'login.html';
            </script>";
        }
    } else {
        echo "<script>
            alert('Incorrect username or password.');
            window.location.href = 'login.html';
        </script>";
    }
    $stmt->close();
}
$conn->close();
?>
