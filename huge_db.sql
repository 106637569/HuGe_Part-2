-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 26, 2026 at 11:53 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `huge_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `about_info`
--

CREATE TABLE `about_info` (
  `content_key` varchar(50) NOT NULL,
  `content_value` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `about_info`
--

INSERT INTO `about_info` (`content_key`, `content_value`) VALUES
('contribution_intro', 'Each member contributed to both parts of the website project. Detail is recorded in Jira and GitHub.'),
('fun_facts_caption', 'A few things you might not know about the HuGe team'),
('group_name', 'Group 04 - HuGe'),
('hero_text', 'This recruitment website was designed and built by a student team for COS10026 Web Technology Project, Semester 2 2026.'),
('hero_title', 'The team behind HuGe'),
('quote_intro', 'A short quote from each team member in their first language with an English translation.'),
('team_photo', 'images/thegang.jpeg'),
('team_photo_alt', 'The HuGe team gathered together in the office'),
('team_photo_caption', 'Group 04, COS10026 Web Technology Project, Semester 2 2026.'),
('tutorial_class', 'Wednesday, 12:30 - 2:30pm'),
('tutor_name', 'Razeen Hashmi');

-- --------------------------------------------------------

--
-- Table structure for table `eoi`
--

CREATE TABLE `eoi` (
  `EOInumber` int(10) UNSIGNED NOT NULL,
  `job_ref` char(6) NOT NULL,
  `first_name` varchar(20) NOT NULL,
  `last_name` varchar(20) NOT NULL,
  `dob` date NOT NULL,
  `gender` enum('male','female','other','prefer-not') NOT NULL,
  `gender_description` varchar(30) NOT NULL DEFAULT '',
  `street` varchar(40) NOT NULL,
  `suburb` varchar(40) NOT NULL,
  `state` enum('VIC','NSW','QLD','NT','WA','SA','TAS','ACT') NOT NULL,
  `postcode` char(4) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(12) NOT NULL,
  `skills` set('html','css','javascript','php','python','sql','java','csharp') NOT NULL,
  `other_skills` text NOT NULL,
  `status` enum('New','Current','Final') NOT NULL DEFAULT 'New',
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `eoi`
--

INSERT INTO `eoi` (`EOInumber`, `job_ref`, `first_name`, `last_name`, `dob`, `gender`, `gender_description`, `street`, `suburb`, `state`, `postcode`, `email`, `phone`, `skills`, `other_skills`, `status`, `submitted_at`) VALUES
(1, 'HGA204', 'Sinhak', 'Lor', '2006-06-06', 'male', '', '442 Elisabeth', 'Malborne', 'VIC', '3000', '106637569@student.swin.edu.au', '040123456', 'html,css,python,java', 'okok', 'Final', '2026-10-18 06:52:29'),
(2, 'HGA204', 'Marco', 'Tan', '2001-06-26', 'male', '', '930 Fkalkasd', 'Melbourne', 'VIC', '3000', '106195915@student.swin.edu.au', '1234456778', 'html,css,sql', 'dfasdfewgfvwref', 'New', '2026-10-20 01:47:28'),
(3, 'HGU309', 'Chandara', 'Yoeun', '2006-09-11', 'male', '', '676 hdfda', 'Melbourne', 'VIC', '3006', '106195915@student.swin.edu.au', '456789022', 'html,css,python,java', 'skillls', 'New', '2026-10-20 01:49:56');

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `job_id` int(10) UNSIGNED NOT NULL,
  `job_ref` char(6) NOT NULL,
  `job_title` varchar(100) NOT NULL,
  `salary` varchar(100) NOT NULL,
  `reporting_line` varchar(100) NOT NULL,
  `short_description` text NOT NULL,
  `key_responsibilities` text NOT NULL,
  `essential_requirements` text NOT NULL,
  `preferable_requirements` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `jobs`
--

INSERT INTO `jobs` (`job_id`, `job_ref`, `job_title`, `salary`, `reporting_line`, `short_description`, `key_responsibilities`, `essential_requirements`, `preferable_requirements`) VALUES
(1, 'HGF101', 'Software Engineer', 'AUD $90,000 - $110,000 per year + super', 'Reports to the Engineering Manager', 'Develop and maintain secure and scalable software solutions for HuGe\'s digital payment and financial platforms.', 'Develop and test web-based financial applications.\nWork with designers and other developers to create new features.\nMaintain clean and readable code.\nIdentify and fix software problems.\nFollow security and privacy requirements.', 'Bachelor degree in Computer Science, Software Engineering or a related field.\nKnowledge of HTML, CSS and a programming language such as Java or Python.\nUnderstanding of basic software development principles.\nGood problem-solving skills.\nAbility to work effectively in a team.', 'Knowledge of cloud services or APIs.\nBasic understanding of cybersecurity.\nPrevious experience with financial or banking software.'),
(2, 'HGA204', 'Data Analyst', 'AUD $80,000 - $98,000 per year + super', 'Reports to the Head of Product', 'Analyse customer and business data to help HuGe improve its financial products and services.', 'Collect and analyse customer and product data.\nPrepare reports for managers and other teams.\nIdentify customer behaviour and usage trends.\nProvide data to support business decisions.\nHelp improve HuGe\'s financial products.', 'Degree in Data Analytics, Business, Information Systems or a related field.\nAbility to understand and interpret data.\nKnowledge of spreadsheets and databases.\nGood communication skills.\nStrong attention to detail.', 'Experience using SQL or Python.\nExperience working with financial data.\nKnowledge of data visualisation tools.'),
(3, 'HGU309', 'UX/UI Designer', 'AUD $78,000 - $95,000 per year + super', 'Reports to the Design Lead', 'Design simple and accessible interfaces for HuGe\'s digital financial services and applications.', 'Design layouts for web and mobile applications.\nCreate wireframes and interface designs.\nWork with developers and product teams.\nFollow accessibility and inclusive design principles.\nImprove designs using user feedback.', 'Qualification in Design, UX, Digital Media or a related area.\nUnderstanding of user-centred design.\nGood communication skills.\nAbility to create simple and consistent interface designs.', 'Portfolio showing previous design projects.\nExperience designing mobile interfaces.\nInterest in financial technology.');

-- --------------------------------------------------------

--
-- Table structure for table `team_members`
--

CREATE TABLE `team_members` (
  `member_id` int(10) UNSIGNED NOT NULL,
  `member_name` varchar(100) NOT NULL,
  `student_id` varchar(20) NOT NULL,
  `part1_contribution` text NOT NULL,
  `part2_contribution` text NOT NULL,
  `quote_original` text NOT NULL,
  `quote_translation` text NOT NULL,
  `quote_lang` varchar(10) NOT NULL,
  `quote_language` varchar(30) NOT NULL,
  `fun_fact` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `team_members`
--

INSERT INTO `team_members` (`member_id`, `member_name`, `student_id`, `part1_contribution`, `part2_contribution`, `quote_original`, `quote_translation`, `quote_lang`, `quote_language`, `fun_fact`) VALUES
(1, 'Sinhak Lor', '106637569', 'Led development of index.html and the home page CSS, including the product table and Acknowledgement of Country section.', 'Built the shared PHP includes (header.inc, nav.inc, footer.inc) and settings.php for the database connection. Created the eoi table, and wrote process_eoi.php to validate all form data on the server.', 'តក់ៗពេញបំពង់។', 'Drop by drop, fills the bucket.', 'km', 'Khmer', 'Sleep acts like a save button, turning short-term memories into long-term ones.'),
(2, 'Marco Tan', '102838708', 'Led development of jobs.html, the job listing content and the styled aside sidebar.', 'Converted the Part 1 HTML pages to PHP and developed the jobs database table, database-driven job listings and PHP job search functionality.', '唔怕行得慢，最怕唔肯行。', 'Don’t be afraid of moving slowly. Be afraid of not moving at all.', 'yue', 'Cantonese', 'Plays airsoft, scuba dives and is a year 4 engineering student doing a year 1 CS unit.'),
(3, 'Jacob Marulli', '106511359', 'Led development of apply.html, the application form and the form layout.', '', 'Kia Kaha, Kia Maia, Kia Manawanii', 'Be Strong, Be Brave, Be Steadfast', 'mi', 'Maori', 'The capital city in Palau has an official permanent population of 0.'),
(4, 'Chandara Yoeun', '106195915', 'Led development of about.html, project coordination in Jira and accessibility across the site.', 'Led  PHP login and logout system, worked on user authentication and session management, setting up the users database and testing and fixing errors in the PHP files .', 'ចេះមួយសាងមួយ ចេះពីរសាងពីរ', 'Know one thing, build one thing; know two, build two.', 'km', 'Khmer', 'Watches 5 movies in one day.');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`) VALUES
(1, 'admin', '$2y$10$XAaLX6wx4K.OlLlxTnKekupdh78JBr/yyZZTqAPnYN.cptMB2euOq');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `about_info`
--
ALTER TABLE `about_info`
  ADD PRIMARY KEY (`content_key`);

--
-- Indexes for table `eoi`
--
ALTER TABLE `eoi`
  ADD PRIMARY KEY (`EOInumber`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`job_id`),
  ADD UNIQUE KEY `unique_job_ref` (`job_ref`);

--
-- Indexes for table `team_members`
--
ALTER TABLE `team_members`
  ADD PRIMARY KEY (`member_id`),
  ADD UNIQUE KEY `unique_student_id` (`student_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `eoi`
--
ALTER TABLE `eoi`
  MODIFY `EOInumber` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `job_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `team_members`
--
ALTER TABLE `team_members`
  MODIFY `member_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
