<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
  session_start();
  
  // Vérifier si l'utilisateur est connecté et est un client
  if (!isset($_SESSION['connecte']) || $_SESSION['connecte'] !== true || $_SESSION['role'] != 1) {
    $_SESSION['erreur'] = "Accès non autorisé.";
    header('Location: ../auth/connexion.php');
    exit();
  }
  
  // Vérifier que l'ID est présent
  if (!isset($_POST['id']) || empty($_POST['id'])) {
    $_SESSION['erreur'] = "Déménagement non trouvé.";
    header('Location: mes_demenagements.php');
    exit();
  }
  
  $demenagement_id = intval($_POST['id']);
  $client_id = $_SESSION['user_id'];
  
  // Récupération des données du formulaire
  $titre = htmlentities($_POST['titre']);
  $description = htmlentities($_POST['description']);
  $date_demenagement = htmlentities($_POST['date_demenagement']);
  $heure_debut = htmlentities($_POST['heure_debut']);
  $ville_depart = htmlentities($_POST['ville_depart']);
  $ville_arrivee = htmlentities($_POST['ville_arrivee']);
  
  $depart_type = trim($_POST['depart_type']);
  if (!in_array($depart_type, ['maison', 'appartement'])) {
      $_SESSION['erreur'] = "Type de départ invalide: " . htmlentities($depart_type);
      header('Location: modifier_demenagement.php?id=' . $demenagement_id);
      exit();
  }
  $depart_etage = isset($_POST['depart_etage']) && !empty($_POST['depart_etage']) ? intval($_POST['depart_etage']) : NULL;
  $depart_ascenseur = isset($_POST['depart_ascenseur']) ? intval($_POST['depart_ascenseur']) : 0;
  
  $arrivee_type = trim($_POST['arrivee_type']);
  if (!in_array($arrivee_type, ['maison', 'appartement'])) {
      $_SESSION['erreur'] = "Type d'arrivée invalide: " . htmlentities($arrivee_type);
      header('Location: modifier_demenagement.php?id=' . $demenagement_id);
      exit();
  }
  $arrivee_etage = isset($_POST['arrivee_etage']) && !empty($_POST['arrivee_etage']) ? intval($_POST['arrivee_etage']) : NULL;
  $arrivee_ascenseur = isset($_POST['arrivee_ascenseur']) ? intval($_POST['arrivee_ascenseur']) : 0;
  
  $volume = isset($_POST['volume']) && !empty($_POST['volume']) ? floatval($_POST['volume']) : NULL;
  $poids = isset($_POST['poids']) && !empty($_POST['poids']) ? floatval($_POST['poids']) : NULL;
  $nombre_demenageurs = intval($_POST['nombre_demenageurs']);
  
  // Connexion BDD
  require_once("../config/param.inc.php");
  $mysqli = new mysqli($host, $login, $passwd, $dbname);
  
  if ($mysqli->connect_error) {
    $_SESSION['erreur'] = "Problème de connexion à la base de données !";
    header('Location: modifier_demenagement.php?id=' . $demenagement_id);
    exit();
  }
  
  // Vérifier que le déménagement appartient bien au client connecté
  $check_query = "SELECT id FROM demenagement WHERE id = ? AND client_id = ?";
  $check_stmt = $mysqli->prepare($check_query);
  $check_stmt->bind_param("ii", $demenagement_id, $client_id);
  $check_stmt->execute();
  $check_result = $check_stmt->get_result();
  
  if ($check_result->num_rows === 0) {
    $_SESSION['erreur'] = "Vous n'avez pas les droits pour modifier ce déménagement.";
    $check_stmt->close();
    $mysqli->close();
    header('Location: mes_demenagements.php');
    exit();
  }
  $check_stmt->close();
  
  // Mettre à jour le déménagement
  $query = "UPDATE demenagement SET 
    titre = ?, description = ?, date_demenagement = ?, heure_debut = ?, 
    ville_depart = ?, ville_arrivee = ?, depart_type = ?, depart_etage = ?, depart_ascenseur = ?,
    arrivee_type = ?, arrivee_etage = ?, arrivee_ascenseur = ?, volume = ?, poids = ?, nombre_demenageurs = ?
    WHERE id = ? AND client_id = ?";
  
  if ($stmt = $mysqli->prepare($query)) {
    $stmt->bind_param(
      "sssssssiisiiddiii",
      $titre, $description, $date_demenagement, $heure_debut,
      $ville_depart, $ville_arrivee, $depart_type, $depart_etage, $depart_ascenseur,
      $arrivee_type, $arrivee_etage, $arrivee_ascenseur, $volume, $poids, $nombre_demenageurs,
      $demenagement_id, $client_id
    );
    
    if ($stmt->execute()) {
      $_SESSION['message'] = "Votre annonce de déménagement a été modifiée avec succès !";
      $stmt->close();
      $mysqli->close();
      header('Location: detail_annonce.php?id=' . $demenagement_id);
      exit();
      
    } else {
      $_SESSION['erreur'] = "Erreur lors de la modification de l'annonce.";
    }
    
    $stmt->close();
  } else {
    $_SESSION['erreur'] = "Erreur lors de la préparation de la requête.";
  }
  
  $mysqli->close();
  header('Location: modifier_demenagement.php?id=' . $demenagement_id);
  exit();
?>
