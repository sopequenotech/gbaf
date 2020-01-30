<?php
session_start();

if(!isset($_SESSION['username']))
{
    header('Location: ../index.php');
    exit;
}

// appel de la connexion a la base de données
require 'fonctions.php';
$bdd = getBdd();

$idActeur = $_GET['idActeur'];
$_SESSION['idActeur'] = $idActeur;


// on récupere les infos de l'acteur sélectioner
$requeteInfoActeur = "SELECT * FROM acteur WHERE id_acteur=?";
$resultatInfoActeur = $bdd->prepare($requeteInfoActeur);
$resultatInfoActeur->execute(array($idActeur));
$donneesActeur = $resultatInfoActeur->fetch();
$resultatInfoActeur->closeCursor();

// on recupere les infos de l'utilisateur 
$requeteInfoUtilisateur = "SELECT * FROM account WHERE username=? AND password=?";
$resultatInfoUtilisateur = $bdd->prepare($requeteInfoUtilisateur);
$username = $_SESSION['username'];
$password = $_SESSION['password'];
$resultatInfoUtilisateur->execute(array($username, $password));
$donneesUtilisateur = $resultatInfoUtilisateur->fetch();
$resultatInfoUtilisateur->closeCursor();


// on recupere les vote de l'acteur
$requeteVoteActeur = "SELECT * FROM vote WHERE id_acteur=?";
$resultatvoteActeur = $bdd->prepare($requeteVoteActeur);
$resultatvoteActeur->execute(array($idActeur));
// on determine la note de l'acteur
$voteTrue = 0;
while($donneesVoteActeur = $resultatvoteActeur->fetch())
{
    if($donneesVoteActeur['vote'] == "true")
    {
        $voteTrue += 1;
    }
}
$resultatvoteActeur->closeCursor();

$voteTotale = $resultatvoteActeur->rowCount();

$noteVote = ($voteTrue * 5) / $voteTotale;


// données de la session
$_SESSION['idUser'] = $donneesUtilisateur['id_user'];
?>


<!doctype html>
<html lang="fr">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css"
        integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
    <link rel="stylesheet" href="../css/acteurPage.css" media="screen" type="text/css">
    <title><?php echo htmlspecialchars($donneesActeur['acteur']); ?></title>
</head>

<body>
    <div class="container-fluid">
        <!-- Header du site -->
        <header>
            <div class="barreDeNavigation">
                <nav class="navbar fixed-top">
                    <div class="logo">
                        <a href="../index.php"><img src="../img/logoGbafOpenclassrooms.png" alt="" class="logoGbaf"></a>&nbsp;
                    </div>
                    <div class="username">
                        <div class="dropdown">
                            <button class="boutonsMenu"><img src="../img/icons8-male_user.png" alt="utilisateur" class="iconeUser"></button>
                            <div class="dropdown-content">
                                <a class="dropdown-item" href="parametreUser.php"><img src="../img/icons8-settings.png" alt="icone de parametrege du compte"> Profil</a>
                                <div class="divider"></div>
                                <a class="dropdown-item" href="logout.php"><img src="../img/icons8-shutdown.png" alt="icone de deconnexion du site"> Déconnexion</a>
                            </div>
                        </div>
                        <!-- information sur l'utilisateur -->
                        <div class="identiteUser">
                            <div class="nom">
                                <h5><?php echo(htmlspecialchars($donneesUtilisateur['nom'])); ?>&nbsp;</h5>
                            </div>
                            <div class="prenom">
                                <h5><?php echo(htmlspecialchars($donneesUtilisateur['prenom'])); ?></h5>
                            </div>
                        </div>
                    </div>
                    
                </nav>
            </div>
        </header>

        <!-- Section acteur -->
        <section class="acteur">
            <div class="acteurContenu">
                <div class="acteurLogo">
                    <img src="../<?php echo htmlspecialchars($donneesActeur['logo']); ?>" alt="logo <?php echo htmlspecialchars($donneesActeur['acteur']); ?>" class="logo">
                </div>
                <div class="nomActeur">
                    <h1><?php echo htmlspecialchars($donneesActeur['acteur']); ?></h1>
                </div>
                <div class="descriptionActeur">
                    <p><?php echo $donneesActeur['description'] ?></p>
                </div>
            </div>
        </section>

        <!-- Section commentaire acteur -->
        <section class="commentaireAnnonce">
            <div class="contenuCommentaire">
                <?php
                    // on recupere les commentaire de l'acteur 
                    $requetePost = "SELECT * FROM post WHERE id_acteur=?";
                    $resultatPostActeur = $bdd->prepare($requetePost);
                    $resultatPostActeur->execute(array($idActeur));

                ?>
                <div class="nombreCommentaires">
                    <h2><?php echo $resultatPostActeur->rowCount() ?> Commentaires.</h2> 
                </div>
                <div class="newCommentaire">
                    <a href="#">Nouveau Commentaire</a>
                </div>
                <div class="likeDislikeForm">
                    <form action="likeDislikeActeur.php" class="likeDislikeForm" method="POST">
                        <p><?php echo number_format($noteVote, 2, '.', ',') ?> , <?php echo $voteTotale; ?> vote(s)</p>
                        <button type="submit" name="like" alt="like icone" class="like"><img src="../img/like.png" alt="icone like"></button>
                        <button type="submit" name="dislike" alt="dislike icone" class="dislike"><img src="../img/disLike.png" alt="dislike icone"></button>
                    </form>
                </div>
                <div class="newPostForm">
                    <form action="nouveauCommentaire.php" method="POST">
                        <label><b>Nouveau commentaire</b></label>
                        <input type="text" placeholder="Entrer votre commentaire" name="nouveauCommentaire">
                        <input type="submit" id="submit" value="Ajouter">
                    </form>
                </div>
                <div class="dernierCommentaires">
                    <?php
                    while ($donneesPostActeur = $resultatPostActeur->fetch())
                    {
                    ?>
                    <div class="postActeur">
                        <p>Prénom = <?php echo $donneesUtilisateur['prenom'] ?></p>
                        <p>Date = <?php echo $donneesPostActeur['date_add'] ?></p>
                        <p>Texte = <?php echo $donneesPostActeur['post'] ?></p>
                    </div>
                    <?php
                    }
                    $resultatPostActeur->closeCursor();
                ?>
                </div>
            </div>
        </section>
        <div class="siteFooter">
            <!-- Footer -->
            <footer class="page-footer font-small">
                <div class="copyright">
                    <p>&copy 2020 - GBAF</p>
                </div>
                <div class="liensFooter">
                    <a href="mentionsLegales.php" class="mentionsLegales">| Mentions légales |</a>
                    <a href="contact.php" class="contact">Contact |</a>
                </div>
            </footer>
        </div>
    
    </div>
    

    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.4.1.slim.min.js"
        integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous">
    </script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"
        integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous">
    </script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js"
        integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous">
    </script>
</body>

</html>