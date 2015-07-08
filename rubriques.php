<?php
session_start();
require_once 'fonctions.php';
require_once 'fct_pagination.php';
include_once 'inc/nav_db.php';

/* specific CODE */
/* Diff 
 * if connected 
 * if page
 */


if (isset($_GET['rub'])) {
    /* NAVIGATION */
// on va compter le nombre de lignes de résultat pour la pagination, le COUNT ne renvoie q'une ligne de résultat
    // !!! correction req à corriger !!! rem: no need Join on rubriques, only on photo_has_rubriques enj INNER!
    //cf probleme-juin
    
    $recup_nb_photo = "SELECT COUNT(*) AS nb FROM photo p
	LEFT JOIN photo_has_rubriques h ON h.photo_id = p.id
	LEFT JOIN rubriques r ON h.rubriques_id = r.id 
    WHERE r.id=".$_GET['rub'].";";
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



// récup des nom de rubriques
    $sql_rub = "SELECT * FROM rubriques r WHERE r.id=" . $_GET['rub'];

    $recup_sql_rub = mysqli_query($mysqli, $sql_rub) or die(mysqli_error($mysqli));
    $rub = mysqli_fetch_assoc($recup_sql_rub);


// Récupération des images selon rubriques passé en GET
    $sql = "SELECT p.lenom,p.lextention,p.letitre,p.ladedsc, u.lelogin, u.lenom AS auteur,
    GROUP_CONCAT(r.id) AS rubid, 
    GROUP_CONCAT(r.lintitule SEPARATOR '|||') AS lintitule 
    FROM photo p
    INNER JOIN utilisateur u ON u.id = p.utilisateur_id
    LEFT JOIN photo_has_rubriques h ON h.photo_id = p.id
    LEFT JOIN rubriques r ON h.rubriques_id = r.id
    WHERE r.id=" . $_GET['rub'] . " 
    GROUP BY p.id
    ORDER BY p.id DESC
    LIMIT $debut,$elements_par_page";

    $recup_sql = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
}







/* Dynamic content */
$htmltitle = "- " . $rub['lintitule'];
$htmlh1 = "Rubriques de ";

include_once 'inc/commun_html.php';
?>
<div id="connect">
    <?php
// si on est pas (ou plus) connecté
    if (!isset($_SESSION['sid']) || $_SESSION['sid'] != session_id()) {
        ?>

        <div class="center">
            <nav>

                <?php
                
                echo pagination($nb_total, $pg_actu, $elements_par_page, $get_pagination)
                ?>
            </nav>
        </div>
        <?php
        echo "<h2>Bienvenue sur la rubrique " . $rub['lintitule'] . " de Telepro-photos.fr</h2>";


        echo "<div id='lesphotos'>";

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
        // sinon on est connecté
    } else {
        /* Si connecté  */




        // texte d'accueil
        echo "<h3>Bienvenue " . $_SESSION['lenom'] . ". Vous êtes connecté en tant que <span title='" . $_SESSION['lenom'] . "'>" . $_SESSION['nom_perm'] . "</span> sur la page catégorie</h3>";
        echo "<h4><a class='right' href='deconnect.php'>Déconnexion</a></h4>";

        // liens  suivant la permission utilisateur
        switch ($_SESSION['laperm']) {
            // si on est l'admin
            case 0 :
                echo "<p><a class='right' href='admin.php'>Administrer le site</a></p>";
                break;
            // si on est modérateur
            case 1:
                echo "<p><a class='right' href='modere.php'>Modérer le site</a></p>";
                break;
            // si autre droit (ici simple utilisateur)
            default :
        }
        ?>




        <div id="milieu">

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
        // affichage des sections
        $sections = explode('|||', $ligne['lintitule']);
        //$idsections = explode(',',$ligne['idrub']);
        foreach ($sections AS $key => $valeur) {
            echo " $valeur<br/>";
        }

        echo "<span>par " . $ligne['auteur'] . "</span>";
        echo "</p>";
        echo "</div>";
    }
}
?>

        </div><!-- FIN div milieu -->    
    </div><!-- FIN div main -->  
<?php
include_once 'inc/footer.php';

