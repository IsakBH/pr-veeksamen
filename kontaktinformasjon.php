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

$db = getDB();
$sql = "select * from users where id = ?";
$stmt = $db->prepare($sql);
$stmt = $stmt->bind_param('i', $_SESSION['user_id']);
$stmt->execute();

?>
<!DOCTYPE html>
    <html lang="en">
    <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — UserApp</title>
</head>
<body>

</body>
</html>
