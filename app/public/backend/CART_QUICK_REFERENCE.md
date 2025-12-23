# SQL Cart - Quick Reference

**Version:** 1.0  
**Type:** SQL-Based (No Redis)

---

## Endpoints

| Method | URL | Purpose |
|--------|-----|---------|
| GET | `/api/cart` | Get current cart |
| POST | `/api/cart/add` | Add item to cart |
| POST | `/api/cart/update/{id}` | Update item quantity |
| GET | `/api/cart/remove/{id}` | Remove item |
| POST | `/api/cart/clear` | Clear all items |

---

## Request Examples

### Add Item
```bash
curl -X POST http://site.com/api/cart/add \
  -H "Content-Type: application/json" \
  -d '{"product_variant_id": 5, "quantity": 2}'
```

### Update Quantity
```bash
curl -X POST http://site.com/api/cart/update/1 \
  -H "Content-Type: application/json" \
  -d '{"quantity": 3}'
```

### Get Cart
```bash
curl http://site.com/api/cart
```

### Remove Item
```bash
curl http://site.com/api/cart/remove/1
```

### Clear Cart
```bash
curl -X POST http://site.com/api/cart/clear
```

---

## Response Example

```json
{
  "success": true,
  "data": {
    "cart": {
      "id": 1,
      "user_id": 5,
      "session_id": "xyz123"
    },
    "items": [
      {
        "id": 1,
        "cart_id": 1,
        "product_variant_id": 5,
        "quantity": 2,
        "price": "999.99",
        "variant": { ... }
      }
    ],
    "total": 1999.98,
    "item_count": 1
  }
}
```

---

## Setup

### 1. Run Migration
```bash
php artisan migrate
```

### 2. Update .env
```
CACHE_STORE=database
SESSION_DRIVER=database
QUEUE_CONNECTION=database
# Remove all REDIS_* settings
```

### 3. Clear Cache
```bash
php artisan cache:clear
```

---

## Files Changed

| File | Status | Change |
|------|--------|--------|
| `.env.example` | ✅ Updated | Removed Redis config |
| `CartService.php` | ✅ New | SQL-based service |
| `CartController.php` | ✅ Updated | Uses CartService |
| `RedisCartService.php` | ❌ Deleted | No longer needed |
| `RedisCartController.php` | ❌ Deleted | No longer needed |

---

## Key Features

✅ Works on Hostinger (no external services)  
✅ Persistent cart data in database  
✅ Support for both users and guests  
✅ Automatic session tracking  
✅ Quantity limits (1-99)  
✅ Full CRUD operations  

---

## Validation

- Quantity: 1-99
- Variant: Must exist in database
- Price: Auto-loaded from variant
- User: Auto-detected from session

---

## Limits

| Setting | Value |
|---------|-------|
| Min Quantity | 1 |
| Max Quantity | 99 |
| Max Items | Unlimited |
| Cart Expiry | Never (persistent) |

---

## Troubleshooting

**Cart not found?**
- Check SESSION_DRIVER=database

**Items not saving?**
- Verify product_variant_id exists
- Check user has database connection

**Getting errors?**
- See IMPLEMENTATION_GUIDE.md

---

## Docs

- `SQL_CART_DOCUMENTATION.md` - Complete API docs
- `IMPLEMENTATION_GUIDE.md` - Setup & config guide
- `API_TEST_COMMANDS.md` - Test examples

---

*No Redis required. Works perfectly on shared hosting.*
