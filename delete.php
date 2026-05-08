<?php
require_once 'config.php';

$id = $_GET['id'];
echo $id;

$sql = "delete from users where id = ?;";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $id);
if($stmt->execute()){
    header('Location: kontaktinformasjon.php');
} else {
    echo "oops";
}