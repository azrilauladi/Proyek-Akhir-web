<?php
session_start();
include('config.php');

// Cek login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Ambil data pengguna dari database
$user_id = $_SESSION['user_id'];
$user_query = "SELECT username, email, profile_photo FROM users WHERE id = ?";
$stmt = $con->prepare($user_query);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$user_result = $stmt->get_result();
$user = $user_result->fetch_assoc();

// Ambil postingan pengguna dari database
$post_query = "SELECT p.id, p.title, p.body, p.created_at, 
               (SELECT photo_path FROM photos WHERE post_id = p.id LIMIT 1) as photo,
               (SELECT COUNT(*) FROM votes WHERE post_id = p.id AND vote_type = 'upvote') as upvote_count,
               (SELECT COUNT(*) FROM votes WHERE post_id = p.id AND vote_type = 'downvote') as downvote_count,
               (SELECT COUNT(*) FROM comments WHERE post_id = p.id) as comment_count,
               u.username
               FROM posts p 
               JOIN users u ON p.user_id = u.id 
               WHERE p.user_id = ? 
               ORDER BY p.created_at DESC";
$stmt = $con->prepare($post_query);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$post_result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <link rel="stylesheet" href="css/userprofile.css">
</head>
<body>
    <!-- Top Header -->
    <div class="Top">
        <div class="Logo">
            <img src="Asset/Logo kecil.png" alt="InfoCation">
        </div>
        <a href="logout.php">
            <button type="button" class="btn">Logout</button>
        </a>
    </div>
    <div class="Line"></div>

    <!-- Navbar -->
    <nav class="navbar">
        <ul>
            <li><a href="home.php"><img src="Asset/logo home.png" alt=""></a></li>
            <li><a href="search.php"><img src="Asset/logo search.png" alt=""></a></li>
            <li><a href="create_post.php"><img src="Asset/logo tambah.png" alt=""></a></li>
            <li><a href="editprofile.php"><img src="Asset/mage-edit.png" alt=""></a></li>
        </ul>
    </nav>

    <!-- Content -->
    <div class="content-wrapper">
        <main class="Content">
            <h1>Welcome, <?php echo htmlspecialchars($user['username']); ?>!</h1>
            <?php if (!empty($user['profile_photo'])): ?>
                <img src="<?php echo htmlspecialchars($user['profile_photo']); ?>" 
                     alt="Profile Photo" 
                     class="profile-photo">
            <?php endif; ?>
            <p><strong>Username:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
            <!-- <a href="logout.php" class="btn">Logout</a>
            <a href="home.php" class="btn">Home</a>
            <a href="editprofile.php" class="btn">Edit</a> -->

            <h2>Your Posts</h2>
            <?php while ($post = $post_result->fetch_assoc()): ?>
                <div class="post-card">
                    <div class="post-header">
                        <span>Oleh: <?php echo htmlspecialchars($post['username'] ?? ''); ?></span>
                        <span>|</span>
                        <span><?php echo date('d M Y H:i', strtotime($post['created_at'])); ?></span>
                    </div>
                    
                    <div class="post-content">
                        <?php echo nl2br(htmlspecialchars($post['body'])); ?>
                        <?php if (!empty($post['photo'])): ?>
                            <img src="<?php echo htmlspecialchars($post['photo']); ?>" 
                                 alt="Post image" 
                                 class="post-image">
                        <?php endif; ?>
                    </div>

                    <div class="post-stats">
                        <div class="stat-item">
                            👍 <span class="vote-count"><?php echo $post['upvote_count']; ?></span>
                        </div>
                        <div class="stat-item">
                            👎 <span class="vote-count"><?php echo $post['downvote_count']; ?></span>
                        </div>
                        <div class="stat-item">
                            💬 <span class="vote-count"><?php echo $post['comment_count']; ?></span>
                        </div>
                    </div>
                    
                    <a href="edit_post.php?post_id=<?php echo $post['id']; ?>" class="btn" style="background-color: #4CAF50; color: white; text-decoration: none;">Edit</a>
                </div>
            <?php endwhile; ?>
        </main>
    </div>
</body>
</html>
