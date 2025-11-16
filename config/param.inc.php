<?php
  // Définir le fuseau horaire pour éviter les warnings
  date_default_timezone_set('Europe/Paris');
  
  // Définir la racine du projet
  define('BASE_PATH', '/Projet-conception-site-web');
  
  // Paramètre de connexion à la BDD 
  $host="localhost";
  $login="root";
  $passwd="";
  $dbname="bdd"; // À modifier si nécessaire
?>