<?php
session_start();
require "config/db.php";

if (!isset($_GET['id'])) { die("User not found"); }
$user_id = (int) $_GET['id'];

$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$userProfile = $stmt->get_result()->fetch_assoc();
if (!$userProfile) { die("User not found"); }

$stmt2 = $conn->prepare("SELECT * FROM posts WHERE user_id = ? ORDER BY created_at DESC");
$stmt2->bind_param("i", $user_id);
$stmt2->execute();
$posts = $stmt2->get_result();

$me = $_SESSION['user'] ?? null;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BS-Blog · <?php echo htmlspecialchars($userProfile['name']); ?></title>
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
        --radius: 14px;
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
        gap: 24px;
    }

    .logo {
        display: flex;
        align-items: center;
        gap: 10px;
        text-decoration: none;
        flex-shrink: 0;
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

    .header-right {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-left: auto;
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
        max-width: 1100px;
        margin: 0 auto;
        padding: 40px 24px 80px;
    }

    /* PROFILE CARD */
    .profile-card {
        background: var(--card);
        border: 1px solid var(--rule);
        border-radius: 20px;
        padding: 32px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 24px;
        margin-bottom: 48px;
    }

    .profile-left {
        display: flex;
        align-items: center;
        gap: 20px;
    }

    .profile-avatar {
        width: 72px;
        height: 72px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid var(--rule);
        flex-shrink: 0;
    }

    .profile-name {
        font-family: var(--serif);
        font-size: 26px;
        letter-spacing: -0.01em;
        margin-bottom: 4px;
    }

    .profile-handle {
        font-size: 14px;
        color: var(--ink-faint);
        margin-bottom: 4px;
    }

    .profile-email {
        font-size: 13px;
        color: var(--ink-soft);
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .btn-edit-profile {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: var(--ink);
        color: white;
        font-size: 13px;
        font-weight: 600;
        padding: 10px 20px;
        border-radius: 10px;
        text-decoration: none;
        flex-shrink: 0;
        transition: opacity .15s;
    }

    .btn-edit-profile:hover {
        opacity: .8;
    }

    /* SECTION */
    .section-header {
        display: flex;
        align-items: baseline;
        justify-content: space-between;
        margin-bottom: 24px;
        padding-bottom: 16px;
        border-bottom: 1px solid var(--rule);
    }

    .section-title {
        font-family: var(--serif);
        font-size: 24px;
    }

    .post-count {
        font-size: 13px;
        color: var(--ink-faint);
    }

    /* GRID */
    .grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 24px;
    }

    @media (max-width: 680px) {
        .grid {
            grid-template-columns: 1fr;
        }
    }

    .card {
        background: var(--card);
        border: 1px solid var(--rule);
        border-radius: var(--radius);
        overflow: hidden;
        display: flex;
        flex-direction: column;
        transition: transform .2s, box-shadow .2s;
    }

    .card:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 32px rgba(0, 0, 0, .08);
    }

    .card-link {
        text-decoration: none;
        color: inherit;
        flex: 1;
        display: flex;
        flex-direction: column;
    }

    .card-img {
        width: 100%;
        height: 196px;
        object-fit: cover;
        display: block;
        background: var(--rule);
    }

    .card-body {
        padding: 20px 20px 16px;
        flex: 1;
        display: flex;
        flex-direction: column;
    }

    .card-category {
        display: inline-block;
        font-size: 11px;
        font-weight: 600;
        letter-spacing: .08em;
        text-transform: uppercase;
        color: var(--amber);
        background: var(--amber-dim);
        padding: 4px 10px;
        border-radius: 6px;
        margin-bottom: 12px;
        width: fit-content;
    }

    .card-title {
        font-family: var(--serif);
        font-size: 18px;
        line-height: 1.35;
        margin-bottom: 10px;
        color: var(--ink);
    }

    .card-excerpt {
        font-size: 13.5px;
        line-height: 1.6;
        color: var(--ink-soft);
        flex: 1;
    }

    .card-meta {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-top: 16px;
        padding-top: 14px;
        border-top: 1px solid var(--rule);
        font-size: 12px;
        color: var(--ink-faint);
    }

    .card-meta-date {
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .card-views {
        display: flex;
        align-items: center;
        gap: 5px;
        font-weight: 500;
        color: var(--ink-soft);
    }

    .card-actions {
        display: flex;
        gap: 8px;
        padding: 12px 20px 16px;
        border-top: 1px solid var(--rule);
    }

    .btn-edit {
        flex: 1;
        text-align: center;
        font-size: 13px;
        font-weight: 500;
        padding: 8px 0;
        border-radius: 8px;
        background: #f0f4ff;
        color: #2563eb;
        text-decoration: none;
        border: 1.5px solid #dce6ff;
        transition: background .15s;
    }

    .btn-edit:hover {
        background: #dce6ff;
    }

    .btn-delete {
        flex: 1;
        font-size: 13px;
        font-weight: 500;
        padding: 8px 0;
        border-radius: 8px;
        background: #fff5f5;
        color: #dc2626;
        border: 1.5px solid #fecaca;
        cursor: pointer;
        transition: background .15s;
    }

    .btn-delete:hover {
        background: #fecaca;
    }

    .empty {
        grid-column: 1/-1;
        text-align: center;
        padding: 64px 24px;
        color: var(--ink-soft);
    }

    .empty-icon {
        font-size: 40px;
        margin-bottom: 14px;
    }

    .empty h3 {
        font-family: var(--serif);
        font-size: 22px;
        margin-bottom: 8px;
        color: var(--ink);
    }

    /* DANGER ZONE */
    .danger-zone {
        margin-top: 60px;
        border: 1.5px solid #fecaca;
        border-radius: 16px;
        padding: 28px 32px;
        background: #fff8f7;
    }

    .danger-zone-header {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 8px;
    }

    .danger-zone-title {
        font-family: var(--serif);
        font-size: 20px;
        color: #b91c1c;
    }

    .danger-zone p {
        font-size: 14px;
        color: var(--ink-soft);
        line-height: 1.6;
        margin-bottom: 20px;
    }

    .btn-danger {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: #dc2626;
        color: white;
        font-size: 14px;
        font-weight: 600;
        padding: 11px 22px;
        border-radius: 10px;
        border: none;
        cursor: pointer;
        transition: background .15s;
    }

    .btn-danger:hover {
        background: #b91c1c;
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
            <div class="header-right">
                <a href="all-blogs.php" class="btn-back">
                    <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path d="M19 12H5M12 5l-7 7 7 7" />
                    </svg>
                    Back
                </a>
            </div>
        </div>
    </header>

    <main>

        <!-- PROFILE CARD -->
        <div class="profile-card">
            <div class="profile-left">
                <img class="profile-avatar"
                    src="<?php echo !empty($userProfile['image_url']) ? htmlspecialchars($userProfile['image_url']) : 'https://ui-avatars.com/api/?name='.urlencode($userProfile['name']).'&background=0f0f0f&color=fff'; ?>"
                    alt="<?php echo htmlspecialchars($userProfile['name']); ?>">
                <div>
                    <h2 class="profile-name"><?php echo htmlspecialchars($userProfile['name']); ?></h2>
                    <p class="profile-handle">@<?php echo htmlspecialchars($userProfile['username']); ?></p>
                    <p class="profile-email">
                        <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            stroke-width="2">
                            <rect x="2" y="4" width="20" height="16" rx="2" />
                            <path d="m2 7 10 7 10-7" />
                        </svg>
                        <?php echo htmlspecialchars($userProfile['email']); ?>
                    </p>
                </div>
            </div>
            <?php if ($me && $me['id'] == $userProfile['id']): ?>
            <a href="edit-profile.php" class="btn-edit-profile">
                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
                </svg>
                Edit Profile
            </a>
            <?php endif; ?>
        </div>

        <!-- POSTS -->
        <?php
    $allPosts = [];
    while ($p = $posts->fetch_assoc()) $allPosts[] = $p;
    $count = count($allPosts);
    ?>
        <div class="section-header">
            <h3 class="section-title">Posts by <?php echo htmlspecialchars($userProfile['name']); ?></h3>
            <span class="post-count"><?php echo $count; ?> post<?php echo $count !== 1 ? 's' : ''; ?></span>
        </div>

        <div class="grid">
            <?php if ($count === 0): ?>
            <div class="empty">
                <div class="empty-icon">✍️</div>
                <h3>No posts yet</h3>
                <p>Nothing published here yet.</p>
            </div>
            <?php else: foreach ($allPosts as $post): ?>
            <article class="card">
                <a href="post.php?id=<?php echo $post['id']; ?>" class="card-link">
                    <img class="card-img"
                        src="<?php echo !empty($post['image_url']) ? htmlspecialchars($post['image_url']) : 'https://picsum.photos/seed/'.$post['id'].'/600/400'; ?>"
                        alt="">
                    <div class="card-body">
                        <span class="card-category"><?php echo htmlspecialchars($post['category']); ?></span>
                        <h4 class="card-title"><?php echo htmlspecialchars($post['title']); ?></h4>
                        <p class="card-excerpt">
                            <?php echo htmlspecialchars(substr(strip_tags($post['content']), 0, 110)); ?>…</p>
                        <div class="card-meta">
                            <span class="card-meta-date">
                                <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                    stroke-width="2">
                                    <rect x="3" y="4" width="18" height="18" rx="2" />
                                    <path d="M16 2v4M8 2v4M3 10h18" />
                                </svg>
                                <?php echo date("M d, Y", strtotime($post['created_at'])); ?>
                            </span>
                            <span class="card-views">
                                <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                    stroke-width="2">
                                    <path d="M1 12S5 4 12 4s11 8 11 8-4 8-11 8S1 12 1 12z" />
                                    <circle cx="12" cy="12" r="3" />
                                </svg>
                                <?php echo number_format($post['views']); ?>
                            </span>
                        </div>
                    </div>
                </a>
                <?php if ($me && $me['id'] == $post['user_id']): ?>
                <div class="card-actions">
                    <a href="post-edit.php?id=<?php echo $post['id']; ?>" class="btn-edit">Edit</a>
                    <button onclick="openPostModal(<?php echo $post['id']; ?>)" class="btn-delete">Delete</button>
                </div>
                <?php endif; ?>
            </article>
            <?php endforeach; endif; ?>
        </div>

        <!-- DANGER ZONE -->
        <?php if ($me && $me['id'] == $userProfile['id']): ?>
        <div class="danger-zone">
            <div class="danger-zone-header">
                <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="#b91c1c" stroke-width="2">
                    <path
                        d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" />
                    <line x1="12" y1="9" x2="12" y2="13" />
                    <line x1="12" y1="17" x2="12.01" y2="17" />
                </svg>
                <h3 class="danger-zone-title">Danger Zone</h3>
            </div>
            <p>Permanently delete your account and all your posts. This cannot be undone — make sure you're certain
                before proceeding.</p>
            <button onclick="openAccountModal()" class="btn-danger">
                <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <polyline points="3 6 5 6 21 6" />
                    <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6" />
                    <path d="M10 11v6M14 11v6" />
                    <path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2" />
                </svg>
                Delete My Account
            </button>
        </div>
        <?php endif; ?>

    </main>

    <!-- POST DELETE MODAL -->
    <div id="postDeleteModal" class="modal-overlay">
        <div class="modal">
            <div class="modal-icon">🗑️</div>
            <h2>Delete this post?</h2>
            <p>This will permanently remove the post and all its data. You can't undo this.</p>
            <div class="modal-actions">
                <button onclick="closePostModal()" class="btn-cancel">Keep it</button>
                <a id="confirmDeletePost" href="#" class="btn-confirm-delete">Yes, delete</a>
            </div>
        </div>
    </div>

    <!-- ACCOUNT DELETE MODAL -->
    <div id="accountDeleteModal" class="modal-overlay">
        <div class="modal">
            <div class="modal-icon">⚠️</div>
            <h2>Delete your account?</h2>
            <p>This will permanently remove your account and every post you've written. There's no going back.</p>
            <div class="modal-actions">
                <button onclick="closeAccountModal()" class="btn-cancel">Cancel</button>
                <a href="delete-account.php?id=<?php echo $userProfile['id']; ?>" class="btn-confirm-delete">Yes,
                    delete</a>
            </div>
        </div>
    </div>

    <script>
    function openPostModal(id) {
        document.getElementById('postDeleteModal').classList.add('open');
        document.getElementById('confirmDeletePost').href = 'delete-post.php?id=' + id;
    }

    function closePostModal() {
        document.getElementById('postDeleteModal').classList.remove('open');
    }

    function openAccountModal() {
        document.getElementById('accountDeleteModal').classList.add('open');
    }

    function closeAccountModal() {
        document.getElementById('accountDeleteModal').classList.remove('open');
    }
    document.querySelectorAll('.modal-overlay').forEach(el => el.addEventListener('click', function(e) {
        if (e.target === this) this.classList.remove('open');
    }));
    </script>
</body>

</html>