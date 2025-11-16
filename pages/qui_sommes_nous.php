<?php
  session_start();
  $titre = "Qui sommes-nous ?";
  require_once('../config/param.inc.php');
  include('../includes/header.inc.php');
  include('../includes/menu.inc.php');
  include('../includes/message.inc.php')
?>

<div class="row mb-5">
  <div class="col-lg-10 mx-auto">
    <h1 class="mb-4">Qui sommes-nous ?</h1>
    <p class="lead">LiftUp, la plateforme de confiance pour simplifier tous vos déménagements</p>
  </div>
</div>

<!-- Notre histoire -->
<div class="row mb-5">
  <div class="col-lg-10 mx-auto">
    <div class="card border-0 shadow-sm">
      <div class="card-body p-4">
        <h2 class="card-title mb-4">Notre Histoire</h2>
        <p class="card-text">
          Fondée en 2025, LiftUp est née d'un constat simple : le déménagement reste une étape stressante et coûteuse 
          dans la vie de millions de personnes chaque année. Face à un marché fragmenté où il est difficile de trouver 
          des professionnels fiables et à des prix transparents, nous avons décidé de créer une solution.
        </p>
        <p class="card-text">
          Notre plateforme connecte directement les particuliers avec des déménageurs professionnels vérifiés, 
          permettant une mise en concurrence saine et une transparence totale sur les prix et les services proposés.
        </p>
        <p class="card-text">
          Aujourd'hui, LiftUp compte plus de 150 déménageurs partenaires et a facilité plus de 500 déménagements 
          à travers toute la France, avec un taux de satisfaction client de 98%.
        </p>
      </div>
    </div>
  </div>
</div>

<!-- Notre équipe -->
<div class="row mb-5">
  <div class="col-lg-10 mx-auto">
    <h2 class="mb-4 text-center">Notre Équipe</h2>
    <div class="row g-4 justify-content-center">
      <div class="col-md-5">
        <div class="card h-100 text-center">
          <div class="card-body">
            <div class="rounded-circle bg-primary text-white mx-auto mb-3 d-flex align-items-center justify-content-center icon-circle">
              <span class="display-4">👨‍💼</span>
            </div>
            <h5 class="card-title">Thomas Cole</h5>
            <p class="text-muted">Co-fondateur</p>
            <p class="card-text">
              Passionné par l'innovation et le service client, Thomas a co-fondé LiftUp pour révolutionner 
              l'expérience du déménagement et faciliter la mise en relation entre clients et professionnels.
            </p>
          </div>
        </div>
      </div>
      
      <div class="col-md-5">
        <div class="card h-100 text-center">
          <div class="card-body">
            <div class="rounded-circle bg-success text-white mx-auto mb-3 d-flex align-items-center justify-content-center icon-circle">
              <span class="display-4">👨‍💻</span>
            </div>
            <h5 class="card-title">Kenzi Allam</h5>
            <p class="text-muted">Co-fondateur</p>
            <p class="card-text">
              Expert en développement et en technologies web, Kenzi a conçu la plateforme LiftUp 
              pour qu'elle soit intuitive, performante et accessible à tous.
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Nos valeurs -->
<div class="row mb-5">
  <div class="col-lg-10 mx-auto">
    <h2 class="mb-4 text-center">Nos Valeurs</h2>
    <div class="row g-4">
      <div class="col-md-6">
        <div class="card h-100 border-primary">
          <div class="card-body">
            <h5 class="card-title text-primary">🤝 Confiance</h5>
            <p class="card-text">
              La confiance est au cœur de notre activité. Nous vérifions minutieusement chaque déménageur 
              et mettons en place des systèmes d'évaluation transparents pour garantir des services de qualité.
            </p>
          </div>
        </div>
      </div>
      
      <div class="col-md-6">
        <div class="card h-100 border-success">
          <div class="card-body">
            <h5 class="card-title text-success">💡 Innovation</h5>
            <p class="card-text">
              Nous innovons constamment pour améliorer l'expérience de nos utilisateurs, 
              que ce soit par de nouvelles fonctionnalités ou des processus plus efficaces.
            </p>
          </div>
        </div>
      </div>
      
      <div class="col-md-6">
        <div class="card h-100 border-warning">
          <div class="card-body">
            <h5 class="card-title text-warning">🎯 Excellence</h5>
            <p class="card-text">
              Nous visons l'excellence dans tout ce que nous faisons, du service client 
              à la qualité de notre plateforme, en passant par la sélection de nos partenaires.
            </p>
          </div>
        </div>
      </div>
      
      <div class="col-md-6">
        <div class="card h-100 border-info">
          <div class="card-body">
            <h5 class="card-title text-info">🌍 Accessibilité</h5>
            <p class="card-text">
              Nous croyons que tout le monde mérite d'accéder à des services de déménagement 
              de qualité, quel que soit son budget ou sa localisation.
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Notre impact -->
<div class="row mb-5">
  <div class="col-lg-10 mx-auto">
    <h2 class="mb-4 text-center">Notre Impact</h2>
    <div class="row text-center g-4">
      <div class="col-md-3">
        <div class="card border-0 bg-light h-100">
          <div class="card-body">
            <h3 class="display-4 text-primary mb-3">500+</h3>
            <h5>Déménagements</h5>
            <p class="text-muted">réalisés avec succès</p>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card border-0 bg-light h-100">
          <div class="card-body">
            <h3 class="display-4 text-success mb-3">150+</h3>
            <h5>Déménageurs</h5>
            <p class="text-muted">certifiés sur la plateforme</p>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card border-0 bg-light h-100">
          <div class="card-body">
            <h3 class="display-4 text-warning mb-3">4.8/5</h3>
            <h5>Satisfaction</h5>
            <p class="text-muted">note moyenne des clients</p>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card border-0 bg-light h-100">
          <div class="card-body">
            <h3 class="display-4 text-info mb-3">24h</h3>
            <h5>Support</h5>
            <p class="text-muted">réponse sous 24h maximum</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Pourquoi choisir LiftUp -->
<div class="row mb-5">
  <div class="col-lg-10 mx-auto">
    <div class="card bg-primary text-white">
      <div class="card-body p-5">
        <h2 class="text-center mb-4">Pourquoi choisir LiftUp ?</h2>
        <div class="row">
          <div class="col-md-6">
            <ul class="list-unstyled">
              <li class="mb-3"> - <strong>Professionnels vérifiés</strong> - Tous nos déménageurs sont contrôlés</li>
              <li class="mb-3"> - <strong>Devis gratuits</strong> - Comparez plusieurs offres sans engagement</li>
              <li class="mb-3"> - <strong>Assurance incluse</strong> - Vos biens sont protégés</li>
            </ul>
          </div>
          <div class="col-md-6">
            <ul class="list-unstyled">
              <li class="mb-3"> - <strong>Prix transparents</strong> - Pas de frais cachés</li>
              <li class="mb-3"> - <strong>Service client réactif</strong> - Une équipe à votre écoute</li>
              <li class="mb-3"> - <strong>Avis certifiés</strong> - Des évaluations authentiques</li>
            </ul>
          </div>
        </div>
        <div class="text-center mt-4">
          <?php if (isset($_SESSION['connecte']) && $_SESSION['connecte'] === true): ?>
            <?php if ($_SESSION['role'] == 1): ?>
              <a href="creer_demenagement.php" class="btn btn-light btn-lg">Créer ma demande</a>
            <?php else: ?>
              <a href="annonces.php" class="btn btn-light btn-lg">Voir les annonces</a>
            <?php endif; ?>
          <?php else: ?>
            <a href="../auth/inscription.php" class="btn btn-light btn-lg">Rejoindre LiftUp</a>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<?php
  include('../includes/footer.inc.php');
?>
