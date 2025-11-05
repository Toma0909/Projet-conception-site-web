<?php
  session_start();

  $email = htmlentities($_POST['email']);
  $password = htmlentities($_POST['password']);
  
  // Connexion :
  require_once("param.inc.php");
  $mysqli = new mysqli($host, $login, $passwd, $dbname);
  if ($mysqli->connect_error) {
    $_SESSION['erreur']="Problème de connexion à la base de données ! &#128557;";
    header('Location: index.php');
    exit();
  }

  // Requête pour trouver l'utilisateur par email
  if ($stmt = $mysqli->prepare("SELECT id, nom, prenom, email, password, role FROM compte WHERE email = ?")) {
    
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Vérifier si l'utilisateur existe
    if ($result->num_rows == 1) {
      $user = $result->fetch_assoc();
      
      // Vérifier le mot de passe saisi avec le mot de passe haché en base
      if (password_verify($password, $user['password'])) {
        // Mot de passe correct - conserver les informations dans la session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['nom'] = $user['nom'];
        $_SESSION['prenom'] = $user['prenom'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['connecte'] = true;
          // Message de succès
        $_SESSION['message'] = "Connexion réussie ! Bienvenue " . $user['prenom'] . " " . $user['nom'];
        
        // Redirection vers le tableau de bord
        header('Location: tableau_bord.php');
        
      } else {
        // Mot de passe incorrect
        $_SESSION['erreur'] = "Email ou mot de passe incorrect ! ";
        header('Location: connexion.php');
      }
      
    } else {
      // Utilisateur non trouvé
      $_SESSION['erreur'] = "Email ou mot de passe incorrect !";
      header('Location: connexion.php');
    }
    
    $stmt->close();
    
  } else {
    // Erreur dans la préparation de la requête
    $_SESSION['erreur'] = "Erreur lors de la connexion ! ";
    header('Location: connexion.php');
  }
  
  $mysqli->close();
  exit();

?>