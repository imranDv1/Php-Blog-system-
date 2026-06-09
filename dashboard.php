<?php
session_start();

if (!isset($_SESSION["user"])) {
    header("Location: auth/login.php");
    exit;
}

$user = $_SESSION["user"];

require "config/db.php";

$search = isset($_GET['q']) ? trim($_GET['q']) : '';
$category = isset($_GET['cat']) ? trim($_GET['cat']) : '';

if ($search !== '' || $category !== '') {
    $sql = "
        SELECT posts.*, users.name, users.username
        FROM posts
        JOIN users ON posts.user_id = users.id
        WHERE 1=1
    ";
    $types = "";
    $params = [];

    if ($search !== '') {
        $sql .= " AND (posts.title LIKE ? OR posts.content LIKE ? OR posts.category LIKE ?) ";
        $like = "%$search%";
        $params[] = $like;
        $params[] = $like;
        $params[] = $like;
        $types .= "sss";
    }

    if ($category !== '' && $category !== 'All') {
        $sql .= " AND posts.category = ? ";
        $params[] = $category;
        $types .= "s";
    }

    $sql .= " ORDER BY posts.created_at DESC LIMIT 6";
    $stmt = $conn->prepare($sql);

    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query("
        SELECT posts.*, users.name, users.username
        FROM posts
        JOIN users ON posts.user_id = users.id
        ORDER BY posts.created_at DESC
        LIMIT 6
    ");
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BS-Blog · Dashboard</title>
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

    /* ── HEADER ─────────────────────────────────── */
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
        letter-spacing: 0.02em;
    }

    .logo-text {
        font-family: var(--serif);
        font-size: 19px;
        color: var(--ink);
        letter-spacing: -0.01em;
    }

    .search-wrap {
        flex: 1;
        max-width: 440px;
        position: relative;
    }

    .search-wrap svg {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--ink-faint);
        pointer-events: none;
    }

    .search-wrap input {
        width: 100%;
        background: var(--surface);
        border: 1.5px solid var(--rule);
        border-radius: 10px;
        padding: 9px 16px 9px 40px;
        font-family: var(--sans);
        font-size: 14px;
        color: var(--ink);
        outline: none;
        transition: border-color .15s;
    }

    .search-wrap input:focus {
        border-color: var(--ink);
    }

    .search-wrap input::placeholder {
        color: var(--ink-faint);
    }

    .header-right {
        display: flex;
        align-items: center;
        gap: 16px;
        margin-left: auto;
    }

    .user-chip {
        display: flex;
        align-items: center;
        gap: 10px;
        text-decoration: none;
        padding: 4px 12px 4px 4px;
        border-radius: 999px;
        border: 1.5px solid var(--rule);
        background: white;
        transition: border-color .15s;
    }

    .user-chip:hover {
        border-color: var(--ink);
    }

    .user-chip img {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        object-fit: cover;
    }

    .user-chip-info {
        line-height: 1.2;
    }

    .user-chip-name {
        font-size: 13px;
        font-weight: 600;
        color: var(--ink);
    }

    .user-chip-handle {
        font-size: 11px;
        color: var(--ink-faint);
    }

    .btn-logout {
        font-size: 13px;
        font-weight: 500;
        color: #c0392b;
        text-decoration: none;
        padding: 8px 14px;
        border-radius: 8px;
        border: 1.5px solid #fad9d5;
        background: #fff8f7;
        transition: background .15s, border-color .15s;
    }

    .btn-logout:hover {
        background: #ffecea;
        border-color: #f5b7b1;
    }

    /* ── MAIN CONTENT ───────────────────────────── */
    main {
        max-width: 1200px;
        margin: 0 auto;
        padding: 40px 24px 80px;
    }

    /* ── BANNER ─────────────────────────────────── */
    .banner {
        background: var(--ink);
        border-radius: 20px;
        padding: 48px 48px 44px;
        margin-bottom: 48px;
        position: relative;
        overflow: hidden;
    }

    .banner::before {
        content: '';
        position: absolute;
        inset: 0;
        background: radial-gradient(ellipse 60% 80% at 80% 50%, rgba(232, 160, 32, .18) 0%, transparent 70%);
        pointer-events: none;
    }

    .banner-eyebrow {
        font-size: 11px;
        font-weight: 600;
        letter-spacing: .12em;
        text-transform: uppercase;
        color: var(--amber);
        margin-bottom: 14px;
    }

    .banner-headline {
        font-family: var(--serif);
        font-size: clamp(28px, 4vw, 44px);
        color: white;
        line-height: 1.15;
        max-width: 520px;
        margin-bottom: 24px;
    }

    .banner-headline em {
        color: var(--amber);
        font-style: italic;
    }

    .btn-create {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: var(--amber);
        color: var(--ink);
        font-weight: 600;
        font-size: 14px;
        padding: 11px 22px;
        border-radius: 10px;
        text-decoration: none;
        transition: opacity .15s;
    }

    .btn-create:hover {
        opacity: .88;
    }

    /* ── CATEGORIES ─────────────────────────────── */
    .section-label {
        font-size: 11px;
        font-weight: 600;
        letter-spacing: .1em;
        text-transform: uppercase;
        color: var(--ink-faint);
        margin-bottom: 14px;
    }

    .cats {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 48px;
    }

    .cat-pill {
        font-size: 13px;
        font-weight: 500;
        padding: 7px 18px;
        border-radius: 999px;
        border: 1.5px solid var(--rule);
        text-decoration: none;
        color: var(--ink-soft);
        background: white;
        transition: all .15s;
    }

    .cat-pill:hover {
        border-color: var(--ink);
        color: var(--ink);
    }

    .cat-pill.active {
        background: var(--ink);
        color: white;
        border-color: var(--ink);
    }

    /* ── SECTION HEADER ─────────────────────────── */
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
        font-size: 26px;
        letter-spacing: -0.01em;
    }

    .view-all {
        font-size: 13px;
        font-weight: 500;
        color: var(--ink-soft);
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .view-all:hover {
        color: var(--ink);
    }

    /* ── FILTER BADGE ───────────────────────────── */
    .filter-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: var(--amber-dim);
        color: #7a5200;
        font-size: 13px;
        padding: 7px 14px;
        border-radius: 8px;
        margin-bottom: 24px;
        border: 1px solid #f0d080;
    }

    /* ── GRID ───────────────────────────────────── */
    .grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 24px;
    }

    @media (max-width: 900px) {
        .grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 580px) {
        .grid {
            grid-template-columns: 1fr;
        }
    }

    /* ── CARD ───────────────────────────────────── */
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

    /* ── OWNER ACTIONS ──────────────────────────── */
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

    /* ── EMPTY STATE ────────────────────────────── */
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

    .empty p {
        font-size: 14px;
    }

    /* ── MODAL ──────────────────────────────────── */
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

    <!-- HEADER -->
    <header>
        <div class="header-inner">

            <a href="index.php" class="logo">
                <div class="logo-mark">BS</div>
                <span class="logo-text">BS·Blog</span>
            </a>

            <form method="GET" class="search-wrap">
                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8" />
                    <path d="m21 21-4.35-4.35" />
                </svg>
                <input type="text" name="q" value="<?php echo htmlspecialchars($search); ?>"
                    placeholder="Search posts…">
            </form>

            <div class="header-right">
                <a href="user-profile.php?id=<?php echo $user['id']; ?>" class="user-chip">
                    <img src="<?php echo !empty($user['image_url'])
                    ? htmlspecialchars($user['image_url'])
                    : 'https://ui-avatars.com/api/?name=' . urlencode($user['name']) . '&background=0f0f0f&color=fff'; ?>"
                        alt="<?php echo htmlspecialchars($user['name']); ?>">
                    <div class="user-chip-info">
                        <div class="user-chip-name"><?php echo htmlspecialchars($user['name']); ?></div>
                        <div class="user-chip-handle">@<?php echo htmlspecialchars($user['username']); ?></div>
                    </div>
                </a>

                <a href="auth/logout.php" class="btn-logout">Sign out</a>
            </div>

        </div>
    </header>

    <!-- MAIN -->
    <main>

        <!-- BANNER -->
        <div class="banner">
            <p class="banner-eyebrow">Welcome back, <?php echo htmlspecialchars($user['name']); ?></p>
            <h2 class="banner-headline">Your corner of the<br>internet — <em>write something.</em></h2>
            <a href="create-post.php" class="btn-create">
                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path d="M12 5v14M5 12h14" />
                </svg>
                New Post
            </a>
        </div>

        <!-- CATEGORIES -->
        <div class="section-label">Browse by topic</div>
        <div class="cats">
            <?php
        $cats = ["All","Technology","Food","Travel","Business","Lifestyle","Health","Education"];
        foreach ($cats as $cat):
            $isActive = ($category === $cat) || ($category === '' && $cat === 'All');
            $href = $cat === 'All' ? '?' : '?cat=' . urlencode($cat);
            if ($search !== '') $href .= ($cat === 'All' ? '' : '&') . 'q=' . urlencode($search);
        ?>
            <a href="<?php echo $href; ?>" class="cat-pill <?php echo $isActive ? 'active' : ''; ?>">
                <?php echo $cat; ?>
            </a>
            <?php endforeach; ?>
        </div>

        <!-- SECTION HEADER -->
        <div class="section-header">
            <h3 class="section-title">Recent Posts</h3>
            <a href="all-blogs.php" class="view-all">
                View all
                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path d="M5 12h14M12 5l7 7-7 7" />
                </svg>
            </a>
        </div>

        <!-- ACTIVE FILTER BADGE -->
        <?php if ($search || $category): ?>
        <div class="filter-badge">
            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path d="M3 6h18M7 12h10M11 18h2" />
            </svg>
            Filtering
            <?php if ($search): ?>&nbsp;· Search:
            <strong><?php echo htmlspecialchars($search); ?></strong><?php endif; ?>
            <?php if ($category): ?>&nbsp;· Category:
            <strong><?php echo htmlspecialchars($category); ?></strong><?php endif; ?>
            &nbsp;<a href="?" style="color:#7a5200;font-weight:600;text-decoration:none;">✕ Clear</a>
        </div>
        <?php endif; ?>

        <!-- POST GRID -->
        <div class="grid">
            <?php
        $hasAny = false;
        while ($post = $result->fetch_assoc()):
            $hasAny = true;
        ?>
            <article class="card">

                <a href="post.php?id=<?php echo $post['id']; ?>" class="card-link">
                    <img class="card-img"
                        src="<?php echo !empty($post['image_url']) ? htmlspecialchars($post['image_url']) : 'https://picsum.photos/seed/'.$post['id'].'/600/400'; ?>"
                        alt="<?php echo htmlspecialchars($post['title']); ?>">

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

                <?php if ($post['user_id'] == $user['id']): ?>
                <div class="card-actions">
                    <a href="post-edit.php?id=<?php echo $post['id']; ?>" class="btn-edit">Edit</a>
                    <button onclick="openModal(<?php echo $post['id']; ?>)" class="btn-delete">Delete</button>
                </div>
                <?php endif; ?>

            </article>
            <?php endwhile; ?>

            <?php if (!$hasAny): ?>
            <div class="empty">
                <div class="empty-icon">📭</div>
                <h3>Nothing here yet</h3>
                <p>No posts match your filters. Try a different search or category.</p>
            </div>
            <?php endif; ?>
        </div>

    </main>

    <!-- DELETE MODAL -->
    <div id="deleteModal" class="modal-overlay">
        <div class="modal">
            <div class="modal-icon">🗑️</div>
            <h2>Delete this post?</h2>
            <p>This will permanently remove the post and all its data. You can't undo this.</p>
            <div class="modal-actions">
                <button onclick="closeModal()" class="btn-cancel">Keep it</button>
                <a id="deleteLink" href="#" class="btn-confirm-delete">Yes, delete</a>
            </div>
        </div>
    </div>

    <script>
    function openModal(id) {
        document.getElementById('deleteModal').classList.add('open');
        document.getElementById('deleteLink').href = 'delete-post.php?id=' + id;
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