<?php
session_start();
require "config/db.php";

if (!isset($_SESSION["user"])) {
    header("Location: auth/login.php");
    exit;
}

$user = $_SESSION["user"];

if (!isset($_GET["id"])) { die("Post not found"); }
$post_id = (int) $_GET["id"];

$stmt = $conn->prepare("SELECT * FROM posts WHERE id = ?");
$stmt->bind_param("i", $post_id);
$stmt->execute();
$post = $stmt->get_result()->fetch_assoc();

if (!$post) { die("Post not found"); }
if ($post["user_id"] != $user["id"]) { die("Access denied"); }

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title     = trim($_POST["title"]);
    $category  = trim($_POST["category"]);
    $content   = trim($_POST["content"]);
    $image_url = trim($_POST["image_url"]);

    $stmt = $conn->prepare("UPDATE posts SET title=?, category=?, content=?, image_url=? WHERE id=?");
    $stmt->bind_param("ssssi", $title, $category, $content, $image_url, $post_id);
    $stmt->execute();

    header("Location: post.php?id=" . $post_id);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BS-Blog · Edit Post</title>
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
        justify-content: space-between;
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
        max-width: 760px;
        margin: 0 auto;
        padding: 48px 24px 80px;
    }

    .page-eyebrow {
        font-size: 11px;
        font-weight: 600;
        letter-spacing: .12em;
        text-transform: uppercase;
        color: var(--amber);
        margin-bottom: 10px;
    }

    .page-title {
        font-family: var(--serif);
        font-size: 32px;
        letter-spacing: -0.01em;
        margin-bottom: 8px;
    }

    .page-subtitle {
        font-size: 14px;
        color: var(--ink-soft);
        margin-bottom: 36px;
    }

    /* CURRENT COVER PREVIEW */
    .current-cover {
        margin-bottom: 32px;
    }

    .current-cover-label {
        font-size: 12px;
        font-weight: 600;
        color: var(--ink-faint);
        text-transform: uppercase;
        letter-spacing: .08em;
        margin-bottom: 10px;
    }

    .current-cover img {
        width: 100%;
        height: 420px;
        object-fit: cover;
        border-radius: 12px;
        border: 1.5px solid var(--rule);
        display: block;
    }

    .form-card {
        background: var(--card);
        border: 1px solid var(--rule);
        border-radius: 20px;
        padding: 36px;
    }

    .field {
        margin-bottom: 24px;
    }

    label {
        display: block;
        font-size: 13px;
        font-weight: 600;
        color: var(--ink);
        margin-bottom: 8px;
    }

    label span {
        font-weight: 400;
        color: var(--ink-faint);
        margin-left: 4px;
    }

    input[type="text"],
    select,
    textarea {
        width: 100%;
        background: var(--surface);
        border: 1.5px solid var(--rule);
        border-radius: 10px;
        padding: 12px 16px;
        font-family: var(--sans);
        font-size: 14px;
        color: var(--ink);
        outline: none;
        transition: border-color .15s, box-shadow .15s;
        appearance: none;
    }

    input:focus,
    select:focus,
    textarea:focus {
        border-color: var(--ink);
        box-shadow: 0 0 0 3px rgba(15, 15, 15, .06);
    }

    input::placeholder,
    textarea::placeholder {
        color: var(--ink-faint);
    }

    .select-wrap {
        position: relative;
    }

    .select-wrap select {
        padding-right: 40px;
        cursor: pointer;
    }

    .select-wrap::after {
        content: '';
        position: absolute;
        right: 16px;
        top: 50%;
        transform: translateY(-50%);
        width: 0;
        height: 0;
        border-left: 5px solid transparent;
        border-right: 5px solid transparent;
        border-top: 5px solid var(--ink-faint);
        pointer-events: none;
    }

    textarea {
        resize: vertical;
        min-height: 260px;
        line-height: 1.65;
    }

    .image-preview {
        margin-top: 10px;
        border-radius: 10px;
        overflow: hidden;
        border: 1.5px solid var(--rule);
        display: none;
    }

    .image-preview img {
        width: 100%;
        max-height: 420px;
        object-fit: cover;
        display: block;
    }

    .form-divider {
        border: none;
        border-top: 1px solid var(--rule);
        margin: 28px 0;
    }

    .form-actions {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }

    .btn-cancel-form {
        font-size: 14px;
        font-weight: 500;
        color: var(--ink-soft);
        text-decoration: none;
        padding: 12px 20px;
        border-radius: 10px;
        border: 1.5px solid var(--rule);
        background: white;
        transition: all .15s;
    }

    .btn-cancel-form:hover {
        border-color: var(--ink);
        color: var(--ink);
    }

    .btn-save {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: var(--ink);
        color: white;
        font-size: 14px;
        font-weight: 600;
        padding: 12px 26px;
        border-radius: 10px;
        border: none;
        cursor: pointer;
        transition: opacity .15s;
    }

    .btn-save:hover {
        opacity: .82;
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
            <a href="post.php?id=<?php echo $post_id; ?>" class="btn-back">
                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path d="M19 12H5M12 5l-7 7 7 7" />
                </svg>
                Back to Post
            </a>
        </div>
    </header>

    <main>
        <p class="page-eyebrow">Editing</p>
        <h1 class="page-title">Update your post</h1>
        <p class="page-subtitle">Make your changes below — they'll be live as soon as you save.</p>

        <?php if (!empty($post['image_url'])): ?>
        <div class="current-cover">
            <p class="current-cover-label">Current cover</p>
            <img id="coverImg" src="<?php echo htmlspecialchars($post['image_url']); ?>" alt="Current cover">
        </div>
        <?php endif; ?>

        <div class="form-card">
            <form method="POST">

                <div class="field">
                    <label for="title">Post Title</label>
                    <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($post['title']); ?>"
                        required>
                </div>

                <div class="field">
                    <label for="category">Category</label>
                    <div class="select-wrap">
                        <select id="category" name="category" required>
                            <?php
                        $categories = ["Technology","Food","Travel","Business","Lifestyle","Health","Education"];
                        foreach ($categories as $cat):
                        ?>
                            <option value="<?php echo $cat; ?>"
                                <?php echo $post['category'] === $cat ? 'selected' : ''; ?>>
                                <?php echo $cat; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="field">
                    <label for="image_url">Cover Image URL <span>(optional)</span></label>
                    <input type="text" id="image_url" name="image_url"
                        value="<?php echo htmlspecialchars($post['image_url']); ?>"
                        placeholder="https://example.com/image.jpg" oninput="previewImage(this.value)">
                    <div class="image-preview" id="imagePreview">
                        <img id="previewImg" src="" alt="New cover preview">
                    </div>
                </div>

                <div class="field">
                    <label for="content">Content</label>
                    <textarea id="content" name="content"
                        required><?php echo htmlspecialchars($post['content']); ?></textarea>
                </div>

                <hr class="form-divider">

                <div class="form-actions">
                    <a href="post.php?id=<?php echo $post_id; ?>" class="btn-cancel-form">Discard changes</a>
                    <button type="submit" class="btn-save">
                        <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            stroke-width="2.5">
                            <path d="M5 12l5 5L20 7" />
                        </svg>
                        Save Changes
                    </button>
                </div>

            </form>
        </div>
    </main>

    <script>
    function previewImage(url) {
        const wrap = document.getElementById('imagePreview');
        const preview = document.getElementById('previewImg');
        const cover = document.getElementById('coverImg');
        if (url.trim()) {
            preview.src = url;
            wrap.style.display = 'block';
            preview.onerror = () => {
                wrap.style.display = 'none';
            };
            preview.onload = () => {
                wrap.style.display = 'block';
                if (cover) cover.src = url;
            };
        } else {
            wrap.style.display = 'none';
        }
    }
    </script>
</body>

</html>