<?php
session_start();
include('config.php');

// Cek login
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Ambil kata kunci pencarian dari parameter URL atau form
$search_query = isset($_GET['q']) ? mysqli_real_escape_string($con, $_GET['q']) : '';

// Query untuk mencari postingan berdasarkan judul dan konten
$post_query = "SELECT p.*, u.username, u.profile_photo, 
               (SELECT photo_path FROM photos WHERE post_id = p.id LIMIT 1) as photo,
               (SELECT COUNT(*) FROM votes WHERE post_id = p.id AND vote_type = 'upvote') as upvote_count,
               (SELECT COUNT(*) FROM votes WHERE post_id = p.id AND vote_type = 'downvote') as downvote_count,
               (SELECT COUNT(*) FROM comments WHERE post_id = p.id) as comment_count
               FROM posts p 
               JOIN users u ON p.user_id = u.id 
               WHERE p.title LIKE ? OR p.body LIKE ?
               ORDER BY p.created_at DESC";
$stmt = $con->prepare($post_query);
$search_term = '%' . $search_query . '%';
$stmt->bind_param('ss', $search_term, $search_term);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Search Results</title>
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
        .logout-button {
            text-decoration: none;
            padding: 5px 10px;
            background-color: #ff4444;
            color: white;
            border-radius: 3px;
            float: right;
        }
        .profile-button {
            text-decoration: none;
            padding: 5px 10px;
            background-color: #4CAF50;
            color: white;
            border-radius: 3px;
            float: right;
            margin-right: 10px;
        }
        .profile-photo {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            margin-right: 10px;
            vertical-align: middle;
        }
        .comment-form {
            margin-top: 20px;
        }
        .comment-form textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-sizing: border-box;
        }
        .comment-form button {
            margin-top: 5px;
            padding: 10px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <h1>Search Results</h1>
    
    <form method="GET" action="search.php">
        <input type="text" name="q" placeholder="Search..." value="<?php echo htmlspecialchars($search_query); ?>" required>
        <button type="submit" class="button" style="background-color: #4CAF50; color: white;">Search</button>
    </form>
    <a href="home.php" class="profile-button">Home</a>
    
    <div id="posts-container">
        <?php if ($result->num_rows > 0): ?>
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
                            <button class="button" onclick="vote(<?php echo $post['id']; ?>, 'upvote')">
                                👍
                            </button>
                            <span class="vote-count"><?php echo $post['upvote_count']; ?></span>
                        </div>
                        <div class="stat-item">
                            <button class="button" onclick="vote(<?php echo $post['id']; ?>, 'downvote')">
                                👎
                            </button>
                            <span class="vote-count"><?php echo $post['downvote_count']; ?></span>
                        </div>
                        <div class="stat-item">
                            <a href="comments.php?post_id=<?php echo $post['id']; ?>" style="text-decoration: none; color: inherit;">
                                💬 <span class="vote-count"><?php echo $post['comment_count']; ?></span>
                            </a>
                        </div>
                    </div>

                    <!-- Comment Form -->
                    <form method="POST" action="comments.php?post_id=<?php echo $post['id']; ?>" class="comment-form">
                        <textarea name="comment" placeholder="Add a comment" required></textarea>
                        <button type="submit" name="add_comment">Comment</button>
                    </form>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No posts found</p>
        <?php endif; ?>
    </div>

    <script>
    let isVoting = false;
    
    async function vote(postId, voteType) {
        if (isVoting) return;
        
        isVoting = true;
        try {
            const response = await fetch('vote.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    post_id: postId,
                    vote_type: voteType
                })
            });
            
            const result = await response.json();
            if (result.success) {
                // Refresh halaman untuk memperbarui jumlah vote
                location.reload();
            } else {
                alert(result.message || 'Error recording vote');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error recording vote');
        } finally {
            isVoting = false;
        }
    }
    </script>
</body>
</html>