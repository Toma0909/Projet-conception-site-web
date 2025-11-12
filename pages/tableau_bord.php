<?php
  session_start();
    // Vérifier si l'utilisateur est connecté
  if (!isset($_SESSION['connecte']) || $_SESSION['connecte'] !== true) {
    $_SESSION['erreur'] = "Vous devez être connecté pour accéder au tableau de bord.";
    header('Location: ../auth/connexion.php');
    exit();
  }

  $titre = "Tableau de bord";
  include('../includes/header.inc.php');
  include('../includes/menu.inc.php');
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
              <a href="../auth/changer_mot_passe.php" class="btn btn-outline-warning">Changer</a>
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
          <p>Gérez votre activité de déménageur :</p>
          <div class="row g-3">
            <div class="col-md-6">
              <div class="card">
                <div class="card-body">
                  <h6 class="card-title">📋 Mes interventions</h6>
                  <p class="card-text small">Consultez les déménagements pour lesquels vous avez été accepté</p>
                  <a href="mes_interventions.php" class="btn btn-info btn-sm w-100">Voir mes interventions</a>
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="card">
                <div class="card-body">
                  <h6 class="card-title">🔍 Annonces disponibles</h6>
                  <p class="card-text small">Trouvez de nouveaux clients et proposez vos services</p>
                  <a href="annonces.php" class="btn btn-info btn-sm w-100">Voir les annonces</a>
                </div>
              </div>
            </div>
          </div>
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
          <p>Gérez vos demandes de déménagement :</p>
          <div class="row g-3">
            <div class="col-md-6">
              <div class="card">
                <div class="card-body">
                  <h6 class="card-title">📦 Mes déménagements</h6>
                  <p class="card-text small">Consultez et gérez vos demandes de déménagement</p>
                  <a href="mes_demenagements.php" class="btn btn-success btn-sm w-100">Voir mes demandes</a>
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="card">
                <div class="card-body">
                  <h6 class="card-title">➕ Nouvelle demande</h6>
                  <p class="card-text small">Créez une nouvelle demande de déménagement</p>
                  <a href="creer_demenagement.php" class="btn btn-success btn-sm w-100">Créer une demande</a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <?php endif; ?>
    
  </div>
</div>

<?php
  include('../includes/footer.inc.php');
?>
