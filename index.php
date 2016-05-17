<?php
function barre_navigation ($nb_total,	$nb_affichage_par_page,	$debut,	$nb_liens_dans_la_barre) {

	$barre = '';

	// on recherche l'URL courante munie de ses paramètre auxquels on ajoute le paramètre 'debut' qui jouera le role du premier élément de notre LIMIT
	if ($_SERVER['QUERY_STRING'] == "") {
	   $query = $_SERVER['PHP_SELF'].'?debut=';
	}	else {
  	$tableau = explode ("debut=", $_SERVER['QUERY_STRING']);
  	$nb_element = count ($tableau);
  	if ($nb_element == 1) {
  		$query = $_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'].'&debut=';
	  }	else {
      if ($tableau[0] == "") {
        $query = $_SERVER['PHP_SELF'].'?debut=';
		  } else {
        $query = $_SERVER['PHP_SELF'].'?'.$tableau[0].'debut=';
		  }
	  }
	}

	// on calcul le numéro de la page active
	$page_active = floor(($debut/$nb_affichage_par_page)+1);
	// on calcul le nombre de pages total que va prendre notre affichage
	$nb_pages_total = ceil($nb_total/$nb_affichage_par_page);

	// on calcul le premier numero de la barre qui va s'afficher, ainsi que le dernier ($cpt_deb et $cpt_fin)
	// exemple : 2 3 4 5 6 7 8 9 10 11 << $cpt_deb = 2 et $cpt_fin = 11
	if ($nb_liens_dans_la_barre%2==0) {
  	$cpt_deb1 = $page_active - ($nb_liens_dans_la_barre/2)+1;
  	$cpt_fin1 = $page_active + ($nb_liens_dans_la_barre/2);
	}	else {
  	$cpt_deb1 = $page_active - floor(($nb_liens_dans_la_barre/2));
  	$cpt_fin1 = $page_active + floor(($nb_liens_dans_la_barre/2));
	}

	if ($cpt_deb1 <= 1) {
  	$cpt_deb = 1;
  	$cpt_fin = $nb_liens_dans_la_barre;
	} elseif ($cpt_deb1>1 && $cpt_fin1<$nb_pages_total) {
  	$cpt_deb = $cpt_deb1;
  	$cpt_fin = $cpt_fin1;
	} else {
  	$cpt_deb = ($nb_pages_total-$nb_liens_dans_la_barre)+1;
  	$cpt_fin = $nb_pages_total;
	}

	if ($nb_pages_total <= $nb_liens_dans_la_barre) {
  	$cpt_deb=1;
  	$cpt_fin=$nb_pages_total;
	}

	// si le premier numéro qui s'affiche est différent de 1, on affiche << qui sera un lien vers la premiere page
	if ($cpt_deb != 1) {
  	$cible = $query.(0);
  	$lien = '<A HREF="'.$cible.'">&lt;&lt;</A>&nbsp;&nbsp;';
	}	else {
	  $lien='';
	}
	$barre .= $lien;

	// on affiche tous les liens de notre barre, tout en vérifiant de ne pas mettre de lien pour la page active
	for ($cpt = $cpt_deb; $cpt <= $cpt_fin; $cpt++) {
    if ($cpt == $page_active) {
		    if ($cpt == $nb_pages_total) {
		        $barre .= $cpt;
		    } else {
          $barre .= $cpt.'&nbsp;-&nbsp;';
		    }
	  } else {
  		if ($cpt == $cpt_fin) {
    		$barre .= "<A HREF='".$query.(($cpt-1)*$nb_affichage_par_page);
    		$barre .= "'>".$cpt."</A>";
  		} else {
    		$barre .= "<A HREF='".$query.(($cpt-1)*$nb_affichage_par_page);
    		$barre .= "'>".$cpt."</A>&nbsp;-&nbsp;";
		  }
	  }
	}

	$fin = ($nb_total - ($nb_total % $nb_affichage_par_page));
	if (($nb_total % $nb_affichage_par_page) == 0) {
	   $fin = $fin - $nb_affichage_par_page;
	}

	// si $cpt_fin ne vaut pas la dernière page de la barre de navigation, on affiche un >> qui sera un lien vers la dernière page de navigation
	if ($cpt_fin != $nb_pages_total) {
	   $cible = $query.$fin;
	    $lien = '&nbsp;&nbsp;<A HREF="'.$cible.'">&gt;&gt;</A>';
	}	else {
	   $lien='';
	}
	$barre .= $lien;

	return $barre;
}
?>

<html>
<head>
<title>Les livres de la bibliothèque</title>
</head>

</body>
Les différents livres de la bibliothèque :<br /><br />
<?php
// Définit le jeu de caractères en utf8 des bases de données
$encodage = array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8");
// Connexion à la base de données prestashop et test de présence d'erreurs
$bddUsername = 'root';
$bddPassword = '';
$bddDsn = 'mysql:host=localhost;dbname=test_pagination';

try {
  $connexion = new PDO($bddDsn, $bddUsername, $bddPassword, $encodage);
}
catch (Exception $e) {
  die('Erreur ! : ' . $e->getMessage());
}

// on prépare une requête permettant de calculer le nombre total d'éléments qu'il faudra afficher sur nos différentes pages
$sql  = $connexion->prepare('SELECT count(*) FROM catalogue');

// on exécute cette requête
$resultat = $sql->execute();;

// on récupère le nombre d'éléments à afficher
$nb_total = $sql->fetch();

// on teste si ce nombre de vaut pas 0
if (($nb_total = $nb_total[0]) == 0) {
	echo 'Aucune réponse trouvée';
}
else {
	echo '<table><tr><td><td>Description</td></tr>';

	// sinon, on regarde si la variable $debut (le x de notre LIMIT) n'a pas déjà été déclarée, et dans ce cas, on l'initialise à 0
	if (!isset($_GET['debut'])) $_GET['debut'] = 0;

	$nb_affichage_par_page = 1;

	// Préparation de la requête avec le LIMIT
	$sql2 = $connexion->prepare('SELECT titre, description FROM catalogue ORDER BY titre ASC LIMIT '.$_GET['debut'].','.$nb_affichage_par_page);

	// on exécute la requête
	$req = $sql2->execute();

	// on va scanner tous les tuples un par un
	while ($data = $sql2->fetch()) {
		// on affiches les résultats dans la <table>
		echo '<tr><td><td>' , htmlentities(trim($data['description'])) , '</td></tr>';
	}

	echo '</table><br />';

	// on affiche enfin notre barre
	echo '[b]'.barre_navigation($nb_total, $nb_affichage_par_page, $_GET['debut'], 3).'[/b]';
}
echo '</table><br />';
?>

</body>
</html>
