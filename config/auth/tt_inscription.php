<?php
  session_start(); // Pour les massages
  // Contenu du formulaire :
  $nom =  htmlentities($_POST['nom']);
  $prenom = htmlentities($_POST['prenom']);
  $email =  htmlentities($_POST['email']);
  $password = htmlentities($_POST['password']);
  $type_utilisateur = htmlentities($_POST['type_utilisateur']);
  
  // Définir le rôle selon le type d'utilisateur choisi :
  if ($type_utilisateur == 'client') {
    $role = 1; // Client
  } elseif ($type_utilisateur == 'demenageur') {
    $role = 2; // Déménageur
  } else {
    $role = 0; // Par défaut (compte non activé)
  }
  
  // Définir des valeurs pour le role :
  // 0 : le compte n'est pas activé,
  // 1 : client,
  // 2 : déménageur,
  // 3 : admin

  // Option pour bcrypt (voir le lien du cours vers le site de PHP) :
  $options = [
        'cost' => 10,
  ];
  // On crypte le mot de passe
  $password_crypt = password_hash($password, PASSWORD_BCRYPT, $options);

  // Connexion :
  require_once("../config/param.inc.php");
  $mysqli = new mysqli($host, $login, $passwd, $dbname);
  if ($mysqli->connect_error) {
    $_SESSION['erreur']="Problème de connexion à la base de données ! &#128557;";
      // die('Erreur de connexion (' . $mysqli->connect_errno . ') '
              // . $mysqli->connect_error);
  }  // Vérifier si l'email est déjà utilisé (éviter les doublons d'inscription)
  if ($checkStmt = $mysqli->prepare("SELECT id, email, nom, prenom FROM compte WHERE email = ?")) {
    $checkStmt->bind_param("s", $email);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult && $checkResult->num_rows > 0) {
      $existing_account = $checkResult->fetch_assoc();
      $_SESSION['erreur'] = "ERREUR : Impossible de s'inscrire avec ce login (email). Un compte existe déjà avec l'adresse email '" . htmlspecialchars($email) . ". Si c'est votre compte, veuillez vous connecter au lieu de vous inscrire à nouveau.";
      $checkStmt->close();
      $mysqli->close();
      header('Location: inscription.php');
      exit();
    }

    $checkStmt->close();
  } else {
    $_SESSION['erreur'] = "Erreur lors de la vérification des doublons d'inscription.";
    $mysqli->close();
    header('Location: inscription.php');
    exit();
  }


  // Modifier la requête en fonction de la table et/ou des attributs :
  if ($stmt = $mysqli->prepare("INSERT INTO compte(nom, prenom, email, password, role) VALUES (?, ?, ?, ?, ?)")) {

    $stmt->bind_param("ssssi", $nom, $prenom, $email, $password_crypt, $role);
    // Le message est mis dans la session, il est préférable de séparer message normal et message d'erreur.
    if($stmt->execute()) {
        // Requête exécutée correctement 
        $_SESSION['message'] = "Enregistrement réussi";

    } else {
        // Il y a eu une erreur
        $_SESSION['erreur'] =  "Impossible d'enregistrer";
    }
    $stmt->close();
  } else {
    $_SESSION['erreur'] = "Impossible de préparer l'enregistrement.";
  }
  $mysqli->close();

  header('Location: ../index.php');


?>
