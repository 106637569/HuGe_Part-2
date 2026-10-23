<?php
/*
 * process_eoi.php
 * ---------------------------------------------------------------
 * What this file does, in order:
 *   1. Stops people opening this page directly (sends them to apply.php)
 *   2. Reads the form data and cleans it
 *   3. Checks every field and collects error messages
 *   4. If there are no errors: saves the application in the database
 *   5. Shows either a success page (with the EOI number) or the errors
 *
 * GenAI acknowledgement: drafted with the assistance of Claude (Anthropic),
 * then reviewed, simplified and tested by Member 2.
 */

require_once 'settings.php';   // gives us $host, $user, $pwd, $sql_db


/* ===============================================================
   STEP 1: Block direct access
   =============================================================== */

// A real form submission uses the POST method. Typing the URL in the
// browser uses GET, so we send those visitors back to the form.
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header('Location: apply.php');
    exit;
}

// Also make sure the main text fields were actually sent.
$required_fields = array('jobRef', 'firstName', 'lastName', 'dob', 'street',
                         'suburb', 'state', 'postcode', 'email', 'phone');

foreach ($required_fields as $field) {
    if (!isset($_POST[$field]) || is_array($_POST[$field])) {
        header('Location: apply.php');
        exit;
    }
}


/* ===============================================================
   STEP 2: Read and clean the form data
   =============================================================== */

// Small helper: removes spaces at the start/end and any backslashes.
function clean($text)
{
    $text = trim($text);
    $text = stripslashes($text);
    return $text;
}

// Small helper: makes text safe to show on a web page.
// (This turns < into &lt; so nobody can sneak a script into the page.)
function esc($text)
{
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

$jobRef    = strtoupper(clean($_POST['jobRef']));
$firstName = clean($_POST['firstName']);
$lastName  = clean($_POST['lastName']);
$dob       = clean($_POST['dob']);
$street    = clean($_POST['street']);
$suburb    = clean($_POST['suburb']);
$state     = clean($_POST['state']);
$postcode  = clean($_POST['postcode']);
$email     = clean($_POST['email']);
$phone     = clean($_POST['phone']);

// These might not be sent at all (e.g. no radio button chosen),
// so we use "if isset" and fall back to an empty value.
$gender = '';
if (isset($_POST['gender']) && !is_array($_POST['gender'])) {
    $gender = clean($_POST['gender']);
}

$genderDescription = '';
if (isset($_POST['genderDescription']) && !is_array($_POST['genderDescription'])) {
    $genderDescription = clean($_POST['genderDescription']);
}

$otherSkills = '';
if (isset($_POST['otherSkills']) && !is_array($_POST['otherSkills'])) {
    $otherSkills = clean($_POST['otherSkills']);
}

$skills = array();   // the ticked checkboxes arrive as a list
if (isset($_POST['skills']) && is_array($_POST['skills'])) {
    $skills = $_POST['skills'];
}


/* ===============================================================
   STEP 3: Validate - check every field
   Each problem is added to the $errors list.
   =============================================================== */

$errors = array();

// --- Job reference: exactly 6 letters or numbers
if (!preg_match('/^[A-Z0-9]{6}$/', $jobRef)) {
    $errors[] = 'Job reference must be exactly 6 letters or numbers.';
}

// --- First and last name: letters only, 1 to 20 characters
if (!preg_match('/^[A-Za-z]{1,20}$/', $firstName)) {
    $errors[] = 'First name must be 1 to 20 letters only.';
}
if (!preg_match('/^[A-Za-z]{1,20}$/', $lastName)) {
    $errors[] = 'Last name must be 1 to 20 letters only.';
}

// --- Date of birth: dd/mm/yyyy, a real date, and age 15 to 80
$dobForDatabase = '';   // the database wants yyyy-mm-dd

if (!preg_match('/^[0-9]{2}\/[0-9]{2}\/[0-9]{4}$/', $dob)) {
    $errors[] = 'Date of birth must be in dd/mm/yyyy format.';
} else {
    // Split "15/06/2000" into day, month and year
    $parts = explode('/', $dob);
    $day   = (int)$parts[0];
    $month = (int)$parts[1];
    $year  = (int)$parts[2];

    if (!checkdate($month, $day, $year)) {
        // checkdate() catches impossible dates such as 31/02/2000
        $errors[] = 'Date of birth is not a real date.';
    } else {
        // Work out the age
        $age = (int)date('Y') - $year;
        if ((int)date('md') < $month * 100 + $day) {
            $age = $age - 1;   // birthday has not happened yet this year
        }

        if ($age < 0) {
            $errors[] = 'Date of birth cannot be in the future.';
        } elseif ($age < 15 || $age > 80) {
            $errors[] = 'Applicants must be between 15 and 80 years old.';
        } else {
            $dobForDatabase = sprintf('%04d-%02d-%02d', $year, $month, $day);
        }
    }
}

// --- Gender: must be one of the four options on the form
if ($gender != 'male' && $gender != 'female' && $gender != 'other' && $gender != 'prefer-not') {
    $errors[] = 'Please select a gender option.';
}

// The "please specify" box only counts when "other" is chosen
if ($gender != 'other') {
    $genderDescription = '';
}
if (strlen($genderDescription) > 30) {
    $errors[] = 'Gender description must be 30 characters or fewer.';
}

// --- Street and suburb: 1 to 40 characters
if ($street == '' || strlen($street) > 40) {
    $errors[] = 'Street address must be 1 to 40 characters.';
}
if ($suburb == '' || strlen($suburb) > 40) {
    $errors[] = 'Suburb/Town must be 1 to 40 characters.';
}

// --- State: must be one of the states in the drop-down
$valid_states = array('VIC', 'NSW', 'QLD', 'NT', 'WA', 'SA', 'TAS', 'ACT');
if (!in_array($state, $valid_states)) {
    $errors[] = 'Please select a valid state.';
}

// --- Postcode: 4 digits, and it must belong to the chosen state
if (!preg_match('/^[0-9]{4}$/', $postcode)) {
    $errors[] = 'Postcode must be exactly 4 digits.';
} elseif (in_array($state, $valid_states)) {
    $pc = (int)$postcode;   // turn "3122" into the number 3122
    $postcode_ok = false;

    if ($state == 'VIC' && (($pc >= 3000 && $pc <= 3999) || ($pc >= 8000 && $pc <= 8999))) {
        $postcode_ok = true;
    }
    if ($state == 'NSW' && (($pc >= 1000 && $pc <= 2599) || ($pc >= 2619 && $pc <= 2899) || ($pc >= 2921 && $pc <= 2999))) {
        $postcode_ok = true;
    }
    if ($state == 'QLD' && (($pc >= 4000 && $pc <= 4999) || ($pc >= 9000 && $pc <= 9999))) {
        $postcode_ok = true;
    }
    if ($state == 'NT' && $pc >= 800 && $pc <= 999) {
        $postcode_ok = true;
    }
    if ($state == 'WA' && $pc >= 6000 && $pc <= 6999) {
        $postcode_ok = true;
    }
    if ($state == 'SA' && $pc >= 5000 && $pc <= 5999) {
        $postcode_ok = true;
    }
    if ($state == 'TAS' && $pc >= 7000 && $pc <= 7999) {
        $postcode_ok = true;
    }
    if ($state == 'ACT' && (($pc >= 200 && $pc <= 299) || ($pc >= 2600 && $pc <= 2618) || ($pc >= 2900 && $pc <= 2920))) {
        $postcode_ok = true;
    }

    if (!$postcode_ok) {
        $errors[] = 'Postcode ' . $postcode . ' does not match the selected state (' . $state . ').';
    }
}

// --- Email: PHP has a built-in email checker
if (strlen($email) > 100 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Please enter a valid email address.';
}

// --- Phone: 8 to 12 digits, numbers only
if (!preg_match('/^[0-9]{8,12}$/', $phone)) {
    $errors[] = 'Phone number must be 8 to 12 digits, numbers only.';
}

// --- Skills: at least one, and every one must be a real option
$valid_skills = array('html', 'css', 'javascript', 'php', 'python', 'sql', 'java', 'csharp');

if (count($skills) == 0) {
    $errors[] = 'Please select at least one skill.';
} else {
    foreach ($skills as $skill) {
        if (is_array($skill) || !in_array($skill, $valid_skills)) {
            $errors[] = 'One or more selected skills are not valid.';
            break;   // stop checking once we find a bad one
        }
    }
}

// --- Other skills: optional, but keep it a sensible length
if (strlen($otherSkills) > 1000) {
    $errors[] = 'Other skills must be 1000 characters or fewer.';
}


/* ===============================================================
   STEP 4: Save to the database (only if there were no errors)
   =============================================================== */

$page = 'errors';    // which page to show at the end: errors, success or failure
$eoiNumber = 0;

if (count($errors) == 0) {

    // Make PHP return false on problems instead of stopping with a big error
    mysqli_report(MYSQLI_REPORT_OFF);

    $conn = mysqli_connect($host, $user, $pwd, $sql_db);

    if (!$conn) {
        $page = 'failure';   // could not reach the database
    } else {

        // Create the eoi table if it is not there yet
        $create_table = "CREATE TABLE IF NOT EXISTS eoi (
            EOInumber          INT UNSIGNED NOT NULL AUTO_INCREMENT,
            job_ref            CHAR(6)      NOT NULL,
            first_name         VARCHAR(20)  NOT NULL,
            last_name          VARCHAR(20)  NOT NULL,
            dob                DATE         NOT NULL,
            gender             ENUM('male','female','other','prefer-not') NOT NULL,
            gender_description VARCHAR(30)  NOT NULL DEFAULT '',
            street             VARCHAR(40)  NOT NULL,
            suburb             VARCHAR(40)  NOT NULL,
            state              ENUM('VIC','NSW','QLD','NT','WA','SA','TAS','ACT') NOT NULL,
            postcode           CHAR(4)      NOT NULL,
            email              VARCHAR(100) NOT NULL,
            phone              VARCHAR(12)  NOT NULL,
            skills             SET('html','css','javascript','php','python','sql','java','csharp') NOT NULL,
            other_skills       TEXT         NOT NULL,
            status             ENUM('New','Current','Final') NOT NULL DEFAULT 'New',
            submitted_at       TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (EOInumber)
        )";
        mysqli_query($conn, $create_table);

        // The skills list ['html','php'] becomes the text "html,php"
        $skillsText = implode(',', $skills);

        // PREPARED STATEMENT: each ? is a blank that we fill in afterwards.
        // The database treats what we put in the blanks purely as data, never as
        // SQL commands. This is what stops SQL injection.
        $sql = "INSERT INTO eoi (job_ref, first_name, last_name, dob, gender,
                    gender_description, street, suburb, state, postcode,
                    email, phone, skills, other_skills)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = mysqli_prepare($conn, $sql);

        // 14 blanks, each one filled with text: that is why there are 14 letter s
        mysqli_stmt_bind_param($stmt, 'ssssssssssssss',
            $jobRef, $firstName, $lastName, $dobForDatabase, $gender,
            $genderDescription, $street, $suburb, $state, $postcode,
            $email, $phone, $skillsText, $otherSkills);

        if (mysqli_stmt_execute($stmt)) {
            // The database made the EOInumber for us. Ask what it was.
            $eoiNumber = mysqli_stmt_insert_id($stmt);
            $page = 'success';
        } else {
            $page = 'failure';
        }

        mysqli_stmt_close($stmt);
        mysqli_close($conn);
    }
}

// Tell the browser what happened
if ($page == 'errors') {
    http_response_code(422);
} elseif ($page == 'failure') {
    http_response_code(500);
}


/* ===============================================================
   STEP 5: Show the result page
   =============================================================== */

$page_title   = 'Application result';
$current_page = 'apply.php';
include 'header.inc';
?>

    <main>
        <section class="form-container eoi-result">

<?php if ($page == 'success') { ?>

            <h1>Application received</h1>
            <p class="eoi-lead">Thank you, <?php echo esc($firstName); ?>. Your Expression of Interest has been submitted.</p>
            <p class="eoi-number">Your EOI number: <strong><?php echo $eoiNumber; ?></strong></p>
            <p>Please keep this number if you need to contact us about your application.</p>
            <dl class="eoi-summary">
                <dt>Job reference</dt><dd><?php echo esc($jobRef); ?></dd>
                <dt>Name</dt><dd><?php echo esc($firstName . ' ' . $lastName); ?></dd>
                <dt>Email</dt><dd><?php echo esc($email); ?></dd>
                <dt>Status</dt><dd>New</dd>
            </dl>
            <p class="eoi-actions"><a class="apply-button" href="jobs.php">Back to job listings</a></p>

<?php } elseif ($page == 'failure') { ?>

            <h1>Something went wrong</h1>
            <p>Sorry, we could not save your application because of a problem on our side. Nothing has been submitted. Please try again in a few minutes.</p>
            <p class="eoi-actions"><a class="apply-button" href="apply.php">Back to the application form</a></p>

<?php } else { ?>

            <h1>Your application was not submitted</h1>
            <p>Please fix the following and submit again. Using your browser's Back button keeps what you typed.</p>
            <ul class="eoi-errors">
<?php foreach ($errors as $message) { ?>
                <li><?php echo esc($message); ?></li>
<?php } ?>
            </ul>
            <p class="eoi-actions"><a class="apply-button" href="apply.php">Back to the application form</a></p>

<?php } ?>

        </section>
    </main>

<?php include 'footer.inc'; ?>
