<?php
session_start();
require_once 'fonctions.php';
require_once 'fct_pagination.php';
include_once 'inc/nav_db.php';


// si tentative de connexion
if (isset($_POST['lelogin'])) {
    $lelogin = traite_chaine($_POST['lelogin']);
    $lemdp = traite_chaine($_POST['lemdp']);

    // vérification de l'utilisateur dans la db
    $sql = "SELECT  u.id, u.lemail, u.lenom,
		d.lenom AS nom_perm, d.laperm 
	FROM utilisateur u
		INNER JOIN droit d ON u.droit_id = d.id 
    WHERE u.lelogin='$lelogin' AND u.lepass = '$lemdp';";
    $requete = mysqli_query($mysqli, $sql)or die(mysqli_error($mysqli));
    $recup_user = mysqli_fetch_assoc($requete);

    // vérifier si on a récupèré un utilisateur
    if (mysqli_num_rows($requete)) { // vaut true si 1 résultat (ou plus), false si 0
        // si l'utilisateur est bien connecté
        $_SESSION = $recup_user; // transformation des résultats de la requête en variable de session
        $_SESSION['sid'] = session_id(); // récupération de la clef de session
        $_SESSION['lelogin'] = $lelogin; // récupération du login (du POST après traitement)
        // var_dump($_SESSION);
        // redirection vers la page d'accueil (pour éviter les doubles connexions par F5)
        header('location: ' . CHEMIN_RACINE . 'client.php');
        

    }
    else {
        /*$erreur="Ces identifiants n'existent pas";*/
        echo "Ces identifiants n'existent pas";
    }
    
}



// si on a envoyé le formulaire et qu'un fichier est bien attaché
if (isset($_POST['letitre']) && isset($_FILES['lefichier'])) {

    // traitement des chaines de caractères
    $letitre = traite_chaine($_POST['letitre']);
    $ladesc = traite_chaine($_POST['ladesc']);

    // récupération des paramètres du fichier uploadé
    $limage = $_FILES['lefichier'];

    // appel de la fonction d'envoi de l'image, le résultat de la fonction est mise dans la variable $upload
    $upload = upload_originales($limage, $dossier_ori, $formats_acceptes);

    // si $upload n'est pas un tableau c'est qu'on a une erreur
    if (!is_array($upload)) {
        // on affiche l'erreur
        echo $upload;

        // si on a pas d'erreur, on va insérer dans la db et créer la miniature et grande image   
    } else {
        //var_dump($upload);
        // création de la grande image qui garde les proportions
        $gd_ok = creation_img($dossier_ori, $upload['nom'], $upload['extension'], $dossier_gd, $grande_large, $grande_haute, $grande_qualite);

        // création de la miniature centrée et coupée
        $min_ok = creation_img($dossier_ori, $upload['nom'], $upload['extension'], $dossier_mini, $mini_large, $mini_haute, $mini_qualite, false);

        // si la création des 2 images sont effectuées
        if ($gd_ok == true && $min_ok == true) {
            //var_dump($_POST);
            // préparation de la requête (on utilise un tableau venant de la fonction upload_originales, de champs de formulaires POST traités et d'une variable de session comme valeurs d'entrée)
            $sql = "INSERT INTO photo (lenom,lextention,lepoids,lahauteur,lalargeur,letitre,ladedsc,utilisateur_id) 
	VALUES ('" . $upload['nom'] . "','" . $upload['extension'] . "'," . $upload['poids'] . "," . $upload['hauteur'] . "," . $upload['largeur'] . ",'$letitre','$ladesc'," . $_SESSION['id'] . ");";

            mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));

            // récupération de la dernière id insérée par la requête qui précède (dans photo par l'utilisateur actuel)
            $id_photo = mysqli_insert_id($mysqli);

            // vérification de l'existence des sections cochées dans le formulaire
            if (isset($_POST['section'])) {
                foreach ($_POST['section'] AS $clef => $valeur) {
                    if (ctype_digit($valeur)) {
                        mysqli_query($mysqli, "INSERT INTO photo_has_rubriques VALUES ($id_photo,$valeur);")or die(mysqli_error($mysqli));
                    }
                }
            }
        } else {
            echo 'Erreur lors de la création des images redimenssionnées';
        }
    }
}



// si on confirme la suppression
if (isset($_GET['delete']) && ctype_digit($_GET['delete'])) {
    $idphoto = $_GET['delete'];
    $idutil = $_SESSION['id'];

    // récupération du nom de la photo
    $sql1 = "SELECT lenom, lextention FROM photo WHERE id=$idphoto;";
    $nom_photo = mysqli_fetch_assoc(mysqli_query($mysqli, $sql1));

    // supression dans la table photo_has_rubriques (sans l'utilisation de la clef étrangère)
    $sql2 = "DELETE FROM photo_has_rubriques WHERE photo_id = $idphoto";
    mysqli_query($mysqli, $sql2);

    // puis suppression dans la table photo
    $sql3 = "DELETE FROM photo WHERE id = $idphoto AND utilisateur_id = $idutil;";
    mysqli_query($mysqli, $sql3);

    /* ISA ::: ? le nom du fichier s'écrit en haut de la page, si on supprime la ligne suivante, ce sont les unlink qui ne trouvent plus le nom de l'image ? */
    $dossier_ori . $nom_photo['lenom'] . "." . $nom_photo['lextention'];

    // supression physique des fichiers
    unlink($dossier_ori . $nom_photo['lenom'] . "." . $nom_photo['lextention']);
    unlink($dossier_gd . $nom_photo['lenom'] . ".jpg");
    unlink($dossier_mini . $nom_photo['lenom'] . ".jpg");
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






// récupération de toutes les rubriques pour le formulaire d'insertion
$sqlrub = "SELECT * FROM rubriques ORDER BY lintitule ASC;";
$recup_section = mysqli_query($mysqli, $sqlrub);

$limit_pagi = "LIMIT $debut,$elements_par_page";

/* Dynamic content */
$htmltitle = "- Espace Membre";
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
        /* Si connecté  */

// récupérations des images de l'utilisateur connecté dans la table photo avec leurs sections même si il n'y a pas de sections sélectionnées (jointure externe avec LEFT)
$sqlprofil = "SELECT p.*, GROUP_CONCAT(r.id) AS idrub, GROUP_CONCAT(r.lintitule SEPARATOR '|||' ) AS lintitule
    FROM photo p
    INNER JOIN utilisateur u ON u.id = p.utilisateur_id
    LEFT JOIN photo_has_rubriques h ON h.photo_id = p.id
    LEFT JOIN rubriques r ON h.rubriques_id = r.id
        WHERE p.utilisateur_id = ".$_SESSION['id']."
        GROUP BY p.id
        ORDER BY p.id DESC
        LIMIT $debut,$elements_par_page";

/* LIMIT $debut,$elements_par_page 
WHERE p.utilisateur_id = ".$_SESSION['id']."
 *  */

$recup_sql = mysqli_query($mysqli, $sqlprofil) or die(mysqli_error($mysqli));


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

            <div id="formulaire">
                <form action="client.php" enctype="multipart/form-data" method="POST" name="onposte">
                    <input type="text" name="letitre" required /><br/>
                   <!-- <input type="hidden" name="MAX_FILE_SIZE" value="50000000" /> -->
                    <input type="file" name="lefichier" required /><br/>
                    <textarea name="ladesc"></textarea><br/>

                    <input type="submit" value="Envoyer le fichier" /><br/>
                    Rubriques : <?php
                    // affichage des sections
                    while ($ligne = mysqli_fetch_assoc($recup_section)) {
                        echo $ligne['lintitule'] . " : <input type='checkbox' name='section[]' value='" . $ligne['id'] . "' > | ";
                    }
                    ?>
                </form>
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
                    echo"<br/><a href='modif.php?id=" . $ligne['id'] . "'><img src='img/modifier.png' alt='modifier' /></a> <img onclick='supprime(" . $ligne['id'] . ");' src='img/supprimer.png' alt='supprimer' />
                     </p>";
                    echo "</div>";
                }
            }
            ?>

        </div><!-- FIN div milieu -->    
    </div><!-- FIN div main -->  
    <?php
    include_once 'inc/footer.php';
    ?>
