<?php include 'php/start';?>
<!doctype html>
<html>
	<head>
		<meta charset="utf-8">
		<meta name="description" content="">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>Vote!</title>
		<link rel="shortcut icon" href="<?php echo URL?>style/img/favicon.ico" />
		<link rel="stylesheet"  type="text/css" href="<?php echo URL?>style/main.css">
	</head>
	<body>
		<div class="vote">
			<h1>Comunidade PHP's!</h1>
			<h2>Qual o melhor horário para os HangOuts?</h3>
			<?php 
				echo table($dt);
				if(USER == 'admin') getHtml('admin');
				if($msg != '') echo '<p class="att">'.$msg.'</p>';
			?>
		</div>
		<script src="<?php echo URL?>script/main.js"></script>
	</body>
</html>