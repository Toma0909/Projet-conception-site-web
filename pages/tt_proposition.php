<?php
  session_start();
  
  // Vérifier si l'utilisateur est connecté et est un déménageur
  if (!isset($_SESSION['connecte']) || $_SESSION['connecte'] !== true || $_SESSION['role'] != 2) {
    $_SESSION['erreur'] = "Accès non autorisé.";
    header('Location: ../auth/connexion.php');
    exit();
  }
  
  // Récupération des données
  $demenagement_id = intval($_POST['demenagement_id']);
  $demenageur_id = $_SESSION['user_id'];
  $prix = floatval($_POST['prix']);
  $commentaire = isset($_POST['commentaire']) ? htmlentities($_POST['commentaire']) : NULL;
  
  // Connexion BDD
  require_once("../config/param.inc.php");
  $mysqli = new mysqli($host, $login, $passwd, $dbname);
  
  if ($mysqli->connect_error) {
    $_SESSION['erreur'] = "Problème de connexion à la base de données !";
    header('Location: detail_annonce.php?id=' . $demenagement_id);
    exit();
  }
  
  // Vérifier que le déménagement existe et est disponible
  $check_query = "SELECT id, client_id FROM demenagement WHERE id = ? AND statut = 'en_attente'";
  $check_stmt = $mysqli->prepare($check_query);
  $check_stmt->bind_param("i", $demenagement_id);
  $check_stmt->execute();
  $check_result = $check_stmt->get_result();
  
  if ($check_result->num_rows == 0) {
    $_SESSION['erreur'] = "Cette annonce n'est plus disponible.";
    $check_stmt->close();
    $mysqli->close();
    header('Location: annonces.php');
    exit();
  }
  
  $demenagement = $check_result->fetch_assoc();
  $check_stmt->close();
  
  // Vérifier que le déménageur ne propose pas sur sa propre annonce
  if ($demenagement['client_id'] == $demenageur_id) {
    $_SESSION['erreur'] = "Vous ne pouvez pas proposer vos services sur votre propre annonce.";
    $mysqli->close();
    header('Location: detail_annonce.php?id=' . $demenagement_id);
    exit();
  }
  
  // Insérer la proposition
  $insert_query = "INSERT INTO proposition (demenagement_id, demenageur_id, prix, commentaire) 
                   VALUES (?, ?, ?, ?)";
  
  if ($stmt = $mysqli->prepare($insert_query)) {
    $stmt->bind_param("iids", $demenagement_id, $demenageur_id, $prix, $commentaire);
    
    if ($stmt->execute()) {
      $_SESSION['message'] = "Votre proposition a été envoyée avec succès !";
    } else {
      // Vérifier si c'est une erreur de doublon
      if ($mysqli->errno == 1062) {
        $_SESSION['erreur'] = "Vous avez déjà fait une proposition pour ce déménagement.";
      } else {
        $_SESSION['erreur'] = "Erreur lors de l'envoi de votre proposition.";
      }
    }
    
    $stmt->close();
  } else {
    $_SESSION['erreur'] = "Erreur lors de la préparation de la requête.";
  }
  
  $mysqli->close();
  header('Location: detail_annonce.php?id=' . $demenagement_id);
  exit();
?>
