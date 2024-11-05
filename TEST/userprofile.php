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
               (SELECT COUNT(*) FROM comments WHERE post_id = p.id) as comment_count
               FROM posts p WHERE p.user_id = ? ORDER BY p.created_at DESC";
$stmt = $con->prepare($post_query);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$post_result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>User Profile</title>
    <style>
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
        .logout-button {
            text-decoration: none;
            padding: 5px 10px;
            background-color: #ff4444;
            color: white;
            border-radius: 3px;
            float: right;
        }
        .home-button {
            text-decoration: none;
            text-emphasis-color: black;
            padding: 5px 10px;
            background-color: #3fc9aa;
            color: white;
            border-radius: 3px;
            float: right;
        }
        .profile-photo {
            max-width: 150px;
            height: auto;
            border-radius: 50%;
            margin-bottom: 20px;
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
    <h1>Welcome, <?php echo htmlspecialchars($user['username']); ?>!</h1>
    <?php if (!empty($user['profile_photo'])): ?>
        <img src="<?php echo htmlspecialchars($user['profile_photo']); ?>" 
             alt="Profile Photo" 
             class="profile-photo">
    <?php endif; ?>
    <p><strong>Username:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
    <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
    <a href="logout.php" class="logout-button">Logout</a>
    <a href="home.php" class="home-button">Home</a>
    <a href="editprofile.php" class="home-button">edit</a>

    <h2>Your Posts</h2>
    <div id="posts-container">
        <?php while ($post = $post_result->fetch_assoc()): ?>
            <div class="post-container">
                <h3><?php echo htmlspecialchars($post['title']); ?></h3>
                <p><?php echo nl2br(htmlspecialchars($post['body'])); ?></p>
                <p><small>Posted on: <?php echo date('d M Y H:i', strtotime($post['created_at'])); ?></small></p>
                
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
                
                <a href="edit_post.php?post_id=<?php echo $post['id']; ?>" class="button" style="background-color: #4CAF50; color: white; text-decoration: none;">Edit</a>
            </div>
        <?php endwhile; ?>
    </div>
</body>
</html>