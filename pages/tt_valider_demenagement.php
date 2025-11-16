<?php
session_start();
require_once '../config/param.inc.php';

// Vérifier que l'utilisateur est connecté et est un client
if (!isset($_SESSION['connecte']) || !$_SESSION['connecte'] || $_SESSION['role'] != 1) {
    $_SESSION['erreur'] = "Vous devez être connecté en tant que client pour valider un déménagement.";
    header("Location: ../auth/connexion.php");
    exit();
}

// Vérifier que l'ID du déménagement est fourni
if (!isset($_POST['demenagement_id']) || empty($_POST['demenagement_id'])) {
    $_SESSION['erreur'] = "ID de déménagement manquant.";
    header("Location: mes_demenagements.php");
    exit();
}

$demenagement_id = intval($_POST['demenagement_id']);
$user_id = $_SESSION['user_id'];

// Connexion à la base de données
$mysqli = new mysqli($host, $login, $passwd, $dbname);
if ($mysqli->connect_error) {
    die("Erreur de connexion à la base de données : " . $mysqli->connect_error);
}
$mysqli->set_charset("utf8");

// Vérifier que le déménagement appartient bien au client connecté
$query = "SELECT d.*, COUNT(dd.demenageur_id) as nb_acceptees 
          FROM demenagement d
          LEFT JOIN demenagement_demenageur dd ON d.id = dd.demenagement_id
          WHERE d.id = ? AND d.client_id = ?
          GROUP BY d.id";

$stmt = $mysqli->prepare($query);
$stmt->bind_param("ii", $demenagement_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    $_SESSION['erreur'] = "Déménagement introuvable ou vous n'avez pas les droits pour le modifier.";
    $stmt->close();
    $mysqli->close();
    header("Location: mes_demenagements.php");
    exit();
}

$demenagement = $result->fetch_assoc();
$stmt->close();

// Vérifier que le déménagement est bien en cours
if ($demenagement['statut'] != 'en_cours') {
    $_SESSION['erreur'] = "Seuls les déménagements en cours peuvent être marqués comme terminés.";
    $mysqli->close();
    header("Location: mes_demenagements.php");
    exit();
}

// Mettre à jour le statut du déménagement
$update_query = "UPDATE demenagement SET statut = 'termine' WHERE id = ?";
$update_stmt = $mysqli->prepare($update_query);
$update_stmt->bind_param("i", $demenagement_id);

if ($update_stmt->execute()) {
    $_SESSION['succes'] = "Le déménagement a été marqué comme terminé avec succès.";
} else {
    $_SESSION['erreur'] = "Erreur lors de la mise à jour du statut : " . $mysqli->error;
}

$update_stmt->close();
$mysqli->close();

header("Location: mes_demenagements.php");
exit();
?>
