# 🚀 Redis Cart API - Complete Endpoints Reference

## Overview
✅ **Status**: Implemented and Ready to Use
✅ **User-Specific Carts**: Each user has isolated cart data
📦 **Storage**: Redis HASH with 24-hour TTL
⚡ **Performance**: In-memory caching (fast)
🔄 **Support**: User ID, Username, or Session-based carts

---

## Quick Start

### Add Items for Specific User
```bash
# Using User ID
curl -X POST http://127.0.0.1:8000/api/cart/redis/add?user_id=5 \
  -H 'Content-Type: application/json' \
  -d '{"product_variant_id": 1, "quantity": 2, "price": 999.99}'

# Using Username
curl -X POST http://127.0.0.1:8000/api/cart/redis/add?username=john_doe \
  -H 'Content-Type: application/json' \
  -d '{"product_variant_id": 1, "quantity": 2, "price": 999.99}'
```

### Get User's Cart
```bash
# User ID
curl http://127.0.0.1:8000/api/cart/redis?user_id=5

# Username
curl http://127.0.0.1:8000/api/cart/redis?username=john_doe
```

---

## API Endpoints

### Base URL
```
http://127.0.0.1:8000/api/cart/redis
```

---

## 1️⃣ GET CART
**Retrieve a user's current cart**

```http
GET /api/cart/redis?user_id=5
GET /api/cart/redis?username=john_doe
GET /api/cart/redis (uses authenticated user or session)
```

**cURL - By User ID:**
```bash
curl -X GET http://127.0.0.1:8000/api/cart/redis?user_id=5 \
  -H 'Accept: application/json'
```

**cURL - By Username:**
```bash
curl -X GET http://127.0.0.1:8000/api/cart/redis?username=john_doe \
  -H 'Accept: application/json'
```

**JavaScript:**
```javascript
// Get cart for user ID 5
const response = await fetch('/api/cart/redis?user_id=5');
const cart = await response.json();

// Get cart for username
const response2 = await fetch('/api/cart/redis?username=john_doe');
const cart2 = await response2.json();
```

**Response (200):**
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
      }
    },
    "total": 1999.98,
    "item_count": 1
  }
}
```

---

## 2️⃣ ADD TO CART
**Add a new item or merge with existing**

```http
POST /api/cart/redis/add
Content-Type: application/json

{
  "product_variant_id": 5,
  "quantity": 2,
  "price": 999.99,
  "user_id": 5
}
```

**cURL - Body with User ID:**
```bash
curl -X POST http://127.0.0.1:8000/api/cart/redis/add \
  -H 'Content-Type: application/json' \
  -H 'Accept: application/json' \
  -d '{
    "product_variant_id": 5,
    "quantity": 2,
    "price": 999.99,
    "user_id": 5
  }'
```

**cURL - Query Param:**
```bash
curl -X POST http://127.0.0.1:8000/api/cart/redis/add?user_id=5 \
  -H 'Content-Type: application/json' \
  -H 'Accept: application/json' \
  -d '{
    "product_variant_id": 5,
    "quantity": 2,
    "price": 999.99
  }'
```

**cURL - By Username:**
```bash
curl -X POST http://127.0.0.1:8000/api/cart/redis/add?username=john_doe \
  -H 'Content-Type: application/json' \
  -H 'Accept: application/json' \
  -d '{
    "product_variant_id": 5,
    "quantity": 2,
    "price": 999.99
  }'
```

**JavaScript:**
```javascript
// Add for user ID
const response = await fetch('/api/cart/redis/add?user_id=5', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    product_variant_id: 5,
    quantity: 2,
    price: 999.99
  })
});

// Add by username
const response2 = await fetch('/api/cart/redis/add?username=john_doe', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    product_variant_id: 5,
    quantity: 2,
    price: 999.99
  })
});
```

**Validation:**
- `product_variant_id`: required, integer, min:1
- `quantity`: required, integer, min:1, max:99
- `price`: required, numeric, min:0
- `user_id`: optional, integer, min:1
- `username`: optional, string, min:1

**Behavior:**
- If item already exists, quantity is added (merged)
- Example: Add qty 2 twice = 4 total (unless capped at 99)

**Response (201):**
```json
{
  "success": true,
  "message": "Item added to cart",
  "data": {
    "cart_id": "user_id:5",
    "user_identifier": "5",
    "items": { ... },
    "total": 1999.98,
    "item_count": 1
  }
}
```

**Error (422):**
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "product_variant_id": ["The product_variant_id field is required."]
  }
}
```

---

## 3️⃣ UPDATE QUANTITY (Exact)
**Set quantity to exact amount**

```http
POST /api/cart/redis/update/{product_variant_id}
Content-Type: application/json

{
  "quantity": 5,
  "user_id": 5
}
```

**cURL - Query Param:**
```bash
curl -X POST http://127.0.0.1:8000/api/cart/redis/update/5?user_id=5 \
  -H 'Content-Type: application/json' \
  -H 'Accept: application/json' \
  -d '{
    "quantity": 5
  }'
```

**cURL - Body:**
```bash
curl -X POST http://127.0.0.1:8000/api/cart/redis/update/5 \
  -H 'Content-Type: application/json' \
  -H 'Accept: application/json' \
  -d '{
    "quantity": 5,
    "user_id": 5
  }'
```

**cURL - By Username:**
```bash
curl -X POST http://127.0.0.1:8000/api/cart/redis/update/5?username=john_doe \
  -H 'Content-Type: application/json' \
  -H 'Accept: application/json' \
  -d '{
    "quantity": 5
  }'
```

**JavaScript:**
```javascript
const response = await fetch('/api/cart/redis/update/5?user_id=5', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({ quantity: 5 })
});
```

**Validation:**
- `quantity`: required, integer, min:1, max:99
- `user_id`: optional, integer, min:1
- `username`: optional, string

**Behavior:**
- Sets quantity to exact value (replaces current)
- Example: Current qty=3, update(5) → qty=5

**Response (200):**
```json
{
  "success": true,
  "message": "Item quantity updated",
  "data": { ... }
}
```

**Error (400):**
```json
{
  "success": false,
  "message": "Item not found in cart"
}
```

---

## 4️⃣ INCREASE QUANTITY
**Increment quantity by amount**

```http
POST /api/cart/redis/increase/{product_variant_id}?user_id=5
Content-Type: application/json

{
  "amount": 2
}
```

**cURL:**
```bash
curl -X POST http://127.0.0.1:8000/api/cart/redis/increase/5?user_id=5 \
  -H 'Content-Type: application/json' \
  -H 'Accept: application/json' \
  -d '{
    "amount": 2
  }'
```

**JavaScript:**
```javascript
const response = await fetch('/api/cart/redis/increase/5?user_id=5', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({ amount: 2 })
});
```

**Parameters:**
- `amount`: optional, defaults to 1, min:1, max:99
- `user_id`: optional in query or body
- `username`: optional in query or body

**Behavior:**
- Adds specified amount to current quantity
- Caps at 99 if would exceed
- Example: Current qty=3, increase(2) → qty=5
- Example: Current qty=98, increase(5) → qty=99 (capped)

**Response (200):**
```json
{
  "success": true,
  "message": "Item quantity increased",
  "data": { ... }
}
```

---

## 5️⃣ REDUCE QUANTITY
**Decrement quantity by amount**

```http
POST /api/cart/redis/reduce/{product_variant_id}?user_id=5
Content-Type: application/json

{
  "amount": 1
}
```

**cURL:**
```bash
curl -X POST http://127.0.0.1:8000/api/cart/redis/reduce/5?user_id=5 \
  -H 'Content-Type: application/json' \
  -H 'Accept: application/json' \
  -d '{
    "amount": 1
  }'
```

**JavaScript:**
```javascript
const response = await fetch('/api/cart/redis/reduce/5?user_id=5', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({ amount: 1 })
});
```

**Parameters:**
- `amount`: optional, defaults to 1, min:1, max:99
- `user_id`: optional in query or body
- `username`: optional in query or body

**Behavior:**
- Subtracts specified amount from quantity
- **AUTO-REMOVES item if quantity would be ≤ 0**
- Example: Current qty=3, reduce(1) → qty=2
- Example: Current qty=2, reduce(3) → REMOVED

**Response (200):**
```json
{
  "success": true,
  "message": "Item quantity reduced",
  "data": { ... }
}
```

---

## 6️⃣ REMOVE ITEM
**Delete item from cart**

```http
DELETE /api/cart/redis/remove/{product_variant_id}?user_id=5
```

**cURL:**
```bash
curl -X DELETE http://127.0.0.1:8000/api/cart/redis/remove/5?user_id=5 \
  -H 'Accept: application/json'
```

**JavaScript:**
```javascript
const response = await fetch('/api/cart/redis/remove/5?user_id=5', {
  method: 'DELETE',
  headers: { 'Accept': 'application/json' }
});
```

**Query Parameters:**
- `user_id`: optional
- `username`: optional

**Behavior:**
- Immediately removes item from cart
- Doesn't matter what quantity was

**Response (200):**
```json
{
  "success": true,
  "message": "Item removed from cart",
  "data": { ... }
}
```

**Error (400):**
```json
{
  "success": false,
  "message": "Item not found in cart"
}
```

---

## 7️⃣ CLEAR CART
**Remove all items from cart**

```http
POST /api/cart/redis/clear?user_id=5
```

**cURL - Query Param:**
```bash
curl -X POST http://127.0.0.1:8000/api/cart/redis/clear?user_id=5 \
  -H 'Accept: application/json'
```

**cURL - Body:**
```bash
curl -X POST http://127.0.0.1:8000/api/cart/redis/clear \
  -H 'Content-Type: application/json' \
  -H 'Accept: application/json' \
  -d '{
    "user_id": 5
  }'
```

**JavaScript:**
```javascript
const response = await fetch('/api/cart/redis/clear?user_id=5', {
  method: 'POST',
  headers: { 'Accept': 'application/json' }
});
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

## 8️⃣ GET CART SIZE
**Get number of unique items in cart**

```http
GET /api/cart/redis/size?user_id=5
```

**cURL - User ID:**
```bash
curl -X GET http://127.0.0.1:8000/api/cart/redis/size?user_id=5 \
  -H 'Accept: application/json'
```

**cURL - Username:**
```bash
curl -X GET http://127.0.0.1:8000/api/cart/redis/size?username=john_doe \
  -H 'Accept: application/json'
```

**JavaScript:**
```javascript
const response = await fetch('/api/cart/redis/size?user_id=5');
const { data } = await response.json();
console.log('Items in cart:', data.item_count);
```

**Response (200):**
```json
{
  "success": true,
  "data": {
    "item_count": 3
  }
}
```

---

## Common Use Cases

### Manage Multiple Users' Carts
```javascript
// Get cart for user 5
const user5Cart = await (await fetch('/api/cart/redis?user_id=5')).json();

// Get cart for user 10
const user10Cart = await (await fetch('/api/cart/redis?user_id=10')).json();

// Each user has their own isolated cart
console.log(user5Cart.data.items);  // Only user 5's items
console.log(user10Cart.data.items); // Only user 10's items
```

### Add & Increase Quantity
```javascript
// Add 2 units for user 5
await fetch('/api/cart/redis/add?user_id=5', {
  method: 'POST',
  body: JSON.stringify({
    product_variant_id: 5,
    quantity: 2,
    price: 999.99
  })
});

// Later, increase by 1
await fetch('/api/cart/redis/increase/5?user_id=5', {
  method: 'POST',
  body: JSON.stringify({ amount: 1 })
});
// Now qty = 3 for user 5
```

### Decrement with Auto-Remove
```javascript
// Current qty: 1 for user 10
await fetch('/api/cart/redis/reduce/5?user_id=10', {
  method: 'POST',
  body: JSON.stringify({ amount: 1 })
});
// Item is removed since 1 - 1 = 0
```

### Switch Between Identification Methods
```javascript
// Add for user ID
await fetch('/api/cart/redis/add?user_id=5', {
  method: 'POST',
  body: JSON.stringify({
    product_variant_id: 5,
    quantity: 2,
    price: 999.99
  })
});

// Later, use username for same user
await fetch('/api/cart/redis?username=john_doe');
// NOTE: user_id and username refer to DIFFERENT carts
// Use one consistently per user
```

---

## Response Structure

All successful responses follow:
```json
{
  "success": true,
  "message": "Optional message",
  "data": {
    "cart_id": "user_id:5 or username:john_doe or session:xyz",
    "user_identifier": "5 or john_doe or unknown",
    "items": {
      "variant_id": {
        "product_variant_id": 5,
        "quantity": 2,
        "price": 999.99
      }
    },
    "total": 1999.98,
    "item_count": 1
  }
}
```

Error responses:
```json
{
  "success": false,
  "message": "Error description",
  "errors": {} // Only in validation errors
}
```

---

## HTTP Status Codes

| Code | Meaning |
|------|---------|
| `200` | OK - Request successful |
| `201` | Created - Item added |
| `400` | Bad Request - Invalid data or item not found |
| `422` | Validation Failed - Invalid parameters |
| `500` | Server Error |

---

## User Identification Priority

When multiple identification methods are provided:

1. **Body field** (in POST requests) - Highest priority
2. **Query parameter** - Secondary
3. **Authenticated request** - Used if no ID/username provided

**Example:**
```bash
# This uses user_id=5 (body takes priority)
POST /api/cart/redis/add?user_id=10
{
  "product_variant_id": 5,
  "quantity": 2,
  "price": 999.99,
  "user_id": 5
}
```

---

## Files Created/Modified

| File | Type | Change |
|------|------|--------|
| `app/Services/RedisCartService.php` | Service | User parameters, 24h TTL |
| `app/Http/Controllers/Api/RedisCartController.php` | Controller | User param handling |
| `routes/api.php` | Routes | Same endpoints |
| `.env` | Config | CACHE_STORE=redis |
| `REDIS_CART_API.md` | Docs | Full reference |
| `REDIS_CART_ENDPOINTS.md` | Docs | This file |

---

## Key Features

✅ **User-Specific Carts** - Each user has isolated data
✅ **24-Hour TTL** - Auto-expiring carts
✅ **Flexible User ID** - Support user_id OR username
✅ **Fast Performance** - Redis in-memory
✅ **Consistent API** - Same response format
✅ **Automatic Cleanup** - Items removed at 0 qty
✅ **Full Validation** - All inputs validated
✅ **Error Handling** - Comprehensive error messages

---

**Ready to use! 🎉**



---

## API Endpoints

### Base URL
```
http://127.0.0.1:8000/api/cart/redis
```

---

## 1️⃣ GET CART
**Retrieve the current cart**

```http
GET /api/cart/redis
```

**cURL:**
```bash
curl -X GET http://127.0.0.1:8000/api/cart/redis \
  -H 'Accept: application/json'
```

**JavaScript:**
```javascript
const response = await fetch('/api/cart/redis');
const cart = await response.json();
```

**Response (200):**
```json
{
  "success": true,
  "data": {
    "id": "user:1",
    "items": {
      "5": {
        "product_variant_id": 5,
        "quantity": 2,
        "price": 999.99
      }
    },
    "total": 1999.98,
    "item_count": 1
  }
}
```

---

## 2️⃣ ADD TO CART
**Add a new item or merge with existing**

```http
POST /api/cart/redis/add
Content-Type: application/json

{
  "product_variant_id": 5,
  "quantity": 2,
  "price": 999.99
}
```

**cURL:**
```bash
curl -X POST http://127.0.0.1:8000/api/cart/redis/add \
  -H 'Content-Type: application/json' \
  -H 'Accept: application/json' \
  -d '{
    "product_variant_id": 5,
    "quantity": 2,
    "price": 999.99
  }'
```

**JavaScript:**
```javascript
const response = await fetch('/api/cart/redis/add', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    product_variant_id: 5,
    quantity: 2,
    price: 999.99
  })
});
const result = await response.json();
```

**Validation:**
- `product_variant_id`: required, integer, min:1
- `quantity`: required, integer, min:1, max:99
- `price`: required, numeric, min:0

**Behavior:**
- If item already exists, quantity is added
- Example: Add 2 twice = 4 total (unless capped at 99)

**Response (201):**
```json
{
  "success": true,
  "message": "Item added to cart",
  "data": {
    "id": "user:1",
    "items": { ... },
    "total": 1999.98,
    "item_count": 1
  }
}
```

**Error (422):**
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "product_variant_id": ["The product_variant_id field is required."],
    "quantity": ["The quantity must be between 1 and 99."]
  }
}
```

---

## 3️⃣ UPDATE QUANTITY (Exact)
**Set quantity to exact amount**

```http
POST /api/cart/redis/update/{product_variant_id}
Content-Type: application/json

{
  "quantity": 5
}
```

**cURL:**
```bash
curl -X POST http://127.0.0.1:8000/api/cart/redis/update/5 \
  -H 'Content-Type: application/json' \
  -H 'Accept: application/json' \
  -d '{
    "quantity": 5
  }'
```

**JavaScript:**
```javascript
const response = await fetch('/api/cart/redis/update/5', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({ quantity: 5 })
});
```

**Validation:**
- `quantity`: required, integer, min:1, max:99

**Behavior:**
- Sets quantity to exact value (replaces current)
- Example: Current qty=3, update(5) → qty=5

**Response (200):**
```json
{
  "success": true,
  "message": "Item quantity updated",
  "data": { ... }
}
```

**Error (400):**
```json
{
  "success": false,
  "message": "Item not found in cart"
}
```

---

## 4️⃣ INCREASE QUANTITY
**Increment quantity by amount**

```http
POST /api/cart/redis/increase/{product_variant_id}
Content-Type: application/json

{
  "amount": 2
}
```

**cURL:**
```bash
curl -X POST http://127.0.0.1:8000/api/cart/redis/increase/5 \
  -H 'Content-Type: application/json' \
  -H 'Accept: application/json' \
  -d '{
    "amount": 2
  }'
```

**JavaScript:**
```javascript
const response = await fetch('/api/cart/redis/increase/5', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({ amount: 2 })
});
```

**Parameters:**
- `amount`: optional, defaults to 1, min:1, max:99

**Behavior:**
- Adds specified amount to current quantity
- Caps at 99 if would exceed
- Example: Current qty=3, increase(2) → qty=5
- Example: Current qty=98, increase(5) → qty=99 (capped)

**Response (200):**
```json
{
  "success": true,
  "message": "Item quantity increased",
  "data": { ... }
}
```

---

## 5️⃣ REDUCE QUANTITY
**Decrement quantity by amount**

```http
POST /api/cart/redis/reduce/{product_variant_id}
Content-Type: application/json

{
  "amount": 1
}
```

**cURL:**
```bash
curl -X POST http://127.0.0.1:8000/api/cart/redis/reduce/5 \
  -H 'Content-Type: application/json' \
  -H 'Accept: application/json' \
  -d '{
    "amount": 1
  }'
```

**JavaScript:**
```javascript
const response = await fetch('/api/cart/redis/reduce/5', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({ amount: 1 })
});
```

**Parameters:**
- `amount`: optional, defaults to 1, min:1, max:99

**Behavior:**
- Subtracts specified amount from quantity
- **AUTO-REMOVES item if quantity would be ≤ 0**
- Example: Current qty=3, reduce(1) → qty=2
- Example: Current qty=2, reduce(3) → REMOVED

**Response (200):**
```json
{
  "success": true,
  "message": "Item quantity reduced",
  "data": { ... }
}
```

---

## 6️⃣ REMOVE ITEM
**Delete item from cart**

```http
DELETE /api/cart/redis/remove/{product_variant_id}
```

**cURL:**
```bash
curl -X DELETE http://127.0.0.1:8000/api/cart/redis/remove/5 \
  -H 'Accept: application/json'
```

**JavaScript:**
```javascript
const response = await fetch('/api/cart/redis/remove/5', {
  method: 'DELETE',
  headers: { 'Accept': 'application/json' }
});
```

**Behavior:**
- Immediately removes item from cart
- Doesn't matter what quantity was

**Response (200):**
```json
{
  "success": true,
  "message": "Item removed from cart",
  "data": { ... }
}
```

**Error (400):**
```json
{
  "success": false,
  "message": "Item not found in cart"
}
```

---

## 7️⃣ CLEAR CART
**Remove all items from cart**

```http
POST /api/cart/redis/clear
```

**cURL:**
```bash
curl -X POST http://127.0.0.1:8000/api/cart/redis/clear \
  -H 'Accept: application/json'
```

**JavaScript:**
```javascript
const response = await fetch('/api/cart/redis/clear', {
  method: 'POST',
  headers: { 'Accept': 'application/json' }
});
```

**Response (200):**
```json
{
  "success": true,
  "message": "Cart cleared",
  "data": {
    "id": "user:1",
    "items": {},
    "total": 0,
    "item_count": 0
  }
}
```

---

## 8️⃣ GET CART SIZE
**Get number of unique items in cart**

```http
GET /api/cart/redis/size
```

**cURL:**
```bash
curl -X GET http://127.0.0.1:8000/api/cart/redis/size \
  -H 'Accept: application/json'
```

**JavaScript:**
```javascript
const response = await fetch('/api/cart/redis/size');
const { data } = await response.json();
console.log('Items in cart:', data.item_count);
```

**Response (200):**
```json
{
  "success": true,
  "data": {
    "item_count": 3
  }
}
```

---

## Common Use Cases

### Add Product & Increase Quantity
```javascript
// Add 2 units
await fetch('/api/cart/redis/add', {
  method: 'POST',
  body: JSON.stringify({
    product_variant_id: 5,
    quantity: 2,
    price: 999.99
  })
});

// Later, increase by 1
await fetch('/api/cart/redis/increase/5', {
  method: 'POST',
  body: JSON.stringify({ amount: 1 })
});
// Now qty = 3
```

### Decrement with Auto-Remove
```javascript
// Current qty: 1
await fetch('/api/cart/redis/reduce/5', {
  method: 'POST',
  body: JSON.stringify({ amount: 1 })
});
// Item is removed since 1 - 1 = 0
```

### Set Quantity from Input
```javascript
// User enters "10" in input
const newQty = parseInt(inputValue);
await fetch('/api/cart/redis/update/5', {
  method: 'POST',
  body: JSON.stringify({ quantity: newQty })
});
```

---

## Response Structure

All successful responses follow:
```json
{
  "success": true,
  "message": "Optional message",
  "data": {
    "id": "user:1 or session:xyz",
    "items": {
      "variant_id": {
        "product_variant_id": 5,
        "quantity": 2,
        "price": 999.99
      }
    },
    "total": 1999.98,
    "item_count": 1
  }
}
```

Error responses:
```json
{
  "success": false,
  "message": "Error description",
  "errors": {} // Only in validation errors
}
```

---

## HTTP Status Codes

| Code | Meaning |
|------|---------|
| `200` | OK - Request successful |
| `201` | Created - Item added |
| `400` | Bad Request - Invalid data or item not found |
| `422` | Validation Failed - Invalid parameters |
| `500` | Server Error |

---

## Files Created

| File | Type | Description |
|------|------|-------------|
| `app/Services/RedisCartService.php` | Service | Cart logic |
| `app/Http/Controllers/Api/RedisCartController.php` | Controller | API endpoints |
| `routes/api.php` | Routes | Endpoint definitions |
| `.env` | Config | CACHE_STORE=redis |
| `REDIS_CART_API.md` | Docs | Full documentation |
| `REDIS_CART_IMPLEMENTATION.md` | Docs | Implementation summary |

---

## Required Configuration

✅ Redis running on `127.0.0.1:6379`
✅ `.env` has `CACHE_STORE=redis`
✅ `REDIS_CLIENT=phpredis`

---

## Example Full Flow

```javascript
// 1. Add item 1
await fetch('/api/cart/redis/add', {
  method: 'POST',
  body: JSON.stringify({
    product_variant_id: 5,
    quantity: 2,
    price: 999.99
  })
});

// 2. Add item 2
await fetch('/api/cart/redis/add', {
  method: 'POST',
  body: JSON.stringify({
    product_variant_id: 7,
    quantity: 1,
    price: 1499.50
  })
});

// 3. View cart
const cart = await (await fetch('/api/cart/redis')).json();
// { total: 3499.48, item_count: 2 }

// 4. Increase item 1 by 1
await fetch('/api/cart/redis/increase/5', {
  method: 'POST',
  body: JSON.stringify({ amount: 1 })
});

// 5. Check size
const size = await (await fetch('/api/cart/redis/size')).json();
// { item_count: 2 }

// 6. Checkout (clear cart)
await fetch('/api/cart/redis/clear', { method: 'POST' });
```

---

**Ready to use! 🎉**

