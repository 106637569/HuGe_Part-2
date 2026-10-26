<?php

$page_title   = $page_title   ?? 'HuGe Financial Technologies';
$current_page = $current_page ?? '';
$extra_head   = $extra_head   ?? '';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title, ENT_QUOTES, 'UTF-8'); ?></title>
    <link rel="stylesheet" href="styles/styles.css">
<?php echo $extra_head; ?>
</head>

<body>
    <header>
        <div class="container header-flex">
            <div class="branding">
                <img src="images/Huge.png"
                     alt="HuGe Financial Technologies logo"
                     class="logo">
                <div>
                    <h2 class="site-title">HuGe<span class="brand-extra"> Financial Technologies</span></h2>
                    <p class="tagline">Huge Goals. Huge Growth. HuGe.</p>
                </div>
            </div>
<?php include 'nav.inc'; ?>
        </div>
    </header>
