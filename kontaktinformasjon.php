<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
$conn = getDB();
$innlogget_id = $_SESSION['user_id'];

// 1. Hent rollen til brukeren som er logget inn
$role_sql = "SELECT r.navn FROM roller r
             JOIN bruker_rolle br ON r.id = br.rolle_id
             WHERE br.bruker_id = ?";
$role_stmt = $conn->prepare($role_sql);
$role_stmt->bind_param("i", $innlogget_id);
$role_stmt->execute();
$role_result = $role_stmt->get_result();
$user_role = $role_result->fetch_assoc()['navn'] ?? 'Ingen';

// 2. Bestem SQL-spørring basert på rettigheter
if ($user_role === 'Admin') {
    // Admin: Se alt (Full lese-tilgang)
    $sql = "SELECT u.id, k.navn, k.adresse, k.telefonnummer, d.modell, d.serienummer
            FROM users u
            LEFT JOIN kontaktinformasjon k ON u.id = k.bruker_id
            LEFT JOIN datamaskiner d ON u.id = d.disponert_til";
    $result = $conn->query($sql);
}
elseif ($user_role === 'IT-medarbeider') {
    // IT: Se kun navn og maskinvare (Skjult kontaktinfo)
    $sql = "SELECT u.id, k.navn, 'SKJERMET' AS adresse, 'SKJERMET' AS telefonnummer, d.modell, d.serienummer
            FROM users u
            LEFT JOIN kontaktinformasjon k ON u.id = k.bruker_id
            LEFT JOIN datamaskiner d ON u.id = d.disponert_til";
    $result = $conn->query($sql);
}
else {
    // Vanlig bruker: Se kun sin egen info
    $sql = "SELECT u.id, k.navn, k.adresse, k.telefonnummer, d.modell, d.serienummer
            FROM users u
            LEFT JOIN kontaktinformasjon k ON u.id = k.bruker_id
            LEFT JOIN datamaskiner d ON u.id = d.disponert_til
            WHERE u.id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $innlogget_id);
    $stmt->execute();
    $result = $stmt->get_result();
}

// 3. HTML-visning
echo "<h1>Kontaktinformasjon</h1>";
echo "<p>Innlogget som: <strong>" . htmlspecialchars($user_role) . "</strong></p>";

echo "<table border='1'>
        <tr>
            <th>Navn</th>
            <th>Adresse</th>
            <th>Telefon</th>
            <th>PC Modell</th>";

// Skrive-rettighet: Vis kolonne kun for Admin
if ($user_role === 'Admin') {
    echo "<th>Handling (Skrive-tilgang)</th>";
}

echo "</tr>";

while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($row['navn'] ?? 'Ikke registrert') . "</td>";
    echo "<td>" . htmlspecialchars($row['adresse']) . "</td>";
    echo "<td>" . htmlspecialchars($row['telefonnummer']) . "</td>";
    echo "<td>" . htmlspecialchars($row['modell'] ?? 'Ingen PC') . "</td>";

    // Skrive-rettighet: Vis knapper kun for Admin
    if ($user_role === 'Admin') {
        echo "<td>
                <a href='edit.php?id=" . $row['id'] . "'>Rediger</a> |
                <a href='delete.php?id=" . $row['id'] . "' onclick='return confirm(\"Sikker?\")'>Slett</a>
              </td>";
    }
    echo "</tr>";
}
echo "</table>";

$conn->close();