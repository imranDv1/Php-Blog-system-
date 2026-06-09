<?php
session_start();
require "config/db.php";

if (!isset($_SESSION["user"])) {
    header("Location: auth/login.php");
    exit;
}

$user = $_SESSION["user"];

$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user['id']);
$stmt->execute();
$currentUser = $stmt->get_result()->fetch_assoc();

$message = "";
$messageType = "error";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name     = trim($_POST['name']);
    $username = trim($_POST['username']);
    $email    = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm  = trim($_POST['confirm_password']);
    $image_url = trim($_POST['image_url']);

    if ($name === "" || $username === "" || $email === "") {
        $message = "Name, username, and email are required.";
    } elseif (!empty($password) && $password !== $confirm) {
        $message = "Passwords do not match.";
    } else {
        if (!empty($password)) {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET name=?, username=?, email=?, password=?, image_url=? WHERE id=?");
            $stmt->bind_param("sssssi", $name, $username, $email, $hashed, $image_url, $user['id']);
        } else {
            $stmt = $conn->prepare("UPDATE users SET name=?, username=?, email=?, image_url=? WHERE id=?");
            $stmt->bind_param("ssssi", $name, $username, $email, $image_url, $user['id']);
        }

        if ($stmt->execute()) {
            $_SESSION["user"]["name"]      = $name;
            $_SESSION["user"]["username"]  = $username;
            $_SESSION["user"]["email"]     = $email;
            $_SESSION["user"]["image_url"] = $image_url;
            header("Location: user-profile.php?id=" . $user['id']);
            exit;
        } else {
            $message = "Update failed. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BS-Blog · Edit Profile</title>
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

    /* ── HEADER ── */
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

    .back-link {
        margin-left: auto;
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
        transition: border-color .15s, color .15s;
    }

    .back-link:hover {
        border-color: var(--ink);
        color: var(--ink);
    }

    /* ── MAIN ── */
    main {
        max-width: 600px;
        margin: 0 auto;
        padding: 40px 24px 80px;
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
        font-size: 30px;
        letter-spacing: -0.01em;
        margin-bottom: 6px;
    }

    .page-sub {
        font-size: 14px;
        color: var(--ink-soft);
        margin-bottom: 32px;
    }

    /* ── ALERT ── */
    .alert {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 14px;
        padding: 12px 16px;
        border-radius: 10px;
        margin-bottom: 24px;
        background: #fff5f5;
        color: #c0392b;
        border: 1px solid #fad9d5;
    }

    /* ── CARD ── */
    .card {
        background: var(--card);
        border: 1px solid var(--rule);
        border-radius: 20px;
        overflow: hidden;
    }

    /* ── AVATAR SECTION ── */
    .avatar-section {
        display: flex;
        align-items: center;
        gap: 20px;
        padding: 28px 28px 24px;
        border-bottom: 1px solid var(--rule);
    }

    .avatar-wrap {
        position: relative;
        flex-shrink: 0;
    }

    .avatar {
        width: 72px;
        height: 72px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid var(--rule);
        display: block;
    }

    .avatar-info p {
        font-family: var(--serif);
        font-size: 18px;
        margin-bottom: 4px;
    }

    .avatar-info span {
        font-size: 12px;
        color: var(--ink-faint);
    }

    /* ── FORM ── */
    .form-body {
        padding: 28px;
    }

    .form-section {
        margin-bottom: 28px;
    }

    .form-section-label {
        font-size: 11px;
        font-weight: 600;
        letter-spacing: .1em;
        text-transform: uppercase;
        color: var(--ink-faint);
        margin-bottom: 14px;
    }

    .row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 14px;
    }

    @media (max-width: 500px) {
        .row {
            grid-template-columns: 1fr;
        }
    }

    .field {
        margin-bottom: 16px;
    }

    .field:last-child {
        margin-bottom: 0;
    }

    .field label {
        display: block;
        font-size: 12px;
        font-weight: 600;
        color: var(--ink-soft);
        margin-bottom: 6px;
        letter-spacing: .01em;
    }

    .field input {
        width: 100%;
        background: var(--surface);
        border: 1.5px solid var(--rule);
        border-radius: 10px;
        padding: 11px 14px;
        font-family: var(--sans);
        font-size: 14px;
        color: var(--ink);
        outline: none;
        transition: border-color .15s, background .15s;
    }

    .field input:focus {
        border-color: var(--ink);
        background: white;
    }

    .field input::placeholder {
        color: var(--ink-faint);
    }

    .divider {
        border: none;
        border-top: 1px solid var(--rule);
        margin: 24px 0;
    }

    .hint {
        font-size: 12px;
        color: var(--ink-faint);
        margin-top: -8px;
        margin-bottom: 16px;
        line-height: 1.5;
    }

    .password-wrap {
        position: relative;
    }

    .password-wrap input {
        padding-right: 44px;
    }

    .pwd-toggle {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        cursor: pointer;
        color: var(--ink-faint);
        display: flex;
        align-items: center;
        padding: 4px;
        transition: color .15s;
    }

    .pwd-toggle:hover {
        color: var(--ink);
    }

    /* ── FOOTER ── */
    .form-footer {
        display: flex;
        gap: 10px;
        padding-top: 4px;
    }

    .btn-cancel {
        flex: 1;
        font-size: 14px;
        font-weight: 500;
        padding: 12px 0;
        border-radius: 10px;
        background: var(--surface);
        border: 1.5px solid var(--rule);
        cursor: pointer;
        color: var(--ink-soft);
        text-decoration: none;
        text-align: center;
        transition: background .15s, border-color .15s;
    }

    .btn-cancel:hover {
        background: var(--rule);
        border-color: var(--ink-faint);
    }

    .btn-save {
        flex: 2;
        font-size: 14px;
        font-weight: 600;
        padding: 12px 0;
        border-radius: 10px;
        background: var(--ink);
        color: white;
        border: none;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        transition: opacity .15s;
    }

    .btn-save:hover {
        opacity: .85;
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
            <a href="user-profile.php?id=<?php echo $user['id']; ?>" class="back-link">
                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path d="M19 12H5M12 5l-7 7 7 7" />
                </svg>
                Back to profile
            </a>
        </div>
    </header>

    <!-- MAIN -->
    <main>

        <p class="page-eyebrow">Account</p>
        <h1 class="page-title">Edit Profile</h1>
        <p class="page-sub">Update your personal information and account settings.</p>

        <?php if ($message): ?>
        <div class="alert">
            <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10" />
                <path d="M12 8v4M12 16h.01" />
            </svg>
            <?php echo htmlspecialchars($message); ?>
        </div>
        <?php endif; ?>

        <div class="card">

            <!-- AVATAR PREVIEW -->
            <div class="avatar-section">
                <div class="avatar-wrap">
                    <img id="avatarPreview" class="avatar"
                        src="<?php echo !empty($currentUser['image_url'])
                        ? htmlspecialchars($currentUser['image_url'])
                        : 'https://ui-avatars.com/api/?name=' . urlencode($currentUser['name']) . '&background=0f0f0f&color=fff'; ?>"
                        alt="<?php echo htmlspecialchars($currentUser['name']); ?>">
                </div>
                <div class="avatar-info">
                    <p><?php echo htmlspecialchars($currentUser['name']); ?></p>
                    <span>@<?php echo htmlspecialchars($currentUser['username']); ?> · Paste a URL below to update your
                        photo</span>
                </div>
            </div>

            <form method="POST" class="form-body">

                <!-- PERSONAL INFO -->
                <div class="form-section">
                    <div class="form-section-label">Personal info</div>
                    <div class="row">
                        <div class="field">
                            <label for="name">Full name</label>
                            <input type="text" id="name" name="name"
                                value="<?php echo htmlspecialchars($currentUser['name']); ?>" placeholder="Your name"
                                required>
                        </div>
                        <div class="field">
                            <label for="username">Username</label>
                            <input type="text" id="username" name="username"
                                value="<?php echo htmlspecialchars($currentUser['username']); ?>" placeholder="handle"
                                required>
                        </div>
                    </div>
                    <div class="field">
                        <label for="email">Email address</label>
                        <input type="email" id="email" name="email"
                            value="<?php echo htmlspecialchars($currentUser['email']); ?>" placeholder="you@example.com"
                            required>
                    </div>
                    <div class="field">
                        <label for="image_url">Profile image URL</label>
                        <input type="text" id="image_url" name="image_url"
                            value="<?php echo htmlspecialchars($currentUser['image_url'] ?? ''); ?>"
                            placeholder="https://…">
                    </div>
                </div>

                <hr class="divider">

                <!-- CHANGE PASSWORD -->
                <div class="form-section">
                    <div class="form-section-label">Change password</div>
                    <p class="hint">Leave both fields blank to keep your current password.</p>
                    <div class="row">
                        <div class="field">
                            <label for="password">New password</label>
                            <div class="password-wrap">
                                <input type="password" id="password" name="password" placeholder="••••••••">
                                <button type="button" class="pwd-toggle" onclick="togglePwd('password', this)"
                                    aria-label="Toggle password visibility">
                                    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                        stroke-width="2">
                                        <path d="M1 12S5 4 12 4s11 8 11 8-4 8-11 8S1 12 1 12z" />
                                        <circle cx="12" cy="12" r="3" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <div class="field">
                            <label for="confirm_password">Confirm password</label>
                            <div class="password-wrap">
                                <input type="password" id="confirm_password" name="confirm_password"
                                    placeholder="••••••••">
                                <button type="button" class="pwd-toggle" onclick="togglePwd('confirm_password', this)"
                                    aria-label="Toggle password visibility">
                                    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                        stroke-width="2">
                                        <path d="M1 12S5 4 12 4s11 8 11 8-4 8-11 8S1 12 1 12z" />
                                        <circle cx="12" cy="12" r="3" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ACTIONS -->
                <div class="form-footer">
                    <a href="user-profile.php?id=<?php echo $user['id']; ?>" class="btn-cancel">Cancel</a>
                    <button type="submit" class="btn-save">
                        <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            stroke-width="2.5">
                            <path d="M20 6 9 17l-5-5" />
                        </svg>
                        Save changes
                    </button>
                </div>

            </form>
        </div>

    </main>

    <script>
    // Live avatar preview when image URL changes
    document.getElementById('image_url').addEventListener('input', function() {
        const url = this.value.trim();
        const img = document.getElementById('avatarPreview');
        if (url) {
            img.src = url;
            img.onerror = () => img.src =
                'https://ui-avatars.com/api/?name=<?php echo urlencode($currentUser['name']); ?>&background=0f0f0f&color=fff';
        }
    });

    function togglePwd(fieldId, btn) {
        const input = document.getElementById(fieldId);
        const isHidden = input.type === 'password';
        input.type = isHidden ? 'text' : 'password';
        btn.querySelector('svg').style.opacity = isHidden ? '1' : '0.4';
    }
    </script>

</body>

</html>