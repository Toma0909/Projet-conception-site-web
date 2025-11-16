<?php
  session_start();
  
  // Vérifier si l'utilisateur est connecté et est un administrateur
  if (!isset($_SESSION['connecte']) || $_SESSION['connecte'] !== true || $_SESSION['role'] != 3) {
    $_SESSION['erreur'] = "Accès réservé aux administrateurs.";
    header('Location: ../auth/connexion.php');
    exit();
  }
  
  // Récupérer l'ID du compte
  $compte_id = intval($_GET['id']);
  
  if ($compte_id <= 0) {
    $_SESSION['erreur'] = "ID de compte invalide.";
    header('Location: admin_comptes.php');
    exit();
  }
  
  // Connexion BDD
  require_once("../config/param.inc.php");
  $mysqli = new mysqli($host, $login, $passwd, $dbname);
  
  if ($mysqli->connect_error) {
    $_SESSION['erreur'] = "Problème de connexion à la base de données !";
    header('Location: admin_comptes.php');
    exit();
  }
  
  $mysqli->set_charset("utf8");
  
  // Vérifier que le compte existe et est actuellement désactivé
  $check_query = "SELECT id, nom, prenom, email, role FROM compte WHERE id = ? AND role = 0";
  $check_stmt = $mysqli->prepare($check_query);
  $check_stmt->bind_param("i", $compte_id);
  $check_stmt->execute();
  $result = $check_stmt->get_result();
  
  if ($result->num_rows == 0) {
    $_SESSION['erreur'] = "Compte non trouvé ou déjà activé.";
    $check_stmt->close();
    $mysqli->close();
    header('Location: admin_comptes.php');
    exit();
  }
  
  $compte = $result->fetch_assoc();
  $check_stmt->close();
  
  // On peut demander à l'admin de choisir le rôle, mais par défaut on met client (role = 1)
  // Pour simplifier, on active en tant que client
  $update_query = "UPDATE compte SET role = 1 WHERE id = ?";
  $update_stmt = $mysqli->prepare($update_query);
  $update_stmt->bind_param("i", $compte_id);
  
  if ($update_stmt->execute()) {
    $_SESSION['message'] = "Le compte de " . htmlspecialchars($compte['prenom'] . ' ' . $compte['nom']) . " a été activé en tant que client.";
  } else {
    $_SESSION['erreur'] = "Erreur lors de l'activation du compte.";
  }
  
  $update_stmt->close();
  $mysqli->close();
  
  header('Location: admin_comptes.php');
  exit();
?>
