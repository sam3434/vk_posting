<?php 
	include_once('Vkontakte.php');
	session_start();
	$flag = true;
	
	if (isset($_POST['application_id']) && $_POST['application_id']!="")
	{
		$_SESSION['application_id'] = $_POST['application_id'];
	}
	else
	{
		$flag = false;
	}
	if (isset($_POST['secret_application_key']) && $_POST['secret_application_key']!="")
	{
		$_SESSION['secret_application_key'] = $_POST['secret_application_key'];
	}
	else
	{
		$flag = false;
	}
	if (isset($_POST['login_or_email']) && $_POST['login_or_email']!="")
	{
		$_SESSION['login_or_email'] = $_POST['login_or_email'];
	}
	else
	{
		$flag = false;
	}
	if (isset($_POST['vk_password']) && $_POST['vk_password']!="")
	{
		$_SESSION['vk_password'] = $_POST['vk_password'];
	}
	else
	{
		$flag = false;
	}

	if ($flag)
	{
		$message = $_POST['message'];
		$groups_id = $_POST['groups_id'];
		$groups = explode(",", $groups_id);
 
		$images = array();
		foreach ($_POST["links"] as $link) {
			if ($link!="")
				$images[] = $link;
		}

		$files = array();
		foreach ($_FILES["files"]["error"] as $key => $error) {
		    if ($error == UPLOAD_ERR_OK) {
		        $tmp_name = $_FILES["files"]["tmp_name"][$key];
		        //$name = $_FILES["files"]["name"][$key];
		        		    
		        $uploadfile = __DIR__ . '/' . basename($_FILES['files']['name'][$key]);    
		        move_uploaded_file($tmp_name, $uploadfile);
		        $files[] = $uploadfile;
		    }
		}
		
		$public = new Vkontakte($_POST['application_id'], $_POST['secret_application_key'], $_POST['login_or_email'], $_POST['vk_password']);
		$public->vkrepost($groups, $message, $images, $files);
		exit(header('Location:'.$_SERVER['REQUEST_URI'].''));
	}

	if (isset($_SESSION['application_id']))
		$application_id = $_SESSION['application_id'];
	else
		$application_id = '';

	if (isset($_SESSION['secret_application_key']))
		$secret_application_key = $_SESSION['secret_application_key'];
	else
		$secret_application_key = '';

	if (isset($_SESSION['login_or_email']))
		$login_or_email = $_SESSION['login_or_email'];
	else
		$login_or_email = '';

	if (isset($_SESSION['vk_password']))
		$vk_password = $_SESSION['vk_password'];
	else
		$vk_password = '';


 ?>
<!doctype html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Постинг ВК</title>
	<style>
		input[type="text"]
		{
			padding: 5px;
			-webkit-border-radius: 5px;
			-moz-border-radius: 5px;
			border-radius: 5px;
		}
		html{
			font-size: 18px;
		}
	</style>
</head>
<body>
	<form action="<?= $_SERVER['PHP_SELF'] ?>" method="post" enctype="multipart/form-data">
	<table>
		<tr>
			<td> 
				<label for="application_id">ID приложения</label>
			</td>
			<td> 
				<input type="text" id="application_id" name="application_id" size="50" value="<?= $application_id ?>">	
			</td>
		</tr>
		<tr>
			<td> 
				<label for="secret_application_key">Защищенный ключ</label>

			</td>
			<td> 
				<input type="text" id="secret_application_key" name="secret_application_key" size="50" value="<?= $secret_application_key ?>">
			</td>
		</tr>
		<tr>
			<td> 
				<label for="login_or_email">Логин или email вконтакте</label>
			</td>
			<td> 
				<input type="text" id="login_or_email" name="login_or_email" size="50" value="<?= $login_or_email ?>">
			</td>
		</tr>
		<tr>
			<td> 
				<label for="vk_password">Пароль вконтакте</label>
			</td>
			<td> 
				<input type="text" id="vk_password" name="vk_password" size="50" value="<?= $vk_password ?>">
			</td>
		</tr>
		<tr>
			<td> 
				<label for="groups_id">ID групп(через запятую) <br>
				(без пробелов! пример - 15014694,15014695)
				</label>
			</td>
			<td> 
				<input type="text" id="groups_id" name="groups_id" size="150">
			</td>
		</tr>
		<tr>
			<td> 
				<label for="message">Текст сообщения</label>
			</td>
			<td> 
				<textarea name="message" id="message" cols="50" rows="10"></textarea>
			</td>
		</tr>
	</table>
	<table style="width:100%">
		<tr>
			<th>Ссылка картинок</th>
			<th>Картинки с компьютера</th>
		</tr>
		<tr>
			<td><input type="text" id="links1" size="50" name="links[]"></td>
			<td><input type="file" id="from_computer1" name="files[]"></td>
		</tr>
		<tr>
			<td><input type="text" id="links2" size="50" name="links[]"></td>
			<td><input type="file" id="from_computer2" name="files[]"></td>
		</tr>
		<tr>
			<td><input type="text" id="links3" size="50" name="links[]"></td>
			<td><input type="file" id="from_computer3" name="files[]"></td>
		</tr>
		<tr>
			<td><input type="text" id="links4" size="50" name="links[]"></td>
			<td><input type="file" id="from_computer4" name="files[]"></td>
		</tr>
		<tr>
			<td><input type="text" id="links5" size="50" name="links[]"></td>
			<td><input type="file" id="from_computer5" name="files[]"></td>
		</tr>
		<tr>
			<td><input type="text" id="links6" size="50" name="links[]"></td>
			<td><input type="file" id="from_computer6" name="files[]"></td>
		</tr>
		<tr>
			<td><input type="text" id="links7" size="50" name="links[]"></td>
			<td><input type="file" id="from_computer7" name="files[]"></td>
		</tr>
		<tr>
			<td><input type="text" id="links8" size="50" name="links[]"></td>
			<td><input type="file" id="from_computer8" name="files[]"></td>
		</tr>
		<tr>
			<td><input type="text" id="links9" size="50" name="links[]"></td>
			<td><input type="file" id="from_computer9" name="files[]"></td>
		</tr>
		<tr>
			<td><input type="text" id="links10" size="50" name="links[]"></td>
			<td><input type="file" id="from_computer10" name="files[]"></td>
		</tr>
	</table>
	
	<input type="submit" value="Отправить">
	</form>
</body>
</html>