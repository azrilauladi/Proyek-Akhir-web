<?php
session_start();
include('config.php');

if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($con, $_POST['username']);
    $password = $_POST['password'];

    // Cek username di database
    $query = "SELECT * FROM users WHERE username = '$username'";
    $result = mysqli_query($con, $query);

    if (mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);

        // Verifikasi password
        if (password_verify($password, $user['password'])) {
            // Set session untuk user yang berhasil login
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];

            // Arahkan berdasarkan role
            if ($user['role'] == 'admin') {
                header("Location: admin_page.php");
            } else {
                header("Location: home.php");  // Diarahkan ke home.php untuk user biasa
            }
            exit();
        } else {
            $_SESSION['error'] = "Username atau password salah.";
        }
    } else {
        $_SESSION['error'] = "Username atau password salah.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>InfoCation Login</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet"/>
    <link rel="stylesheet" href="css/login.css">
    <!-- <style>
        body {
            margin: 0;
            font-family: 'Roboto', sans-serif;
            background-color: #004d4d;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            display: flex;
            width: 80%;
            max-width: 900px;
            background-color: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .left {
            background-color: #d9e6e6;
            padding: 40px;
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            border-top-left-radius: 15px;
            border-bottom-left-radius: 15px;
        }
        .left p {
            font-size: 24px;
            font-weight: 700;
            color: #000;
            margin: 0;
        }
        .left p span {
            color: #b3b3b3;
        }
        .right {
            padding: 40px;
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            border-top-right-radius: 15px;
            border-bottom-right-radius: 15px;
        }
        .logo {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }
        .logo svg {
            width: 50px;
            height: 50px;
            margin-right: 10px;
        }
        .logo h1 {
            font-size: 32px;
            font-weight: 700;
            margin: 0;
        }
        .logo p {
            font-size: 14px;
            color: #666;
            margin: 0;
        }
        .form-group {
            width: 100%;
            margin-bottom: 20px;
        }
        .form-group input {
            width: 100%;
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }
        .form-group a {
            font-size: 14px;
            color: #004d4d;
            text-decoration: none;
            display: block;
            text-align: right;
            margin-top: 5px;
        }
        .form-group a:hover {
            text-decoration: underline;
        }
        .btn {
            width: 100%;
            padding: 10px;
            font-size: 16px;
            color: white;
            background-color: #004d4d;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .btn:hover {
            background-color: #003333;
        }
        .signup {
            margin-top: 20px;
            font-size: 14px;
        }
        .signup a {
            color: #004d4d;
            text-decoration: none;
        }
        .signup a:hover {
            text-decoration: underline;
        }
        .error-message {
            color: #ff0000;
            text-align: center;
            margin-bottom: 15px;
            font-size: 14px;
        }
    </style> -->
</head>
<body>
    <div class="container">
        <div class="left">
            <p>
                InfoCation helps you find the best travel spots and hidden gems effortlessly,
                <span>making every trip unforgettable.</span>
            </p>
        </div>
        <div class="right">
            <div class="logo">
                <svg width="50" height="50" viewBox="0 0 132 135" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <ellipse cx="62.92" cy="66.339" rx="52.0651" ry="50.0743" fill="black"/>
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M66 132C102.451 132 132 102.451 132 66C132 29.5492 102.451 0 66 0C29.5491 0 0 29.5492 0 66C0 102.451 29.5491 132 66 132ZM86.5897 30.7456C87.5919 27.1856 84.1372 25.0804 80.982 27.3283L36.9373 58.7056C33.5155 61.1433 34.0537 66 37.7458 66H49.3439V65.9102H71.9483L53.53 72.4089L45.4104 101.255C44.4081 104.815 47.8626 106.92 51.018 104.672L95.0628 73.2946C98.4846 70.8569 97.946 66 94.2543 66H76.666L86.5897 30.7456Z" fill="#08545C"/>
                </svg>
                <div>
                    <h1>InfoCation</h1>
                    <p>SHARE. EXPLORE. ENJOY</p>
                </div>
            </div>
            <?php
            if (isset($_SESSION['error'])) {
                echo '<div class="error-message">' . $_SESSION['error'] . '</div>';
                unset($_SESSION['error']);
            }
            ?>
            <form method="post">
                <div class="form-group">
                    <input type="text" name="username" placeholder="Username" required>
                </div>
                <div class="form-group">
                    <input type="password" name="password" placeholder="Password" required>
                    <a href="forgot_password.php">Forgot password?</a>
                </div>
                <button type="submit" name="login" class="btn">Login</button>
            </form>
            <div class="signup">
                Don't have an account? <a href="register.php">Sign Up</a>
            </div>
        </div>
    </div>
</body>
</html>