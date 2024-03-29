<?php

require_once 'tools/common.php';

//si un utilisateur est connécté et que l'on reçoit le paramètre "lougout" via URL, on le déconnecte

if(isset($_GET['logout']) && isset($_SESSION['user'])){

	//la fonction unset() détruit une variable ou une partie de tableau. ici on détruit la session user
	unset($_SESSION["user"]);
	//détruire $_SESSION["user"] va permettre l'affichage du bouton connexion / inscription de la nav, et permettre à nouveau l'accès aux formulaires de connexion / inscription
	//détruire $_SESSION["is_admin"] va empêcher l'accès au back-office
	unset($_SESSION["is_admin"]);
	unset($_SESSION["user_id"]);
}

?>

<!DOCTYPE html>
<html>
	<head>

		<title>Homepage - Mon premier blog !</title>

		<?php require 'partials/head_assets.php'; ?>

	</head>
	<body class="index-body">
		<div class="container-fluid">

			<?php require 'partials/header.php'; ?>

			<div class="row my-3 index-content">

				<?php require 'partials/nav.php'; ?>

				<main class="col-9">
					<section class="latest_articles">
						<header class="mb-4"><h1>Les 3 derniers articles :</h1></header>

						<?php
						//requête avec jointure double et table de liaison
						//la table article_category sert à créer les liaisons entre les id d'articles et les id de catégories (multicatégorisation)
						//la fonction sql GROUP_CONCAT sert à grouper et concaténer un champ qui se répera en un seul, en ajoutant un séparateur
						//le résultat doit être groupé par article.id afin d'éviter les doublons dans les résultats
						$query = $db->query('
							SELECT article.*, GROUP_CONCAT(category.name SEPARATOR " / ") AS categories
							FROM article
							JOIN article_category ON article.id = article_category.article_id
							JOIN category ON category.id = article_category.category_id
							WHERE article.is_published = 1
							GROUP BY article.id
							ORDER BY created_at DESC
							LIMIT 0, 3'
						);
						?>

						<?php while($article = $query->fetch()): ?>
						<article class="mb-4">
							<h2 class="h3"><?php echo $article['title']; ?></h2>
							<div class="row">
								<!-- on affiche le bloc image que si le champ image de l'article n'est pas vide -->
								<?php if(!empty($article['image'])): ?>
								<div class="col-12 col-md-4 col-lg-3">
									<img class="img-fluid" src="img/article/<?php echo $article['image']; ?>" alt="<?php echo $article['title']; ?>" />
								</div>
								<?php endif; ?>
								<div class="col-12 <?php if(!empty($article['image'])): ?>col-md-8 col-lg-9<?php endif; ?>">
									<!-- ici la clé "category_name" (alias de "category.name" dans la requête) a pour valeur la nom de la catégorie -->
									<b class="article-category">[<?php echo $article['categories']; ?>]</b>
									<span class="article-date">Créé le <?php echo $article['created_at']; ?></span>
									<div class="article-content">
										<?php echo $article['summary']; ?>
									</div>
									<a href="article.php?article_id=<?php echo $article['id']; ?>">> Lire l'article</a>
								</div>
							</div>
						</article>
						<?php endwhile; ?>

						<?php $query->closeCursor(); ?>

					</section>
					<div class="text-right">
						<a href="article_list.php"> Tous les articles</a>
					</div>
				</main>
			</div>

			<?php require 'partials/footer.php'; ?>

		</div>
	</body>
</html>
