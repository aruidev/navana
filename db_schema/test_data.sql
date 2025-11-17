USE Pt04_Alex_Ruiz;

-- Items (bookmarks) with some linked to users
INSERT INTO items (title, description, link, category, user_id) VALUES
('MDN Web Docs', 'Comprehensive web developer documentation — HTML, CSS, JavaScript, and web APIs.', 'https://developer.mozilla.org', 'tutorial', NULL),
('Stack Overflow', 'Community Q&A for programmers; great for troubleshooting and code snippets.', 'https://stackoverflow.com', 'tutorial', NULL),
('PHP Manual', 'Official PHP language reference and documentation.', 'https://www.php.net/manual/en/', 'tutorial', NULL),
('W3Schools', 'Web development tutorials and references for beginners and professionals.', 'https://www.w3schools.com', 'tutorial', NULL),
('Hacker News', 'Tech news and discussions focused on startups and engineering.', 'https://news.ycombinator.com', NULL, NULL),
('GitHub Docs', 'Guides and reference for GitHub features, workflows and APIs.', 'https://docs.github.com', 'tutorial', NULL),
('DEV Community', 'Community-written articles, tutorials and discussions for developers.', 'https://dev.to', NULL, NULL),
('Can I Use', 'Browser support tables for modern web technologies and features.', 'https://caniuse.com', 'tutorial', NULL),
('Smashing Magazine', 'Articles on web design, UX and front-end best practices.', 'https://www.smashingmagazine.com',NULL, NULL),
('freeCodeCamp News', 'Tutorials and long-form articles on programming and web development.', 'https://www.freecodecamp.org/news', NULL, NULL),
('Google Developers', 'Tools, APIs, and technical documentation from Google.', 'https://developers.google.com', NULL, NULL),
('MySQL Reference', 'Official MySQL reference documentation and guides.', 'https://dev.mysql.com/doc/', 'tutorial', NULL),
('CSS-Tricks', 'Tips, techniques and articles for CSS and front-end development.', 'https://css-tricks.com', NULL, NULL),
('TutorialsPoint: PHP Sessions', 'Comprehensive guide on PHP sessions.', 'https://www.tutorialspoint.com/php/php_sessions.htm', NULL, NULL),
('TutorialsPoint: PHP Cookies', 'Comprehensive guide on PHP cookies.', 'https://www.tutorialspoint.com/php/php_cookies.htm', 'tutorial', NULL);