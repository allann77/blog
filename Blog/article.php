<?php

require_once 'tools/common.php';

if(isset($_GET['article_id'] ) ){

	//selection de l'article dont l'ID est envoyé en paramètre GET
	$query = $db->prepare('
		SELECT article.*, GROUP_CONCAT(category.name SEPARATOR " / ") AS categories
		FROM article
		JOIN article_category ON article.id = article_category.article_id
		JOIN category ON article_category.category_id = category.id
		WHERE article.id = ? AND article.is_published = 1
	');

	$query->execute( array( $_GET['article_id'] ) );

	$article = $query->fetch();

	//si pas d'article trouvé dans la base de données, renvoyer l'utilisateur vers la page index
	if(!$article['id']){
		header('location:index.php');
		exit;
	}

}
else{ //si article_id n'est pas envoyé en URL, renvoyer l'utilisateur vers la page index
	header('location:index.php');
	exit;
}

// affichage d'image
$query = $db->prepare('SELECT * FROM image WHERE article_id = ?');
$query->execute(array($_GET['article_id']));

$articleImages = $query->fetchAll();
?>

<!DOCTYPE html>
<html>
 <head>

	<title><?php echo $article['title']; ?> - Mon premier blog !</title>

   <?php require 'partials/head_assets.php'; ?>

 </head>
 <body class="article-body">
	<div class="container-fluid">

		<?php require 'partials/header.php'; ?>

		<div class="row my-3 article-content">

			<?php require 'partials/nav.php'; ?>

			<main class="col-9">
				<article>
					<?php if(!empty($article['image'])): ?>
					<img class="pb-4 img-fluid" src="img/article/<?php echo $article['image']; ?>" alt="<?php echo $article['title']; ?>" />
					<?php endif; ?>
					<h1 class="h3"><?php echo $article['title']; ?></h1>
					<b class="article-category">[<?php echo $article['categories']; ?>]</b>
					<span class="article-date">Créé le <?php echo $article['created_at']; ?></span>

					<h5>Liste des images :</h5>
					<div class="d-flex flex-row flex-wrap d-flex">
						<?php if($articleImages): ?>
						<?php foreach($articleImages as $articleImage): ?>
							<div class="w-25 d-flex m-4 flex-column">
								<img class="w-100" src="./img/article/<?php echo $articleImage['name']; ?>" alt="<?php echo $articleImage['caption']; ?>">
							</div>
							<?php endforeach; ?>
						<?php else: ?>
                            <p>Aucun image est disponible pour ce article.</p>
						<?php endif; ?>
				</div>

					<div class="article-content">
						<?php echo $article['content']; ?>
					</div>
				</article>
			</main>

		</div>

		<?php require 'partials/footer.php'; ?>

	</div>
 </body>
</html>
