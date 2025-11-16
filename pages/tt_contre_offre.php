<?php
  session_start();
  
  // Vérifier si l'utilisateur est connecté
  if (!isset($_SESSION['connecte']) || $_SESSION['connecte'] !== true) {
    $_SESSION['erreur'] = "Vous devez être connecté.";
    header('Location: ../auth/connexion.php');
    exit();
  }
  
  // Récupération des données
  $proposition_id = intval($_POST['proposition_id']);
  $demenagement_id = intval($_POST['demenagement_id']);
  $prix_propose = floatval($_POST['prix_propose']);
  $commentaire = isset($_POST['commentaire']) ? trim($_POST['commentaire']) : null;
  $auteur_id = $_SESSION['user_id'];
  
  // Connexion BDD
  require_once("../config/param.inc.php");
  $mysqli = new mysqli($host, $login, $passwd, $dbname);
  
  if ($mysqli->connect_error) {
    $_SESSION['erreur'] = "Problème de connexion à la base de données !";
    header('Location: propositions.php?id=' . $demenagement_id);
    exit();
  }
  
  // Vérifier que la proposition existe et récupérer son prix actuel
  $query = "SELECT p.*, d.client_id 
            FROM proposition p 
            JOIN demenagement d ON p.demenagement_id = d.id 
            WHERE p.id = ?";
  $stmt = $mysqli->prepare($query);
  $stmt->bind_param("i", $proposition_id);
  $stmt->execute();
  $result = $stmt->get_result();
  
  if ($result->num_rows === 0) {
    $_SESSION['erreur'] = "Proposition non trouvée.";
    $stmt->close();
    $mysqli->close();
    header('Location: mes_demenagements.php');
    exit();
  }
  
  $proposition = $result->fetch_assoc();
  $stmt->close();
  
  // Vérifier les droits (client du déménagement ou déménageur de la proposition)
  if ($_SESSION['role'] == 1 && $proposition['client_id'] != $auteur_id) {
    $_SESSION['erreur'] = "Vous n'avez pas les droits pour cette action.";
    $mysqli->close();
    header('Location: mes_demenagements.php');
    exit();
  }
  
  if ($_SESSION['role'] == 2 && $proposition['demenageur_id'] != $auteur_id) {
    $_SESSION['erreur'] = "Vous n'avez pas les droits pour cette action.";
    $mysqli->close();
    header('Location: mes_interventions.php');
    exit();
  }
  
  // Récupérer le prix actuel (dernière contre-offre acceptée ou prix initial)
  $co_query = "SELECT prix_propose FROM contre_offre 
               WHERE proposition_id = ? AND statut = 'accepte' 
               ORDER BY date_creation DESC LIMIT 1";
  $co_stmt = $mysqli->prepare($co_query);
  $co_stmt->bind_param("i", $proposition_id);
  $co_stmt->execute();
  $co_result = $co_stmt->get_result();
  
  $prix_actuel = $proposition['prix'];
  if ($co_result->num_rows > 0) {
    $prix_actuel = $co_result->fetch_assoc()['prix_propose'];
  }
  $co_stmt->close();
  
  // Valider le prix (le client doit proposer moins, le déménageur peut proposer plus)
  if ($_SESSION['role'] == 1 && $prix_propose >= $prix_actuel) {
    $_SESSION['erreur'] = "Votre contre-offre doit être inférieure au prix actuel.";
    $mysqli->close();
    header('Location: propositions.php?id=' . $demenagement_id);
    exit();
  }
  
  // Insérer la contre-offre
  $insert_query = "INSERT INTO contre_offre (proposition_id, auteur_id, prix_propose, commentaire) 
                   VALUES (?, ?, ?, ?)";
  $insert_stmt = $mysqli->prepare($insert_query);
  $insert_stmt->bind_param("iids", $proposition_id, $auteur_id, $prix_propose, $commentaire);
  
  if ($insert_stmt->execute()) {
    $_SESSION['succes'] = "Contre-offre envoyée avec succès !";
  } else {
    $_SESSION['erreur'] = "Erreur lors de l'envoi de la contre-offre.";
  }
  
  $insert_stmt->close();
  $mysqli->close();
  
  // Redirection
  if ($_SESSION['role'] == 1) {
    header('Location: propositions.php?id=' . $demenagement_id);
  } else {
    header('Location: mes_interventions.php');
  }
  exit();
?>
