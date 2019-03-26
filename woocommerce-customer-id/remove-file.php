<?php
if(isset($_POST)){
	if($_POST['path']){
		if (strpos($a, 'profile-user.png') !== false) {
			$path = $_POST['path'];
			$parse_image = parse_url($path);
			$real_image_path = $parse_image['path'];
			unlink($_SERVER['DOCUMENT_ROOT'].$real_image_path);
	   }
	}
}
?>