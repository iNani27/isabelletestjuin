<?php
session_start();
require_once 'fonctions.php';
include_once 'inc/nav_db.php';

// si on est pas (ou plus) connecté
if (!isset($_SESSION['sid']) || $_SESSION['sid'] != session_id()) {
    header("location: deconnect.php");
}

// si il existe un id de type get et qu'il est numérique
if (isset($_GET['id']) && ctype_digit($_GET['id'])) {
    $idphoto = $_GET['id'];
} else {
    header("location: ./");
}

    // si on est admin ou modo
    if($_SESSION['laperm']<2){
        // on peut modifier toutes les photos
        $selon_perm="";
    // sinon on peut supprimer que ses photos
    }else{
    $selon_perm = "AND p.utilisateur_id = ".$_SESSION['id'];
    }

// si on a envoyé le formulaire et qu'un fichier est bien attaché
if (isset($_POST['letitre'])) {

    // traitement des chaines de caractères
    $letitre = traite_chaine($_POST['letitre']);
    $ladesc = traite_chaine($_POST['ladesc']);


    // mise à jour du titre et du texte si appartient à l'utilisateur connecté, ou à un admin ou un modo
    $update=mysqli_query($mysqli, "UPDATE photo p SET p.letitre='$letitre', p.ladedsc='$ladesc' WHERE p.id = $idphoto $selon_perm ;");
    if($update){
    // supression dans la table photo_has_rubrique (sans l'utilisation de la clef étrangère)
    $sql2 = "DELETE FROM photo_has_rubriques WHERE photo_id = $idphoto";
    mysqli_query($mysqli, $sql2);

    // vérification de l'existence des sections cochées dans le formulaire
    if (isset($_POST['section'])) {
        foreach ($_POST['section'] AS $clef => $valeur) {
            if (ctype_digit($valeur)) {
                // insertion dans la table photo_has_rubrique
                mysqli_query($mysqli, "INSERT INTO photo_has_rubriques VALUES ($idphoto,$valeur);")or die(mysqli_error($mysqli));
            }
        }
    }
    header("Location: client.php");
    }
}


// récupérations des images de l'utilisateur connecté dans la table photo avec leurs sections même si il n'y a pas de sections sélectionnées (jointure externe avec LEFT)
$sql = "SELECT p.*,u.lenom AS auteur, GROUP_CONCAT(r.id) AS idrub, GROUP_CONCAT(r.lintitule SEPARATOR '|||' ) AS lintitule
    FROM photo p
    INNER JOIN utilisateur u 
	LEFT JOIN photo_has_rubriques h ON h.photo_id = p.id
        LEFT JOIN rubriques r ON h.rubriques_id = r.id
        WHERE p.id = $idphoto
            $selon_perm
        GROUP BY p.id
        ORDER BY p.id DESC
        ;
    ";
$recup_sql = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));

$recup_photo = mysqli_fetch_assoc($recup_sql);

// récupération de toutes les rubriques pour le formulaire d'insertion
$sql = "SELECT * FROM rubriques ORDER BY lintitule ASC;";
$recup_section = mysqli_query($mysqli, $sql);





/* Dynamic content */
$htmltitle = "- Espace Client";
$htmlh1 = "Espace membre de ";

include_once 'inc/commun_html.php';
?>

<div id="connect"><?php
    // texte d'accueil
    echo "<h3>Bienvenue " . $_SESSION['lenom'] . ". Vous êtes connecté en tant que <span title='" . $_SESSION['lenom'] . "'>" . $_SESSION['nom_perm'] . "</span> sur votre espace de modification</h3>";
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

</div>

<div id="milieu">
    <div id="formulaire">
        <form action="" method="POST" name="onposte">
            <input type="text" name="letitre" value="<?php echo $recup_photo['letitre'] ?>" required /><br/>

            <textarea name="ladesc"><?php echo $recup_photo['ladedsc'] ?></textarea><br/>

            <input type="submit" value="Modifier" /><br/>
            Sections : <?php
            // récupération des sections de l'image dans un tableau
            $recup_sect_img = explode(',', $recup_photo['idrub']);


            // affichage des sections
            while ($ligne = mysqli_fetch_assoc($recup_section)) {
                if (in_array($ligne['id'], $recup_sect_img)) {
                    $coche = "checked";
                } else {
                    $coche = "";
                }
                echo $ligne['lintitule'] . " : <input type='checkbox' name='section[]' value='" . $ligne['id'] . "' $coche > | ";
            }
            echo "<br/><img src='" . CHEMIN_RACINE . $dossier_mini . $recup_photo['lenom'] . ".jpg' alt='' />";
            ?>
        </form>
    </div>
</div><!-- FIN div milieu -->    
</div><!-- FIN div main -->  
<?php
include_once 'inc/footer.php';

