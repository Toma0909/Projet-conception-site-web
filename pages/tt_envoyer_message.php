<?php
  session_start();
  
  // Vérifier si l'utilisateur est connecté
  if (!isset($_SESSION['connecte']) || $_SESSION['connecte'] !== true) {
    $_SESSION['erreur'] = "Vous devez être connecté pour envoyer un message.";
    header('Location: ../auth/connexion.php');
    exit();
  }
  
  // Vérifier que tous les champs sont présents
  if (!isset($_POST['demenagement_id'], $_POST['destinataire_id'], $_POST['contenu']) || 
      empty($_POST['demenagement_id']) || empty($_POST['destinataire_id']) || empty(trim($_POST['contenu']))) {
    $_SESSION['erreur'] = "Tous les champs sont obligatoires.";
    header('Location: ' . ($_SESSION['role'] == 1 ? 'mes_demenagements.php' : 'mes_interventions.php'));
    exit();
  }
  
  $demenagement_id = intval($_POST['demenagement_id']);
  $destinataire_id = intval($_POST['destinataire_id']);
  $contenu = trim($_POST['contenu']);
  $expediteur_id = $_SESSION['user_id'];
  
  // Connexion BDD
  require_once("../config/param.inc.php");
  $mysqli = new mysqli($host, $login, $passwd, $dbname);
  
  if ($mysqli->connect_error) {
    $_SESSION['erreur'] = "Problème de connexion à la base de données !";
    header('Location: ' . ($_SESSION['role'] == 1 ? 'mes_demenagements.php' : 'mes_interventions.php'));
    exit();
  }
  
  // Vérifier que le déménagement existe
  $check_query = "SELECT client_id FROM demenagement WHERE id = ?";
  $check_stmt = $mysqli->prepare($check_query);
  $check_stmt->bind_param("i", $demenagement_id);
  $check_stmt->execute();
  $check_result = $check_stmt->get_result();
  
  if ($check_result->num_rows === 0) {
    $_SESSION['erreur'] = "Déménagement non trouvé.";
    $check_stmt->close();
    $mysqli->close();
    header('Location: ' . ($_SESSION['role'] == 1 ? 'mes_demenagements.php' : 'mes_interventions.php'));
    exit();
  }
  
  $demenagement_data = $check_result->fetch_assoc();
  $check_stmt->close();
  
  // Vérifier que l'utilisateur a le droit d'envoyer un message
  $is_client = ($expediteur_id == $demenagement_data['client_id']);
  $is_demenageur = false;
  
  if (!$is_client) {
    // Vérifier si l'utilisateur est un déménageur ayant fait une proposition
    $prop_query = "SELECT id FROM proposition WHERE demenagement_id = ? AND demenageur_id = ?";
    $prop_stmt = $mysqli->prepare($prop_query);
    $prop_stmt->bind_param("ii", $demenagement_id, $expediteur_id);
    $prop_stmt->execute();
    $prop_result = $prop_stmt->get_result();
    $is_demenageur = ($prop_result->num_rows > 0);
    $prop_stmt->close();
  }
  
  if (!$is_client && !$is_demenageur) {
    $_SESSION['erreur'] = "Vous n'avez pas le droit d'envoyer un message pour ce déménagement.";
    $mysqli->close();
    header('Location: ' . ($_SESSION['role'] == 1 ? 'mes_demenagements.php' : 'mes_interventions.php'));
    exit();
  }
  
  // Insérer le message
  $insert_query = "INSERT INTO message (demenagement_id, expediteur_id, destinataire_id, contenu, date_envoi, lu) 
                   VALUES (?, ?, ?, ?, NOW(), 0)";
  $insert_stmt = $mysqli->prepare($insert_query);
  $insert_stmt->bind_param("iiis", $demenagement_id, $expediteur_id, $destinataire_id, $contenu);
  
  if ($insert_stmt->execute()) {
    $_SESSION['succes'] = "Message envoyé avec succès.";
    $insert_stmt->close();
    $mysqli->close();
    
    // Redirection vers la messagerie
    if ($is_client) {
      header('Location: messagerie.php?id=' . $demenagement_id . '&demenageur_id=' . $destinataire_id);
    } else {
      header('Location: messagerie.php?id=' . $demenagement_id);
    }
    exit();
  } else {
    $_SESSION['erreur'] = "Erreur lors de l'envoi du message : " . $mysqli->error;
    $insert_stmt->close();
    $mysqli->close();
    
    // Redirection vers la messagerie
    if ($is_client) {
      header('Location: messagerie.php?id=' . $demenagement_id . '&demenageur_id=' . $destinataire_id);
    } else {
      header('Location: messagerie.php?id=' . $demenagement_id);
    }
    exit();
  }
?>
