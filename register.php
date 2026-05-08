<?php
session_start();
require_once 'config.php';

if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    if (empty($username) || empty($email) || empty($password)) {
        $error = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email address.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        $db   = getDB();
        $stmt = $db->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = 'Username or email already taken.';
        } else {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $ins  = $db->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            $ins->bind_param("sss", $username, $email, $hash);
            if ($ins->execute()) {
                $success = 'Account created! You can now log in.';
            } else {
                $error = 'Registration failed. Please try again.';
            }
            $ins->close();
        }
        $stmt->close();
        $db->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Register — UserApp</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

  :root {
    --bg:       #0a0a0f;
    --surface:  #111118;
    --border:   #1e1e2e;
    --accent:   #7c6dfa;
    --accent2:  #e066ff;
    --text:     #e8e8f0;
    --muted:    #6b6b80;
    --error:    #ff5c7a;
    --success:  #3de8a0;
  }

  body {
    background: var(--bg);
    color: var(--text);
    font-family: 'DM Sans', sans-serif;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2rem;
    background-image:
      radial-gradient(ellipse 80% 50% at 20% -20%, rgba(124,109,250,0.15) 0%, transparent 60%),
      radial-gradient(ellipse 60% 40% at 80% 110%, rgba(224,102,255,0.1) 0%, transparent 60%);
  }

  .card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 20px;
    padding: 2.5rem;
    width: 100%;
    max-width: 420px;
    box-shadow: 0 0 60px rgba(124,109,250,0.08);
    animation: fadeUp 0.5s ease both;
  }

  @keyframes fadeUp {
    from { opacity: 0; transform: translateY(20px); }
    to   { opacity: 1; transform: translateY(0); }
  }

  .logo {
    font-family: 'Syne', sans-serif;
    font-weight: 800;
    font-size: 1.6rem;
    background: linear-gradient(135deg, var(--accent), var(--accent2));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin-bottom: 0.25rem;
  }

  .subtitle {
    color: var(--muted);
    font-size: 0.9rem;
    margin-bottom: 2rem;
  }

  .form-group {
    margin-bottom: 1.1rem;
  }

  label {
    display: block;
    font-size: 0.8rem;
    font-weight: 500;
    color: var(--muted);
    text-transform: uppercase;
    letter-spacing: 0.08em;
    margin-bottom: 0.4rem;
  }

  input {
    width: 100%;
    background: var(--bg);
    border: 1px solid var(--border);
    border-radius: 10px;
    padding: 0.75rem 1rem;
    color: var(--text);
    font-family: 'DM Sans', sans-serif;
    font-size: 0.95rem;
    transition: border-color 0.2s, box-shadow 0.2s;
    outline: none;
  }

  input:focus {
    border-color: var(--accent);
    box-shadow: 0 0 0 3px rgba(124,109,250,0.15);
  }

  .btn {
    width: 100%;
    padding: 0.85rem;
    background: linear-gradient(135deg, var(--accent), var(--accent2));
    border: none;
    border-radius: 10px;
    color: #fff;
    font-family: 'Syne', sans-serif;
    font-weight: 700;
    font-size: 1rem;
    cursor: pointer;
    margin-top: 0.5rem;
    transition: opacity 0.2s, transform 0.15s;
    letter-spacing: 0.03em;
  }

  .btn:hover { opacity: 0.9; transform: translateY(-1px); }
  .btn:active { transform: translateY(0); }

  .alert {
    padding: 0.75rem 1rem;
    border-radius: 10px;
    font-size: 0.875rem;
    margin-bottom: 1.25rem;
  }

  .alert-error   { background: rgba(255,92,122,0.1); border: 1px solid rgba(255,92,122,0.3); color: var(--error); }
  .alert-success { background: rgba(61,232,160,0.1); border: 1px solid rgba(61,232,160,0.3); color: var(--success); }

  .footer-link {
    text-align: center;
    margin-top: 1.5rem;
    font-size: 0.875rem;
    color: var(--muted);
  }

  .footer-link a {
    color: var(--accent);
    text-decoration: none;
    font-weight: 500;
  }

  .footer-link a:hover { text-decoration: underline; }
</style>
</head>
<body>
<div class="card">
  <div class="logo">UserApp</div>
  <div class="subtitle">Create your account</div>

  <?php if ($error): ?>
    <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>
  <?php if ($success): ?>
    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
  <?php endif; ?>

  <form method="POST">
    <div class="form-group">
      <label>Username</label>
      <input type="text" name="username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" placeholder="johndoe" required>
    </div>
    <div class="form-group">
      <label>Email</label>
      <input type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" placeholder="john@example.com" required>
    </div>
    <div class="form-group">
      <label>Password</label>
      <input type="password" name="password" placeholder="Min. 6 characters" required>
    </div>
    <div class="form-group">
      <label>Confirm Password</label>
      <input type="password" name="confirm_password" placeholder="Repeat password" required>
    </div>
    <button type="submit" class="btn">Create Account</button>
  </form>

  <div class="footer-link">
    Already have an account? <a href="login.php">Sign in</a>
  </div>
</div>
</body>
</html>
