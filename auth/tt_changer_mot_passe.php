<?php
  session_start();
  
  // Vérifier si l'utilisateur est connecté
  if (!isset($_SESSION['connecte']) || $_SESSION['connecte'] !== true) {
    $_SESSION['erreur'] = "Vous devez être connecté pour changer votre mot de passe.";
    header('Location: connexion.php');
    exit();
  }

  // Récupération des données du formulaire
  $ancien_password = htmlentities($_POST['ancien_password']);
  $nouveau_password = htmlentities($_POST['nouveau_password']);
  $confirmer_password = htmlentities($_POST['confirmer_password']);
  
  // Vérifications
  if (empty($ancien_password) || empty($nouveau_password) || empty($confirmer_password)) {
    $_SESSION['erreur'] = "Tous les champs sont obligatoires.";
    header('Location: changer_mot_passe.php');
    exit();
  }
  
  if ($nouveau_password !== $confirmer_password) {
    $_SESSION['erreur'] = "Les nouveaux mots de passe ne correspondent pas.";
    header('Location: changer_mot_passe.php');
    exit();
  }
  
  if (strlen($nouveau_password) < 6) {
    $_SESSION['erreur'] = "Le nouveau mot de passe doit contenir au moins 6 caractères.";
    header('Location: changer_mot_passe.php');
    exit();
  }
    // Connexion à la base de données
  require_once("../config/param.inc.php");
  $mysqli = new mysqli($host, $login, $passwd, $dbname);
  if ($mysqli->connect_error) {
    $_SESSION['erreur'] = "Problème de connexion à la base de données !";
    header('Location: changer_mot_passe.php');
    exit();
  }

  // Récupérer le mot de passe actuel de l'utilisateur
  if ($stmt = $mysqli->prepare("SELECT password FROM compte WHERE id = ?")) {
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
      $user = $result->fetch_assoc();
      
      // Vérifier l'ancien mot de passe
      if (password_verify($ancien_password, $user['password'])) {
        
        // L'ancien mot de passe est correct, on peut changer
        $options = [
          'cost' => 10,
        ];
        $nouveau_password_crypt = password_hash($nouveau_password, PASSWORD_BCRYPT, $options);
        
        // Mettre à jour le mot de passe en base
        if ($updateStmt = $mysqli->prepare("UPDATE compte SET password = ? WHERE id = ?")) {
          $updateStmt->bind_param("si", $nouveau_password_crypt, $_SESSION['user_id']);
          
          if ($updateStmt->execute()) {            $_SESSION['message'] = "Votre mot de passe a été modifié avec succès !";
            $updateStmt->close();
            $stmt->close();
            $mysqli->close();
            header('Location: ../pages/tableau_bord.php');
            exit();
          } else {
            $_SESSION['erreur'] = "Erreur lors de la mise à jour du mot de passe.";
          }
          
          $updateStmt->close();
        } else {
          $_SESSION['erreur'] = "Erreur lors de la préparation de la requête de mise à jour.";
        }
        
      } else {
        $_SESSION['erreur'] = "Le mot de passe actuel est incorrect.";
      }
      
    } else {
      $_SESSION['erreur'] = "Utilisateur non trouvé.";
    }
    
    $stmt->close();
  } else {
    $_SESSION['erreur'] = "Erreur lors de la préparation de la requête.";
  }

  $mysqli->close();
  header('Location: changer_mot_passe.php');
  exit();
?>
