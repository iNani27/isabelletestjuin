// fonction de confirmation pour supprimer un élément    
function supprime(id) {
    var question = confirm("Voulez-vous vraiment supprimer le proverbe dont l'id est " + id);
    if (question) { // si on répond ok
        // on redirige sur la page actuelle en donnant l'id à supprimer
        document.location.href = "?delete=" + id;
    }else{
        return false;
    }
}
