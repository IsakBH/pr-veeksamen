<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
$conn = getDB();
$innlogget_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === "POST") {
    $telefonnummer = $_POST['telefonnummer'];
    $adresse = $_POST['adresse'];

    $sql = "INSERT INTO kontaktinformasjon (telefonnummer, adresse, bruker_id)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE telefonnummer = ?, adresse = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("isiis", $telefonnummer, $adresse, $innlogget_id, $telefonnummer, $adresse);
    if($stmt->execute()){
        echo "Woohoo! Det funket!";
        /*header('Location: dashboard.php');
        exit;*/
    } else {
        echo "Oops";
    }
}

?>

<!DOCTYPE html>
<html lang=no>

<head>

</head>

<body>
    <form action="addkontaktinformasjon.php" method="post">
        <label>Telefonnummer</label> <br>
        <input type="text" name="telefonnummer" placeholder="45848234"> <br> <br>

        <label>Adresse</label> <br>
        <input type="text" name="adresse" placeholder="Loddefjordveien 24"> <br><br>

        <input type="submit">
    </form>
</body>

</html>