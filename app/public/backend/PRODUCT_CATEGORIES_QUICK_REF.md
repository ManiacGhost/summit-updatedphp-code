# Product Categories CRUD - Quick Reference

## Files Created/Modified

### 1. Migration
📁 **Location**: `database/migrations/2025_01_15_000000_create_sm_product_categories_table.php`

Creates the `sm_product_categories` table with:
- `category_id` (VARCHAR 20, PRIMARY KEY)
- `category_name` (VARCHAR 100)
- `sort_order` (INT, DEFAULT 0)
- `created_at` & `updated_at` timestamps

### 2. Model
📁 **Location**: `app/Models/ProductCategory.php`

Eloquent model with:
- Custom table name: `sm_product_categories`
- String primary key: `category_id`
- Fillable fields: `category_id`, `category_name`, `sort_order`
- Type casting for `sort_order`

### 3. Controller
📁 **Location**: `app/Http/Controllers/Admin/ProductCategoryController.php`

RESTful controller with methods:
- `index()` - GET all categories
- `store()` - POST create new category
- `show()` - GET specific category
- `update()` - PUT/PATCH update category
- `destroy()` - DELETE category
- `updateSortOrder()` - BULK update sort orders

### 4. Routes
📁 **Location**: `routes/api.php`

API endpoints:
```
GET    /api/admin/categories                    - Get all categories
POST   /api/admin/categories                    - Create category
GET    /api/admin/categories/{categoryId}       - Get single category
PUT    /api/admin/categories/{categoryId}       - Update category
DELETE /api/admin/categories/{categoryId}       - Delete category
POST   /api/admin/categories/sort-order/update  - Bulk update sort order
```

---

## Setup Instructions

### 1. Run Migration
```bash
cd app/public/backend
php artisan migrate
```

### 2. Verify Database
```sql
SELECT * FROM sm_product_categories;
```

---

## Sample API Requests

### Create Category
```bash
curl -X POST http://localhost/api/admin/categories \
  -H "Content-Type: application/json" \
  -d '{
    "category_id": "ELEC",
    "category_name": "Electronics",
    "sort_order": 1
  }'
```

### Get All Categories
```bash
curl http://localhost/api/admin/categories
```

### Get Single Category
```bash
curl http://localhost/api/admin/categories/ELEC
```

### Update Category
```bash
curl -X PUT http://localhost/api/admin/categories/ELEC \
  -H "Content-Type: application/json" \
  -d '{
    "category_name": "Electronics & Gadgets",
    "sort_order": 2
  }'
```

### Delete Category
```bash
curl -X DELETE http://localhost/api/admin/categories/ELEC
```

### Bulk Update Sort Order
```bash
curl -X POST http://localhost/api/admin/categories/sort-order/update \
  -H "Content-Type: application/json" \
  -d '{
    "categories": [
      {"category_id": "ELEC", "sort_order": 1},
      {"category_id": "BOOKS", "sort_order": 2}
    ]
  }'
```

---

## Response Format

All endpoints return JSON with consistent structure:

**Success Response:**
```json
{
  "success": true,
  "message": "Operation message",
  "data": {...},
  "count": 0
}
```

**Error Response:**
```json
{
  "success": false,
  "message": "Error message",
  "error": "Details (if applicable)",
  "errors": {...} // Validation errors if applicable
}
```

---

## Validation Rules

| Field | Rules |
|-------|-------|
| `category_id` | required, string, max:20, unique |
| `category_name` | required, string, max:100 |
| `sort_order` | nullable, integer, min:0 |

---

## HTTP Status Codes Used

- `200 OK` - Successful GET/PUT/PATCH/DELETE
- `201 Created` - Successful POST
- `404 Not Found` - Resource not found
- `422 Unprocessable Entity` - Validation failed
- `500 Internal Server Error` - Server error

---

## Next Steps

Ready to create APIs for other tables. Provide DDL for the next table and I'll:
1. Create migration
2. Create model
3. Create controller with full CRUD
4. Add routes
5. Create documentation

