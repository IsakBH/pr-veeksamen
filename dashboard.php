<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$db    = getDB();
$result = $db->query("SELECT id, username, email, created_at FROM users ORDER BY created_at DESC");
$users  = $result->fetch_all(MYSQLI_ASSOC);
$total  = count($users);
$db->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard — UserApp</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

  :root {
    --bg:       #0a0a0f;
    --surface:  #111118;
    --surface2: #16161f;
    --border:   #1e1e2e;
    --accent:   #7c6dfa;
    --accent2:  #e066ff;
    --text:     #e8e8f0;
    --muted:    #6b6b80;
    --success:  #3de8a0;
  }

  body {
    background: var(--bg);
    color: var(--text);
    font-family: 'DM Sans', sans-serif;
    min-height: 100vh;
    background-image:
      radial-gradient(ellipse 60% 40% at 0% 0%, rgba(124,109,250,0.1) 0%, transparent 50%),
      radial-gradient(ellipse 40% 30% at 100% 100%, rgba(224,102,255,0.08) 0%, transparent 50%);
  }

  /* NAV */
  nav {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1.25rem 2rem;
    border-bottom: 1px solid var(--border);
    background: rgba(17,17,24,0.8);
    backdrop-filter: blur(12px);
    position: sticky;
    top: 0;
    z-index: 10;
  }

  .logo {
    font-family: 'Syne', sans-serif;
    font-weight: 800;
    font-size: 1.4rem;
    background: linear-gradient(135deg, var(--accent), var(--accent2));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
  }

  .nav-right {
    display: flex;
    align-items: center;
    gap: 1.25rem;
  }

  .nav-user {
    font-size: 0.875rem;
    color: var(--muted);
  }

  .nav-user span {
    color: var(--text);
    font-weight: 500;
  }

  .btn-logout {
    background: transparent;
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 0.45rem 1rem;
    color: var(--muted);
    font-family: 'DM Sans', sans-serif;
    font-size: 0.85rem;
    cursor: pointer;
    transition: border-color 0.2s, color 0.2s;
    text-decoration: none;
    display: inline-block;
  }

  .btn-logout:hover { border-color: var(--accent); color: var(--text); }

  /* MAIN */
  main {
    max-width: 900px;
    margin: 0 auto;
    padding: 2.5rem 1.5rem;
    animation: fadeUp 0.5s ease both;
  }

  @keyframes fadeUp {
    from { opacity: 0; transform: translateY(16px); }
    to   { opacity: 1; transform: translateY(0); }
  }

  .page-header {
    margin-bottom: 2rem;
  }

  .page-title {
    font-family: 'Syne', sans-serif;
    font-weight: 800;
    font-size: 2rem;
    margin-bottom: 0.4rem;
  }

  .page-desc {
    color: var(--muted);
    font-size: 0.9rem;
  }

  /* STAT CARD */
  .stat-row {
    display: flex;
    gap: 1rem;
    margin-bottom: 2rem;
  }

  .stat-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 14px;
    padding: 1.25rem 1.5rem;
    flex: 1;
  }

  .stat-label {
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: var(--muted);
    margin-bottom: 0.4rem;
  }

  .stat-value {
    font-family: 'Syne', sans-serif;
    font-size: 2rem;
    font-weight: 800;
    background: linear-gradient(135deg, var(--accent), var(--accent2));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
  }

  /* TABLE */
  .table-wrap {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 16px;
    overflow: hidden;
  }

  .table-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid var(--border);
  }

  .table-title {
    font-family: 'Syne', sans-serif;
    font-weight: 700;
    font-size: 1rem;
  }

  .badge {
    background: rgba(124,109,250,0.15);
    color: var(--accent);
    border-radius: 20px;
    padding: 0.2rem 0.75rem;
    font-size: 0.8rem;
    font-weight: 500;
  }

  table {
    width: 100%;
    border-collapse: collapse;
  }

  thead th {
    text-align: left;
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: var(--muted);
    padding: 0.85rem 1.5rem;
    border-bottom: 1px solid var(--border);
    font-weight: 500;
  }

  tbody tr {
    transition: background 0.15s;
  }

  tbody tr:hover { background: var(--surface2); }

  tbody tr + tr td { border-top: 1px solid var(--border); }

  td {
    padding: 1rem 1.5rem;
    font-size: 0.9rem;
    vertical-align: middle;
  }

  .user-id {
    color: var(--muted);
    font-size: 0.8rem;
    font-weight: 500;
  }

  .user-name {
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 0.6rem;
  }

  .avatar {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--accent), var(--accent2));
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: 'Syne', sans-serif;
    font-weight: 700;
    font-size: 0.75rem;
    color: #fff;
    flex-shrink: 0;
  }

  .you-badge {
    background: rgba(61,232,160,0.1);
    color: var(--success);
    border-radius: 20px;
    padding: 0.1rem 0.5rem;
    font-size: 0.7rem;
    font-weight: 600;
    letter-spacing: 0.05em;
  }

  .email { color: var(--muted); }

  .date { color: var(--muted); font-size: 0.82rem; }

  .empty {
    text-align: center;
    padding: 3rem;
    color: var(--muted);
  }
</style>
</head>
<body>

<nav>
  <div class="logo">UserApp</div>
  <div class="nav-right">
    <div class="nav-user">Signed in as <span><?= htmlspecialchars($_SESSION['username']) ?></span></div>
    <a href="logout.php" class="btn-logout">Log out</a>
  </div>
</nav>

<main>
  <div class="page-header">
    <h1 class="page-title">User Directory</h1>
    <p class="page-desc">All registered accounts on this platform.</p>
  </div>

  <div class="stat-row">
    <div class="stat-card">
      <div class="stat-label">Total Users</div>
      <div class="stat-value"><?= $total ?></div>
    </div>
  </div>

  <div class="table-wrap">
    <div class="table-header">
      <div class="table-title">All Users</div>
      <div class="badge"><?= $total ?> total</div>
    </div>

    <?php if (empty($users)): ?>
      <div class="empty">No users found.</div>
    <?php else: ?>
    <table>
      <thead>
        <tr>
          <th>#</th>
          <th>User</th>
          <th>Email</th>
          <th>Joined</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($users as $u): ?>
        <tr>
          <td><span class="user-id"><?= $u['id'] ?></span></td>
          <td>
            <div class="user-name">
              <div class="avatar"><?= strtoupper(substr($u['username'], 0, 1)) ?></div>
              <?= htmlspecialchars($u['username']) ?>
              <?php if ($u['id'] == $_SESSION['user_id']): ?>
                <span class="you-badge">YOU</span>
              <?php endif; ?>
            </div>
          </td>
          <td><span class="email"><?= htmlspecialchars($u['email']) ?></span></td>
          <td><span class="date"><?= date('M j, Y', strtotime($u['created_at'])) ?></span></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>
</main>

</body>
</html>
