<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Inscription</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <header>
    <h1>Inscription</h1>
    <nav>
      <ul>
        <li><a href="index.html">Accueil</a></li>
        <li><a href="connexion.html">Connexion</a></li>
      </ul>
    </nav>
  </header>

  <main>
    <form action="#" method="post">
      <label for="nom">Nom :</label>
      <input type="text" id="nom" name="nom" required>

      <label for="email">Email :</label>
      <input type="email" id="email" name="email" required>

      <label for="mdp">Mot de passe :</label>
      <input type="password" id="mdp" name="mdp" required>

      <button type="submit">S'inscrire</button>
    </form>
  </main>
</body>
</html>
