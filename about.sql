-- Task 7: database-driven About page
-- Import this file while the huge_db database is selected in phpMyAdmin.

CREATE TABLE IF NOT EXISTS about_info (
    content_key VARCHAR(50) NOT NULL,
    content_value TEXT NOT NULL,
    PRIMARY KEY (content_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO about_info (content_key, content_value) VALUES
('hero_title', 'The team behind HuGe'),
('hero_text', 'This recruitment website was designed and built by a student team for COS10026 Web Technology Project, Semester 2 2026.'),
('group_name', 'Group 04 - HuGe'),
('tutorial_class', 'Wednesday, 12:30 - 2:30pm'),
('tutor_name', 'Razeen Hashmi'),
('contribution_intro', 'Each member contributed to both parts of the website project. Detail is recorded in Jira and GitHub.'),
('quote_intro', 'A short quote from each team member in their first language with an English translation.'),
('team_photo', 'images/thegang.jpeg'),
('team_photo_alt', 'The HuGe team gathered together in the office'),
('team_photo_caption', 'Group 04, COS10026 Web Technology Project, Semester 2 2026.'),
('fun_facts_caption', 'A few things you might not know about the HuGe team')
ON DUPLICATE KEY UPDATE content_value = VALUES(content_value);

CREATE TABLE IF NOT EXISTS team_members (
    member_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    member_name VARCHAR(100) NOT NULL,
    student_id VARCHAR(20) NOT NULL,
    part1_contribution TEXT NOT NULL,
    part2_contribution TEXT NOT NULL,
    quote_original TEXT NOT NULL,
    quote_translation TEXT NOT NULL,
    quote_lang VARCHAR(10) NOT NULL,
    quote_language VARCHAR(30) NOT NULL,
    fun_fact TEXT NOT NULL,
    PRIMARY KEY (member_id),
    UNIQUE KEY unique_student_id (student_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO team_members
(member_name, student_id, part1_contribution, part2_contribution,
 quote_original, quote_translation, quote_lang, quote_language, fun_fact)
VALUES
(
    'Sinhak Lor',
    '106637569',
    'Led development of index.html and the home page CSS, including the product table and Acknowledgement of Country section.',
    'TO UPDATE: add Sinhak''s final Part 2 contribution.',
    'តក់ៗពេញបំពង់។',
    'Drop by drop, fills the bucket.',
    'km',
    'Khmer',
    'Sleep acts like a save button, turning short-term memories into long-term ones.'
),
(
    'Marco Tan',
    '102838708',
    'Led development of jobs.html, the job listing content and the styled aside sidebar.',
    'Converted the Part 1 HTML pages to PHP and developed the jobs database table, database-driven job listings and PHP job search functionality.',
    '唔怕行得慢，最怕唔肯行。',
    'Don’t be afraid of moving slowly. Be afraid of not moving at all.',
    'yue',
    'Cantonese',
    'Plays airsoft, scuba dives and is a year 4 engineering student doing a year 1 CS unit.'
),
(
    'Jacob Marulli',
    '106511359',
    'Led development of apply.html, the application form and the form layout.',
    'TO UPDATE: add Jacob''s final Part 2 contribution.',
    'Kia Kaha, Kia Maia, Kia Manawanii',
    'Be Strong, Be Brave, Be Steadfast',
    'mi',
    'Maori',
    'The capital city in Palau has an official permanent population of 0.'
),
(
    'Chandara Yoeun',
    '106195915',
    'Led development of about.html, project coordination in Jira and accessibility across the site.',
    'TO UPDATE: add Chandara''s final Part 2 contribution.',
    'ចេះមួយសាងមួយ ចេះពីរសាងពីរ',
    'Know one thing, build one thing; know two, build two.',
    'km',
    'Khmer',
    'Watches 5 movies in one day.'
)
ON DUPLICATE KEY UPDATE
    member_name = VALUES(member_name),
    part1_contribution = VALUES(part1_contribution),
    part2_contribution = VALUES(part2_contribution),
    quote_original = VALUES(quote_original),
    quote_translation = VALUES(quote_translation),
    quote_lang = VALUES(quote_lang),
    quote_language = VALUES(quote_language),
    fun_fact = VALUES(fun_fact);
