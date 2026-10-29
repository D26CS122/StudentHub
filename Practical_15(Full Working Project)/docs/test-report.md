# Practical 15 packaging test report

Test date: 2026-10-28

| Check | Result | Notes |
|---|---|---|
| PHP syntax | PASS | `php -l` run across project PHP files; no syntax errors detected at initial check. Re-run after extraction if files are edited. |
| Archive contents | PENDING | Final ZIP validation is performed after packaging. |
| SQL schema / foreign-key syntax | NOT TESTED | No MySQL client/server available in the packaging environment. Import with phpMyAdmin on XAMPP. |
| Registration/auth end-to-end | NOT TESTED | Requires running Apache + MySQL and a browser session. Code path reviewed statically. |
| Authorization and CSRF runtime | NOT TESTED | Requires running PHP web server and session cookies. |
| Student/event CRUD runtime | NOT TESTED | Requires MySQL-backed browser/API testing. |
| Poster upload runtime | NOT TESTED | Requires Apache/PHP upload configuration and writable directory. |
| JavaScript browser console | NOT TESTED | No browser automation runtime available. |
| Original theme assets retained | PASS | Original `style.css`, `css/`, `images/`, wireframes, and primary JavaScript retained; supplemental CSS added. |
| Demo dataset counts | PASS | SQL defines 20 student seed rows and 15 event seed rows; JSON event/student/FAQ files each contain at least 15 records. |

Use `docs/test-cases.md` to run end-to-end tests locally. Do not treat static syntax checks as proof of MySQL integration.
