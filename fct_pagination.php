<?php


function pagination($total, $page_actu = 1, $par_pg = 5, $var_get = "pg") {
    
    
    if (isset($_GET['rub'])) {
    $rub_add="rub=".$_GET['rub']."&";
}else{
    $rub_add="";
}

    
// par sécurité, il faudrait vérifier les types de var, surtout $var_get
    //On calcul le nbre de page à afficher (INT), arrondi à l'entier supérieur (ceil)
    $nombre_pg = ceil($total / $par_pg);
    // Si on a plus d'une page => alors afficher la pagination
    if ($nombre_pg > 1) {
        $sortie = "Page ";
        for ($i = 1; $i <= $nombre_pg; $i++) {
            if ($i == 1) {
                if ($i == $page_actu) {
                    $sortie .="<< < ";
                } else {
                    $sortie .="<a href='?".$rub_add."$var_get=$i'><<</a> <a href='?".$rub_add."$var_get=" . ($page_actu - 1) . "'><</a> ";
                }
            }
            if ($i != $page_actu) {
                $sortie .= "<a href='?".$rub_add."$var_get=$i'>$i</a>";
            } else {
                $sortie .= "$i";
            }
            //Si on est pas à la dernière boucle, on rajoute le tiret pourt séparé les liens vers les pages
            if ($i != $nombre_pg) {
                $sortie.=" - ";
            } else {
                if ($i == $page_actu) {
                    $sortie .=" > >>";
                } else {
                    $sortie .=" <a href='?".$rub_add."$var_get=" . ($page_actu + 1) . "'>></a> <a href='?".$rub_add."$var_get=$nombre_pg'>>></a> ";
                }
            }
        }
        return $sortie;
    } else {
        return "Page 1";
    }
    return $total;
}
