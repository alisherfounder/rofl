# Habit Tracker Database Documentation

## Overview

This is a PHP-based habit tracking application with a MySQL database backend. The system allows users to create, track, and manage personal habits with completion tracking and analytics.

## Database Schema

### Connection Details

- **Host**: `mysql`
- **Database**: `mydb`
- **User**: `appuser`
- **Password**: `apppassword`
- **Charset**: `utf8mb4`

The database connection is managed through PDO with error handling enabled. Tables are automatically initialized on application startup via `initDb()` function.

---

## Tables

### 1. `users`

Stores user account information and authentication data.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | INT | PRIMARY KEY, AUTO_INCREMENT | Unique user identifier |
| `email` | VARCHAR(255) | UNIQUE, NOT NULL | User email address (used for login) |
| `password` | VARCHAR(255) | NOT NULL | Hashed password (PHP `password_hash()`) |
| `name` | VARCHAR(255) | DEFAULT NULL | Optional user display name |
| `status` | VARCHAR(500) | DEFAULT NULL | Optional user status/bio text |
| `created_at` | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Account creation timestamp |

**Notes:**
- Email must be unique across all users
- Passwords are hashed using PHP's `password_hash()` with default algorithm
- `name` and `status` fields were added via ALTER TABLE (handled gracefully if already exist)

---

### 2. `habits`

Stores user-defined habits with their tracking frequency.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | INT | PRIMARY KEY, AUTO_INCREMENT | Unique habit identifier |
| `user_id` | INT | NOT NULL, FOREIGN KEY → `users(id)` ON DELETE CASCADE | Owner of the habit |
| `name` | VARCHAR(255) | NOT NULL | Habit name/description |
| `frequency` | ENUM | NOT NULL | Tracking frequency: `'daily'`, `'weekly'`, or `'monthly'` |
| `created_at` | DATETIME | DEFAULT CURRENT_TIMESTAMP | Habit creation timestamp |
| `updated_at` | DATETIME | DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP | Last modification timestamp |

**Notes:**
- Deleting a user automatically deletes all their habits (CASCADE)
- Frequency is restricted to three predefined values
- `updated_at` automatically updates on any row modification

---

### 3. `habit_completions`

Tracks when habits are marked as completed.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | INT | PRIMARY KEY, AUTO_INCREMENT | Unique completion record identifier |
| `habit_id` | INT | NOT NULL, FOREIGN KEY → `habits(id)` ON DELETE CASCADE | Habit being completed |
| `completed_at` | DATE | NOT NULL | Date of completion (date only, no time) |
| UNIQUE KEY | `unique_completion` | (`habit_id`, `completed_at`) | Prevents duplicate completions for same habit on same day |

**Notes:**
- Deleting a habit automatically deletes all its completion records (CASCADE)
- A habit can only be marked as done once per day (enforced by unique constraint)
- Uses `INSERT IGNORE` to handle duplicate attempts gracefully
- Completion date is stored as DATE (not DATETIME) for daily tracking

---

## Relationships

```
users (1) ──< (many) habits (1) ──< (many) habit_completions
```

- One user can have many habits
- One habit can have many completion records
- Foreign keys enforce referential integrity with CASCADE deletion

---

## Application Flow

### Authentication

1. **Sign Up** (`signup.php`)
   - Validates email uniqueness
   - Requires password minimum 6 characters
   - Creates user record with hashed password

2. **Sign In** (`signin.php`)
   - Validates email/password combination
   - Sets `$_SESSION['user_id']` on success
   - Redirects authenticated users away from sign-in page

3. **Logout** (`logout.php`)
   - Destroys session
   - Redirects to sign-in

### Habit Management

1. **Create Habit** (`create.php` → `store.php`)
   - Requires: name, frequency (daily/weekly/monthly)
   - Creates habit linked to current user

2. **List Habits** (`index.php`)
   - Shows all user's habits
   - Displays completion status for today
   - Provides quick "Done" action for incomplete habits

3. **Mark Complete** (`mark_done.php`)
   - Records completion for today's date
   - Uses `INSERT IGNORE` to prevent duplicates
   - Only processes POST requests

4. **Edit Habit** (`edit.php` → `update.php`)
   - Updates habit name and/or frequency
   - Validates user ownership before update

5. **Delete Habit** (`delete.php`)
   - Removes habit (cascades to completions)
   - Validates user ownership

6. **View Stats** (`view.php`)
   - Shows completion history chart (7/14/30/90 days)
   - Displays summary statistics
   - Allows marking as done from stats page

### Analytics

1. **Dashboard** (`dashboard.php`)
   - Today's completion count
   - Total habits count
   - 30-day trend chart
   - 90-day activity heatmap (GitHub-style contribution graph)
   - Quick stats: this week, this month, last 90 days

### User Profile

1. **View/Edit Profile** (`profile.php` → `update_profile.php`)
   - Email is read-only (cannot be changed)
   - Can update name and status
   - Name max 255 chars, status max 500 chars

---

## Key Queries

### Get Today's Completions
```sql
SELECT habit_id FROM habit_completions 
WHERE habit_id IN (?) AND completed_at = CURDATE()
```

### Get Completion History
```sql
SELECT completed_at, COUNT(*) as count 
FROM habit_completions 
WHERE habit_id = ? 
AND completed_at >= CURDATE() - INTERVAL ? DAY 
GROUP BY completed_at
ORDER BY completed_at ASC
```

### Get User Habits
```sql
SELECT id, name, frequency, created_at 
FROM habits 
WHERE user_id = ? 
ORDER BY created_at DESC
```

### Get Dashboard Stats
```sql
SELECT hc.completed_at, COUNT(*) as count 
FROM habit_completions hc
INNER JOIN habits h ON hc.habit_id = h.id
WHERE h.user_id = ? 
AND hc.completed_at >= CURDATE() - INTERVAL 90 DAY 
GROUP BY hc.completed_at
ORDER BY hc.completed_at ASC
```

---

## Security Features

1. **Password Hashing**: Uses PHP `password_hash()` / `password_verify()`
2. **SQL Injection Prevention**: All queries use prepared statements
3. **Session Management**: User authentication via PHP sessions
4. **Authorization**: All actions verify `user_id` ownership
5. **Input Validation**: Form data validated before database operations
6. **XSS Protection**: Output escaped with `htmlspecialchars()`

---

## Data Integrity

- Foreign key constraints ensure referential integrity
- CASCADE deletion maintains data consistency
- Unique constraints prevent duplicate completions
- ENUM type restricts frequency values
- NOT NULL constraints on critical fields

---

## Common Operations

### Add a New User
```sql
INSERT INTO users (email, password) 
VALUES (?, ?)
-- Password must be hashed with password_hash()
```

### Create a Habit
```sql
INSERT INTO habits (user_id, name, frequency) 
VALUES (?, ?, ?)
```

### Mark Habit as Done
```sql
INSERT IGNORE INTO habit_completions (habit_id, completed_at) 
VALUES (?, CURDATE())
```

### Update Habit
```sql
UPDATE habits 
SET name = ?, frequency = ? 
WHERE id = ? AND user_id = ?
```

### Delete Habit
```sql
DELETE FROM habits 
WHERE id = ? AND user_id = ?
-- Automatically deletes related completions via CASCADE
```

---

## Notes

- Database initialization happens automatically on each request via `initDb()`
- The system uses `CURDATE()` for date comparisons (MySQL function)
- Completion tracking is date-based, not time-based
- All timestamps use server timezone
- The application assumes MySQL/MariaDB compatibility

