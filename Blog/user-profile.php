<?php

require_once 'tools/common.php';

if(!isset($_SESSION['user'])){
	header('location:../index.php');
	exit;
}

//si on modifie un utilisateur, on doit séléctionner l'utilisateur en question (id en session) pour pré-remplir le formulaire plus bas
$query = $db->prepare('SELECT * FROM user WHERE id = ?');
$query->execute(array($_SESSION['user_id']));
//$user contiendra les informations de l'utilisateur dont l'id est en session
$user = $query->fetch();

//En cas de mise à jour des informations
if(isset($_POST['update'])){

	//la mise à jour d'un utilisateur ne pourra se faire que sous certaines conditions

	//en premier lieu, vérifier que l'adresse email renseignée n'est pas déjà utilisée
	$query = $db->prepare('SELECT email FROM user WHERE email = ?');
	$query->execute(array($_POST['email']));

	//$emailAlreadyExists vaudra false si l'email n'a pas été trouvé, ou un tableau contenant le résultat dans le cas contraire
	$emailAlreadyExists = $query->fetch();

	//on teste donc $emailAlreadyExists. Si différent de false, l'adresse a été trouvée en base de données
	//on teste également si l'utilisateur a modifié son email
	if($emailAlreadyExists && $emailAlreadyExists['email'] != $user['email']){
		$updateMessage = "Adresse email déjà utilisée";
	}
	elseif(empty($_POST['firstname']) OR empty($_POST['email'])){
		//ici on test si les champs obligatoires ont été remplis
        $updateMessage = "Merci de remplir tous les champs obligatoires (*)";
    }
	//uniquement si l'utilisateur souhaite modifier son mot de passe
	elseif( !empty($_POST['password']) AND ($_POST['password'] != $_POST['password_confirm'])) {
		//ici on teste si les mots de passe renseignés sont identiques
		$updateMessage = "Les mots de passe ne sont pas identiques";
	}
    else {
		
		//début de la chaîne de caractères de la requête de mise à jour
		$queryString = 'UPDATE user SET firstname = :firstname, lastname = :lastname, email = :email, bio = :bio ';
		//début du tableau de paramètres de la requête de mise à jour
		$queryParameters = [ 'firstname' => $_POST['firstname'], 'lastname' => $_POST['lastname'], 'email' => $_POST['email'], 'bio' => $_POST['bio'], 'id' => $_SESSION['user_id'] ];

		//uniquement si l'utilisateur souhaite modifier son mot de passe
		if( !empty($_POST['password'])) {
			//concaténation du champ password à mettre à jour
			$queryString .= ', password = :password ';
			//ajout du paramètre password à mettre à jour
			$queryParameters['password'] = hash('md5', $_POST['password']);
		}
		
		//fin de la chaîne de caractères de la requête de mise à jour
		$queryString .= 'WHERE id = :id';
		
		//préparation et execution de la requête avec la chaîne de caractères et le tableau de données
		$query = $db->prepare($queryString);
		$result = $query->execute($queryParameters);

		if($result){
			//une fois l'utilisateur enregistré, on modifie $_SESSION['user'] car il a peut être changé son firstName
			$_SESSION['user'] = $_POST['firstname'];
			$updateMessage = "Informations mises à jour avec succès !";
			
			//récupération des informations utilisateur qui ont été mises à jour pour affichage
			$query = $db->prepare('SELECT * FROM user WHERE id = ?');
			$query->execute(array($_SESSION['user_id']));
			$user = $query->fetch();
		}
		else{
			$updateMessage = "Erreur";
		}
    }
}

?>

<!DOCTYPE html>
<html>
 <head>

	<title>Login - Mon premier blog !</title>

   <?php require 'partials/head_assets.php'; ?>

 </head>
 <body class="article-body">
	<div class="container-fluid">

		<?php require 'partials/header.php'; ?>

		<div class="row my-3 article-content">

			<?php require 'partials/nav.php'; ?>

			<main class="col-9">

				<form action="user-profile.php" method="post" class="p-4 row flex-column">

					<h4 class="pb-4 col-sm-8 offset-sm-2">Mise à jour des informations utilisateur</h4>

					<?php if(isset($updateMessage)): ?>
					<div class="text-danger col-sm-8 offset-sm-2 mb-4"><?php echo $updateMessage; ?></div>
					<?php endif; ?>

					<div class="form-group col-sm-8 offset-sm-2">
						<label for="firstname">Prénom <b class="text-danger">*</b></label>
						<input class="form-control" value="<?php echo $user['firstname']?>" type="text" placeholder="Prénom" name="firstname" id="firstname" />
					</div>
					<div class="form-group col-sm-8 offset-sm-2">
						<label for="lastname">Nom de famille</label>
						<input class="form-control" value="<?php echo $user['lastname']?>" type="text" placeholder="Nom de famille" name="lastname" id="lastname" />
					</div>
					<div class="form-group col-sm-8 offset-sm-2">
						<label for="email">Email <b class="text-danger">*</b></label>
						<input class="form-control" value="<?php echo $user['email']?>" type="email" placeholder="Email" name="email" id="email" />
					</div>
					<div class="form-group col-sm-8 offset-sm-2">
						<label for="password">Mot de passe (uniquement si vous souhaitez modifier votre mot de passe actuel)</label>
						<input class="form-control" value="" type="password" placeholder="Mot de passe" name="password" id="password" />
					</div>
					<div class="form-group col-sm-8 offset-sm-2">
						<label for="password_confirm">Confirmation du mot de passe (uniquement si vous souhaitez modifier votre mot de passe actuel)</label>
						<input class="form-control" value="" type="password" placeholder="Confirmation du mot de passe" name="password_confirm" id="password_confirm" />
					</div>
					<div class="form-group col-sm-8 offset-sm-2">
						<label for="bio">Biographie</label>
						<textarea class="form-control" name="bio" id="bio" placeholder="Ta vie Ton oeuvre..."><?php echo $user['bio']?></textarea>
					</div>

					<div class="text-right col-sm-8 offset-sm-2">
						<p class="text-danger">* champs requis</p>
						<input class="btn btn-success" type="submit" name="update" value="Valider" />
					</div>

				</form>
			</main>
		</div>

		<?php require 'partials/footer.php'; ?>

	</div>
 </body>
</html>

