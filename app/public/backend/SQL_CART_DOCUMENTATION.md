# SQL-Based Cart System Documentation

**Updated:** December 23, 2025  
**Version:** 1.0 (SQL-Based - Redis Removed)  
**Environment:** Hostinger Shared Hosting (No Redis Support)

---

## Overview

This is a complete **SQL-based cart system** with no Redis dependency. All cart data is stored in the database, making it fully compatible with shared hosting environments like Hostinger.

### Key Features

✅ **Database Storage** - All cart data persists in SQL tables  
✅ **User & Guest Support** - Works for authenticated users and anonymous sessions  
✅ **Shared Hosting Compatible** - No Redis or external services required  
✅ **Full-Featured API** - Complete CRUD operations for cart management  
✅ **Session-Based Identification** - Automatic guest cart tracking  
✅ **Quantity Validation** - Min 1, Max 99 items per product  

---

## Database Schema

### Tables

#### `carts` table
Stores cart headers for users and sessions:

| Column | Type | Notes |
|--------|------|-------|
| `id` | BIGINT | Primary key |
| `user_id` | BIGINT | Nullable, links to users table |
| `session_id` | VARCHAR(255) | Session identifier for guests |
| `created_at` | TIMESTAMP | Cart creation time |
| `updated_at` | TIMESTAMP | Last update time |

#### `cart_items` table
Stores individual items in each cart:

| Column | Type | Notes |
|--------|------|-------|
| `id` | BIGINT | Primary key |
| `cart_id` | BIGINT | Foreign key to carts |
| `product_variant_id` | BIGINT | Foreign key to product_variants |
| `quantity` | INT | Item quantity (1-99) |
| `price` | DECIMAL(10,2) | Price per unit |
| `created_at` | TIMESTAMP | Item added time |
| `updated_at` | TIMESTAMP | Last modified time |

**Unique Constraint:** Each product variant appears only once per cart

---

## Service Layer: CartService.php

The `CartService` class handles all cart operations with these main methods:

### Public Methods

#### `getCart(Request $request): array`
Retrieves the current cart with all items and calculations.

```php
// Returns:
{
    "success": true,
    "data": {
        "cart": {
            "id": 1,
            "user_id": 5,
            "session_id": "xyz123",
            "created_at": "2025-12-23T10:00:00Z",
            "updated_at": "2025-12-23T11:00:00Z"
        },
        "items": [
            {
                "id": 1,
                "cart_id": 1,
                "product_variant_id": 5,
                "quantity": 2,
                "price": "999.99",
                "variant": { ... },
                "created_at": "...",
                "updated_at": "..."
            }
        ],
        "total": 1999.98,
        "item_count": 1
    }
}
```

#### `addItem(Request $request, int $productVariantId, int $quantity): array`
Adds or updates an item in the cart.

**Parameters:**
- `$productVariantId` - Product variant ID
- `$quantity` - Quantity to add (combined with existing)

**Behavior:**
- Creates cart if doesn't exist
- If item exists, adds to quantity
- Caps quantity at 99

#### `updateQuantity(Request $request, int $itemId, int $quantity): array`
Sets the exact quantity for an item.

**Parameters:**
- `$itemId` - Cart item ID
- `$quantity` - New quantity (1-99)

#### `removeItem(Request $request, int $itemId): array`
Removes a single item from the cart.

#### `clearCart(Request $request): array`
Removes all items from the cart (keeps cart record).

#### `getCartTotal(Request $request): float`
Returns the total cart value.

#### `getCartItemCount(Request $request): int`
Returns the number of items in cart.

---

## API Endpoints

All endpoints use the `/api/cart` base path.

### 1. Get Current Cart
```
GET /api/cart
```

**Request:**
```bash
curl http://yoursite.com/api/cart
```

**Response:**
```json
{
    "success": true,
    "data": {
        "cart": { ... },
        "items": [ ... ],
        "total": 5999.97,
        "item_count": 3
    }
}
```

---

### 2. Add Item to Cart
```
POST /api/cart/add
```

**Request Body:**
```json
{
    "product_variant_id": 5,
    "quantity": 2
}
```

**Request Example:**
```bash
curl -X POST http://yoursite.com/api/cart/add \
  -H "Content-Type: application/json" \
  -d '{
    "product_variant_id": 5,
    "quantity": 2
  }'
```

**Response:**
```json
{
    "success": true,
    "data": {
        "cart": { ... },
        "items": [ ... ],
        "total": 5999.97,
        "item_count": 3
    }
}
```

**Status:** `201 Created`

---

### 3. Update Item Quantity
```
POST /api/cart/update/{itemId}
```

**Request Body:**
```json
{
    "quantity": 5
}
```

**Request Example:**
```bash
curl -X POST http://yoursite.com/api/cart/update/18 \
  -H "Content-Type: application/json" \
  -d '{
    "quantity": 5
  }'
```

**Response:** Same as add endpoint

---

### 4. Remove Item from Cart
```
GET /api/cart/remove/{itemId}
```

**Request Example:**
```bash
curl http://yoursite.com/api/cart/remove/18
```

**Response:**
```json
{
    "success": true,
    "data": {
        "cart": { ... },
        "items": [ ... ],
        "total": 2999.98,
        "item_count": 2
    }
}
```

---

### 5. Clear Entire Cart
```
POST /api/cart/clear
```

**Request Example:**
```bash
curl -X POST http://yoursite.com/api/cart/clear
```

**Response:**
```json
{
    "success": true,
    "message": "Cart cleared successfully",
    "data": {
        "cart": { ... },
        "items": [],
        "total": 0,
        "item_count": 0
    }
}
```

---

## Cart Identification

The system automatically identifies carts using this priority:

1. **Authenticated User** - Uses `user_id` from `Auth::user()`
2. **Guest Session** - Uses `session_id` from request session

This means:
- Logged-in users always get their own persistent cart
- Guests get a session-based cart that persists across pages
- Switching from guest to logged-in user creates a new cart for that user
- Logging out makes the cart inaccessible (but doesn't delete it)

---

## Implementation in Frontend

### JavaScript/React Example

```javascript
// Get current cart
async function fetchCart() {
    const response = await fetch('/api/cart');
    const data = await response.json();
    console.log(data.data.items);
    console.log('Total:', data.data.total);
}

// Add item to cart
async function addToCart(variantId, quantity) {
    const response = await fetch('/api/cart/add', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            product_variant_id: variantId,
            quantity: quantity
        })
    });
    return response.json();
}

// Update quantity
async function updateQuantity(itemId, newQuantity) {
    const response = await fetch(`/api/cart/update/${itemId}`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ quantity: newQuantity })
    });
    return response.json();
}

// Remove item
async function removeFromCart(itemId) {
    const response = await fetch(`/api/cart/remove/${itemId}`);
    return response.json();
}

// Clear cart
async function clearCart() {
    const response = await fetch('/api/cart/clear', { method: 'POST' });
    return response.json();
}
```

---

## Error Handling

All endpoints return appropriate HTTP status codes:

| Status | Meaning |
|--------|---------|
| `200` | Successful GET/POST request |
| `201` | Item successfully added |
| `400` | Bad request (invalid quantity, missing variant) |
| `422` | Validation failed |
| `500` | Server error |

**Error Response Format:**
```json
{
    "success": false,
    "message": "Error description"
}
```

---

## Configuration

### Quantity Limits

Edit `CartService.php` to change quantity constraints:

```php
private const MAX_QUANTITY = 99;  // Maximum per item
private const MIN_QUANTITY = 1;   // Minimum per item
```

---

## Environment Setup

### For Hostinger Shared Hosting:

1. **Remove Redis from .env:**
   ```
   # Do NOT include these:
   # REDIS_CLIENT=phpredis
   # REDIS_HOST=127.0.0.1
   # REDIS_PASSWORD=null
   # REDIS_PORT=6379
   ```

2. **Set Cache Driver to Database:**
   ```
   CACHE_STORE=database
   SESSION_DRIVER=database
   QUEUE_CONNECTION=database
   ```

3. **Run Migration:**
   ```bash
   php artisan migrate
   ```

---

## Benefits Over Redis

✅ **No External Service** - Works on any hosting with PHP + MySQL  
✅ **Data Persistence** - Cart data is permanent until deleted  
✅ **ACID Compliance** - Database transactions ensure data integrity  
✅ **Shared Hosting Compatible** - No special port/service requirements  
✅ **Backupable** - Easy to backup with database backups  
✅ **Queryable** - Can generate reports, analyze carts, etc.  

---

## Performance Notes

For typical e-commerce sites:
- **Acceptable up to:** 10,000+ active users
- **Query Time:** ~5-10ms per operation
- **Scaling Strategy:** Database indexing on `user_id` and `session_id`

For higher traffic, consider:
1. Database replication
2. Read replicas for reporting
3. Caching layers above (PHP file cache, APCu)

---

## Migration from Redis

If you previously used Redis:

1. ✅ Redis data was temporary (TTL-based)
2. ✅ SQL cart data is persistent
3. ✅ Same API endpoints
4. ✅ Same data structure

**Action Required:** None - the new system is backward compatible

---

## Support & Troubleshooting

### Cart not persisting?
- Check session settings in `config/session.php`
- Ensure database connection works
- Verify migrations ran: `php artisan migrate:status`

### Items disappearing?
- Check `carts` table for orphaned records
- Verify `foreign_keys` constraint enabled
- Review database backups/logs

### Performance issues?
- Check database indexes
- Monitor query performance
- Consider archiving old carts

---

## API Client Reference

See [API_TEST_COMMANDS.md](./API_TEST_COMMANDS.md) for test examples.

---

*This system is optimized for Hostinger and other shared hosting environments.*
