<?php
session_start();
include("../config/db.php");

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $input    = trim($_POST["email"]);
    $password = $_POST["password"];

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? OR username = ?");
    $stmt->bind_param("ss", $input, $input);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if ($user && password_verify($password, $user["password"])) {
        $_SESSION["user"] = $user;
        header("Location: ../dashboard.php");
        exit;
    } else {
        $error = "Invalid credentials. Please check your email/username and password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BS-Blog · Sign In</title>
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
        --serif: 'Instrument Serif', Georgia, serif;
        --sans: 'Inter', system-ui, sans-serif;
    }

    body {
        font-family: var(--sans);
        background: var(--surface);
        color: var(--ink);
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 24px;
    }

    .auth-card {
        background: var(--card);
        border: 1px solid var(--rule);
        border-radius: 20px;
        overflow: hidden;
        width: 100%;
        max-width: 400px;
    }

    .auth-top {
        background: var(--ink);
        padding: 32px 32px 28px;
        position: relative;
        overflow: hidden;
    }

    .auth-top::before {
        content: '';
        position: absolute;
        inset: 0;
        background: radial-gradient(ellipse 80% 100% at 100% 0%, rgba(232, 160, 32, .2) 0%, transparent 60%);
        pointer-events: none;
    }

    .auth-logo {
        display: flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
        margin-bottom: 24px;
    }

    .auth-logo-mark {
        width: 32px;
        height: 32px;
        background: rgba(255, 255, 255, .12);
        border-radius: 9px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-family: var(--serif);
        font-size: 13px;
        color: white;
        border: 1px solid rgba(255, 255, 255, .15);
    }

    .auth-logo-text {
        font-family: var(--serif);
        font-size: 17px;
        color: white;
    }

    .auth-top h1 {
        font-family: var(--serif);
        font-size: 26px;
        color: white;
        margin-bottom: 6px;
        position: relative;
    }

    .auth-top p {
        font-size: 13.5px;
        color: rgba(255, 255, 255, .5);
        position: relative;
    }

    .auth-body {
        padding: 28px 28px 24px;
    }

    .alert {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        font-size: 13.5px;
        padding: 12px 14px;
        border-radius: 10px;
        margin-bottom: 20px;
        background: #fff5f5;
        color: #c0392b;
        border: 1px solid #fad9d5;
        line-height: 1.4;
    }

    .alert svg {
        flex-shrink: 0;
        margin-top: 1px;
    }

    .field {
        margin-bottom: 16px;
    }

    .field label {
        display: block;
        font-size: 11px;
        font-weight: 600;
        letter-spacing: .06em;
        text-transform: uppercase;
        color: var(--ink-soft);
        margin-bottom: 7px;
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

    .pwd-row {
        position: relative;
    }

    .pwd-row input {
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
        padding: 4px;
        transition: color .15s;
    }

    .pwd-toggle:hover {
        color: var(--ink);
    }

    .btn-primary {
        width: 100%;
        background: var(--ink);
        color: white;
        border: none;
        border-radius: 10px;
        padding: 12px;
        font-family: var(--sans);
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        transition: opacity .15s;
        margin-top: 4px;
    }

    .btn-primary:hover {
        opacity: .85;
    }

    .auth-footer {
        text-align: center;
        font-size: 13.5px;
        color: var(--ink-soft);
        margin-top: 18px;
    }

    .auth-footer a {
        color: var(--amber);
        font-weight: 600;
        text-decoration: none;
    }

    .auth-footer a:hover {
        text-decoration: underline;
    }
    </style>
</head>

<body>

    <div class="auth-card">

        <div class="auth-top">
            <a href="../index.php" class="auth-logo">
                <div class="auth-logo-mark">BS</div>
                <span class="auth-logo-text">BS·Blog</span>
            </a>
            <h1>Welcome back</h1>
            <p>Sign in to continue writing</p>
        </div>

        <div class="auth-body">

            <?php if ($error): ?>
            <div class="alert">
                <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10" />
                    <path d="M12 8v4M12 16h.01" />
                </svg>
                <?php echo htmlspecialchars($error); ?>
            </div>
            <?php endif; ?>

            <form method="POST">
                <div class="field">
                    <label for="email">Email or username</label>
                    <input type="text" id="email" name="email"
                        value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" placeholder="you@example.com"
                        required autofocus>
                </div>
                <div class="field">
                    <label for="password">Password</label>
                    <div class="pwd-row">
                        <input type="password" id="password" name="password" placeholder="••••••••" required>
                        <button type="button" class="pwd-toggle" onclick="togglePwd('password', this)"
                            aria-label="Toggle password">
                            <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="2">
                                <path d="M1 12S5 4 12 4s11 8 11 8-4 8-11 8S1 12 1 12z" />
                                <circle cx="12" cy="12" r="3" />
                            </svg>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn-primary">
                    <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                        stroke-width="2.5">
                        <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4M10 17l5-5-5-5M15 12H3" />
                    </svg>
                    Sign in
                </button>
            </form>

            <p class="auth-footer">No account? <a href="register.php">Create one</a></p>

        </div>
    </div>

    <script>
    function togglePwd(id, btn) {
        const input = document.getElementById(id);
        input.type = input.type === 'password' ? 'text' : 'password';
        btn.style.opacity = input.type === 'text' ? '1' : '0.5';
    }
    </script>

</body>

</html>