<?php
require_once 'settings.php';

$page_title = 'About the Team | HuGe';
$current_page = 'about.php';
$extra_head = '<meta name="description" content="Meet the student team behind the HuGe recruitment website project.">';

/*
 * Makes text safe to display in HTML.
 */
function h($text)
{
    return htmlspecialchars((string)$text, ENT_QUOTES, 'UTF-8');
}


/* ===============================================================
   Connect to database
   =============================================================== */

mysqli_report(MYSQLI_REPORT_OFF);

$conn = mysqli_connect($host, $user, $pwd, $sql_db);

$db_error = '';

$about = array();
$members = array();


if (!$conn) {

    $db_error = 'Could not connect to the database.';

} else {

    mysqli_set_charset($conn, 'utf8mb4');


    /* ===============================================================
       Get general About page information
       =============================================================== */

    $about_sql = "
        SELECT content_key, content_value
        FROM about_info
    ";

    $about_result = mysqli_query($conn, $about_sql);


    if ($about_result) {

        while ($row = mysqli_fetch_assoc($about_result)) {

            $about[$row['content_key']] = $row['content_value'];

        }

        mysqli_free_result($about_result);

    } else {

        $db_error = 'Could not load the About page information.';

    }


    /* ===============================================================
       Get team member information
       =============================================================== */

    $member_sql = "
        SELECT
            member_id,
            member_name,
            student_id,
            part1_contribution,
            part2_contribution,
            quote_original,
            quote_translation,
            quote_lang,
            quote_language,
            fun_fact
        FROM team_members
        ORDER BY member_id ASC
    ";

    $member_result = mysqli_query($conn, $member_sql);


    if ($member_result) {

        while ($row = mysqli_fetch_assoc($member_result)) {

            $members[] = $row;

        }

        mysqli_free_result($member_result);

    } else {

        $db_error = 'Could not load the team member information.';

    }


    mysqli_close($conn);
}


/* ===============================================================
   Shared header
   =============================================================== */

include 'header.inc';

?>


<main id="main">


    <!-- About introduction -->
    <section class="about-hero">

        <div class="wrap">

            <h1>
                <?php
                echo h(
                    $about['hero_title']
                    ?? 'The team behind HuGe'
                );
                ?>
            </h1>

            <p>
                <?php
                echo h(
                    $about['hero_text']
                    ?? 'COS10026 Web Technology Project, Semester 2 2026.'
                );
                ?>
            </p>

        </div>

    </section>



    <div class="wrap section">


<?php if ($db_error !== '') { ?>


        <!-- Database error -->
        <p>
            <?php echo h($db_error); ?>
        </p>


<?php } else { ?>


        <!-- =====================================================
             Group details
             ===================================================== -->

        <h2>Group and class details</h2>


        <ul class="team-nested-list">

            <li>

                <?php
                echo h(
                    $about['group_name']
                    ?? 'Group 04 - HuGe'
                );
                ?>


                <ul>

                    <li>

                        Tutorial class:

                        <?php
                        echo h(
                            $about['tutorial_class']
                            ?? 'Wednesday, 12:30 - 2:30pm'
                        );
                        ?>

                    </li>


                    <li>

                        Tutor:

                        <?php
                        echo h(
                            $about['tutor_name']
                            ?? 'Razeen Hashmi'
                        );
                        ?>

                    </li>

                </ul>

            </li>

        </ul>



        <!-- =====================================================
             Member contributions
             ===================================================== -->

        <h2>Member contributions</h2>


        <p>

            <?php
            echo h(
                $about['contribution_intro']
                ?? 'Each member contributed to both parts of the project.'
            );
            ?>

        </p>



        <dl class="contribution-list">


<?php foreach ($members as $member) { ?>


            <dt>

                <?php echo h($member['member_name']); ?>

                -

                <span class="student-id">

                    <?php echo h($member['student_id']); ?>

                </span>

            </dt>


            <dd>

                <strong>Part 1:</strong>

                <?php
                echo h(
                    $member['part1_contribution']
                );
                ?>


                <br>


                <strong>Part 2:</strong>

                <?php
                echo h(
                    $member['part2_contribution']
                );
                ?>

            </dd>


<?php } ?>


        </dl>



        <!-- =====================================================
             Quotes
             ===================================================== -->

        <h2>Quote from team members</h2>


        <p>

            <?php
            echo h(
                $about['quote_intro']
                ?? 'A short quote from each team member in their first language with an English translation.'
            );
            ?>

        </p>



        <div class="quote-list">


<?php foreach ($members as $member) { ?>


            <blockquote class="member-quote">


                <p
                    class="original"
                    lang="<?php echo h($member['quote_lang']); ?>"
                >

                    <?php
                    echo h(
                        $member['quote_original']
                    );
                    ?>

                </p>


                <p class="translation">

                    <span class="lang-label">

                        <?php
                        echo h(
                            $member['quote_language']
                        );
                        ?>

                        - English translation:

                    </span>


                    "

                    <?php
                    echo h(
                        $member['quote_translation']
                    );
                    ?>

                    "

                </p>


                <footer>

                    -
                    <?php
                    echo h(
                        $member['member_name']
                    );
                    ?>

                </footer>


            </blockquote>


<?php } ?>


        </div>



        <!-- =====================================================
             Group photo
             ===================================================== -->

        <h2>The team</h2>


        <figure
            class="team-figure"
            style="max-width: 480px;"
        >


            <img
                src="<?php
                    echo h(
                        $about['team_photo']
                        ?? 'images/thegang.jpeg'
                    );
                ?>"
                
                alt="<?php
                    echo h(
                        $about['team_photo_alt']
                        ?? 'The HuGe team gathered together in the office'
                    );
                ?>"

                style="
                    margin-top: 1.5rem;
                    max-width: 480px;
                    border: 1px solid #dfe6e1;
                "
            >


            <figcaption>

                <?php
                echo h(
                    $about['team_photo_caption']
                    ?? 'Group 04, COS10026 Web Technology Project, Semester 2 2026.'
                );
                ?>

            </figcaption>


        </figure>



        <!-- =====================================================
             Fun facts
             ===================================================== -->

        <h2>Fun facts</h2>


        <div class="table-wrap">


            <table class="data-table fun-facts">


                <caption>

                    <?php
                    echo h(
                        $about['fun_facts_caption']
                        ?? 'A few things you might not know about the HuGe team'
                    );
                    ?>

                </caption>


                <thead>

                    <tr>

                        <th scope="col">
                            Member
                        </th>

                        <th scope="col">
                            Fun fact
                        </th>

                    </tr>

                </thead>


                <tbody>


<?php foreach ($members as $member) { ?>


                    <tr>

                        <td>

                            <?php
                            echo h(
                                $member['member_name']
                            );
                            ?>

                        </td>


                        <td>

                            <?php
                            echo h(
                                $member['fun_fact']
                            );
                            ?>

                        </td>

                    </tr>


<?php } ?>


                </tbody>


            </table>


        </div>


<?php } ?>


    </div>


</main>


<?php

include 'footer.inc';

?>