<?php
  session_start();
  
  // Vérifier si l'utilisateur est connecté et est un client
  if (!isset($_SESSION['connecte']) || $_SESSION['connecte'] !== true || $_SESSION['role'] != 1) {
    $_SESSION['erreur'] = "Accès non autorisé.";
    header('Location: ../auth/connexion.php');
    exit();
  }
  
  // Vérifier que l'ID est présent et que la suppression est confirmée
  if (!isset($_POST['id']) || empty($_POST['id']) || !isset($_POST['confirmer'])) {
    $_SESSION['erreur'] = "Requête invalide.";
    header('Location: mes_demenagements.php');
    exit();
  }
  
  $demenagement_id = intval($_POST['id']);
  $client_id = $_SESSION['user_id'];
  
  // Connexion BDD
  require_once("../config/param.inc.php");
  $mysqli = new mysqli($host, $login, $passwd, $dbname);
  
  if ($mysqli->connect_error) {
    $_SESSION['erreur'] = "Problème de connexion à la base de données !";
    header('Location: mes_demenagements.php');
    exit();
  }
  
  // Vérifier que le déménagement appartient bien au client connecté
  $check_query = "SELECT id FROM demenagement WHERE id = ? AND client_id = ?";
  $check_stmt = $mysqli->prepare($check_query);
  $check_stmt->bind_param("ii", $demenagement_id, $client_id);
  $check_stmt->execute();
  $check_result = $check_stmt->get_result();
  
  if ($check_result->num_rows === 0) {
    $_SESSION['erreur'] = "Vous n'avez pas les droits pour supprimer ce déménagement.";
    $check_stmt->close();
    $mysqli->close();
    header('Location: mes_demenagements.php');
    exit();
  }
  $check_stmt->close();
  
  // Supprimer d'abord les propositions liées
  $delete_propositions = "DELETE FROM proposition WHERE demenagement_id = ?";
  $stmt_prop = $mysqli->prepare($delete_propositions);
  $stmt_prop->bind_param("i", $demenagement_id);
  $stmt_prop->execute();
  $stmt_prop->close();
  
  // Supprimer les images liées (si vous avez une table pour les images)
  $delete_images = "DELETE FROM demenagement_image WHERE demenagement_id = ?";
  $stmt_img = $mysqli->prepare($delete_images);
  $stmt_img->bind_param("i", $demenagement_id);
  $stmt_img->execute();
  $stmt_img->close();
  
  // Supprimer le déménagement
  $delete_query = "DELETE FROM demenagement WHERE id = ? AND client_id = ?";
  $stmt = $mysqli->prepare($delete_query);
  $stmt->bind_param("ii", $demenagement_id, $client_id);
  
  if ($stmt->execute()) {
    $_SESSION['message'] = "Le déménagement a été supprimé avec succès.";
  } else {
    $_SESSION['erreur'] = "Erreur lors de la suppression du déménagement.";
  }
  
  $stmt->close();
  $mysqli->close();
  
  header('Location: mes_demenagements.php');
  exit();
?>
