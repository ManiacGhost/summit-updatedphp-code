# Product Categories CRUD API Documentation

## Overview
Complete CRUD API for managing product categories in the `sm_product_categories` table.

## Base URL
```
/api/admin/categories
```

## Endpoints

### 1. Get All Categories
**GET** `/api/admin/categories`

Retrieve all product categories with optional sorting and filtering.

**Response (200 OK):**
```json
{
  "success": true,
  "message": "Categories retrieved successfully",
  "data": [
    {
      "category_id": "CAT001",
      "category_name": "Electronics",
      "sort_order": 1,
      "created_at": "2025-01-15T10:30:00.000000Z",
      "updated_at": "2025-01-15T10:30:00.000000Z"
    },
    {
      "category_id": "CAT002",
      "category_name": "Books",
      "sort_order": 2,
      "created_at": "2025-01-15T10:31:00.000000Z",
      "updated_at": "2025-01-15T10:31:00.000000Z"
    }
  ],
  "count": 2
}
```

**Error Response (500):**
```json
{
  "success": false,
  "message": "Error retrieving categories",
  "error": "error details"
}
```

---

### 2. Create New Category
**POST** `/api/admin/categories`

Create a new product category.

**Request Body:**
```json
{
  "category_id": "CAT001",
  "category_name": "Electronics",
  "sort_order": 1
}
```

**Required Fields:**
- `category_id` (string, max 20): Unique identifier for the category
- `category_name` (string, max 100): Display name of the category

**Optional Fields:**
- `sort_order` (integer, min 0): Display order (default: 0)

**Response (201 Created):**
```json
{
  "success": true,
  "message": "Category created successfully",
  "data": {
    "category_id": "CAT001",
    "category_name": "Electronics",
    "sort_order": 1,
    "created_at": "2025-01-15T10:30:00.000000Z",
    "updated_at": "2025-01-15T10:30:00.000000Z"
  }
}
```

**Validation Error (422):**
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "category_id": ["The category_id field is required."],
    "category_name": ["The category_name field is required."]
  }
}
```

**Error Response (500):**
```json
{
  "success": false,
  "message": "Error creating category",
  "error": "error details"
}
```

---

### 3. Get Single Category
**GET** `/api/admin/categories/{categoryId}`

Retrieve a specific category by ID.

**URL Parameters:**
- `categoryId` (string): The category ID to retrieve

**Response (200 OK):**
```json
{
  "success": true,
  "message": "Category retrieved successfully",
  "data": {
    "category_id": "CAT001",
    "category_name": "Electronics",
    "sort_order": 1,
    "created_at": "2025-01-15T10:30:00.000000Z",
    "updated_at": "2025-01-15T10:30:00.000000Z"
  }
}
```

**Not Found (404):**
```json
{
  "success": false,
  "message": "Category not found"
}
```

---

### 4. Update Category
**PUT/PATCH** `/api/admin/categories/{categoryId}`

Update an existing product category.

**URL Parameters:**
- `categoryId` (string): The category ID to update

**Request Body:**
```json
{
  "category_name": "Electronics & Gadgets",
  "sort_order": 2
}
```

**Optional Fields:**
- `category_name` (string, max 100): Updated display name
- `sort_order` (integer, min 0): Updated display order

**Response (200 OK):**
```json
{
  "success": true,
  "message": "Category updated successfully",
  "data": {
    "category_id": "CAT001",
    "category_name": "Electronics & Gadgets",
    "sort_order": 2,
    "created_at": "2025-01-15T10:30:00.000000Z",
    "updated_at": "2025-01-15T11:00:00.000000Z"
  }
}
```

**Validation Error (422):**
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "category_name": ["The category_name must not exceed 100 characters."]
  }
}
```

**Not Found (404):**
```json
{
  "success": false,
  "message": "Category not found"
}
```

---

### 5. Delete Category
**DELETE** `/api/admin/categories/{categoryId}`

Remove a product category.

**URL Parameters:**
- `categoryId` (string): The category ID to delete

**Response (200 OK):**
```json
{
  "success": true,
  "message": "Category deleted successfully",
  "data": {
    "category_id": "CAT001"
  }
}
```

**Not Found (404):**
```json
{
  "success": false,
  "message": "Category not found"
}
```

---

### 6. Bulk Update Sort Order
**POST** `/api/admin/categories/sort-order/update`

Update the sort order for multiple categories at once.

**Request Body:**
```json
{
  "categories": [
    {
      "category_id": "CAT001",
      "sort_order": 3
    },
    {
      "category_id": "CAT002",
      "sort_order": 1
    },
    {
      "category_id": "CAT003",
      "sort_order": 2
    }
  ]
}
```

**Required Fields:**
- `categories` (array): Array of category objects with:
  - `category_id` (string): Category ID
  - `sort_order` (integer, min 0): New sort order

**Response (200 OK):**
```json
{
  "success": true,
  "message": "Sort order updated successfully",
  "data": [
    {
      "category_id": "CAT001",
      "category_name": "Electronics",
      "sort_order": 3,
      "created_at": "2025-01-15T10:30:00.000000Z",
      "updated_at": "2025-01-15T11:05:00.000000Z"
    },
    {
      "category_id": "CAT002",
      "category_name": "Books",
      "sort_order": 1,
      "created_at": "2025-01-15T10:31:00.000000Z",
      "updated_at": "2025-01-15T11:05:00.000000Z"
    },
    {
      "category_id": "CAT003",
      "category_name": "Clothing",
      "sort_order": 2,
      "created_at": "2025-01-15T10:32:00.000000Z",
      "updated_at": "2025-01-15T11:05:00.000000Z"
    }
  ]
}
```

**Validation Error (422):**
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "categories": ["The categories field is required."],
    "categories.0.category_id": ["The categories.0.category_id field is required."]
  }
}
```

---

## Status Codes

| Code | Description |
|------|-------------|
| 200 | OK - Request succeeded |
| 201 | Created - Resource created successfully |
| 400 | Bad Request - Invalid request format |
| 404 | Not Found - Resource does not exist |
| 422 | Unprocessable Entity - Validation failed |
| 500 | Internal Server Error - Server error |

---

## Validation Rules

### category_id
- Required
- String type
- Maximum 20 characters
- Must be unique in the table
- Cannot be changed after creation

### category_name
- Required (for create)
- Optional (for update)
- String type
- Maximum 100 characters

### sort_order
- Optional
- Integer type
- Minimum value: 0
- Default: 0

---

## Example Usage with cURL

### Create a category
```bash
curl -X POST http://localhost/api/admin/categories \
  -H "Content-Type: application/json" \
  -d '{
    "category_id": "CAT001",
    "category_name": "Electronics",
    "sort_order": 1
  }'
```

### Get all categories
```bash
curl -X GET http://localhost/api/admin/categories
```

### Get single category
```bash
curl -X GET http://localhost/api/admin/categories/CAT001
```

### Update category
```bash
curl -X PUT http://localhost/api/admin/categories/CAT001 \
  -H "Content-Type: application/json" \
  -d '{
    "category_name": "Electronics & Gadgets",
    "sort_order": 2
  }'
```

### Delete category
```bash
curl -X DELETE http://localhost/api/admin/categories/CAT001
```

### Bulk update sort order
```bash
curl -X POST http://localhost/api/admin/categories/sort-order/update \
  -H "Content-Type: application/json" \
  -d '{
    "categories": [
      {"category_id": "CAT001", "sort_order": 3},
      {"category_id": "CAT002", "sort_order": 1}
    ]
  }'
```

---

## Notes

1. **Database Migration**: Run `php artisan migrate` to create the `sm_product_categories` table
2. **Timestamps**: All responses include `created_at` and `updated_at` timestamps
3. **Sorting**: Categories are returned sorted by `sort_order` (ascending) and then by `category_name` (ascending)
4. **Error Handling**: All endpoints include comprehensive error handling with meaningful error messages
5. **Validation**: Input validation is performed on all POST/PUT/PATCH requests

