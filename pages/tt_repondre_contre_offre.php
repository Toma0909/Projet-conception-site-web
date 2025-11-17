<?php
  session_start();
  
  // Vérifier si l'utilisateur est connecté
  if (!isset($_SESSION['connecte']) || $_SESSION['connecte'] !== true) {
    $_SESSION['erreur'] = "Vous devez être connecté.";
    header('Location: ../auth/connexion.php');
    exit();
  }
  
  // Récupération des données
  $contre_offre_id = intval($_POST['contre_offre_id']);
  $action = $_POST['action']; // 'accepter' ou 'refuser'
  $demenagement_id = intval($_POST['demenagement_id']);
  $user_id = $_SESSION['user_id'];
  
  // Connexion BDD
  require_once("../config/param.inc.php");
  $mysqli = new mysqli($host, $login, $passwd, $dbname);
  
  if ($mysqli->connect_error) {
    $_SESSION['erreur'] = "Problème de connexion à la base de données !";
    if ($_SESSION['role'] == 1) {
      header('Location: propositions.php?id=' . $demenagement_id);
    } else {
      header('Location: mes_interventions.php');
    }
    exit();
  }
  
  // Récupérer les infos de la contre-offre
  $query = "SELECT co.*, p.demenageur_id, d.client_id 
            FROM contre_offre co
            JOIN proposition p ON co.proposition_id = p.id
            JOIN demenagement d ON p.demenagement_id = d.id
            WHERE co.id = ?";
  $stmt = $mysqli->prepare($query);
  $stmt->bind_param("i", $contre_offre_id);
  $stmt->execute();
  $result = $stmt->get_result();
  
  if ($result->num_rows === 0) {
    $_SESSION['erreur'] = "Contre-offre non trouvée.";
    $stmt->close();
    $mysqli->close();
    header('Location: mes_demenagements.php');
    exit();
  }
  
  $contre_offre = $result->fetch_assoc();
  $stmt->close();
  
  // Vérifier les droits
  // Si l'auteur est le client, seul le déménageur peut répondre et vice-versa
  if ($contre_offre['auteur_id'] == $contre_offre['client_id']) {
    // Contre-offre du client, le déménageur doit répondre
    if ($contre_offre['demenageur_id'] != $user_id) {
      $_SESSION['erreur'] = "Vous n'avez pas les droits pour cette action.";
      $mysqli->close();
      header('Location: mes_interventions.php');
      exit();
    }
  } else {
    // Contre-offre du déménageur, le client doit répondre
    if ($contre_offre['client_id'] != $user_id) {
      $_SESSION['erreur'] = "Vous n'avez pas les droits pour cette action.";
      $mysqli->close();
      header('Location: mes_demenagements.php');
      exit();
    }
  }
  
  // Mettre à jour le statut
  $nouveau_statut = ($action == 'accepter') ? 'accepte' : 'refuse';
  $update_query = "UPDATE contre_offre SET statut = ? WHERE id = ?";
  $update_stmt = $mysqli->prepare($update_query);
  $update_stmt->bind_param("si", $nouveau_statut, $contre_offre_id);
  
  if ($update_stmt->execute()) {
    if ($action == 'accepter') {
      // Refuser toutes les autres contre-offres en attente pour cette proposition
      $refuse_co_query = "UPDATE contre_offre SET statut = 'refuse' WHERE proposition_id = ? AND id != ? AND statut = 'en_attente'";
      $refuse_co_stmt = $mysqli->prepare($refuse_co_query);
      $refuse_co_stmt->bind_param("ii", $contre_offre['proposition_id'], $contre_offre_id);
      $refuse_co_stmt->execute();
      $refuse_co_stmt->close();
      
      // Si le déménageur accepte la contre-offre du client, valider automatiquement la proposition
      if ($contre_offre['auteur_id'] == $contre_offre['client_id'] && $_SESSION['role'] == 2) {
        // Mettre à jour le prix de la proposition
        $update_prop_query = "UPDATE proposition SET prix = ?, statut = 'accepte' WHERE id = ?";
        $update_prop_stmt = $mysqli->prepare($update_prop_query);
        $update_prop_stmt->bind_param("di", $contre_offre['prix_propose'], $contre_offre['proposition_id']);
        $update_prop_stmt->execute();
        $update_prop_stmt->close();
        
        // Créer l'entrée dans demenagement_demenageur
        $dd_query = "INSERT INTO demenagement_demenageur (demenagement_id, demenageur_id, proposition_id) 
                     VALUES ((SELECT demenagement_id FROM proposition WHERE id = ?), ?, ?)";
        $dd_stmt = $mysqli->prepare($dd_query);
        $dd_stmt->bind_param("iii", $contre_offre['proposition_id'], $user_id, $contre_offre['proposition_id']);
        $dd_stmt->execute();
        $dd_stmt->close();
        
        // Refuser toutes les autres propositions pour ce déménagement
        $refuse_query = "UPDATE proposition 
                        SET statut = 'refuse' 
                        WHERE demenagement_id = (SELECT demenagement_id FROM proposition WHERE id = ?) 
                        AND id != ?";
        $refuse_stmt = $mysqli->prepare($refuse_query);
        $refuse_stmt->bind_param("ii", $contre_offre['proposition_id'], $contre_offre['proposition_id']);
        $refuse_stmt->execute();
        $refuse_stmt->close();
        
        $_SESSION['succes'] = "Contre-offre acceptée ! Vous avez été sélectionné pour ce déménagement au prix de " . number_format($contre_offre['prix_propose'], 2, ',', ' ') . " €";
      } 
      // Si le client accepte la contre-offre du déménageur, valider automatiquement la proposition
      elseif ($contre_offre['auteur_id'] == $contre_offre['demenageur_id'] && $_SESSION['role'] == 1) {
        // Mettre à jour le prix de la proposition
        $update_prop_query = "UPDATE proposition SET prix = ?, statut = 'accepte' WHERE id = ?";
        $update_prop_stmt = $mysqli->prepare($update_prop_query);
        $update_prop_stmt->bind_param("di", $contre_offre['prix_propose'], $contre_offre['proposition_id']);
        $update_prop_stmt->execute();
        $update_prop_stmt->close();
        
        // Créer l'entrée dans demenagement_demenageur
        $dd_query = "INSERT INTO demenagement_demenageur (demenagement_id, demenageur_id, proposition_id) 
                     VALUES ((SELECT demenagement_id FROM proposition WHERE id = ?), ?, ?)";
        $dd_stmt = $mysqli->prepare($dd_query);
        $dd_stmt->bind_param("iii", $contre_offre['proposition_id'], $contre_offre['demenageur_id'], $contre_offre['proposition_id']);
        $dd_stmt->execute();
        $dd_stmt->close();
        
        // Refuser toutes les autres propositions pour ce déménagement
        $refuse_query = "UPDATE proposition 
                        SET statut = 'refuse' 
                        WHERE demenagement_id = (SELECT demenagement_id FROM proposition WHERE id = ?) 
                        AND id != ?";
        $refuse_stmt = $mysqli->prepare($refuse_query);
        $refuse_stmt->bind_param("ii", $contre_offre['proposition_id'], $contre_offre['proposition_id']);
        $refuse_stmt->execute();
        $refuse_stmt->close();
        
        $_SESSION['succes'] = "Contre-offre acceptée ! Le déménageur a été sélectionné au prix de " . number_format($contre_offre['prix_propose'], 2, ',', ' ') . " €";
      } else {
        $_SESSION['succes'] = "Contre-offre acceptée ! Le nouveau prix est de " . number_format($contre_offre['prix_propose'], 2, ',', ' ') . " €";
      }
    } else {
      $_SESSION['succes'] = "Contre-offre refusée.";
    }
  } else {
    $_SESSION['erreur'] = "Erreur lors de la mise à jour.";
  }
  
  $update_stmt->close();
  $mysqli->close();
  
  // Redirection
  if ($_SESSION['role'] == 1) {
    header('Location: propositions.php?id=' . $demenagement_id);
  } else {
    header('Location: mes_interventions.php');
  }
  exit();
?>
