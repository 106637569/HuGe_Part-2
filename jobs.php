<?php
require_once 'settings.php';

$page_title = 'HuGe - Jobs';
$current_page = 'jobs.php';
$extra_head = <<<'HTML'
<style>
    .jobs-content > h2 {
        border-bottom: 2px solid #1fa463;
        padding-bottom: 0.4em;
    }
</style>
HTML;

function h($text)
{
    return htmlspecialchars((string)$text, ENT_QUOTES, 'UTF-8');
}

function print_job_list($text)
{
    $items = preg_split('/\R+/', trim((string)$text));
    foreach ($items as $item) {
        $item = trim($item);
        if ($item !== '') {
            echo '<li>' . h($item) . '</li>';
        }
    }
}

mysqli_report(MYSQLI_REPORT_OFF);
$conn = mysqli_connect($host, $user, $pwd, $sql_db);
$result = false;
$db_error = '';

if (!$conn) {
    $db_error = 'Could not connect to the database.';
} else {
    mysqli_set_charset($conn, 'utf8mb4');
    $result = mysqli_query($conn, 'SELECT * FROM jobs ORDER BY job_id ASC');
    if (!$result) {
        $db_error = 'The jobs table could not be loaded. Import jobs.sql into huge_db first.';
    }
}

include 'header.inc';
?>

<main class="container">
    <section class="page-intro">
        <h1>Career Opportunities at HuGe</h1>
        <p style="font-weight: bold; color: #0b2c5f;">
            Join HuGe and help build the future of digital finance.
        </p>
        <p>
            HuGe Financial Technologies develops secure and user-friendly
            digital solutions for payments, budgeting, account management
            and financial education.
        </p>
        <p>
            We are looking for people who are interested in technology,
            finance and creating better digital experiences for customers.
        </p>

        <form class="job-search" action="search.php" method="get">
            <label for="search">Search jobs</label>
            <input type="text" id="search" name="search" placeholder="Job title, reference or keyword" required>
            <button type="submit">Search</button>
        </form>
    </section>

    <div class="jobs-layout">
        <section class="jobs-content">
            <h2>Open Positions</h2>

<?php if ($db_error !== '') { ?>
            <p><?php echo h($db_error); ?></p>
<?php } elseif (mysqli_num_rows($result) === 0) { ?>
            <p>There are currently no open positions.</p>
<?php } else { ?>

<?php while ($job = mysqli_fetch_assoc($result)) { ?>
            <article class="job-card">
                <div class="job-heading">
                    <h3><?php echo h($job['job_title']); ?></h3>
                    <span class="job-ref"><?php echo h($job['job_ref']); ?></span>
                </div>

                <p><strong>Salary:</strong> <?php echo h($job['salary']); ?></p>
                <p><strong>Reporting Line:</strong> <?php echo h($job['reporting_line']); ?></p>
                <p><strong>Short Description:</strong> <?php echo h($job['short_description']); ?></p>

                <section>
                    <h4>Key Responsibilities</h4>
                    <ul><?php print_job_list($job['key_responsibilities']); ?></ul>
                </section>

                <section>
                    <h4>Essential Requirements</h4>
                    <ul><?php print_job_list($job['essential_requirements']); ?></ul>
                </section>

                <section>
                    <h4>Preferable Requirements</h4>
                    <ul><?php print_job_list($job['preferable_requirements']); ?></ul>
                </section>

                <a class="apply-button"
                   href="apply.php?jobRef=<?php echo urlencode($job['job_ref']); ?>#jobRef">
                    Apply for <?php echo h($job['job_ref']); ?>
                </a>
            </article>
<?php } ?>

<?php } ?>
        </section>

        <aside class="jobs-aside">
            <h2>Why Work at HuGe?</h2>
            <p>
                HuGe provides a supportive workplace where employees can
                develop their skills while working on modern financial technology.
            </p>

            <h3>Employee Benefits</h3>
            <ul>
                <li>Flexible working arrangements</li>
                <li>Career development opportunities</li>
                <li>Supportive team environment</li>
                <li>Modern digital workplace</li>
            </ul>

            <h3>How to Apply</h3>
            <ol>
                <li>Read the job description and requirements.</li>
                <li>Choose the position that matches your skills.</li>
                <li>Select the Apply button for the position.</li>
                <li>Check the six-character job reference number.</li>
                <li>Complete and submit the application form.</li>
            </ol>

            <section class="inclusive-note">
                <h3>Inclusive Employment</h3>
                <p>
                    HuGe values diversity and encourages applications
                    from people of all backgrounds, including Aboriginal
                    and Torres Strait Islander peoples.
                </p>
            </section>
        </aside>
    </div>
</main>

<?php
if ($result) {
    mysqli_free_result($result);
}
if ($conn) {
    mysqli_close($conn);
}
include 'footer.inc';
?>
