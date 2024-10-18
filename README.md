# Task Management API

A RESTful API built with Lumen for managing tasks. The API provides CRUD operations for tasks with features like filtering, pagination, and search functionality.

## Requirements

* PHP >= 8.2
* Composer
* PostgreSQL
* Git

## Installation

### 1. Clone the repository
```bash
git clone [repository-url]
cd task-management-api
```

### 2. Install dependencies
```bash
composer install
```

### 3. Environment Setup
```bash
cp .env.example .env
```

### 4. Configure your `.env` file
```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=task_management
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 5. Create the database
```sql
# Using psql
psql -U postgres
CREATE DATABASE task_management;
\q
```

### 6. Run migrations
```bash
php artisan migrate
```

### 7. Start the development server
```bash
php -S localhost:8000 -t public
```

## API Endpoints

### Tasks

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/tasks` | Get all tasks (with filtering & pagination) |
| GET | `/api/tasks/{id}` | Get a specific task |
| POST | `/api/tasks` | Create a new task |
| PUT | `/api/tasks/{id}` | Update a task |
| DELETE | `/api/tasks/{id}` | Delete a task |

### Query Parameters for GET /api/tasks

* `status` - Filter by status (pending, in_progress, completed)
* `due_date` - Filter by due date (YYYY-MM-DD)
* `search` - Search in title and description
* `per_page` - Number of items per page (default: 10)
* `page` - Page number
* `sort_by` - Sort field (title, status, due_date, created_at)
* `sort_direction` - Sort direction (asc, desc)

## Data Validation

### Creating/Updating Tasks

* `title`
  * Required
  * Unique
  * Max 255 characters
* `description`
  * Optional
  * String
* `status`
  * Optional
  * Must be one of: pending, in_progress, completed
* `due_date`
  * Required for creation
  * Must be a future date

## Example Usage

### Create a Task
```bash
curl -X POST http://localhost:8000/api/tasks \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Complete Documentation",
    "description": "Write project documentation",
    "due_date": "2024-12-31"
  }'
```

### Get Tasks with Filters
```bash
curl "http://localhost:8000/api/tasks?status=pending&search=documentation&per_page=20"
```

## Error Handling

The API returns appropriate HTTP status codes and messages:

| Status Code | Description |
|-------------|-------------|
| 200 | Success |
| 201 | Created |
| 404 | Not Found |
| 422 | Validation Error |
| 500 | Server Error |

## Project Structure

```
task-management-api/
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │       └── TaskController.php
│   └── Models/
│       └── Task.php
├── database/
│   └── migrations/
│       └── [timestamp]_create_tasks_table.php
├── routes/
│   └── web.php
├── .env
└── composer.json
```

## API Response Examples

### Successful Task Creation
```json
{
    "message": "Task created successfully",
    "data": {
        "id": 1,
        "title": "Complete Documentation",
        "description": "Write project documentation",
        "status": "pending",
        "due_date": "2024-12-31",
        "created_at": "2024-10-18T10:00:00.000000Z",
        "updated_at": "2024-10-18T10:00:00.000000Z"
    }
}
```

### Task List Response
```json
{
    "data": [
        {
            "id": 1,
            "title": "Complete Documentation",
            "description": "Write project documentation",
            "status": "pending",
            "due_date": "2024-12-31",
            "created_at": "2024-10-18T10:00:00.000000Z",
            "updated_at": "2024-10-18T10:00:00.000000Z"
        }
    ],
    "meta": {
        "current_page": 1,
        "last_page": 1,
        "per_page": 10,
        "total": 1
    }
}
```

### Validation Error Response
```json
{
    "message": "Validation failed",
    "errors": {
        "title": [
            "The title field is required"
        ],
        "due_date": [
            "The due date must be a date after today"
        ]
    }
}
```

## Additional Notes

* All dates should be in YYYY-MM-DD format
* The API uses JSON for request and response bodies
* Pagination is enabled by default with 10 items per page
* Search functionality looks for matches in both title and description
* Task titles must be unique across all tasks

