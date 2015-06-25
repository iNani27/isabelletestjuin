<?php
session_start();
require_once 'fonctions.php';
include_once 'inc/nav_db.php';

/* specific CODE */
/* Diff 
 * if connected 
 * if page
 */

// si on est pas (ou plus) connecté
if (!isset($_SESSION['sid']) || $_SESSION['sid'] != session_id()) {
    header("location: deconnect.php");
}



// récupérations des images de l'utilisateur connecté dans la table photo avec leurs sections même si il n'y a pas de sections sélectionnées (jointure interne avec LEFT)
$sqlconnex = "SELECT p.*, GROUP_CONCAT(r.id) AS idrub, GROUP_CONCAT(r.lintitule SEPARATOR '|||' ) AS lintitule
    FROM photo p
	LEFT JOIN photo_has_rubriques h ON h.photo_id = p.id
    LEFT JOIN rubriques r ON h.rubriques_id = r.id
        WHERE p.utilisateur_id = " . $_SESSION['id'] . "
        GROUP BY p.id
        ORDER BY p.id DESC;
    ";
$recup_sqlconnex = mysqli_query($mysqli, $sqlconnex) or die(mysqli_error($mysqli));

// récupération de toutes les rubriques pour le formulaire d'insertion
$sqlrub = "SELECT * FROM rubriques ORDER BY lintitule ASC;";
$recup_section = mysqli_query($mysqli, $sqlrub);


/* Dynamic content */
$htmltitle = "- Espace Membre";
$htmlh1 = "Espace membre de ";

include_once 'inc/commun_html.php';
?>

<?php
// texte d'accueil
echo "<h2>Bonjour " . $_SESSION['lenom'] . '</h2>';
echo "<p>Vous êtes connecté en tant que <span title='" . $_SESSION['lenom'] . "'>" . $_SESSION['nom_perm'] . "</span> sur votre espace client</p>";
echo "<h4><a class='right' href='deconnect.php'>Déconnexion</a></h4>";

// liens  suivant la permission utilisateur
switch ($_SESSION['laperm']) {
    // si on est l'admin
    case 0 :
        echo "<p><a class='right' href='admin.php'>Administrer le site</a>  <a class='right' href='membre.php'>Espace membre</a></p>";
        break;
    // si on est modérateur
    case 1:
        echo "<p><a class='right' href='modere.php'>Modérer le site</a>  <a class='right' href='membre.php'>Espace membre</a></p>";
        break;
    // si autre droit (ici simple utilisateur)
    default :
        echo "<p><a class='right' href='membre.php'>Espace membre</a></p>";
}
?>




<div id="milieu">




    <div id="lesphotos">
        <?php
        while ($ligne = mysqli_fetch_assoc($recup_sqlconnex)) {
            echo "<div class='miniatures'>";
            echo "<h3>" . $ligne['letitre'] . "</h3>";
            echo "<a href='" . CHEMIN_RACINE . $dossier_gd . $ligne['lenom'] . ".jpg' target='_blank'><img src='" . CHEMIN_RACINE . $dossier_mini . $ligne['lenom'] . ".jpg' alt='' /></a>";
            echo "<p>" . $ligne['ladedsc'] . "<br /><br />";
            // affichage des sections
            $sections = explode('|||', $ligne['lintitule']);
            //$idsections = explode(',',$ligne['idrub']);
            foreach ($sections AS $key => $valeur) {
                echo " $valeur<br/>";
            }
            echo "</p>";
            echo "</div>";
        }
        ?>

    </div><!-- FIN div milieu -->    
</div><!-- FIN div main -->  
<?php
include_once 'inc/footer.php';
?>
