<?php
session_start();
include('config.php');

// Cek login dan cek apakah user adalah admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Proses untuk menghapus postingan
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_post'])) {
    $post_id = intval($_POST['post_id']);

    // Hapus foto jika ada
    $photo_query = "SELECT photo_path FROM photos WHERE post_id = ?";
    $stmt = $con->prepare($photo_query);
    $stmt->bind_param('i', $post_id);
    $stmt->execute();
    $photo_result = $stmt->get_result();
    while ($photo = $photo_result->fetch_assoc()) {
        if (!empty($photo['photo_path'])) {
            unlink($photo['photo_path']);
        }
    }

    // Hapus data postingan dan foto dari database
    $delete_post_query = "DELETE FROM posts WHERE id = ?";
    $stmt = $con->prepare($delete_post_query);
    $stmt->bind_param('i', $post_id);
    $stmt->execute();

    $delete_photo_query = "DELETE FROM photos WHERE post_id = ?";
    $stmt = $con->prepare($delete_photo_query);
    $stmt->bind_param('i', $post_id);
    $stmt->execute();

    header("Location: cek_post.php");
    exit();
}

// Ambil data postingan dari database
$post_query = "SELECT 
    p.*, 
    u.username,
    u.profile_photo,
    (SELECT photo_path FROM photos WHERE post_id = p.id LIMIT 1) as photo,
    (SELECT COUNT(*) FROM votes WHERE post_id = p.id AND vote_type = 'upvote') as upvote_count,
    (SELECT COUNT(*) FROM votes WHERE post_id = p.id AND vote_type = 'downvote') as downvote_count,
    (SELECT COUNT(*) FROM comments WHERE post_id = p.id) as comment_count
FROM posts p 
JOIN users u ON p.user_id = u.id 
ORDER BY p.created_at DESC";

$stmt = $con->prepare($post_query);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Check Posts</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
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
        .post-container {
            margin-bottom: 20px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .post-image {
            max-width: 200px;
            height: auto;
        }
        .button {
            padding: 5px 10px;
            margin: 5px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
        }
        .delete-button {
            background-color: #ff4444;
            color: white;
        }
        .stats-container {
            display: flex;
            gap: 15px;
            margin-top: 10px;
            padding: 5px;
            background-color: #f5f5f5;
            border-radius: 3px;
        }
        .stat-item {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .vote-count {
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <!-- <a href="home.php">Home</a> -->
        <a href="admin_page.php">Admin Page</a>
        <a href="logout.php">Logout</a>
    </div>
    <div class="container">
        <h1>Check Posts</h1>
        <div id="posts-container">
            <?php while ($post = $result->fetch_assoc()): ?>
                <div class="post-container">
                    <p>
                        <?php if (!empty($post['profile_photo'])): ?>
                            <img src="<?php echo htmlspecialchars($post['profile_photo']); ?>" 
                                 alt="Profile Photo" 
                                 class="profile-photo">
                        <?php endif; ?>
                        Oleh: <?php echo htmlspecialchars($post['username']); ?> | 
                        <?php echo date('d M Y H:i', strtotime($post['created_at'])); ?>
                    </p>
                    <h2><?php echo htmlspecialchars($post['title']); ?></h2>
                    <p><?php echo nl2br(htmlspecialchars($post['body'])); ?></p>
                    
                    <?php if (!empty($post['photo'])): ?>
                        <img src="<?php echo htmlspecialchars($post['photo']); ?>" 
                             alt="Post image" 
                             class="post-image">
                    <?php endif; ?>
                    
                    <div class="stats-container">
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
                    
                    <form method="POST" action="" onsubmit="return confirm('Are you sure you want to delete this post?');">
                        <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                        <button type="submit" name="delete_post" class="button delete-button">Delete</button>
                    </form>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</body>
</html>