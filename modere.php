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


// si on est pas (ou plus) connecté
if (!isset($_SESSION['sid']) || $_SESSION['sid'] != session_id() || $_SESSION['laperm']!=1) {
    header("location: deconnect.php");
}



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


// récupérations des images de tous modif only
$sqlallmod = "SELECT u.id,p.*,u.lenom AS auteur, GROUP_CONCAT(r.id) AS idrub, GROUP_CONCAT(r.lintitule SEPARATOR '|||' ) AS lintitule
    FROM photo p
    INNER JOIN utilisateur u ON u.id = p.utilisateur_id
    LEFT JOIN photo_has_rubriques h ON h.photo_id = p.id
    LEFT JOIN rubriques r ON h.rubriques_id = r.id
        GROUP BY p.id
        ORDER BY p.id DESC
        LIMIT $debut,$elements_par_page";

$recup_sql = mysqli_query($mysqli, $sqlallmod) or die(mysqli_error($mysqli));


// récupération de toutes les rubriques pour le formulaire d'insertion
$sqlrub = "SELECT * FROM rubriques ORDER BY lintitule ASC;";
$recup_section = mysqli_query($mysqli, $sqlrub);

$limit_pagi= "LIMIT $debut,$elements_par_page";

/* Dynamic content */
$htmltitle = "- Espace Modération";
$htmlh1 = "Espace membre de ";

include_once 'inc/commun_html.php';
?>
<div id="connect">
    <?php
// si on est pas (ou plus) connecté
    if (!isset($_SESSION['sid']) || $_SESSION['sid'] != session_id()) {
        ?>
        <form action="" id="connexion" name="connexion" method="POST">
            <input type="text" name="lelogin" required />
            <input type="password" name="lemdp" required />
            <input type="submit" value="Connexion" />
        </form>

        <p>
            <a href="mdp.php">Mot de passe oublié?</a> | 
            <a href="inscription.php">Inscription</a>
        </p>
        <?php
        // sinon on est connecté
    } else {

        // texte d'accueil
        echo "<h3>Bienvenue " . $_SESSION['lenom'] . ". Vous êtes connecté en tant que <span title='" . $_SESSION['lenom'] . "'>" . $_SESSION['nom_perm'] . "</span> sur votre espace client</h3>";
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

            
            </div>
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
                    echo"<br/><a href='modif.php?id=" . $ligne['id'] . "'><img src='img/modifier.png' alt='modifier' /></a> 
                     </p>";
                    echo "</div>";
                }
            }
            ?>

        </div><!-- FIN div milieu -->    
    </div><!-- FIN div main -->  
    <?php
    include_once 'inc/footer.php';

