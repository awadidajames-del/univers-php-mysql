<?php
$pageTitle = $pageTitle ?? 'Univer Cosmetics';
?>
<!doctype html>
<html lang="fr">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="theme-color" content="#302a28">
	<title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@600;700&family=Playfair+Display:wght@600&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="styles.css">
	<?php foreach ($extraStyles ?? [] as $extraStyle): ?>
	<link rel="stylesheet" href="<?= htmlspecialchars($extraStyle, ENT_QUOTES, 'UTF-8') ?>">
	<?php endforeach; ?>
</head>
<body>