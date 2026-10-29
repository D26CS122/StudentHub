# Practical 15 test cases

| ID | Test | Expected result |
|---|---|---|
| T01 | Import `database.sql` in phpMyAdmin | Tables and 20 student / 15 event seed records exist |
| T02 | Register valid new account | Password hash saved; account signed in |
| T03 | Register duplicate email | Friendly validation error; no duplicate account |
| T04 | Register weak/mismatched password | Server rejects submission |
| T05 | Login with invalid password | Generic invalid-credentials message |
| T06 | Student opens `/admin/` | HTTP 403 / access denied |
| T07 | Admin opens admin dashboard | DB-backed counts render |
| T08 | Admin creates/searches/deletes student | Database changes; audit record written |
| T09 | Admin creates event with valid image | Event and validated poster saved |
| T10 | Upload PHP or >2 MB poster | Rejected |
| T11 | GET `/api/students.php` as admin | JSON list with pagination |
| T12 | API mutation without CSRF | HTTP 419 rejection |
| T13 | Student attempts API CRUD | HTTP 403 |
| T14 | Register same event twice | Unique constraint prevents duplicate |
| T15 | Leave session idle over 30 minutes | Session expires on next request |
| T16 | Search/filter public events | Cards update without page reload |
| T17 | Check JSON demo datasets | events, students, FAQs each contain at least 15 records |
| T18 | Review audit log | Login/logout and admin changes visible |
