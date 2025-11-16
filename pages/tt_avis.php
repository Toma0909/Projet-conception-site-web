<?php
session_start();
require_once '../config/param.inc.php';

// Vérifier que l'utilisateur est connecté et est un client
if (!isset($_SESSION['connecte']) || !$_SESSION['connecte'] || $_SESSION['role'] != 1) {
    $_SESSION['erreur'] = "Vous devez être connecté en tant que client.";
    header("Location: ../auth/connexion.php");
    exit();
}

// Vérifier les données POST
if (!isset($_POST['demenagement_id'], $_POST['demenageur_id'], $_POST['note'], $_POST['action'])) {
    $_SESSION['erreur'] = "Données manquantes.";
    header("Location: mes_demenagements.php");
    exit();
}

$demenagement_id = intval($_POST['demenagement_id']);
$demenageur_id = intval($_POST['demenageur_id']);
$note = intval($_POST['note']);
$commentaire = isset($_POST['commentaire']) ? trim($_POST['commentaire']) : '';
$action = $_POST['action'];
$user_id = $_SESSION['user_id'];

// Valider la note
if ($note < 1 || $note > 5) {
    $_SESSION['erreur'] = "La note doit être entre 1 et 5.";
    header("Location: noter_demenageurs.php?id=" . $demenagement_id);
    exit();
}

// Connexion à la base de données
$mysqli = new mysqli($host, $login, $passwd, $dbname);
if ($mysqli->connect_error) {
    die("Erreur de connexion à la base de données : " . $mysqli->connect_error);
}
$mysqli->set_charset("utf8");

// Vérifier que le déménagement appartient au client et est terminé
$verif_query = "SELECT d.id 
                FROM demenagement d
                JOIN demenagement_demenageur dd ON d.id = dd.demenagement_id
                WHERE d.id = ? AND d.client_id = ? AND d.statut = 'termine' 
                AND dd.demenageur_id = ?";
$verif_stmt = $mysqli->prepare($verif_query);
$verif_stmt->bind_param("iii", $demenagement_id, $user_id, $demenageur_id);
$verif_stmt->execute();
$verif_result = $verif_stmt->get_result();

if ($verif_result->num_rows == 0) {
    $_SESSION['erreur'] = "Vous ne pouvez pas noter ce déménageur.";
    $verif_stmt->close();
    $mysqli->close();
    header("Location: mes_demenagements.php");
    exit();
}
$verif_stmt->close();

if ($action == 'ajouter') {
    // Vérifier si un avis n'existe pas déjà
    $check_query = "SELECT id FROM avis WHERE demenagement_id = ? AND demenageur_id = ?";
    $check_stmt = $mysqli->prepare($check_query);
    $check_stmt->bind_param("ii", $demenagement_id, $demenageur_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        $_SESSION['erreur'] = "Vous avez déjà noté ce déménageur.";
        $check_stmt->close();
        $mysqli->close();
        header("Location: noter_demenageurs.php?id=" . $demenagement_id);
        exit();
    }
    $check_stmt->close();
    
    // Insérer le nouvel avis
    $insert_query = "INSERT INTO avis (demenagement_id, demenageur_id, client_id, note, commentaire) 
                     VALUES (?, ?, ?, ?, ?)";
    $insert_stmt = $mysqli->prepare($insert_query);
    $insert_stmt->bind_param("iiiis", $demenagement_id, $demenageur_id, $user_id, $note, $commentaire);
    
    if ($insert_stmt->execute()) {
        $_SESSION['succes'] = "Votre avis a été enregistré avec succès.";
    } else {
        $_SESSION['erreur'] = "Erreur lors de l'enregistrement de l'avis : " . $mysqli->error;
    }
    $insert_stmt->close();
    
} elseif ($action == 'modifier') {
    // Mettre à jour l'avis existant
    $update_query = "UPDATE avis SET note = ?, commentaire = ? 
                     WHERE demenagement_id = ? AND demenageur_id = ? AND client_id = ?";
    $update_stmt = $mysqli->prepare($update_query);
    $update_stmt->bind_param("isiii", $note, $commentaire, $demenagement_id, $demenageur_id, $user_id);
    
    if ($update_stmt->execute()) {
        $_SESSION['succes'] = "Votre avis a été modifié avec succès.";
    } else {
        $_SESSION['erreur'] = "Erreur lors de la modification de l'avis : " . $mysqli->error;
    }
    $update_stmt->close();
}

$mysqli->close();
header("Location: noter_demenageurs.php?id=" . $demenagement_id);
exit();
?>
