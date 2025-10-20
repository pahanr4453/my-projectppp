<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.html");
    exit;
}

$servername = "localhost";
$username = "root";
$password = "0715";
$dbname = "MyDb";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");

$user_profile = null;
$user_id = $_SESSION['user_id'];

$sql = "SELECT username, fullname, address, telephone, gmail, id_number, exam_year, dob, gender, class FROM student WHERE id = ?";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    die("Error preparing statement: " . $conn->error);
}

$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user_profile = $result->fetch_assoc();
} else {
    echo "<script>alert('User profile not found.'); window.location.href='First.html';</script>";
    exit;
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile | ET Class</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #1e40af;
            --primary-light: #3b82f6;
            --secondary-color: #0891b2;
            --accent-color: #ea580c;
            --success-color: #059669;
            --neutral-50: #f8fafc;
            --neutral-100: #f1f5f9;
            --neutral-200: #e2e8f0;
            --neutral-300: #cbd5e1;
            --neutral-600: #475569;
            --neutral-700: #334155;
            --neutral-800: #1e293b;
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
            --shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
            --shadow-2xl: 0 25px 50px -12px rgb(0 0 0 / 0.25);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0;
            padding: 2rem;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            position: relative;
            overflow-x: hidden;
        }

        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at 30% 70%, rgba(255, 255, 255, 0.1) 0%, transparent 50%),
                        radial-gradient(circle at 70% 30%, rgba(30, 64, 175, 0.1) 0%, transparent 50%);
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(1deg); }
        }

        .profile-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-radius: 24px;
            box-shadow: var(--shadow-2xl);
            border: 1px solid rgba(255, 255, 255, 0.2);
            max-width: 900px;
            width: 100%;
            padding: 0;
            text-align: center;
            animation: fadeInScale 0.8s ease-out forwards;
            position: relative;
            z-index: 1;
            overflow: hidden;
        }

        .profile-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        }

        .profile-header {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
            color: white;
            padding: 3rem 2rem 2rem;
            position: relative;
            overflow: hidden;
        }

        .profile-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at 30% 70%, rgba(255, 255, 255, 0.1) 0%, transparent 50%);
        }

        .profile-avatar {
            width: 120px;
            height: 120px;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            color: white;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 3rem;
            font-weight: 700;
            margin: 0 auto 1.5rem;
            border: 4px solid rgba(255, 255, 255, 0.3);
            box-shadow: var(--shadow-lg);
            animation: pulse 2s infinite;
            position: relative;
            z-index: 1;
        }

        .profile-name {
            font-size: 2.5rem;
            font-weight: 700;
            margin: 0 0 0.5rem 0;
            animation: fadeInDown 0.8s ease-out 0.2s forwards;
            opacity: 0;
            position: relative;
            z-index: 1;
        }

        .profile-role {
            font-size: 1.2rem;
            opacity: 0.9;
            font-weight: 500;
            animation: fadeInDown 0.8s ease-out 0.4s forwards;
            opacity: 0;
            position: relative;
            z-index: 1;
        }

        .profile-content {
            padding: 2.5rem;
        }

        .profile-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-bottom: 2.5rem;
        }

        .info-item {
            background: linear-gradient(135deg, var(--neutral-50), var(--neutral-100));
            border-radius: 16px;
            padding: 1.5rem;
            text-align: left;
            border-left: 4px solid var(--primary-color);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            animation: fadeInUp 0.6s ease-out forwards;
            opacity: 0;
            position: relative;
            overflow: hidden;
        }

        .info-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, transparent, rgba(30, 64, 175, 0.05));
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .info-item:hover::before {
            opacity: 1;
        }

        .info-item:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-lg);
            border-left-color: var(--primary-light);
        }

        .info-item:nth-child(1) { animation-delay: 0.1s; }
        .info-item:nth-child(2) { animation-delay: 0.2s; }
        .info-item:nth-child(3) { animation-delay: 0.3s; }
        .info-item:nth-child(4) { animation-delay: 0.4s; }
        .info-item:nth-child(5) { animation-delay: 0.5s; }
        .info-item:nth-child(6) { animation-delay: 0.6s; }
        .info-item:nth-child(7) { animation-delay: 0.7s; }
        .info-item:nth-child(8) { animation-delay: 0.8s; }
        .info-item:nth-child(9) { animation-delay: 0.9s; }
        .info-item:nth-child(10) { animation-delay: 1.0s; }

        .info-label {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--neutral-600);
            margin-bottom: 0.5rem;
            text-transform: uppercase;
            letter-spacing: 0.025em;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .info-value {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--neutral-800);
            word-break: break-word;
        }

        .button-container {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
            animation: fadeInUp 0.8s ease-out 1.2s forwards;
            opacity: 0;
        }

        .edit-button {
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            padding: 1rem 2rem;
            background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
            color: white;
            text-decoration: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            letter-spacing: 0.025em;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: var(--shadow-lg);
            position: relative;
            overflow: hidden;
        }

        .edit-button::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            transition: left 0.6s ease;
        }

        .edit-button:hover::before {
            left: 100%;
        }

        .edit-button:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-xl);
            background: linear-gradient(135deg, var(--primary-light), var(--secondary-color));
        }

        .back-button {
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            padding: 1rem 2rem;
            background: linear-gradient(135deg, var(--neutral-600), var(--neutral-700));
            color: white;
            text-decoration: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: var(--shadow-lg);
        }

        .back-button:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-xl);
            background: linear-gradient(135deg, var(--neutral-700), var(--neutral-800));
        }

        /* Icons for different info types */
        .info-label::before {
            font-size: 1rem;
        }

        .info-item:nth-child(1) .info-label::before { content: '👤'; }
        .info-item:nth-child(2) .info-label::before { content: '🏷️'; }
        .info-item:nth-child(3) .info-label::before { content: '🏠'; }
        .info-item:nth-child(4) .info-label::before { content: '📞'; }
        .info-item:nth-child(5) .info-label::before { content: '📧'; }
        .info-item:nth-child(6) .info-label::before { content: '🆔'; }
        .info-item:nth-child(7) .info-label::before { content: '📅'; }
        .info-item:nth-child(8) .info-label::before { content: '🎂'; }
        .info-item:nth-child(9) .info-label::before { content: '⚧️'; }
        .info-item:nth-child(10) .info-label::before { content: '🏫'; }

        /* Keyframe Animations */
        @keyframes fadeInScale {
            from { opacity: 0; transform: scale(0.95) translateY(20px); }
            to { opacity: 1; transform: scale(1) translateY(0); }
        }

        @keyframes fadeInDown {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes pulse {
            0%, 100% { box-shadow: 0 0 0 0 rgba(255, 255, 255, 0.7); }
            70% { box-shadow: 0 0 0 10px rgba(255, 255, 255, 0); }
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            body {
                padding: 1rem;
            }

            .profile-card {
                margin: 0;
            }

            .profile-header {
                padding: 2rem 1.5rem 1.5rem;
            }

            .profile-name {
                font-size: 2rem;
            }

            .profile-role {
                font-size: 1rem;
            }

            .profile-avatar {
                width: 100px;
                height: 100px;
                font-size: 2.5rem;
            }

            .profile-content {
                padding: 2rem 1.5rem;
            }

            .profile-info {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }

            .info-item {
                padding: 1.25rem;
            }

            .button-container {
                flex-direction: column;
                align-items: center;
            }

            .edit-button,
            .back-button {
                width: 100%;
                max-width: 300px;
                justify-content: center;
            }
        }

        @media (max-width: 480px) {
            .profile-header {
                padding: 1.5rem 1rem 1rem;
            }

            .profile-name {
                font-size: 1.75rem;
            }

            .profile-content {
                padding: 1.5rem 1rem;
            }

            .info-item {
                padding: 1rem;
            }

            .info-label {
                font-size: 0.8rem;
            }

            .info-value {
                font-size: 1rem;
            }
        }

        /* Accessibility improvements */
        @media (prefers-reduced-motion: reduce) {
            * {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
        }

        /* Focus styles for better accessibility */
        .edit-button:focus,
        .back-button:focus {
            outline: 2px solid var(--primary-color);
            outline-offset: 2px;
        }

        /* Loading state */
        .loading {
            opacity: 0.7;
            pointer-events: none;
        }
    </style>
</head>
<body>
    <div class="profile-card">
        <?php if ($user_profile): ?>
            <div class="profile-header">
                <div class="profile-avatar">
                    <span><?php echo strtoupper(substr($user_profile['fullname'], 0, 1)); ?></span>
                </div>
                <h1 class="profile-name"><?php echo htmlspecialchars($user_profile['fullname']); ?></h1>
                <p class="profile-role">Engineering Technology Student</p>
            </div>
            
            <div class="profile-content">
                <div class="profile-info">
                    <div class="info-item">
                        <div class="info-label">Full Name</div>
                        <div class="info-value"><?php echo htmlspecialchars($user_profile['fullname']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Username</div>
                        <div class="info-value"><?php echo htmlspecialchars($user_profile['username']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Address</div>
                        <div class="info-value"><?php echo htmlspecialchars($user_profile['address'] ?: 'Not provided'); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Phone Number</div>
                        <div class="info-value"><?php echo htmlspecialchars($user_profile['telephone']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Email Address</div>
                        <div class="info-value"><?php echo htmlspecialchars($user_profile['gmail']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">ID Number</div>
                        <div class="info-value"><?php echo htmlspecialchars($user_profile['id_number']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Exam Year</div>
                        <div class="info-value"><?php echo htmlspecialchars($user_profile['exam_year']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Date of Birth</div>
                        <div class="info-value"><?php echo htmlspecialchars($user_profile['dob'] !== '0000-00-00' ? date('F j, Y', strtotime($user_profile['dob'])) : 'Not provided'); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Gender</div>
                        <div class="info-value"><?php echo htmlspecialchars($user_profile['gender'] ?: 'Not specified'); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Class Location</div>
                        <div class="info-value"><?php echo htmlspecialchars($user_profile['class']); ?></div>
                    </div>
                </div>
                
                <div class="button-container">
                    <a href="edit profile.php" class="edit-button">
                        <span>✏️</span>
                        Edit Profile
                    </a>
                    <a href="First.html" class="back-button">
                        <span>←</span>
                        Back to Dashboard
                    </a>
                </div>
            </div>
        <?php else: ?>
            <div class="profile-content">
                <p style="text-align: center; color: #dc2626; font-size: 1.1rem; padding: 2rem;">
                    Could not load profile information. Please try again later.
                </p>
                <div class="button-container">
                    <a href="First.html" class="back-button">
                        <span>←</span>
                        Back to Dashboard
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Add loading animation on page load
        window.addEventListener('load', function() {
            document.body.style.opacity = '0';
            setTimeout(() => {
                document.body.style.transition = 'opacity 0.6s ease';
                document.body.style.opacity = '1';
            }, 100);
        });

        // Add click animations to buttons
        document.querySelectorAll('.edit-button, .back-button').forEach(button => {
            button.addEventListener('click', function() {
                this.classList.add('loading');
            });
        });

        // Add intersection observer for scroll animations
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, { threshold: 0.1 });

        // Observe all info items
        document.querySelectorAll('.info-item').forEach(item => {
            observer.observe(item);
        });

        // Add smooth scrolling for any anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Add keyboard navigation support
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                window.location.href = 'First.html';
            }
        });
    </script>
</body>
</html>