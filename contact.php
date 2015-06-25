<?php

    if ( isset( $_POST['letitre'] ) ){

        $titre = strip_tags(trim( $_POST['letitre'] ));
        $mail = strip_tags(trim( $_POST['lemail'] ));
        $texte = strip_tags(trim( $_POST['lemessage'] ));

        $mailwebdvlpr = "webdeveloperinani@gmail.com";

        $entete = 'From: '.$mail."\r\n".'Reply-To: '.$mail."\r\n".'X-mailer: PHP/'.phpversion();
        
        // ENVOYER le mail
        mail($mailwebdvlpr,$titre,$texte,$entete);
		// Confirmation
		$affiche = '<h3>Votre message nous est transmit, merci</h3>';	
    }
include_once 'inc/nav_db.php'; 
/* specific CODE */
/* Diff 
 * if connected 
 * if page
 */
/* Dynamic content */
$htmltitle="- Nous contacter";
$htmlh1="Bienvenue sur la page de contact de ";

include_once 'inc/commun_html.php';
?>       
        <?php
	if(isset($affiche)){
		echo utf8_encode($affiche);}else{
	?>
		<h1>Formulaire de contact</h1>
                <form name="monform" method="post">
   <input name="lenom" type="text" placeholder="Votre nom" required />
    <input name="leprenom" type="text" placeholder="Votre prénom" />
    
    <?php /* le titre */ ?>
    <input type="radio" value="Mr" name="titre" id="mr"><label for="mr">Mr</label>
    <input type="radio" value="Mme" name="titre" id="mme"><label for="mme">Mme</label>
    <input type="radio" value="Melle" name="titre" id="melle"><label for="melle">Melle</label>
   <p><br /></p>
                    <input name="letitre" type="text" placeholder="Objet de votre message" required />
                    <p><br /></p>

                    <input name="lemail" type="email" placeholder="Votre adresse e-mail" required />
                    <p><br /></p>

                    <textarea maxlength="500" name="lemessage" placeholder="Votre demande" required></textarea>

                    <p><br /></p>
                    <input type="submit" value="Envoyer" />
                </form>
		<?php		
		}
		?>
	
              
    </div><!-- FIN div main -->       
    </body>
</html>
