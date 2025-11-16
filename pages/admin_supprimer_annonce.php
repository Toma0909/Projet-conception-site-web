<?php
  session_start();
  
  // Vérifier si l'utilisateur est connecté et est un administrateur
  if (!isset($_SESSION['connecte']) || $_SESSION['connecte'] !== true || $_SESSION['role'] != 3) {
    $_SESSION['erreur'] = "Accès réservé aux administrateurs.";
    header('Location: ../auth/connexion.php');
    exit();
  }
  
  // Récupérer l'ID de l'annonce
  $annonce_id = intval($_GET['id']);
  
  if ($annonce_id <= 0) {
    $_SESSION['erreur'] = "ID d'annonce invalide.";
    header('Location: admin_annonces.php');
    exit();
  }
  
  // Connexion BDD
  require_once("../config/param.inc.php");
  $mysqli = new mysqli($host, $login, $passwd, $dbname);
  
  if ($mysqli->connect_error) {
    $_SESSION['erreur'] = "Problème de connexion à la base de données !";
    header('Location: admin_annonces.php');
    exit();
  }
  
  $mysqli->set_charset("utf8");
  
  // Vérifier que l'annonce existe
  $check_query = "SELECT id, titre FROM demenagement WHERE id = ?";
  $check_stmt = $mysqli->prepare($check_query);
  $check_stmt->bind_param("i", $annonce_id);
  $check_stmt->execute();
  $result = $check_stmt->get_result();
  
  if ($result->num_rows == 0) {
    $_SESSION['erreur'] = "Annonce non trouvée.";
    $check_stmt->close();
    $mysqli->close();
    header('Location: admin_annonces.php');
    exit();
  }
  
  $annonce = $result->fetch_assoc();
  $check_stmt->close();
  
  // Démarrer une transaction pour supprimer en cascade
  $mysqli->begin_transaction();
  
  try {
    // Supprimer les avis liés aux déménageurs de ce déménagement
    $delete_avis = "DELETE FROM avis WHERE demenagement_id = ?";
    $stmt_avis = $mysqli->prepare($delete_avis);
    $stmt_avis->bind_param("i", $annonce_id);
    $stmt_avis->execute();
    $stmt_avis->close();
    
    // Supprimer les messages liés
    $delete_messages = "DELETE FROM message WHERE demenagement_id = ?";
    $stmt_messages = $mysqli->prepare($delete_messages);
    $stmt_messages->bind_param("i", $annonce_id);
    $stmt_messages->execute();
    $stmt_messages->close();
    
    // Supprimer les relations demenagement_demenageur
    $delete_liaison = "DELETE FROM demenagement_demenageur WHERE demenagement_id = ?";
    $stmt_liaison = $mysqli->prepare($delete_liaison);
    $stmt_liaison->bind_param("i", $annonce_id);
    $stmt_liaison->execute();
    $stmt_liaison->close();
    
    // Supprimer les propositions
    $delete_prop = "DELETE FROM proposition WHERE demenagement_id = ?";
    $stmt_prop = $mysqli->prepare($delete_prop);
    $stmt_prop->bind_param("i", $annonce_id);
    $stmt_prop->execute();
    $stmt_prop->close();
    
    // Supprimer l'annonce elle-même
    $delete_annonce = "DELETE FROM demenagement WHERE id = ?";
    $stmt_annonce = $mysqli->prepare($delete_annonce);
    $stmt_annonce->bind_param("i", $annonce_id);
    $stmt_annonce->execute();
    $stmt_annonce->close();
    
    // Valider la transaction
    $mysqli->commit();
    $_SESSION['message'] = "L'annonce \"" . htmlspecialchars($annonce['titre']) . "\" a été supprimée avec succès.";
    
  } catch (Exception $e) {
    // Annuler la transaction en cas d'erreur
    $mysqli->rollback();
    $_SESSION['erreur'] = "Erreur lors de la suppression de l'annonce : " . $e->getMessage();
  }
  
  $mysqli->close();
  header('Location: admin_annonces.php');
  exit();
?>
