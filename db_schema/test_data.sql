USE Pt04_Alex_Ruiz;

-- Sample users (password_hash are placeholders)
INSERT INTO users (username, email, password_hash) VALUES
('alice', 'alice@example.com', ''),
('bob', 'bob@example.com', ''),
('carol', 'carol@example.com', '');

-- Items (bookmarks) with some linked to users
INSERT INTO items (title, description, link, user_id) VALUES
('MDN Web Docs', 'Comprehensive web developer documentation — HTML, CSS, JavaScript, and web APIs.', 'https://developer.mozilla.org', 1),
('Stack Overflow', 'Community Q&A for programmers; great for troubleshooting and code snippets.', 'https://stackoverflow.com', 1),
('PHP Manual', 'Official PHP language reference and documentation.', 'https://www.php.net/manual/en/', 2),
('W3Schools', 'Web development tutorials and references for beginners and professionals.', 'https://www.w3schools.com', 2),
('Hacker News', 'Tech news and discussions focused on startups and engineering.', 'https://news.ycombinator.com', NULL),
('GitHub Docs', 'Guides and reference for GitHub features, workflows and APIs.', 'https://docs.github.com', 3),
('DEV Community', 'Community-written articles, tutorials and discussions for developers.', 'https://dev.to', NULL),
('Can I Use', 'Browser support tables for modern web technologies and features.', 'https://caniuse.com', 1),
('Smashing Magazine', 'Articles on web design, UX and front-end best practices.', 'https://www.smashingmagazine.com', 2),
('freeCodeCamp News', 'Tutorials and long-form articles on programming and web development.', 'https://www.freecodecamp.org/news', 3),
('Google Developers', 'Tools, APIs, and technical documentation from Google.', 'https://developers.google.com', 1),
('MySQL Reference', 'Official MySQL reference documentation and guides.', 'https://dev.mysql.com/doc/', 2),
('CSS-Tricks', 'Tips, techniques and articles for CSS and front-end development.', 'https://css-tricks.com', NULL),
('TutorialsPoint: PHP Sessions', 'Comprehensive guide on PHP sessions.', 'https://www.tutorialspoint.com/php/php_sessions.htm', NULL),
('TutorialsPoint: PHP Cookies', 'Comprehensive guide on PHP cookies.', 'https://www.tutorialspoint.com/php/php_cookies.htm', NULL);
