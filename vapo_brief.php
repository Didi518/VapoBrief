<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
//require ('dbconf.php');
// équivalent fetch de JS
//$connexion = new PDO(DB_DRIVER . ":host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET, DB_LOGIN, DB_PASS, DB_OPTIONS);
$dsn = 'mysql:dbname=brief_vapo;host=127.0.0.1';
$user = 'root';
$password = 'proot';
try {
    $pdo = new PDO($dsn, $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo 'Échec lors de la connexion : ' . $e->getMessage();
}
//décla variables utiles
$content = '';
$error = '';
//set la supression
if (isset($_GET['action']) && $_GET['action'] == 'supression') {
    execute_requete("DELETE FROM produit WHERE id = '$_GET[id]'");
}
$pdostatement = $pdo->query("SELECT id, nom, typeobj, info, prix_achat, prix_vente,quantite ,reference FROM produit ORDER BY typeobj DESC ");

//fonction qui permet de repérer les erreurs de code
function debug($arg)
{
    echo "<div style='background:#fda500; z-index:1000; padding:15px;>";

    $trace = debug_backtrace();

    echo "<p>Debug demandé dans le fichier : " . $trace[0]['file'] . "à la ligne" . $trace[0]['line'] . "</p>";

    echo "<pre>";
    print_r($arg);
    echo "</pre>";

    echo "</div>";
}

//fonction qui utilise le pdo (interraction bdd)
function execute_requete($req)
{
    global $pdo;

    $pdostatement = $pdo->query($req);

    return $pdostatement;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VapoBrief</title>
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.8.2/css/all.css" integrity="sha384-oS3vJWv+0UjzBfQzYUhtDYW+Pj2yciDJxpsK1OYPAYjqT085Qq/1cq5FLXAZQ7Ay" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
</head>

<body>
    <?php

    

    //set la modif => créer le tableau
    echo "<table class='table table-bordered' cellpadding='8'>";
    echo "<tr>";
    $nbr_colonne = $pdostatement->columnCount();
    for ($i = 0; $i < $nbr_colonne; $i++) {
        $info_colonne = $pdostatement->getColumnMeta($i);

        echo "<th> $info_colonne[name] </th>";
    }

    echo "<th> Supression </th>";
    echo "<th> Modification </th>";
    echo "</tr>";

    while ($ligne = $pdostatement->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>";
        foreach ($ligne as $indice => $valeur) {
            echo "<td> $valeur </td>";
        }
        echo '<td class="text-center">
            <a href="?action=supression&id=' . $ligne['id'] . '"onclick="return(confirm( \' Voulez vous supprimer cet id : ' . $ligne['nom'] . '\' ) )" >
            <i class="far fa-trash-alt"></i>
            </a>
            </td>';

        echo '<td class="text-center">
            <a href="?action=modification&id=' . $ligne['id'] . ' ">
            <i class="far fa-edit"></i>
            </a>
            </td>';

        echo "</tr>";
        

    }
    echo "</table>";
    

    //boutton add, pour préparer requête insérer

    echo '<div class="text-center">
    <a href="?action=ajout ">
    <i class="fas fa-plus"> ADD</i>
    </a>
    </div> ';

    //commande modifier, update sql
    if (isset($_GET['action']) && $_GET['action'] == 'modification') :
        $recup = execute_requete("SELECT nom, info, typeobj, prix_achat, prix_vente, quantite, reference FROM produit WHERE id = $_GET[id]");
        $modifier = $recup->fetch(PDO::FETCH_ASSOC);
        $nom = $modifier["nom"];
        $info = $modifier["info"];
        $typeobj = $modifier["typeobj"];
        $prix_achat = $modifier["prix_achat"];
        $prix_vente = $modifier["prix_vente"];
        $quantite = $modifier["quantite"];
        $reference = $modifier["reference"];

        if ($_POST) {
            execute_requete("UPDATE produit SET
            nom = '$_POST[nom]',
            info = '$_POST[descri]',
            typeobj = '$_POST[produit]',
            prix_achat = '$_POST[pa]';
            prix_vente = '$_POST[pv]';
            quantite = '$_POST[quantite];
            reference = '$_POST[ref]';
            WHERE id='$_POST[id]' ");
            header('location:vapo_brief.php');
        }
        echo '<div class="text-center">
        <a href=?action= "<i class="fas fa-undo-alt"> UNDO</i>
        </a>
        </div>';
    ?>

        <form method="post">
            <div class='d-flex justify-content-center'>
                <div class='d-flex flex-column bd-highlight mb-3'>

                    <label class="text-center">Nom du produit </label>
                    <input type="text" name="nom"><br>

                    <label class="text-center">Description</label>
                    <input type="text" name="descri"><br>

                    <label class="text-center">Prix d'achat</label>
                    <input type="number" name="pa"><br>

                    <label class="text-center">Prix de vente</label>
                    <input type="number" name="pv"><br>

                    <label class="text-center">Quantité</label>
                    <input type="number" name="quantite"><br>

                    <label class="text-center"> Reference </label>
                    <input type="text" name="ref"><br>

                    <select name="produit">
                        <label class="text-center">Type de produit</label>

                        <option value="Vapoteuse"> Vapoteuse </option>
                        <option value="E-liquide"> E-liquide </option>
                    </select><br>

                    <input type="submit" class="btn btn-secondary" value="SUBMIT">
        </form>
    <?php
    endif;

// ajout des données dans bdd via pdo, meth $_POST + gesion erreurs inputs
if(isset($_GET['action']) && $_GET['action'] == 'ajout'):
    echo '<div class="text-center">
    <a href="?action= "><i class="fas fa-undo-a<p> Product added </p>lt"> UNDO</i>
    </a>
    </div>';

    if($_POST) {
        if (!is_numeric($_POST['quantite']) || !is_numeric($_POST['pa']) || !is_numeric($_POST['pv']) || !is_numeric($_POST['ref'])) {
    
            $error .= '
            <div class="d-flex justify-content-center">
                <div class="alert alert-danger d-flex align-items-center" role="alert">
                        <div>
                            Vous devez saisir un nombre !
                        </div>
                        </div>
            </div>
            ';
        }
        if (strlen($_POST['nom']) < 3 || strlen($_POST['nom']) > 15) {
            $error .= '<div class="alert alert-danger"> Erreur taille nom (doit etre compris entre 3 et 15 caractères)</div>';
        }

        
        $r = execute_requete(" SELECT nom FROM produit WHERE nom = '$_POST[nom]' ");

        if ($r->rowCount() >= 1) {

            $error .= "<div class='alert alert-danger'> Nom indisponible </div>";
        }

        $f = execute_requete(" SELECT reference FROM produit WHERE reference = '$_POST[ref]' ");

        if ($f->rowCount() >= 1) {

            $error .= "<div class='alert alert-danger'> Reference indisponible </div>";
        }
        if ($_POST["nom"]) {
            $nom = $_POST["nom"];
        } else {
            $nom = "";
        }
        if (empty($error)) {
            execute_requete("INSERT INTO produit (nom,info,typeobj,prix_achat,prix_vente,quantite,reference ) 
            VALUES ( 
                '$nom',
                '$_POST[descri]',
                '$_POST[produit]',
                '$_POST[pa]',
                '$_POST[pv]',
                '$_POST[quantite]',
                '$_POST[ref]' )
            ");
            $content .= '<div class="d-flex justify-content-center">
            <div class="alert alert-succes d-flex align-items-center" role="alert">
            <div>
            <p> Product added </p>
            </div>
            </div>
            </div>';
        }

        echo $content; //Affichage de content 
        header('location:vapo_brief.php');
    }
    echo $error; //afficher les erreurs éventuelles
    ?>

<form method="post">
    <div class='d-flex justify-content-center'>
        <div class='d-flex flex-column bd-highlight mb-3'>

        <label class="text-center" > Type de produit </label>
            <select name="produit" >
            <option value="Vapoteuse"  > Vapoteuse </option>
            <option value="E-liquide"  > E-liquide </option>
            </select>

            <label class="text-center">Nom du produit </label>
            <input type="text" name="nom"><br>

            <label class="text-center">Description</label>
            <input type="text" name="descri"><br>

            <label class="text-center">Prix d'achat</label>
            <input type="number" name="pa"><br>

            <label class="text-center">Prix de vente</label>
            <input type="number" name="pv"><br>

            <label class="text-center">Quantité</label>
            <input type="number" name="quantite"><br>

            <label class="text-center"> Reference  </label>
            <input type="text" name="ref"><br>

           
            <label class="text-center" > Bouton de validation </label>
            <input type="submit" class="btn btn-secondary" value="SUBMIT" >
</form>   
</body>

</html>
<?php
endif;
?>