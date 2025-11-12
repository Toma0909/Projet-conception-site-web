<?php
  session_start();
  
  // Vérifier si l'utilisateur est connecté et est un client
  if (!isset($_SESSION['connecte']) || $_SESSION['connecte'] !== true || $_SESSION['role'] != 1) {
    $_SESSION['erreur'] = "Accès non autorisé.";
    header('Location: ../auth/connexion.php');
    exit();
  }
  
  // Récupération des données
  $proposition_id = intval($_POST['proposition_id']);
  $demenagement_id = intval($_POST['demenagement_id']);
  $client_id = $_SESSION['user_id'];
  
  // Connexion BDD
  require_once("../config/param.inc.php");
  $mysqli = new mysqli($host, $login, $passwd, $dbname);
  
  if ($mysqli->connect_error) {
    $_SESSION['erreur'] = "Problème de connexion à la base de données !";
    header('Location: detail_annonce.php?id=' . $demenagement_id);
    exit();
  }
  
  // Vérifier que le client est bien propriétaire du déménagement
  $check_query = "SELECT id, nombre_demenageurs FROM demenagement WHERE id = ? AND client_id = ? AND statut = 'en_attente'";
  $check_stmt = $mysqli->prepare($check_query);
  $check_stmt->bind_param("ii", $demenagement_id, $client_id);
  $check_stmt->execute();
  $check_result = $check_stmt->get_result();
  
  if ($check_result->num_rows == 0) {
    $_SESSION['erreur'] = "Vous n'êtes pas autorisé à accepter cette proposition.";
    $check_stmt->close();
    $mysqli->close();
    header('Location: detail_annonce.php?id=' . $demenagement_id);
    exit();
  }
  
  $demenagement = $check_result->fetch_assoc();
  $nombre_demenageurs_requis = $demenagement['nombre_demenageurs'];
  $check_stmt->close();
  
  // Vérifier le nombre de déménageurs déjà acceptés
  $count_query = "SELECT COUNT(*) as nb_acceptes FROM proposition 
                  WHERE demenagement_id = ? AND statut = 'accepte'";
  $count_stmt = $mysqli->prepare($count_query);
  $count_stmt->bind_param("i", $demenagement_id);
  $count_stmt->execute();
  $count_result = $count_stmt->get_result();
  $nb_acceptes = $count_result->fetch_assoc()['nb_acceptes'];
  $count_stmt->close();
  
  if ($nb_acceptes >= $nombre_demenageurs_requis) {
    $_SESSION['erreur'] = "Vous avez déjà sélectionné le nombre maximum de déménageurs pour cette annonce.";
    $mysqli->close();
    header('Location: detail_annonce.php?id=' . $demenagement_id);
    exit();
  }
  
  // Récupérer les infos de la proposition
  $prop_query = "SELECT demenageur_id FROM proposition WHERE id = ? AND demenagement_id = ?";
  $prop_stmt = $mysqli->prepare($prop_query);
  $prop_stmt->bind_param("ii", $proposition_id, $demenagement_id);
  $prop_stmt->execute();
  $prop_result = $prop_stmt->get_result();
  
  if ($prop_result->num_rows == 0) {
    $_SESSION['erreur'] = "Proposition non trouvée.";
    $prop_stmt->close();
    $mysqli->close();
    header('Location: detail_annonce.php?id=' . $demenagement_id);
    exit();
  }
  
  $proposition = $prop_result->fetch_assoc();
  $demenageur_id = $proposition['demenageur_id'];
  $prop_stmt->close();
  
  // Démarrer une transaction
  $mysqli->begin_transaction();
  
  try {
    // Accepter la proposition
    $update_query = "UPDATE proposition SET statut = 'accepte' WHERE id = ?";
    $update_stmt = $mysqli->prepare($update_query);
    $update_stmt->bind_param("i", $proposition_id);
    $update_stmt->execute();
    $update_stmt->close();    // Ajouter dans la table de liaison
    $insert_query = "INSERT INTO demenagement_demenageur (demenagement_id, demenageur_id, proposition_id) 
                     VALUES (?, ?, ?)";
    $insert_stmt = $mysqli->prepare($insert_query);
    $insert_stmt->bind_param("iii", $demenagement_id, $demenageur_id, $proposition_id);
    $insert_stmt->execute();
    $insert_stmt->close();
    
    // Si on a atteint le nombre de déménageurs requis, changer le statut de l'annonce
    $nb_acceptes++;
    if ($nb_acceptes >= $nombre_demenageurs_requis) {
      $status_query = "UPDATE demenagement SET statut = 'en_cours' WHERE id = ?";
      $status_stmt = $mysqli->prepare($status_query);
      $status_stmt->bind_param("i", $demenagement_id);
      $status_stmt->execute();
      $status_stmt->close();
    }
    
    // Valider la transaction
    $mysqli->commit();
    $_SESSION['message'] = "Proposition acceptée avec succès !";
    
  } catch (Exception $e) {
    // Annuler la transaction en cas d'erreur
    $mysqli->rollback();
    $_SESSION['erreur'] = "Erreur lors de l'acceptation de la proposition.";
  }
  
  $mysqli->close();
  header('Location: detail_annonce.php?id=' . $demenagement_id);
  exit();
?>
