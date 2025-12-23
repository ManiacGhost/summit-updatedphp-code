# Redis Cart API - Quick Reference v2.0

**User-Specific Carts | 24-Hour TTL**

---

## 🎯 Quick Start

### Add Item for User
```bash
curl -X POST http://127.0.0.1:8000/api/cart/redis/add?user_id=5 \
  -H 'Content-Type: application/json' \
  -d '{"product_variant_id":10, "quantity":2, "price":999.99}'
```

### Get User's Cart
```bash
curl http://127.0.0.1:8000/api/cart/redis?user_id=5
```

### By Username
```bash
curl http://127.0.0.1:8000/api/cart/redis?username=john_smith
```

---

## 📍 Base URL
```
http://127.0.0.1:8000/api/cart/redis
```

---

## 🔧 All Operations

| Operation | Method | Endpoint | User Param |
|-----------|--------|----------|-----------|
| **Get** | GET | `/` | `?user_id=5` |
| **Add** | POST | `/add` | `?user_id=5` |
| **Update** | POST | `/update/{id}` | `?user_id=5` |
| **Increase** | POST | `/increase/{id}` | `?user_id=5` |
| **Reduce** | POST | `/reduce/{id}` | `?user_id=5` |
| **Remove** | DELETE | `/remove/{id}` | `?user_id=5` |
| **Clear** | POST | `/clear` | `?user_id=5` |
| **Size** | GET | `/size` | `?user_id=5` |

---

## 📥 Add Item

```bash
POST /add?user_id=5
{
  "product_variant_id": 10,
  "quantity": 2,
  "price": 999.99
}
```

**OR in body:**
```bash
POST /add
{
  "product_variant_id": 10,
  "quantity": 2,
  "price": 999.99,
  "user_id": 5
}
```

---

## 📤 Get Cart

```bash
GET /?user_id=5
```

**Response:**
```json
{
  "success": true,
  "data": {
    "cart_id": "user_id:5",
    "user_identifier": "5",
    "items": {
      "10": {
        "product_variant_id": 10,
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

## 🔄 Update Quantity (Exact)

```bash
POST /update/10?user_id=5
{
  "quantity": 5
}
```

---

## ⬆️ Increase Quantity

```bash
POST /increase/10?user_id=5
{
  "amount": 2
}
```

---

## ⬇️ Reduce Quantity

```bash
POST /reduce/10?user_id=5
{
  "amount": 1
}
```

*Auto-removes if qty ≤ 0*

---

## ❌ Remove Item

```bash
DELETE /remove/10?user_id=5
```

---

## 🗑️ Clear Cart

```bash
POST /clear?user_id=5
```

---

## 📊 Get Size

```bash
GET /size?user_id=5

# Response
{
  "success": true,
  "data": {
    "item_count": 1
  }
}
```

---

## 👤 User Identification

### Option 1: Query Parameter
```bash
?user_id=5
?username=john_smith
```

### Option 2: Request Body
```json
{
  "product_variant_id": 10,
  "user_id": 5
}
```

### Option 3: Authentication
```bash
# Uses Auth::user()->id or session (no param needed)
GET /
```

---

## ⏱️ TTL Information

- **Duration:** 24 hours (86,400 seconds)
- **Reset:** Every cart operation extends TTL
- **Expiration:** Auto-removed after 24h inactivity
- **Redis Key:** `cart:user_id:5`

---

## 🧪 JavaScript Example

```javascript
// Add item
await fetch('/api/cart/redis/add?user_id=5', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    product_variant_id: 10,
    quantity: 2,
    price: 999.99
  })
});

// Get cart
const cart = await (await fetch('/api/cart/redis?user_id=5')).json();
console.log(cart.data.items);

// Update qty
await fetch('/api/cart/redis/update/10?user_id=5', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({ quantity: 5 })
});

// Remove item
await fetch('/api/cart/redis/remove/10?user_id=5', {
  method: 'DELETE'
});

// Clear cart
await fetch('/api/cart/redis/clear?user_id=5', {
  method: 'POST'
});
```

---

## 📋 HTTP Status Codes

| Code | Meaning |
|------|---------|
| 200 | Success |
| 201 | Item added |
| 400 | Bad request |
| 422 | Validation failed |
| 500 | Server error |

---

## 🆔 User Parameters

**Supported Formats:**

```bash
# User ID
?user_id=5
?user_id=1

# Username
?username=john_smith
?username=alice_wonder

# In body
{"user_id": 5}
{"username": "john_smith"}
```

---

## 🔍 Redis Keys

```
cart:user_id:5         # User 5's cart
cart:username:john     # john's cart
cart:session:xyz       # Guest session
```

---

## ⚙️ Configuration

```ini
CACHE_STORE=redis
REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
```

---

## ✅ Validation

- `product_variant_id`: required, integer, min:1
- `quantity`: required, integer, min:1, max:99
- `price`: required, numeric, min:0
- `user_id`: optional, integer, min:1
- `username`: optional, string

---

## 📚 Documentation

- **REDIS_CART_API.md** - Full reference
- **REDIS_CART_ENDPOINTS.md** - Detailed examples
- **REDIS_CART_UPDATE_v2.md** - Change log
- **USER_SPECIFIC_CART_SUMMARY.md** - Implementation guide

---

## 🚀 Status

✅ Production Ready
✅ User-Specific
✅ 24-Hour TTL
✅ Fully Documented

