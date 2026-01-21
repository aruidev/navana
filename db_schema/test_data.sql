USE Pt05_Alex_Ruiz;

-- Admin user
INSERT INTO users (username, email, password_hash, is_admin) VALUES
('admin', 'admin@admin.com', '$2y$10$Oy/U49m.Z1DojBre6Fr94uyBD5Hq30o/pSrONnmiPjh96Ge97TU7y', TRUE),
('test1', 'test1@mail.com', '$2y$10$Oy/U49m.Z1DojBre6Fr94uyBD5Hq30o/pSrONnmiPjh96Ge97TU7y', FALSE),
('test2', 'test2@mail.com', '$2y$10$Oy/U49m.Z1DojBre6Fr94uyBD5Hq30o/pSrONnmiPjh96Ge97TU7y', FALSE);

-- Items (bookmarks) with some linked to users
INSERT INTO items (title, description, link, tag, user_id) VALUES
('MDN Web Docs', 'Comprehensive web developer documentation — HTML, CSS, JavaScript, and web APIs.', 'https://developer.mozilla.org', 'Docs', 2),
('Stack Overflow', 'Community Q&A for programmers; great for troubleshooting and code snippets.', 'https://stackoverflow.com', 'Social', 3),
('PHP Manual', 'Official PHP language reference and documentation.', 'https://www.php.net/manual/en/', 'Docs', NULL),
('W3Schools', 'Web development tutorials and references for beginners and professionals.', 'https://www.w3schools.com', 'Docs', 2),
('Hacker News', 'Tech news and discussions focused on startups and engineering.', 'https://news.ycombinator.com', 'News', 2),
('GitHub Docs', 'Guides and reference for GitHub features, workflows and APIs.', 'https://docs.github.com', 'Docs', 3),
('DEV Community', 'Community-written articles, tutorials and discussions for developers.', 'https://dev.to', NULL, 3),
('Can I Use', 'Browser support tables for modern web technologies and features.', 'https://caniuse.com', 'Docs', NULL),
('Smashing Magazine', 'Articles on web design, UX and front-end best practices.', 'https://www.smashingmagazine.com', NULL, 3),
('freeCodeCamp News', 'Tutorials and long-form articles on programming and web development.', 'https://www.freecodecamp.org/news', NULL, 2),
('Google Developers', 'Tools, APIs, and technical documentation from Google.', 'https://developers.google.com', NULL, 2),
('MySQL Reference', 'Official MySQL reference documentation and guides.', 'https://dev.mysql.com/doc/', 'Docs', 3),
('CSS-Tricks', 'Tips, techniques and articles for CSS and front-end development.', 'https://css-tricks.com', NULL, 2),
('TutorialsPoint: PHP Sessions', 'Comprehensive guide on PHP sessions.', 'https://www.tutorialspoint.com/php/php_sessions.htm', NULL, 3),
('TutorialsPoint: PHP Cookies', 'Comprehensive guide on PHP cookies.', 'https://www.tutorialspoint.com/php/php_cookies.htm', 'Docs', NULL),
('Reddit: r/webdev', 'Subreddit for web developers to share news and resources.', 'https://www.reddit.com/r/webdev/', 'Social', NULL),
('LinkedIn Learning: Web Development', 'Courses and tutorials on web development topics.', 'https://www.linkedin.com/learning/topics/web-development', NULL, 2),
('A List Apart', 'Articles exploring the design, development, and meaning of web content.', 'https://alistapart.com', 'Docs', 2),
('SitePoint', 'Web development tutorials, articles, and courses.', 'https://www.sitepoint.com', NULL, 2),
('CodePen', 'Online code editor and community for front-end developers.', 'https://codepen.io', 'Social', NULL),
('Mozilla Hacks', 'Articles and tutorials from the Mozilla developer community.', 'https://hacks.mozilla.org', 'Docs', 3),
('Egghead.io', 'High-quality video tutorials for web developers.', 'https://egghead.io', NULL, NULL),
('Frontend Mentor', 'Real-world front-end challenges to improve your skills.', 'https://www.frontendmentor.io', NULL, 2),
('The Odin Project', 'Open-source curriculum for learning web development.', 'https://www.theodinproject.com', 'Docs', 3),
('CSS Reference', 'A free visual guide to CSS.', 'https://cssreference.io', 'Docs', NULL),
('JavaScript Info', 'Modern JavaScript tutorial from the basics to advanced topics.', 'https://javascript.info', 'Docs', NULL),
('Reddit: r/learnprogramming', 'Subreddit for learning programming and coding.', 'https://www.reddit.com/r/learnprogramming/', 'Social', 2),
('GeeksforGeeks', 'Computer science portal with tutorials and coding problems.', 'https://www.geeksforgeeks.org', 'Docs', 3),
('Codewars', 'Platform for coding challenges to improve your skills.', 'https://www.codewars.com', 'Social', 2);