<?php
session_start();
require "config/db.php";

if (!isset($_GET['id'])) { die("Post not found"); }
$id = (int) $_GET['id'];

if (isset($_SESSION['user'])) {
    $user_id = $_SESSION['user']['id'];
    $check = $conn->prepare("SELECT id FROM post_views WHERE post_id = ? AND user_id = ?");
    $check->bind_param("ii", $id, $user_id);
    $check->execute();
    if ($check->get_result()->num_rows === 0) {
        $insert = $conn->prepare("INSERT INTO post_views (post_id, user_id) VALUES (?, ?)");
        $insert->bind_param("ii", $id, $user_id);
        $insert->execute();
        $conn->query("UPDATE posts SET views = views + 1 WHERE id = $id");
    }
}

$stmt = $conn->prepare("
    SELECT posts.*, posts.image_url AS post_image,
           users.name, users.username, users.image_url AS user_image, users.id AS author_id
    FROM posts
    JOIN users ON posts.user_id = users.id
    WHERE posts.id = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$post = $stmt->get_result()->fetch_assoc();
if (!$post) { die("Post not found"); }
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BS-Blog · <?php echo htmlspecialchars($post['title']); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Instrument+Serif:ital@0;1&family=Inter:wght@300;400;500;600&display=swap"
        rel="stylesheet">
    <style>
    *,
    *::before,
    *::after {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
    }

    :root {
        --ink: #0f0f0f;
        --ink-soft: #6b6b6b;
        --ink-faint: #b0b0b0;
        --surface: #fafaf8;
        --card: #ffffff;
        --rule: #e8e8e4;
        --amber: #e8a020;
        --amber-dim: #fdf3e0;
        --serif: 'Instrument Serif', Georgia, serif;
        --sans: 'Inter', system-ui, sans-serif;
    }

    body {
        font-family: var(--sans);
        background: var(--surface);
        color: var(--ink);
        min-height: 100vh;
    }

    header {
        background: var(--card);
        border-bottom: 1px solid var(--rule);
        position: sticky;
        top: 0;
        z-index: 100;
    }

    .header-inner {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 24px;
        height: 64px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 24px;
    }

    .logo {
        display: flex;
        align-items: center;
        gap: 10px;
        text-decoration: none;
    }

    .logo-mark {
        width: 36px;
        height: 36px;
        background: var(--ink);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-family: var(--serif);
        font-size: 14px;
        color: white;
    }

    .logo-text {
        font-family: var(--serif);
        font-size: 19px;
        color: var(--ink);
        letter-spacing: -0.01em;
    }

    .btn-back {
        font-size: 13px;
        font-weight: 500;
        color: var(--ink-soft);
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 6px;
        padding: 8px 14px;
        border-radius: 8px;
        border: 1.5px solid var(--rule);
        background: white;
        transition: all .15s;
    }

    .btn-back:hover {
        color: var(--ink);
        border-color: var(--ink);
    }

    main {
        max-width: 860px;
        margin: 0 auto;
        padding: 48px 24px 80px;
    }

    /* HERO IMAGE */
    .hero-img {
        width: 100%;
        height: 460px;
        object-fit: cover;
        border-radius: 20px;
        display: block;
        background: var(--rule);
        margin-bottom: 36px;
    }

    /* META TOP */
    .post-meta-top {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 20px;
    }

    .cat-badge {
        display: inline-block;
        font-size: 11px;
        font-weight: 600;
        letter-spacing: .08em;
        text-transform: uppercase;
        color: var(--amber);
        background: var(--amber-dim);
        padding: 5px 12px;
        border-radius: 6px;
    }

    /* TITLE */
    .post-title {
        font-family: var(--serif);
        font-size: clamp(28px, 5vw, 46px);
        line-height: 1.15;
        letter-spacing: -0.02em;
        margin-bottom: 28px;
    }

    /* AUTHOR BAR */
    .author-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 20px 0;
        border-top: 1px solid var(--rule);
        border-bottom: 1px solid var(--rule);
        margin-bottom: 40px;
        gap: 16px;
        flex-wrap: wrap;
    }

    .author-left {
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .author-avatar {
        width: 46px;
        height: 46px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid var(--rule);
        flex-shrink: 0;
    }

    .author-name {
        font-size: 15px;
        font-weight: 600;
        color: var(--ink);
        text-decoration: none;
        display: block;
    }

    .author-name:hover {
        text-decoration: underline;
    }

    .author-handle {
        font-size: 13px;
        color: var(--ink-faint);
    }

    .author-right {
        display: flex;
        align-items: center;
        gap: 20px;
    }

    .meta-stat {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 13px;
        color: var(--ink-soft);
    }

    /* ARTICLE BODY */
    .article-body {
        background: var(--card);
        border: 1px solid var(--rule);
        border-radius: 20px;
        padding: 40px 44px;
    }

    .article-body .content {
        font-size: 16.5px;
        line-height: 1.85;
        color: #2a2a2a;
        white-space: pre-line;
    }

    @media (max-width: 600px) {
        .article-body {
            padding: 24px 20px;
        }
    }

    /* OWNER ACTIONS */
    .post-actions {
        display: flex;
        gap: 12px;
        margin-top: 32px;
    }

    .btn-edit-post {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: #f0f4ff;
        color: #2563eb;
        font-size: 14px;
        font-weight: 600;
        padding: 12px 22px;
        border-radius: 10px;
        border: 1.5px solid #dce6ff;
        text-decoration: none;
        transition: background .15s;
    }

    .btn-edit-post:hover {
        background: #dce6ff;
    }

    .btn-delete-post {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: #fff5f5;
        color: #dc2626;
        font-size: 14px;
        font-weight: 600;
        padding: 12px 22px;
        border-radius: 10px;
        border: 1.5px solid #fecaca;
        cursor: pointer;
        transition: background .15s;
    }

    .btn-delete-post:hover {
        background: #fecaca;
    }

    /* MODAL */
    .modal-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, .45);
        backdrop-filter: blur(4px);
        z-index: 200;
        align-items: center;
        justify-content: center;
    }

    .modal-overlay.open {
        display: flex;
    }

    .modal {
        background: white;
        border-radius: 18px;
        padding: 32px;
        width: 100%;
        max-width: 380px;
        box-shadow: 0 24px 60px rgba(0, 0, 0, .18);
        animation: pop .18s ease;
    }

    @keyframes pop {
        from {
            transform: scale(.94);
            opacity: 0;
        }

        to {
            transform: scale(1);
            opacity: 1;
        }
    }

    .modal-icon {
        width: 48px;
        height: 48px;
        background: #fff5f5;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
        margin-bottom: 16px;
    }

    .modal h2 {
        font-family: var(--serif);
        font-size: 22px;
        margin-bottom: 8px;
    }

    .modal p {
        font-size: 14px;
        color: var(--ink-soft);
        line-height: 1.55;
        margin-bottom: 24px;
    }

    .modal-actions {
        display: flex;
        gap: 10px;
    }

    .btn-cancel {
        flex: 1;
        font-size: 14px;
        font-weight: 500;
        padding: 11px 0;
        border-radius: 10px;
        background: var(--surface);
        border: 1.5px solid var(--rule);
        cursor: pointer;
        color: var(--ink);
        transition: background .15s;
    }

    .btn-cancel:hover {
        background: var(--rule);
    }

    .btn-confirm-delete {
        flex: 1;
        font-size: 14px;
        font-weight: 600;
        padding: 11px 0;
        border-radius: 10px;
        background: #dc2626;
        color: white;
        text-decoration: none;
        text-align: center;
        transition: background .15s;
    }

    .btn-confirm-delete:hover {
        background: #b91c1c;
    }
    </style>
</head>

<body>

    <header>
        <div class="header-inner">
            <a href="dashboard.php" class="logo">
                <div class="logo-mark">BS</div>
                <span class="logo-text">BS·Blog</span>
            </a>
            <a href="dashboard.php" class="btn-back">
                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path d="M19 12H5M12 5l-7 7 7 7" />
                </svg>
                Back
            </a>
        </div>
    </header>

    <main>

        <img class="hero-img"
            src="<?php echo !empty($post['post_image']) ? htmlspecialchars($post['post_image']) : 'https://picsum.photos/seed/'.$post['id'].'/1200/600'; ?>"
            alt="<?php echo htmlspecialchars($post['title']); ?>">

        <div class="post-meta-top">
            <span class="cat-badge"><?php echo htmlspecialchars($post['category']); ?></span>
        </div>

        <h1 class="post-title"><?php echo htmlspecialchars($post['title']); ?></h1>

        <div class="author-bar">
            <div class="author-left">
                <img class="author-avatar"
                    src="<?php echo !empty($post['user_image']) ? htmlspecialchars($post['user_image']) : 'https://ui-avatars.com/api/?name='.urlencode($post['name']).'&background=0f0f0f&color=fff'; ?>"
                    alt="<?php echo htmlspecialchars($post['name']); ?>">
                <div>
                    <a class="author-name" href="user-profile.php?id=<?php echo $post['user_id']; ?>">
                        <?php echo htmlspecialchars($post['name']); ?>
                    </a>
                    <span class="author-handle">@<?php echo htmlspecialchars($post['username']); ?></span>
                </div>
            </div>
            <div class="author-right">
                <span class="meta-stat">
                    <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="4" width="18" height="18" rx="2" />
                        <path d="M16 2v4M8 2v4M3 10h18" />
                    </svg>
                    <?php echo date("M d, Y", strtotime($post['created_at'])); ?>
                </span>
                <span class="meta-stat">
                    <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path d="M1 12S5 4 12 4s11 8 11 8-4 8-11 8S1 12 1 12z" />
                        <circle cx="12" cy="12" r="3" />
                    </svg>
                    <?php echo number_format($post['views']); ?> views
                </span>
            </div>
        </div>

        <div class="article-body">
            <div class="content"><?php echo htmlspecialchars($post['content']); ?></div>
        </div>

        <?php if (isset($_SESSION['user']) && $_SESSION['user']['id'] == $post['user_id']): ?>
        <div class="post-actions">
            <a href="post-edit.php?id=<?php echo $post['id']; ?>" class="btn-edit-post">
                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
                </svg>
                Edit Post
            </a>
            <button onclick="openModal()" class="btn-delete-post">
                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <polyline points="3 6 5 6 21 6" />
                    <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6" />
                </svg>
                Delete Post
            </button>
        </div>
        <?php endif; ?>

    </main>

    <div id="deleteModal" class="modal-overlay">
        <div class="modal">
            <div class="modal-icon">🗑️</div>
            <h2>Delete this post?</h2>
            <p>This will permanently remove the post and all its data. You can't undo this.</p>
            <div class="modal-actions">
                <button onclick="closeModal()" class="btn-cancel">Keep it</button>
                <a href="delete-post.php?id=<?php echo $post['id']; ?>" class="btn-confirm-delete">Yes, delete</a>
            </div>
        </div>
    </div>

    <script>
    function openModal() {
        document.getElementById('deleteModal').classList.add('open');
    }

    function closeModal() {
        document.getElementById('deleteModal').classList.remove('open');
    }
    document.getElementById('deleteModal').addEventListener('click', function(e) {
        if (e.target === this) closeModal();
    });
    </script>
</body>

</html>