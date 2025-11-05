<?php
  session_start();
  
  // Vérifier si l'utilisateur est connecté
  if (!isset($_SESSION['connecte']) || $_SESSION['connecte'] !== true) {
    $_SESSION['erreur'] = "Vous devez être connecté pour accéder au tableau de bord.";
    header('Location: connexion.php');
    exit();
  }

  $titre = "Tableau de bord";
  include('header.inc.php');
  include('menu.inc.php');
?>

<div class="row">
  <div class="col-lg-8 mx-auto">
    <h1>Tableau de bord</h1>    <div class="card">
      <div class="card-header">
        <h5 class="card-title mb-0">Informations de votre compte</h5>
      </div>
      <div class="card-body">        <div class="row mb-3">
          <div class="col-sm-3">
            <strong>Nom :</strong>
          </div>
          <div class="col-sm-9">
            <?php echo htmlspecialchars($_SESSION['nom']); ?>
          </div>
        </div>
        
        <div class="row mb-3">
          <div class="col-sm-3">
            <strong>Prénom :</strong>
          </div>
          <div class="col-sm-9">
            <?php echo htmlspecialchars($_SESSION['prenom']); ?>
          </div>
        </div>
        
        <div class="row mb-3">
          <div class="col-sm-3">
            <strong>Email :</strong>
          </div>
          <div class="col-sm-9">
            <?php echo htmlspecialchars($_SESSION['email']); ?>
          </div>
        </div>
        
        <div class="row mb-3">
          <div class="col-sm-3">
            <strong>Type de compte :</strong>
          </div>
          <div class="col-sm-9">
            <?php 
              switch($_SESSION['role']) {
                case 0:
                  echo '<span class="badge bg-warning">Compte non activé</span>';
                  break;
                case 1:
                  echo '<span class="badge bg-success">Client</span>';
                  break;
                case 2:
                  echo '<span class="badge bg-info">Déménageur</span>';
                  break;
                case 3:
                  echo '<span class="badge bg-danger">Administrateur</span>';
                  break;
                default:
                  echo '<span class="badge bg-secondary">Inconnu</span>';
              }
            ?>
          </div>
        </div>
      </div>
    </div>
    
    <div class="mt-4">
      <div class="row">
        <div class="col-md-6 mb-3">
          <div class="card h-100">
            <div class="card-body text-center">
              <h6 class="card-title">Modifier mes informations</h6>
              <p class="card-text">Mettre à jour vos informations personnelles</p>
              <a href="#" class="btn btn-outline-primary">Modifier</a>
            </div>
          </div>
        </div>
          <div class="col-md-6 mb-3">
          <div class="card h-100">
            <div class="card-body text-center">
              <h6 class="card-title">Changer mot de passe</h6>
              <p class="card-text">Modifier votre mot de passe de connexion</p>
              <a href="changer_mot_passe.php" class="btn btn-outline-warning">Changer</a>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <?php if ($_SESSION['role'] == 2): // Si c'est un déménageur ?>
    <div class="mt-4">
      <div class="card border-info">
        <div class="card-header bg-info text-white">
          <h6 class="mb-0">Espace Déménageur</h6>
        </div>
        <div class="card-body">
          <p>Fonctionnalités spécifiques aux déménageurs :</p>
          <ul>
            <li>Gestion des demandes de devis</li>
            <li>Planning des interventions</li>
            <li>Historique des déménagements</li>
          </ul>
          <a href="#" class="btn btn-info">Accéder aux outils</a>
        </div>
      </div>
    </div>
    <?php elseif ($_SESSION['role'] == 1): // Si c'est un client ?>
    <div class="mt-4">
      <div class="card border-success">
        <div class="card-header bg-success text-white">
          <h6 class="mb-0">Espace Client</h6>
        </div>
        <div class="card-body">
          <p>Fonctionnalités spécifiques aux clients :</p>
          <ul>
            <li>Demander un devis</li>
            <li>Suivi de mes demandes</li>
            <li>Historique des déménagements</li>
          </ul>
          <a href="#" class="btn btn-success">Faire une demande</a>
        </div>
      </div>
    </div>
    <?php endif; ?>
    
  </div>
</div>

<?php
  include('footer.inc.php');
?>
