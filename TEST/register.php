<?php
session_start();
include('config.php');

if (isset($_POST['register'])) {
    $username = mysqli_real_escape_string($con, $_POST['username']);
    $email = mysqli_real_escape_string($con, $_POST['email']);
    $password = $_POST['password'];
    
    // Validasi panjang password
    if (strlen($password) < 6) {
        $_SESSION['error'] = "Password harus minimal 6 karakter.";
        header("Location: register.php");
        exit();
    }

    // Hash password menggunakan password_hash yang lebih aman
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Prepared statement untuk mencegah SQL injection
    $check_query = $con->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
    $check_query->bind_param("ss", $username, $email);
    $check_query->execute();
    $result = $check_query->get_result();

    if ($result->num_rows > 0) {
        $_SESSION['error'] = "Username atau email sudah terpakai, silakan gunakan yang lain.";
        header("Location: register.php");
        exit();
    } else {
        // Prepared statement untuk insert
        $insert_query = $con->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'user')");
        $insert_query->bind_param("sss", $username, $email, $hashed_password);
        
        if ($insert_query->execute()) {
            $_SESSION['success'] = "Registrasi berhasil. Silakan login.";
            header("Location: index.php");
            exit();
        } else {
            $_SESSION['error'] = "Terjadi kesalahan saat registrasi.";
            header("Location: register.php");
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-color: #0f5c5c;
            font-family: Arial, sans-serif;
        }
        .container {
            background-color: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            width: 400px;
            text-align: center;
        }
        h1 {
            color: #0f5c5c;
            margin-bottom: 30px;
        }
        input[type="text"], input[type="password"], input[type="email"] {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }
        button {
            background-color: #0f5c5c;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 5px;
            width: 100%;
            cursor: pointer;
            font-size: 16px;
            margin-top: 10px;
        }
        button:hover {
            background-color: #0d4a4a;
        }
        .error-message {
            color: #ff0000;
            margin-bottom: 10px;
        }
        .success-message {
            color: #008000;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Register</h1>
        <?php
        if (isset($_SESSION['error'])) {
            echo '<div class="error-message">' . $_SESSION['error'] . '</div>';
            unset($_SESSION['error']);
        }
        if (isset($_SESSION['success'])) {
            echo '<div class="success-message">' . $_SESSION['success'] . '</div>';
            unset($_SESSION['success']);
        }
        ?>
        <form method="post" action="">
            <input type="email" name="email" placeholder="Email" required>
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" name="register">Register</button>
        </form>
    </div>
</body>
</html>