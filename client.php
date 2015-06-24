<?php
session_start();
require_once 'fonctions.php';
include_once 'inc/nav_db.php';

/* specific CODE */
/* Diff 
 * if connected 
 * if page
 */
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
        header('location: ' . CHEMIN_RACINE.'client.php');
    }
}



/* Dynamic content */
$htmltitle = "- Espace Client";
$htmlh1 = "Connectez vous à ";

include_once 'inc/commun_html.php';
?>       
<?php
/* specific CODE */
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
    echo "<h2>Bonjour " . $_SESSION['lenom'] . '</h2>';
    echo "<p>Vous êtes connecté en tant que <span title='" . $_SESSION['lenom'] . "'>" . $_SESSION['nom_perm'] . "</span></p>";
    echo "<h4><a href='deconnect.php'>Déconnexion</a></h4>";

    // liens  suivant la permission utilisateur
    switch ($_SESSION['laperm']) {
        // si on est l'admin
        case 0 :
            echo "<a href='admin.php'>Administrer le site</a> - <a href='membre.php'>Espace membre</a>";
            break;
        // si on est modérateur
        case 1:
            echo "<a href='modere.php'>Modérer le site</a> - <a href='membre.php'>Espace membre</a>";
            break;
        // si autre droit (ici simple utilisateur)
        default :
            echo "<a href='membre.php'>Espace membre</a>";
    }
}
?>
</div>
    <?php
    include_once 'inc/footer.php';
    ?>

