<?php
session_start();
require_once 'fonctions.php';
require_once 'fct_pagination.php';
include_once 'inc/nav_db.php';

 /* NAVIGATION */
// on va compter le nombre de lignes de résultat pour la pagination, le COUNT ne renvoie q'une ligne de résultat
$recup_nb_photo = "SELECT COUNT(*) AS nb FROM photo;";
// requete de récupération
$tot = mysqli_query($mysqli, $recup_nb_photo);
// transformation du résultat en tableau associatif
$maligne = mysqli_fetch_assoc($tot);
// variable contenant le nombre total de proverbes
$nb_total = $maligne['nb'];

// Vérification de la variable GET de la pagination
if (isset($_GET[$get_pagination])) {
    //Si la var est un entier positif
    if (ctype_digit($_GET[$get_pagination])) {
        // on récupère la valeur de la var ssi elle est numeric +
        $pg_actu = $_GET[$get_pagination];
    } else {
        $pg_actu = 1; // par défaut aller en page 1
    }
} else {
    $pg_actu = 1;
}

// Création de la varible $debut utilisée dans le LIMIT
$debut = (($pg_actu - 1) * $elements_par_page);
/* FIN NAVIGATION */
/* specific CODE */
/* Diff 
 * if connected 
 * if page
 */

// si on est pas (ou plus) connecté
/* if (!isset($_SESSION['sid']) || $_SESSION['sid'] != session_id()) {
  /*header("location: deconnect.php"); */
/* header("location: index.php"); */
/* } */


/* Affiche all img */
// récupérations des images de l'utilisateur connecté dans la table photo avec leurs sections même si il n'y a pas de sections sélectionnées (jointure externe avec LEFT)
$sqlall = "SELECT p.*,u.id, u.lenom AS auteur
    FROM photo p
     INNER JOIN utilisateur u ON u.id = p.utilisateur_id 
	
        
        GROUP BY p.id
        ORDER BY p.id DESC
        LIMIT $debut,$elements_par_page";
$recup_sql = mysqli_query($mysqli, $sqlall) or die(mysqli_error($mysqli));

// récupération de toutes les rubriques pour le formulaire d'insertion
/*
  $sqlrub = "SELECT * FROM rubriques ORDER BY lintitule ASC;";
  $recup_section = mysqli_query($mysqli, $sqlrub);

  /* Dynamic content */
$htmltitle = "";
$htmlh1 = "Bienvenue sur ";

include_once 'inc/commun_html.php';
?>       
<?php
/* specific CODE */
/* Affiche all img */
?>
<div class="center">
    <nav>
        <?php
        echo pagination($nb_total, $pg_actu, $elements_par_page, $get_pagination)
        ?>
    </nav>
</div>
<div id="lesphotos">
    <?php
    while ($ligne = mysqli_fetch_assoc($recup_sql)) {
        echo "<div class='miniatures'>";
        echo "<h3>" . $ligne['letitre'] . "</h3>";
        echo "<a href='" . CHEMIN_RACINE . $dossier_gd . $ligne['lenom'] . ".jpg' target='_blank'><img src='" . CHEMIN_RACINE . $dossier_mini . $ligne['lenom'] . ".jpg' alt='' /></a>";
        echo "<p>" . $ligne['ladedsc'] . "<br /><br />";
        echo "<span>par " . $ligne['auteur'] . "</span>";




        echo "</p>";


        echo "</div>";
    }
    ?>


    <?php
    include_once 'inc/footer.php';
    ?>
