<?php
session_start();
include('config.php');

// Cek login dan cek apakah user adalah admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: index.php");
    exit();
}

// Ambil jumlah pengguna dari database
$user_count_query = "SELECT COUNT(*) as user_count FROM users";
$result = $con->query($user_count_query);
$user_count = $result->fetch_assoc()['user_count'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Page</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            display: flex;
        }
        .navbar {
            background-color: #333;
            width: 200px;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding-top: 20px;
        }
        .navbar a {
            display: block;
            color: white;
            text-align: center;
            padding: 14px 20px;
            text-decoration: none;
            width: 100%;
        }
        .navbar a:hover {
            background-color: #ddd;
            color: black;
        }
        .container {
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            text-align: center;
            margin: 20px auto;
            margin-left: 220px; /* Adjust margin to account for the navbar width */
            width: 80%;
        }
        .logout-button {
            text-decoration: none;
            padding: 10px 20px;
            background-color: #ff4444;
            color: white;
            border-radius: 5px;
            margin-top: 20px;
            display: inline-block;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <a href="cek_post.php">Periksa Postingan</a>
        <a href="check_user.php">Check Users</a>
        <a href="logout.php">Logout</a>
    </div>
    <div class="container">
        <h1>Admin Page</h1>
        <p><strong>Total Users:</strong> <?php echo $user_count; ?></p>
    </div>
</body>
</html>