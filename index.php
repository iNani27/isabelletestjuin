<?php
require_once 'connect.php';
$sql_nav="SELECT `id`, `lintitule` FROM `rubriques`";
$req_nav=mysqli_query($mysqli, $sql_nav)or die(mysqli_error($mysqli));

?>
<!DOCTYPE html>
<!--
TI juin
20150624 INani@CF2m
-->
<html>
    <head>
        <meta charset="UTF-8">
        <title>Telepro-photos.fr</title>
        <link rel="stylesheet" href="css/style.css" />
    </head>
    <body>
        <h1>Telepro-photos.fr</h1>
        <nav>
            <ul>
                <li><a href="index.php">Accueil</a></li>
                <li class="rubr">Rubriques
                <ul>
                    <?php
                    while($ligne = mysqli_fetch_assoc($req_nav)){
                    echo "<li><a href='".$ligne['id']."'>".$ligne['lintitule']."</a></li>";
                    }
                    ?>
                </ul>
                
                </li>
                <li><a href="contact.php">Nous contacter</a></li>
                <li><a href="admin.php">Espace Client</a></li>
            </ul>
        </nav>
        <?php
        // put your code here
        ?>
    </body>
</html>
