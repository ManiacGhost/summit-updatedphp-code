# API Test Commands for Postman

**Base URL:** `http://127.0.0.1:8000/api`

## Products API

### 1. Get All Products (Paginated)
```bash
curl --location 'http://127.0.0.1:8000/api/products?page=1&per_page=10' \
--header 'Accept: application/json'
```

### 2. Get Products with Filters
```bash
curl --location 'http://127.0.0.1:8000/api/products?page=1&per_page=12&category_id=1&min_price=500&max_price=3000&sort=price&order=asc' \
--header 'Accept: application/json'
```

### 3. Search Products
```bash
curl --location 'http://127.0.0.1:8000/api/products?search=pressure&page=1&per_page=10' \
--header 'Accept: application/json'
```

### 4. Filter by Multiple Categories
```bash
curl --location 'http://127.0.0.1:8000/api/products?category_id=1,2,3&per_page=20' \
--header 'Accept: application/json'
```

### 5. Sort by Price (Low to High)
```bash
curl --location 'http://127.0.0.1:8000/api/products?sort=price&order=asc&per_page=15' \
--header 'Accept: application/json'
```

### 6. Sort by Newest
```bash
curl --location 'http://127.0.0.1:8000/api/products?sort=newest&order=desc&per_page=15' \
--header 'Accept: application/json'
```

### 7. Price Range Filter (₹1000 to ₹5000)
```bash
curl --location 'http://127.0.0.1:8000/api/products?min_price=1000&max_price=5000&per_page=20' \
--header 'Accept: application/json'
```

### 8. Get Single Product by Slug
```bash
curl --location 'http://127.0.0.1:8000/api/products/summit-innerlid-1l-plain-fine' \
--header 'Accept: application/json'
```

## Categories API

### 9. Get All Categories
```bash
curl --location 'http://127.0.0.1:8000/api/categories' \
--header 'Accept: application/json'
```

### 10. Get Category by Slug
```bash
curl --location 'http://127.0.0.1:8000/api/categories/pressure-cooker' \
--header 'Accept: application/json'
```

## Cart API

### 11. Get Current Cart
```bash
curl --location 'http://127.0.0.1:8000/api/cart' \
--header 'Accept: application/json'
```

### 12. Add Item to Cart
```bash
curl --location 'http://127.0.0.1:8000/api/cart/add' \
--request POST \
--header 'Content-Type: application/json' \
--header 'Accept: application/json' \
--data '{
  "variant_id": 1,
  "quantity": 2
}'
```

### 13. Remove Item from Cart
```bash
curl --location 'http://127.0.0.1:8000/api/cart/remove/1' \
--request GET \
--header 'Accept: application/json'
```

### 14. Update Cart Item Quantity
```bash
curl --location 'http://127.0.0.1:8000/api/cart/update/1' \
--request POST \
--header 'Content-Type: application/json' \
--header 'Accept: application/json' \
--data '{
  "quantity": 5
}'
```

### 15. Clear Cart
```bash
curl --location 'http://127.0.0.1:8000/api/cart/clear' \
--request POST \
--header 'Accept: application/json'
```

## Variants API

### 16. Get Product Variants
```bash
curl --location 'http://127.0.0.1:8000/api/variants/1' \
--header 'Accept: application/json'
```

---

## Combined Filter Examples

### Filter by Category + Price Range + Sort
```bash
curl --location 'http://127.0.0.1:8000/api/products?category_id=1&min_price=1000&max_price=5000&sort=price&order=asc&per_page=12' \
--header 'Accept: application/json'
```

### Search + Price Filter + Pagination
```bash
curl --location 'http://127.0.0.1:8000/api/products?search=pressure&min_price=1500&max_price=4000&page=1&per_page=10' \
--header 'Accept: application/json'
```

### Multiple Categories + Sort by Name
```bash
curl --location 'http://127.0.0.1:8000/api/products?category_id=1,2,3&sort=name&order=asc&per_page=25' \
--header 'Accept: application/json'
```

---

## How to Use in Postman

1. Open Postman
2. Copy any of the curl commands above
3. Click **Import** → **Raw Text** or use **File** → **Import**
4. Paste the curl command
5. Click **Import**
6. Send the request

**OR** manually create requests:
- **Method:** GET (or POST for cart endpoints)
- **URL:** Copy the URL from commands above
- **Headers:** Add `Accept: application/json` and `Content-Type: application/json` (for POST)
- **Body:** For POST requests, use the JSON provided in the commands

---

## Query Parameter Reference

| Parameter | Type | Example | Description |
|-----------|------|---------|-------------|
| `page` | integer | 1 | Page number for pagination |
| `per_page` | integer | 12 | Items per page (max 100) |
| `category_id` | integer/comma-separated | 1 or 1,2,3 | Filter by category ID(s) |
| `min_price` | float | 1000 | Minimum price filter |
| `max_price` | float | 5000 | Maximum price filter |
| `search` | string | pressure cooker | Search in name/description |
| `sort` | string | newest, price, name, popularity | Sort field |
| `order` | string | asc, desc | Sort order |
