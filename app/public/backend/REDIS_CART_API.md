# Redis Cart API Documentation

**Base URL:** `http://127.0.0.1:8000/api/cart/redis`

Uses **Redis HASH + TTL** (24-hour expiration) for high-performance caching with user-specific cart management.

---

## Overview

✅ **User-Specific Carts** - Each user has isolated cart data
✅ **24-Hour TTL** - Carts auto-expire after 24 hours of inactivity
✅ **Flexible User Identification** - Use user_id OR username
✅ **Session Fallback** - Guest carts supported via session
✅ **Fast Performance** - In-memory Redis storage

---

## User Identification

You can specify which user's cart to access in two ways:

### Option 1: Query Parameters
```bash
GET /api/cart/redis?user_id=5
GET /api/cart/redis?username=john_doe
```

### Option 2: Request Body
```json
{
  "product_variant_id": 5,
  "quantity": 2,
  "price": 999.99,
  "user_id": 5,
  "username": "john_doe"
}
```

### Option 3: Authenticated Request (Default)
If no user_id or username provided, uses authenticated request user or session.

---

## Endpoints Overview

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/` | Get current cart |
| POST | `/add` | Add item to cart |
| POST | `/update/{product_variant_id}` | Update quantity (exact) |
| POST | `/increase/{product_variant_id}` | Increase quantity |
| POST | `/reduce/{product_variant_id}` | Reduce quantity |
| DELETE | `/remove/{product_variant_id}` | Remove item |
| POST | `/clear` | Clear entire cart |
| GET | `/size` | Get cart item count |

---

## API Endpoints

### 1. Get Current Cart
```bash
GET /cart/redis
```

**With User ID:**
```bash
GET /cart/redis?user_id=5
```

**With Username:**
```bash
GET /cart/redis?username=john_doe
```

**Response:**
```json
{
  "success": true,
  "data": {
    "cart_id": "user_id:5",
    "user_identifier": "5",
    "items": {
      "5": {
        "product_variant_id": 5,
        "quantity": 2,
        "price": 999.99
      },
      "7": {
        "product_variant_id": 7,
        "quantity": 1,
        "price": 1499.50
      }
    },
    "total": 3499.48,
    "item_count": 2
  }
}
```

---

### 2. Add Item to Cart
```bash
POST /cart/redis/add
Content-Type: application/json

{
  "product_variant_id": 5,
  "quantity": 2,
  "price": 999.99,
  "user_id": 5
}
```

**Alternatively with Query Parameter:**
```bash
POST /cart/redis/add?user_id=5
Content-Type: application/json

{
  "product_variant_id": 5,
  "quantity": 2,
  "price": 999.99
}
```

**With Username:**
```bash
POST /cart/redis/add
Content-Type: application/json

{
  "product_variant_id": 5,
  "quantity": 2,
  "price": 999.99,
  "username": "john_doe"
}
```

**Validation Rules:**
- `product_variant_id`: required, integer, min:1
- `quantity`: required, integer, min:1, max:99
- `price`: required, numeric, min:0
- `user_id`: optional, integer, min:1
- `username`: optional, string

**Response (201):**
```json
{
  "success": true,
  "message": "Item added to cart",
  "data": {
    "cart_id": "user_id:5",
    "user_identifier": "5",
    "items": { ... },
    "total": 3499.48,
    "item_count": 2
  }
}
```

---

### 3. Update Item Quantity (Exact)
```bash
POST /cart/redis/update/5?user_id=5
Content-Type: application/json

{
  "quantity": 5
}
```

**Or in body:**
```bash
POST /cart/redis/update/5
Content-Type: application/json

{
  "quantity": 5,
  "user_id": 5
}
```

**Validation Rules:**
- `quantity`: required, integer, min:1, max:99
- `user_id`: optional, integer, min:1
- `username`: optional, string

**Response (200):** Updated cart

---

### 4. Increase Quantity
```bash
POST /cart/redis/increase/5?user_id=5
Content-Type: application/json

{
  "amount": 2
}
```

**Parameters:**
- `amount`: optional, defaults to 1, min:1, max:99
- `user_id`: optional, integer, min:1
- `username`: optional, string

**Behavior:**
- Increment by specific amount (e.g., add 2 more units)
- If `amount` exceeds MAX (99), caps at 99

**Response (200):** Updated cart

---

### 5. Reduce Quantity
```bash
POST /cart/redis/reduce/5?user_id=5
Content-Type: application/json

{
  "amount": 1
}
```

**Parameters:**
- `amount`: optional, defaults to 1, min:1, max:99
- `user_id`: optional, integer, min:1
- `username`: optional, string

**Behavior:**
- If quantity - amount ≤ 0, item is automatically removed
- Otherwise quantity is updated

**Response (200):** Updated cart

---

### 6. Remove Item from Cart
```bash
DELETE /cart/redis/remove/5?user_id=5
```

**Or with username:**
```bash
DELETE /cart/redis/remove/5?username=john_doe
```

**Response (200):** Updated cart without the item

---

### 7. Clear Entire Cart
```bash
POST /cart/redis/clear?user_id=5
```

**Or in body:**
```bash
POST /cart/redis/clear
Content-Type: application/json

{
  "user_id": 5
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Cart cleared",
  "data": {
    "cart_id": "user_id:5",
    "user_identifier": "5",
    "items": {},
    "total": 0,
    "item_count": 0
  }
}
```

---

### 8. Get Cart Size
```bash
GET /cart/redis/size?user_id=5
```

**With Username:**
```bash
GET /cart/redis/size?username=john_doe
```

**Response (200):**
```json
{
  "success": true,
  "data": {
    "item_count": 2
  }
}
```

---

## Redis Data Structure

**Key Format:**
- `cart:user_id:5` (for user_id = 5)
- `cart:username:john_doe` (for username = john_doe)
- `cart:session:xyz` (for guests)

**Type:** Redis HASH

**Example Redis Data:**
```redis
cart:user_id:5 {
  "5" => '{"product_variant_id":5,"quantity":2,"price":999.99}',
  "7" => '{"product_variant_id":7,"quantity":1,"price":1499.50}'
}

TTL: 86400 seconds (24 hours)
```

---

## Features

✅ **User-Specific Carts** - Different carts per user
✅ **24-Hour TTL** - Auto-expiring carts
✅ **Dual Identification** - Use user_id OR username
✅ **HASH Storage** - Efficient key-value pairs
✅ **Quantity Constraints** - Min 1, Max 99 per item
✅ **Atomic Operations** - Data consistency guaranteed
✅ **Fast Performance** - In-memory caching
✅ **Auto-remove** - Items removed when quantity ≤ 0

---

## Configuration

### .env Settings
```ini
CACHE_STORE=redis
REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

### Service Location
- **Service**: `app/Services/RedisCartService.php`
- **Controller**: `app/Http/Controllers/Api/RedisCartController.php`

---

## Usage Examples

### Add Items for User ID 5
```javascript
const response = await fetch('/api/cart/redis/add?user_id=5', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    product_variant_id: 5,
    quantity: 2,
    price: 999.99
  })
});
```

### Increase Quantity for Username 'john_doe'
```javascript
const response = await fetch('/api/cart/redis/increase/5?username=john_doe', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({ amount: 1 })
});
```

### Get Multiple Users' Carts
```javascript
// Get cart for user 5
const user5 = await fetch('/api/cart/redis?user_id=5');

// Get cart for user 10
const user10 = await fetch('/api/cart/redis?user_id=10');

// Get cart for username
const john = await fetch('/api/cart/redis?username=john_doe');
```

### Clear Specific User's Cart
```javascript
const response = await fetch('/api/cart/redis/clear?user_id=5', {
  method: 'POST'
});
```

---

## Error Handling

All endpoints return consistent error responses:

```json
{
  "success": false,
  "message": "Error description",
  "errors": {} // Validation errors if applicable
}
```

**HTTP Status Codes:**
- `200`: Success
- `201`: Created (add item)
- `400`: Invalid request / item not found / missing user id
- `422`: Validation failed
- `500`: Server error

---

## TTL Management

**Cart Expiration:** 24 hours from last activity
- Every operation extends the TTL
- When TTL expires, cart is automatically removed from Redis
- Re-opening expired cart starts fresh

**Example Timeline:**
1. User adds item at 10:00 AM → Expires at 10:00 AM next day
2. User updates quantity at 2:00 PM → Expires at 2:00 PM next day
3. User clears cart at 11:50 PM → Cart deleted, no expiration needed


