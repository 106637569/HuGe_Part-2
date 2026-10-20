<?php
$page_title = 'About the Team | HuGe';
$current_page = 'about.php';
$extra_head = '<meta name="description" content="Meet the student team behind the HuGe recruitment website project.">' . PHP_EOL .
              '<!-- GenAI acknowledgement: page structure, wireframe and CSS drafted with the assistance of Claude (Anthropic). Content, job details and final edits reviewed and adapted by the group. -->';
include 'header.inc';
?>

<main id="main">

<section class="about-hero">
  <div class="wrap">
    <h1>The team behind HuGe</h1>
    <p>This recruitment website was designed and built by a student team for
      COS10026 Web Technology Project, Semester 2 2026.
    </p>
  </div>
</section>

<div class="wrap section">
<!-- Tutor and lab session -->
<h2>Group and class details</h2>
<ul class="team-nested-list">
  <li>Group 04 - HuGe
    <ul>
      <li>Tutorial class: Wednesday, 12:30 - 2:30pm</li>
      <li>Tutor: Razeen Hashmi</li>
    </ul>
  </li>
</ul>

<h2>Member contributions</h2>
    <p>Each member led one main area of the website. Detail is recorded
      in Jira and GitHub.
    </p>
<!-- Team members work and contribution -->
<dl class="contribution-list">
  <dt>Sinhak Lor - <span class="student-id">106637569</span></dt>
  <dd>Led development of index.html and the home page CSS, including the
    product table and Acknowledgement of Country section.</dd>

  <dt>Marco Tan - <span class="student-id">102838708</span></dt>
    <dd>Led development of jobs.html, the job listing content and the styled
    aside sidebar.
    </dd>

  <dt>Jacob Marulli - <span class="student-id">106511359</span></dt>
    <dd>Led development of apply.html, the application form and the form
    layout.
      </dd>


  <dt>Chandara Yoeun - <span class="student-id">106195915</span></dt>
    <dd>Led development of about.html, project coordination in Jira and
    accessibility across the site.
      </dd>
</dl>
<!-- Quote from each members -->
<h2>Quote from team members</h2>
<p>A short quote from each team members in their first language something they enjoy or feel strongly about.</p>

<div class="quote-list">

  <blockquote class="member-quote">
    <p class="original" lang="yue">[ADD CANTONESE QUOTE HERE]</p>
    <p class="translation"><span class="lang-label">Cantonese - English translation:</span> "If you are not scuba diving and exploring the biggest part of the world, what are you doing with your life."</p>
    <footer>- Marco Tan</footer>
  </blockquote>

  <blockquote class="member-quote">
    <p class="original" lang="km">[ADD KHMER QUOTE HERE]</p>
    <p class="translation"><span class="lang-label">Khmer - English translation:</span> "Water dripping little by little, fills the bucket."</p>
    <footer>- Sinhak Lor</footer>
  </blockquote>

  <blockquote class="member-quote">
    <p class="original" lang="km">[ADD KHMER QUOTE HERE]</p>
    <p class="translation"><span class="lang-label">Khmer - English translation:</span> "A winner is someone who keeps trying."</p>
    <footer>- Chandara Yoeun</footer>
  </blockquote>

  <blockquote class="member-quote">
    <p class="original" lang="mi">[ADD MAORI QUOTE HERE]</p>
    <p class="translation"><span class="lang-label">Maori - English translation:</span> "Cast away your fears and elevate to success."</p>
    <footer>- Jacob Marulli</footer>
  </blockquote>

</div>
<!-- The gang group picture, take using a fire extinguisher as a stand -->
<h2>The team</h2>

<figure class="team-figure" style="max-width: 480px;">
  <img src="images/thegang.jpeg" alt="The HuGe team gathered together in the office" style="margin-top:1.5rem; max-width: 480px; border: 1px solid #dfe6e1;" />
  <figcaption>Group 04, COS10026 Web Technology Project, Semester 2 2026.</figcaption>
</figure>

<h2>Fun facts</h2>

<div class="table-wrap">
  <table class="data-table fun-facts">
    <caption>A few things you might not know about the HuGe team</caption>

    <thead>
      <tr>
        <th scope="col">Member</th>
        <th scope="col">Fun fact</th>
      </tr>
    </thead>
      <!-- Fun fact about the team --> 
    <tbody>
      <tr>
        <td>Marco Tan</td>
        <td>Plays airsoft, scuba dives and is a year 4 engineering student
          doing a year 1 CS unit.</td>
      </tr>

      <tr>
        <td>Sinhak Lor</td>
        <td>Sleep acts like a save button, turning short-term memories into long-term ones.</td>
      </tr>

      <tr>
        <td>Chandara Yoeun</td>
        <td>Watches 5 movies in one day.</td>
      </tr>

      <tr>
        <td>Jacob Marulli</td>
        <td>The capital city in Palau has an official permanent population of 0.</td>
      </tr>
    </tbody>
  </table>
</div>

</div>
</main>

<?php include 'footer.inc'; ?>
