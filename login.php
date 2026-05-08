<?php
session_start();
require_once 'config.php';

if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login    = trim($_POST['login'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($login) || empty($password)) {
        $error = 'Please fill in all fields.';
    } else {
        $db   = getDB();
        $stmt = $db->prepare("SELECT id, username, password FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $login, $login);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            if (password_verify($password, $row['password'])) {
                $_SESSION['user_id']  = $row['id'];
                $_SESSION['username'] = $row['username'];
                header('Location: dashboard.php');
                exit;
            }
        }
        $error = 'Invalid username/email or password.';
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
<title>Login — UserApp</title>
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
      radial-gradient(ellipse 80% 50% at 80% -20%, rgba(224,102,255,0.12) 0%, transparent 60%),
      radial-gradient(ellipse 60% 40% at 10% 110%, rgba(124,109,250,0.1) 0%, transparent 60%);
  }

  .card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 20px;
    padding: 2.5rem;
    width: 100%;
    max-width: 400px;
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

  .form-group { margin-bottom: 1.1rem; }

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

  .alert-error {
    background: rgba(255,92,122,0.1);
    border: 1px solid rgba(255,92,122,0.3);
    color: var(--error);
    padding: 0.75rem 1rem;
    border-radius: 10px;
    font-size: 0.875rem;
    margin-bottom: 1.25rem;
  }

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
  <div class="subtitle">Welcome back</div>

  <?php if ($error): ?>
    <div class="alert-error"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form method="POST">
    <div class="form-group">
      <label>Username or Email</label>
      <input type="text" name="login" value="<?= htmlspecialchars($_POST['login'] ?? '') ?>" placeholder="johndoe or john@example.com" required>
    </div>
    <div class="form-group">
      <label>Password</label>
      <input type="password" name="password" placeholder="Your password" required>
    </div>
    <button type="submit" class="btn">Sign In</button>
  </form>

  <div class="footer-link">
    No account yet? <a href="register.php">Register</a>
  </div>
</div>
</body>
</html>
