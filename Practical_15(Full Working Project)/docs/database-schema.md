# Database schema

`users` stores accounts and roles. `students` holds student directory records and optionally links to a user. `events` stores event details and poster filenames. `registrations` connects users to events with a unique `(user_id,event_id)` pair. `feedback` stores contact feedback. `audit_logs` records administrative/authentication actions. Foreign keys use InnoDB; delete behavior is explicit in `database.sql`.

Import `database.sql` into a fresh `studenthub` schema. The SQL uses `CREATE TABLE IF NOT EXISTS` and `INSERT IGNORE`; it does not drop tables. If upgrading an older database, back it up and reconcile existing table columns before import; this project cannot safely infer every legacy schema variation.
