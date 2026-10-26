<?php
/* nav.inc - main navigation*/
$current_page = $current_page ?? '';
$links = [
    'index.php' => 'Home',
    'jobs.php'  => 'Jobs',
    'apply.php' => 'Apply',
    'about.php' => 'About',
    'login.php' => 'Login'
];
?>
            <nav aria-label="Main navigation">
                <!-- CSS-only burger menu (no JavaScript): checkbox toggles the menu on small screens -->
                <input type="checkbox" id="nav-toggle" class="nav-toggle">
                <label for="nav-toggle" class="nav-toggle-label">
                    <span class="visually-hidden">Menu</span>
                    <span class="burger-bars" aria-hidden="true"></span>
                </label>
                <ul class="nav-menu">
<?php foreach ($links as $file => $label): ?>
                    <li><a href="<?php echo $file; ?>"<?php echo ($file === $current_page) ? ' aria-current="page"' : ''; ?>><?php echo $label; ?></a></li>
<?php endforeach; ?>
                </ul>
            </nav>
