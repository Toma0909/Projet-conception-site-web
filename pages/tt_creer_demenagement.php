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
  
  // Récupération des données du formulaire
  $titre = trim($_POST['titre']);
  $description = trim($_POST['description']);
  $date_demenagement = $_POST['date_demenagement'];
  $heure_debut = $_POST['heure_debut'];
  $ville_depart = trim($_POST['ville_depart']);
  $ville_arrivee = trim($_POST['ville_arrivee']);
  
  $depart_type = trim($_POST['depart_type']);
  if (!in_array($depart_type, ['maison', 'appartement'])) {
      $_SESSION['erreur'] = "Type de départ invalide: " . htmlentities($depart_type);
      header('Location: creer_demenagement.php');
      exit();
  }
  $depart_etage = isset($_POST['depart_etage']) && !empty($_POST['depart_etage']) ? intval($_POST['depart_etage']) : NULL;
  $depart_ascenseur = isset($_POST['depart_ascenseur']) ? intval($_POST['depart_ascenseur']) : 0;
  
  $arrivee_type = trim($_POST['arrivee_type']);
  if (!in_array($arrivee_type, ['maison', 'appartement'])) {
      $_SESSION['erreur'] = "Type d'arrivée invalide: " . htmlentities($arrivee_type);
      header('Location: creer_demenagement.php');
      exit();
  }
  error_log("depart_type: '" . $depart_type . "', arrivee_type: '" . $arrivee_type . "'");
  $arrivee_etage = isset($_POST['arrivee_etage']) && !empty($_POST['arrivee_etage']) ? intval($_POST['arrivee_etage']) : NULL;
  $arrivee_ascenseur = isset($_POST['arrivee_ascenseur']) ? intval($_POST['arrivee_ascenseur']) : 0;
  
  $volume = isset($_POST['volume']) && !empty($_POST['volume']) ? floatval($_POST['volume']) : NULL;
  $poids = isset($_POST['poids']) && !empty($_POST['poids']) ? floatval($_POST['poids']) : NULL;
  $nombre_demenageurs = intval($_POST['nombre_demenageurs']);
  
  $client_id = $_SESSION['user_id'];
  
  // Vérifier que le client_id est valide
  if (empty($client_id)) {
    $_SESSION['erreur'] = "Erreur : ID utilisateur manquant. Veuillez vous reconnecter.";
    header('Location: ../auth/connexion.php');
    exit();
  }
  
  // Connexion BDD
  require_once("../config/param.inc.php");
  $mysqli = new mysqli($host, $login, $passwd, $dbname);
  
  if ($mysqli->connect_error) {
    $_SESSION['erreur'] = "Problème de connexion à la base de données !";
    header('Location: creer_demenagement.php');
    exit();
  }
  
  // Insérer le déménagement
  $query = "INSERT INTO demenagement (
    client_id, titre, description, date_demenagement, heure_debut, 
    ville_depart, ville_arrivee, depart_type, depart_etage, depart_ascenseur,
    arrivee_type, arrivee_etage, arrivee_ascenseur, volume, poids, nombre_demenageurs
  ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
  
  if ($stmt = $mysqli->prepare($query)) {
    $stmt->bind_param(
      "isssssssiisiiddi",
      $client_id, $titre, $description, $date_demenagement, $heure_debut,
      $ville_depart, $ville_arrivee, $depart_type, $depart_etage, $depart_ascenseur,
      $arrivee_type, $arrivee_etage, $arrivee_ascenseur, $volume, $poids, $nombre_demenageurs
    );
    
    if ($stmt->execute()) {
      $demenagement_id = $mysqli->insert_id;
      
      // Traiter les images si présentes
      if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
        $upload_dir = __DIR__ . '/../assets/images/demenagements/';
        
        // Créer le dossier s'il n'existe pas
        if (!file_exists($upload_dir)) {
          mkdir($upload_dir, 0777, true);
        }
        
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        $max_size = 5 * 1024 * 1024; // 5 MB
        
        foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
          if ($_FILES['images']['error'][$key] == 0) {
            $file_type = $_FILES['images']['type'][$key];
            $file_size = $_FILES['images']['size'][$key];
            
            if (in_array($file_type, $allowed_types) && $file_size <= $max_size) {
              $file_extension = pathinfo($_FILES['images']['name'][$key], PATHINFO_EXTENSION);
              $new_filename = 'dem_' . $demenagement_id . '_' . time() . '_' . $key . '.' . $file_extension;
              $destination = $upload_dir . $new_filename;
              $chemin_relatif = '/Projet-conception-site-web/assets/images/demenagements/' . $new_filename;
              
              if (move_uploaded_file($tmp_name, $destination)) {
                // Insérer en base de données avec le chemin relatif
                $image_query = "INSERT INTO demenagement_image (demenagement_id, nom_fichier, chemin) VALUES (?, ?, ?)";
                if ($img_stmt = $mysqli->prepare($image_query)) {
                  $img_stmt->bind_param("iss", $demenagement_id, $new_filename, $chemin_relatif);
                  $img_stmt->execute();
                  $img_stmt->close();
                }
              }
            }
          }
        }
      }
      
      $_SESSION['message'] = "Votre annonce de déménagement a été publiée avec succès !";
      $stmt->close();
      $mysqli->close();
      header('Location: detail_annonce.php?id=' . $demenagement_id);
      exit();
      
    } else {
      $_SESSION['erreur'] = "Erreur lors de la création de l'annonce.";
    }
    
    $stmt->close();
  } else {
    $_SESSION['erreur'] = "Erreur lors de la préparation de la requête.";
  }
  
  $mysqli->close();
  header('Location: creer_demenagement.php');
  exit();
?>
