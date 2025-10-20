<?php
// Database configuration
$servername = "localhost";
$username = "root";
$password = "0715"; // Replace with your secure password
$dbname = "MyDb";

// Create database connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check the connection
if (!$conn) {
    die("Connection failed: " . $conn->connect_error);
}


// Prepare an SQL statement (safe against SQL injection)
$sql="INSERT INTO student (username, address, telephone, gmail, id_number, fullname, password, exam_year, dob, gender, class)
                        VALUES('$_POST[username]','$_POST[address]','$_POST[telephone]','$_POST[gmail]','$_POST[id_number]','$_POST[fullname]','$_POST[password]','$_POST[exam_year]','$_POST[dob]','$_POST[gender]','$_POST[class]')";

// Execute the query
if (mysqli_query($conn,$sql)) {
    echo "<script>
        alert('Registration Successful!');
        window.location.href = 'login.html';
    </script>";
} else {
    echo "<h3 style='color:red;'>Error: " . $stmt->error . "</h3>";
}

// Close connections
mysqli_close($conn)
?>
