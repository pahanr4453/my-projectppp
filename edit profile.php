<?php
session_start();

// 1. User ලොග් වෙලාද කියලා පරීක්ෂා කරන්න
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.html");
    exit;
}

// 2. Database සම්බන්ධතාවය පිහිටුවා ගන්න
$servername = "localhost";
$username = "root";
$password = "0715";
$dbname = "MyDb";

$conn = new mysqli($servername, $username, $password, $dbname);

// සම්බන්ධතාවය පරීක්ෂා කරන්න
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");

$user_profile = null;
$message = "";

// 3. Session එකෙන් user ID එක ලබා ගන්න
$user_id = $_SESSION['user_id'];

// POST request එකක් නම් (form එක submit කරලා නම්) දත්ත Update කරන්න
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Profile Details Update Logic (Address පමණක් සංස්කරණය කිරීමට)
    if (isset($_POST['update_profile'])) {
        $input_address = trim($_POST['address']);
        
        $sql = "UPDATE student SET address=? WHERE id=?";
        $stmt = $conn->prepare($sql);

        if ($stmt === false) {
            $message .= "Error preparing profile statement: " . $conn->error;
        } else {
            $stmt->bind_param("si", $input_address, $user_id);

            if ($stmt->execute()) {
                $message .= "Address updated successfully!";
                header("Location: profile.php?message=" . urlencode($message));
                exit;
            } else {
                $message .= "Error updating address: " . $stmt->error;
            }
            $stmt->close();
        }
    }
    
    // Password Change Logic
    if (isset($_POST['change_password'])) {
        $current_pass = $_POST['current_password'];
        $new_pass = $_POST['new_password'];

        if (empty($current_pass) || empty($new_pass)) {
            $message = "Both password fields are required.";
        } else {
            // දැනට පවතින මුරපදය database එකෙන් ලබා ගන්න
            $sql_fetch_pass = "SELECT password FROM student WHERE id = ?";
            $stmt_fetch_pass = $conn->prepare($sql_fetch_pass);
            $stmt_fetch_pass->bind_param("i", $user_id);
            $stmt_fetch_pass->execute();
            $result_fetch_pass = $stmt_fetch_pass->get_result();
            $user_data = $result_fetch_pass->fetch_assoc();
            $stmt_fetch_pass->close(); // stmt එක close කරන්න

            if ($user_data) {
                $stored_password = $user_data['password'];

                // සාමාන්‍ය String එකක් ලෙස මුරපද දෙක සසඳා බලන්න
                if ($current_pass === $stored_password) {
                    // නව මුරපදය database එකේ update කරන්න
                    $sql_update_pass = "UPDATE student SET password = ? WHERE id = ?";
                    $stmt_update_pass = $conn->prepare($sql_update_pass);
                    $stmt_update_pass->bind_param("si", $new_pass, $user_id);

                    if ($stmt_update_pass->execute()) {
                        $message = "Password successfully updated!";
                        header("Location: profile.php?message=" . urlencode($message));
                        exit;
                    } else {
                        $message = "Error updating password: " . $stmt_update_pass->error;
                    }
                    $stmt_update_pass->close();
                } else {
                    $message = "Incorrect current password.";
                }
            } else {
                $message = "User not found.";
            }
        }
    }
}

// GET request එකක් නම් හෝ POST එකෙන් පසුව යාවත්කාලීන වූ දත්ත fetch කරන්න
// සියලුම ක්ෂේත්‍ර තෝරාගන්න
$sql_fetch = "SELECT username, fullname, address, telephone, gmail, id_number, exam_year, dob, gender, class FROM student WHERE id = ?";
$stmt_fetch = $conn->prepare($sql_fetch);

if ($stmt_fetch === false) {
    die("Error preparing statement: " . $conn->error);
}

$stmt_fetch->bind_param("i", $user_id);
$stmt_fetch->execute();
$result_fetch = $stmt_fetch->get_result();

if ($result_fetch->num_rows > 0) {
    $user_profile = $result_fetch->fetch_assoc();
} else {
    echo "<script>alert('User profile not found.'); window.location.href='dashboard.php';</script>";
    exit;
}

$stmt_fetch->close();

if (isset($_GET['message'])) {
    $message = urldecode($_GET['message']);
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit My Profile</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* CSS කේතය එලෙසම තිබේ */
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap');

        :root {
            --bg-color: #e3f2fd;
            --card-bg: #ffffff;
            --primary-color: #4a90e2;
            --text-color: #333;
            --input-bg: #f5f8fa;
            --border-color: #d1d9e6;
            --shadow: rgba(0, 0, 0, 0.1);
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--bg-color);
            margin: 0;
            padding: 40px 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            color: var(--text-color);
        }

        .container {
            width: 100%;
            max-width: 900px;
            display: grid;
            grid-template-columns: 1fr;
            gap: 30px;
            animation: fadeIn 0.8s ease-out forwards;
        }

        @media (min-width: 768px) {
            .container {
                grid-template-columns: 1fr 1fr;
            }
        }

        .card {
            background-color: var(--card-bg);
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 8px 25px var(--shadow);
            border: 1px solid var(--border-color);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 30px var(--shadow);
        }

        h2 {
            text-align: center;
            color: var(--primary-color);
            margin-bottom: 25px;
            font-size: 1.8em;
            font-weight: 700;
        }

        .message {
            text-align: center;
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 8px;
            font-weight: 500;
            color: #fff;
            background-color: #2ecc71;
            animation: fadeIn 0.5s ease-out;
        }
        .message.error {
            background-color: #e74c3c;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .form-group {
            position: relative;
        }

        .form-group label {
            position: absolute;
            top: 15px;
            left: 15px;
            color: #999;
            pointer-events: none;
            transition: all 0.3s ease;
            font-size: 1em;
            font-weight: 500;
        }

        input[type="text"],
        input[type="email"],
        input[type="date"],
        input[type="password"],
        select {
            width: 100%;
            padding: 15px;
            padding-top: 25px;
            border: 1px solid var(--border-color);
            background-color: var(--input-bg);
            border-radius: 8px;
            box-sizing: border-box;
            font-size: 1em;
            color: var(--text-color);
            transition: all 0.3s ease;
        }

        input[readonly] {
            background-color: #e9ecef;
        }

        input:focus, select:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(74, 144, 226, 0.2);
            background-color: var(--card-bg);
        }

        .form-group input:focus + label,
        .form-group input:not(:placeholder-shown) + label,
        .form-group select:focus + label,
        .form-group select:not([value=""]) + label {
            top: 5px;
            left: 12px;
            font-size: 0.75em;
            color: var(--primary-color);
        }
        
        select {
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
        }
        .form-group select {
            padding-top: 15px;
        }

        .button-group {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 20px;
        }

        button {
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1em;
            font-weight: 700;
            color: white;
            transition: all 0.3s ease;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .update-button {
            background-color: var(--primary-color);
        }
        .update-button:hover {
            background-color: #3f79c2;
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(74, 144, 226, 0.3);
        }
        
        .change-pass-button {
            background-color: #f39c12;
        }
        .change-pass-button:hover {
            background-color: #e08e0b;
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(243, 156, 18, 0.3);
        }

        .cancel-button {
            background-color: #95a5a6;
        }
        .cancel-button:hover {
            background-color: #7f8c8d;
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(149, 165, 166, 0.3);
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <div class="container">
        
        <div class="card">
            <h2>Edit My Profile</h2>
            <?php if ($message): ?>
                <p class="message <?php echo strpos($message, 'Error') !== false ? 'error' : ''; ?>"><?php echo $message; ?></p>
            <?php endif; ?>

            <?php if ($user_profile): ?>
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($user_id); ?>">
                    <input type="hidden" name="update_profile" value="1">
                    
                    <div class="form-group">
                        <input type="text" id="fullname" name="fullname" value="<?php echo htmlspecialchars($user_profile['fullname']); ?>" readonly placeholder=" ">
                        <label for="fullname">Full Name</label>
                    </div>
                    <div class="form-group">
                        <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user_profile['username']); ?>" readonly placeholder=" ">
                        <label for="username">Username</label>
                    </div>
                    <div class="form-group">
                        <input type="text" id="address" name="address" value="<?php echo htmlspecialchars($user_profile['address']); ?>" placeholder=" ">
                        <label for="address">Address</label>
                    </div>
                    <div class="form-group">
                        <input type="text" id="telephone" name="telephone" value="<?php echo htmlspecialchars($user_profile['telephone']); ?>" readonly placeholder=" ">
                        <label for="telephone">Telephone</label>
                    </div>
                    <div class="form-group">
                        <input type="email" id="gmail" name="gmail" value="<?php echo htmlspecialchars($user_profile['gmail']); ?>" readonly placeholder=" ">
                        <label for="gmail">Gmail</label>
                    </div>
                    <div class="form-group">
                        <input type="text" id="id_number" name="id_number" value="<?php echo htmlspecialchars($user_profile['id_number']); ?>" readonly placeholder=" ">
                        <label for="id_number">ID Number</label>
                    </div>
                    <div class="form-group">
                        <input type="text" id="exam_year" name="exam_year" value="<?php echo htmlspecialchars($user_profile['exam_year']); ?>" readonly placeholder=" ">
                        <label for="exam_year">Exam Year</label>
                    </div>
                    <div class="form-group">
                        <input type="date" id="dob" name="dob" value="<?php echo htmlspecialchars($user_profile['dob']); ?>" readonly placeholder=" ">
                        <label for="dob">Date of Birth</label>
                    </div>
                    <div class="form-group">
                        <input type="text" id="gender" name="gender" value="<?php echo htmlspecialchars($user_profile['gender']); ?>" readonly placeholder=" ">
                        <label for="gender">Gender</label>
                    </div>
                    <div class="form-group">
                        <input type="text" id="class" name="class" value="<?php echo htmlspecialchars($user_profile['class']); ?>" readonly placeholder=" ">
                        <label for="class">Class</label>
                    </div>
                    
                    <div class="button-group">
                        <button type="submit" name="update_profile" class="update-button"><i class="fas fa-save"></i> Update Address</button>
                    </div>
                </form>
            <?php else: ?>
                <p>Could not load profile information for editing.</p>
            <?php endif; ?>
        </div>
        
        <div class="card">
            <h2>Change Password</h2>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($user_id); ?>">
                <input type="hidden" name="change_password" value="1">
                
                <div class="form-group">
                    <input type="password" id="current_password" name="current_password" required placeholder=" ">
                    <label for="current_password">Current Password</label>
                </div>
                
                <div class="form-group">
                    <input type="password" id="new_password" name="new_password" required placeholder=" ">
                    <label for="new_password">New Password</label>
                </div>
                
                <div class="button-group">
                    <button type="submit" name="change_password" class="change-pass-button"><i class="fas fa-key"></i> Change Password</button>
                </div>
            </form>
        </div>
        
    </div>
</body>
</html>