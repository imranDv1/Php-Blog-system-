<?php
session_start();
require "config/db.php";

if (!isset($_SESSION["user"])) {
    header("Location: auth/login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id   = $_SESSION["user"]["id"];
    $category  = $_POST["category"];
    $title     = $_POST["title"];
    $content   = $_POST["content"];
    $image_url = $_POST["image_url"];

    $stmt = $conn->prepare("INSERT INTO posts (user_id, category, title, content, image_url) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issss", $user_id, $category, $title, $content, $image_url);
    $stmt->execute();

    header("Location: dashboard.php");
    exit;
}

$user = $_SESSION["user"];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BS-Blog · New Post</title>
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

    /* HEADER */
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

    /* LAYOUT */
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
        font-size: 34px;
        letter-spacing: -0.01em;
        margin-bottom: 8px;
    }

    .page-subtitle {
        font-size: 15px;
        color: var(--ink-soft);
        margin-bottom: 40px;
    }

    /* FORM CARD */
    .form-card {
        background: var(--card);
        border: 1px solid var(--rule);
        border-radius: 20px;
        padding: 36px;
    }

    .field {
        margin-bottom: 24px;
    }

    .field:last-of-type {
        margin-bottom: 0;
    }

    label {
        display: block;
        font-size: 13px;
        font-weight: 600;
        color: var(--ink);
        margin-bottom: 8px;
        letter-spacing: .01em;
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

    input[type="text"]:focus,
    select:focus,
    textarea:focus {
        border-color: var(--ink);
        box-shadow: 0 0 0 3px rgba(15, 15, 15, .06);
    }

    input::placeholder,
    textarea::placeholder {
        color: var(--ink-faint);
    }

    /* SELECT WRAPPER */
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
        min-height: 220px;
        line-height: 1.65;
    }

    /* IMAGE PREVIEW */
    .image-preview {
        margin-top: 10px;
        border-radius: 10px;
        overflow: hidden;
        border: 1.5px solid var(--rule);
        display: none;
    }

    .image-preview img {
        width: 100%;
        max-height: 220px;
        object-fit: cover;
        display: block;
    }

    /* DIVIDER */
    .form-divider {
        border: none;
        border-top: 1px solid var(--rule);
        margin: 28px 0;
    }

    /* ACTIONS */
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

    .btn-publish {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: var(--ink);
        color: white;
        font-size: 15px;
        font-weight: 600;
        padding: 13px 28px;
        border-radius: 10px;
        border: none;
        cursor: pointer;
        transition: opacity .15s;
    }

    .btn-publish:hover {
        opacity: .82;
    }

    /* TIPS */
    .tips {
        margin-top: 32px;
        background: var(--amber-dim);
        border: 1px solid #f0d080;
        border-radius: 14px;
        padding: 20px 24px;
    }

    .tips-title {
        font-size: 13px;
        font-weight: 600;
        color: #7a5200;
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .tips ul {
        list-style: none;
        padding: 0;
    }

    .tips li {
        font-size: 13px;
        color: #7a5200;
        line-height: 1.7;
        display: flex;
        align-items: flex-start;
        gap: 8px;
    }

    .tips li::before {
        content: '→';
        flex-shrink: 0;
        margin-top: 1px;
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
                Back to Dashboard
            </a>
        </div>
    </header>

    <main>
        <p class="page-eyebrow">New Post</p>
        <h1 class="page-title">Write something great</h1>
        <p class="page-subtitle">Share your thoughts, knowledge, or story with the world.</p>

        <div class="form-card">
            <form method="POST">

                <div class="field">
                    <label for="title">Post Title</label>
                    <input type="text" id="title" name="title" placeholder="Give your post a clear, catchy title…"
                        required>
                </div>

                <div class="field">
                    <label for="category">Category</label>
                    <div class="select-wrap">
                        <select id="category" name="category" required>
                            <option value="" disabled selected>Select a category</option>
                            <option>Technology</option>
                            <option>Food</option>
                            <option>Travel</option>
                            <option>Business</option>
                            <option>Lifestyle</option>
                            <option>Health</option>
                            <option>Education</option>
                        </select>
                    </div>
                </div>

                <div class="field">
                    <label for="image_url">Cover Image URL <span>(optional)</span></label>
                    <input type="text" id="image_url" name="image_url" placeholder="https://example.com/image.jpg"
                        oninput="previewImage(this.value)">
                    <div class="image-preview" id="imagePreview">
                        <img id="previewImg" src="" alt="Cover preview">
                    </div>
                </div>

                <div class="field">
                    <label for="content">Content</label>
                    <textarea id="content" name="content" placeholder="Start writing your post here…"
                        required></textarea>
                </div>

                <hr class="form-divider">

                <div class="form-actions">
                    <a href="dashboard.php" class="btn-cancel-form">Discard</a>
                    <button type="submit" class="btn-publish">
                        <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            stroke-width="2.5">
                            <path d="M5 12l5 5L20 7" />
                        </svg>
                        Publish Post
                    </button>
                </div>

            </form>
        </div>

        <div class="tips">
            <p class="tips-title">
                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="#7a5200" stroke-width="2">
                    <circle cx="12" cy="12" r="10" />
                    <path d="M12 16v-4M12 8h.01" />
                </svg>
                Writing tips
            </p>
            <ul>
                <li>Start with a strong opening sentence to hook your reader.</li>
                <li>Use short paragraphs to keep your content easy to scan.</li>
                <li>A high-quality cover image can significantly boost views.</li>
            </ul>
        </div>
    </main>

    <script>
    function previewImage(url) {
        const wrap = document.getElementById('imagePreview');
        const img = document.getElementById('previewImg');
        if (url.trim()) {
            img.src = url;
            wrap.style.display = 'block';
            img.onerror = () => {
                wrap.style.display = 'none';
            };
            img.onload = () => {
                wrap.style.display = 'block';
            };
        } else {
            wrap.style.display = 'none';
        }
    }
    </script>

</body>

</html>