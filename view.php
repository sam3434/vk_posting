<?php
	session_start();

	function in_array_out_names($insert_value, $in_file)
	{
		unset($insert_value["name"]);
		for ($i=0; $i < count($in_file); $i++) { 
			unset($in_file[$i]["name"]);
		}
		return in_array($insert_value, $in_file);
	}

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

		$in_file = array();
		if (file_exists("data.txt"))
		{
			$str = file_get_contents("data.txt");
			$in_file = unserialize($str);
		}
		$insert_value = array("application_id" => $_POST['application_id'],
							"secret_application_key" => $_POST['secret_application_key'],
							"login_or_email" => $_POST['login_or_email'],
							"vk_password" => $_POST['vk_password'],
							"message" => $message,
							"groups_id" => $groups_id,
							"images" => $images,
							"name" => ""
						);
		$inserted = false;
		if (!in_array_out_names($insert_value, $in_file))
		{
			$in_file[] = $insert_value;
			$inserted = true;
		}
		file_put_contents("data.txt", serialize($in_file));
		if ($inserted)
		{
			$size = count($in_file) - 1;			
		}
		else
		{
			if (isset($_POST["page"]))
				$size = $_POST["page"];
			else
				$size = 0;
		}

		$public = new Vkontakte($_POST['application_id'], $_POST['secret_application_key'], $_POST['login_or_email'], $_POST['vk_password']);
		$public->vkrepost($groups, $message, $images, $files);
		exit(header('Location:'.$_SERVER['REQUEST_URI'].'?page='.$size));
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

	if (isset($_GET['page']))
	{
		$page = intval($_GET['page']);
	}
	else
	{
		$page = 0;
	}
	$in_file = array();
	if (file_exists("data.txt"))
	{
		$in_file = unserialize(file_get_contents("data.txt"));
		$in_file = array_values($in_file);
		{
			if (isset($in_file[$page]))
			{
				$application_id = $in_file[$page]['application_id'];
				$secret_application_key = $in_file[$page]['secret_application_key'];
				$login_or_email = $in_file[$page]['login_or_email'];
				$vk_password = $in_file[$page]['vk_password'];
				$message = $in_file[$page]['message'];
				$groups_id = $in_file[$page]['groups_id'];
				$links = $in_file[$page]['images'];
			}			
		}
	}

	
 ?>
<!doctype html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Постинг ВК</title>
	<link rel="stylesheet" href="style.css">
	<script src="jquery-1.11.0.min.js"></script>

	<script>
		jQuery(document).ready(function($) {

			$('#show_memory').on('click', ".posts span", function(event) {
				var name = ""
				if ($(this).prev().is("b"))
				{
					name = $.trim($(this).prev().children().html())
				}
				else
				{
					name = $.trim($(this).prev().html())
				}
				if (confirm("Вы уверены что хотите удалить "+name))
				{
					$.ajax({
					      url: 'post_ajax.php',
					      type: 'post',
					      dataType: "text",
					      data: {delete_post: $(this).attr("id")},
					      success: function (data) {
					      	$('#show_memory').html(data)
					      },
					      error: function(er){
					            alert("ajax error")
					            //alert(er)
					      },
					});
				}
			});	

			$('#save').on('click', function(event) {
				var ans = prompt("Введите название ссылки")
				var links = []
				for (var i = 1; i <= 10; i++) {
					if ($('#links'+i).val()!="")
					{
						links.push($('#links'+i).val())
					}
				}

				if (ans!=null)
				{
					$.ajax({
					      url: 'post_ajax.php',
					      type: 'post',
					      dataType: "text",
					      data: {save_post: true, application_id: $('#application_id').val()
					      , secret_application_key: $('#secret_application_key').val(), login_or_email: $('#login_or_email').val()
					      , vk_password: $('#vk_password').val(), groups_id: $('#groups_id').val()
					      , message: $('#message').val(), name: ans, images: links },
					      success: function (data) {
					        $('#show_memory').html(data)
					      },
					      error: function(er){
					            alert("ajax error save")
					            //alert(er)
					      },
					});	
				}				
				
			});	
		});

		
	</script>
</head>
<body>
	
	<div id="show_memory">
		<?	for ($i=0; $i < count($in_file); $i++) :	?>
			<div class="posts">
				<? if ($page==$i): ?>
					<b>
				<? endif; ?>
				<a href="<?= $_SERVER['PHP_SELF'].'?page='.$i ?>">
					<? 
						if ($in_file[$i]["name"]==""):
					 ?>
						<?= $i+1  ?>-ый пост
					<? 
						else:
					 ?>
						<?= $in_file[$i]["name"] ?>
					<? endif; ?>

				</a>
				<? if ($page==$i): ?>
					</b>
				<? endif; ?>
				<span id="<?= $i ?>">X</span>
			</div>	
			
		<?	endfor; ?>	
	</div>
	<div id="save">
		Сохранить
	</div>
	
	<form action="<?= $_SERVER['PHP_SELF'] ?>" method="post" enctype="multipart/form-data">
		<input type="hidden" name="page" value="<? if (isset($_GET['page'])) echo intval($_GET['page']); else echo 0; ?>">
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