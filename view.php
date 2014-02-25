<?php
	session_start();
	include_once("config.php");
	$con = mysql_connect($host, $user, $password);
	
	$create_db = "create database if not exists $db_name";
	mysql_query($create_db);
	mysql_select_db($db_name, $con);
	$create_tbl  = "create table if not exists `vk_data`(`id` int, `application_id` varchar(64), `secret_application_key`
		 varchar(128), `login_or_email` varchar(256), `vk_password` varchar(256), `message` text, `groups_id` text, `links` text, primary key(`id`))";
	mysql_query($create_tbl);
	// $insert = "replace into `vk_data` values('1', '', '', '', '', '', '', '' )";
	mysql_query($insert);
	include_once('Vkontakte.php');

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

	if (isset($_POST['message']) && $_POST['message']!="")
	{
		$_SESSION['message'] = $_POST['message'];
	}
	if (isset($_POST['groups_id']) && $_POST['groups_id']!="")
	{
		$_SESSION['groups_id'] = $_POST['groups_id'];
	}
	if (isset($_POST['links']) && $_POST['links']!="")
	{
		$_SESSION['links'] = implode("||", $_POST['links']);
	}

	if ($flag)
	{
		$message = $_POST['message'];
		$groups_id = trim($_POST['groups_id']);
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
		
		$insert = "replace into `vk_data` values('1', '".$_POST['application_id']."', '".$_POST['secret_application_key'].
			"', '".$_POST['login_or_email']."', '".$_POST['vk_password']."', '".$message."', '".$groups_id."', '".implode("||", $images)."' )";
		mysql_query($insert);

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

	if (isset($_SESSION['message']))
		$message = $_SESSION['message'];
	else
		$message = '';
	if (isset($_SESSION['groups_id']))
		$groups_id = $_SESSION['groups_id'];
	else
		$groups_id = '';
	if (isset($_SESSION['links']))
		$links = explode("||", $_SESSION['links']);
	else
		$links = array();

	$select = "select * from `vk_data` limit 1";
	$res = mysql_query($select);
	if ($row = mysql_fetch_assoc($res))
	{
		$application_id = $row['application_id'];
		$secret_application_key = $row['secret_application_key'];
		$login_or_email = $row['login_or_email'];
		$vk_password = $row['vk_password'];
		$message = $row['message'];
		$groups_id = $row['groups_id'];
		$links = explode("||", $row['links']);
	}
	else
	{
		//echo mysql_error();
	}


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
				<textarea name="groups_id" id="groups_id" cols="100" rows="6"><?= trim($groups_id) ?></textarea>
			</td>
		</tr>
		<tr>
			<td> 
				<label for="message">Текст сообщения</label>
			</td>
			<td> 
				<textarea name="message" id="message" cols="50" rows="10"><?= $message ?></textarea>
			</td>
		</tr>
	</table>
	<table style="width:100%">
		<tr>
			<th>Ссылка картинок</th>
			<th>Картинки с компьютера</th>
		</tr>
		<?
			for ($i=1; $i <= 10; $i++) { 
				if (isset($links[$i-1]))
					$link_value = $links[$i-1];
				else
					$link_value = '';
				echo "<tr>";
					echo "<td>";
						echo "<input type='text' id='links{$i}' size='50' name='links[]' value='$link_value'>";
					echo "</td>";
					echo "<td>";
						echo "<input type='file' id='from_computer{$i}' name='files[]'>";
					echo "</td>";
				echo "</tr>";
			}

		 ?>
	</table>
	
	<input type="submit" value="Отправить">
	</form>
</body>
</html>