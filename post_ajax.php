<?php
	if (isset($_POST['delete_post']))
		$del = intval($_POST['delete_post']);
	if (isset($_POST['delete_post']))
	{
		if (file_exists("data.txt"))
		{
			$in_file = unserialize(file_get_contents("data.txt"));
			$in_file = array_values($in_file);
			
			unset($in_file[$del]);
			file_put_contents("data.txt", serialize($in_file));
		}				
	}
	elseif (isset($_POST['save_post']))
	{
		$in_file = array();
		if (file_exists("data.txt"))
		{
			$str = file_get_contents("data.txt");
			$in_file = unserialize($str);
		}
		$in_file = array_values($in_file);
		$insert_value = array("application_id" => $_POST['application_id'],
							"secret_application_key" => $_POST['secret_application_key'],
							"login_or_email" => $_POST['login_or_email'],
							"vk_password" => $_POST['vk_password'],
							"message" => $_POST['message'],
							"groups_id" => $_POST['groups_id'],
							"images" => $_POST['images'],
							"name" => $_POST['name']
						);
		if (!in_array($insert_value, $in_file))
		{
			$in_file[] = $insert_value;
		}
		file_put_contents("data.txt", serialize($in_file));
	}
	else
	{
		die();
	}
	$in_file = array_values($in_file);

		$path = str_replace("post_ajax.php", "view.php", $_SERVER['PHP_SELF']);
		for ($i=0; $i < count($in_file); $i++) :	
	?>
		<div class="posts">
				<a href="<?= $path.'?page='.$i ?>">
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
				<span id="<?= $i ?>">X</span>
			</div>	
	<?
		endfor; 
?>
	
	