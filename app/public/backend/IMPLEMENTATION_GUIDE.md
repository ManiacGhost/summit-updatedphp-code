# SQL Cart Implementation Guide

**For Hostinger Shared Hosting**  
**Date:** December 23, 2025

---

## Summary of Changes

### Removed ❌
- `RedisCartService.php` - Deleted
- `RedisCartController.php` - Deleted
- Redis configuration from `.env`
- Redis routes from `api.php`

### Added ✅
- `CartService.php` - SQL-based cart service
- Database migration for cart tables
- Updated `CartController.php` - Uses CartService
- `SQL_CART_DOCUMENTATION.md` - Complete API docs

### Modified ⚙️
- `.env.example` - Removed Redis settings
- `API_TEST_COMMANDS.md` - Updated endpoint examples

---

## File Structure

```
app/
├── Services/
│   └── CartService.php                    [NEW] SQL-based cart logic
├── Http/Controllers/Admin/
│   └── CartController.php                 [UPDATED] Uses CartService
├── Models/
│   ├── Cart.php                           [EXISTING] No changes
│   └── CartItem.php                       [EXISTING] No changes
└── database/migrations/
    └── 2025_12_23_000000_create_cart_tables.php  [NEW] Migration
```

---

## Setup Instructions

### Step 1: Apply Database Migration

```bash
cd app/public/backend

# Run migrations
php artisan migrate

# Or specifically:
php artisan migrate --path=database/migrations/2025_12_23_000000_create_cart_tables.php
```

**Expected Output:**
```
Migrating: 2025_12_23_000000_create_cart_tables
Migrated:  2025_12_23_000000_create_cart_tables
```

### Step 2: Update Environment Configuration

**File:** `.env`

Remove or comment out:
```
# REDIS_CLIENT=phpredis
# REDIS_HOST=127.0.0.1
# REDIS_PASSWORD=null
# REDIS_PORT=6379
```

Ensure these are set:
```
CACHE_STORE=database
SESSION_DRIVER=database
QUEUE_CONNECTION=database
DB_CONNECTION=mysql
DB_HOST=your_hostinger_host
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### Step 3: Clear Laravel Cache

```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear
```

### Step 4: Restart Application (if on shared hosting)

- Log into cPanel/Hostinger control panel
- Restart PHP if available
- Or simply wait for PHP-FPM to reload (usually automatic)

---

## How It Works

### Cart Creation Flow

```
User/Guest makes request
    ↓
CartController receives request
    ↓
CartService::getCart() called
    ↓
Check if Cart exists:
    - For logged-in users: Find by user_id
    - For guests: Find by session_id
    ↓
If not found AND create=true:
    Create new Cart record
    ↓
Load items with relationships
    ↓
Calculate total
    ↓
Return formatted response
```

### Add to Cart Flow

```
POST /api/cart/add
    ↓
Validate product_variant_id exists
Validate quantity 1-99
    ↓
CartService::addItem()
    ↓
Get or create cart
    ↓
Check if item already in cart:
    - Exists: Add quantity
    - New: Create cart_item
    ↓
Enforce max quantity (99)
    ↓
Save to database
    ↓
Load relationships & return
```

---

## Database Queries Generated

### Get Cart
```sql
SELECT * FROM carts 
WHERE user_id = ? OR session_id = ?

SELECT * FROM cart_items 
WHERE cart_id = ?
WITH relationships to product_variants, products, categories
```

### Add Item
```sql
INSERT INTO carts (user_id, session_id) 
VALUES (?, ?)

INSERT INTO cart_items (cart_id, product_variant_id, quantity, price)
VALUES (?, ?, ?, ?)
ON DUPLICATE KEY UPDATE quantity = quantity + ?
```

### Update Quantity
```sql
UPDATE cart_items 
SET quantity = ?, updated_at = NOW()
WHERE id = ? AND cart_id = ?
```

### Remove Item
```sql
DELETE FROM cart_items 
WHERE id = ? AND cart_id = ?
```

### Clear Cart
```sql
DELETE FROM cart_items 
WHERE cart_id = ?
```

---

## API Response Format

### Success Response
```json
{
  "success": true,
  "data": {
    "cart": {
      "id": 1,
      "user_id": 5,
      "session_id": "xyz123",
      "created_at": "2025-12-23T10:00:00Z",
      "updated_at": "2025-12-23T10:05:00Z"
    },
    "items": [
      {
        "id": 1,
        "cart_id": 1,
        "product_variant_id": 5,
        "quantity": 2,
        "price": "999.99",
        "created_at": "2025-12-23T10:01:00Z",
        "updated_at": "2025-12-23T10:01:00Z",
        "variant": {
          "id": 5,
          "product_id": 2,
          "sku": "VAR-001",
          "price": "999.99",
          "stock": 100,
          "product": { ... },
          "attributes": [ ... ]
        }
      }
    ],
    "total": 1999.98,
    "item_count": 1
  }
}
```

### Error Response
```json
{
  "success": false,
  "message": "Product variant not found",
  "errors": {
    "product_variant_id": ["The selected product variant id is invalid."]
  }
}
```

---

## Testing the Implementation

### Test 1: Create Cart and Add Item

```bash
curl -X POST http://yoursite.com/api/cart/add \
  -H "Content-Type: application/json" \
  -d '{
    "product_variant_id": 1,
    "quantity": 2
  }'
```

Expected: Cart created, item added, returns full cart

### Test 2: Get Cart

```bash
curl http://yoursite.com/api/cart
```

Expected: Returns items added in Test 1

### Test 3: Update Quantity

```bash
curl -X POST http://yoursite.com/api/cart/update/1 \
  -H "Content-Type: application/json" \
  -d '{"quantity": 5}'
```

Expected: Item quantity updated to 5

### Test 4: Remove Item

```bash
curl http://yoursite.com/api/cart/remove/1
```

Expected: Item removed, cart returned without that item

### Test 5: Clear Cart

```bash
curl -X POST http://yoursite.com/api/cart/clear
```

Expected: All items removed, empty cart returned

---

## Performance Optimization

### Database Indexes

Already included in migration, but verify:

```sql
-- Check indexes exist
SHOW INDEX FROM carts WHERE Key_name IN ('user_id', 'session_id');
SHOW INDEX FROM cart_items WHERE Key_name IN ('cart_id', 'product_variant_id');
```

### Query Optimization Tips

1. **Use eager loading** - `with('items.variant.product')`
2. **Limit relationships** - Don't load all images if not needed
3. **Cache cart summary** - Use Redis cache (different from data storage)
4. **Batch operations** - Update multiple items at once

### Recommended Cache Strategy

```php
// Cache cart total for 5 minutes
cache()->remember('cart:' . auth()->id() . ':total', 300, function() {
    return CartService::getCartTotal($request);
});
```

---

## Common Issues & Solutions

### Issue: "Cart not found" errors

**Solution:**
- Check session configuration in `config/session.php`
- Verify `SESSION_DRIVER=database`
- Run: `php artisan session:table` to create sessions table
- Run: `php artisan migrate`

### Issue: Items not saving

**Solution:**
- Check foreign key constraints are enabled
- Verify `product_variants` table exists
- Ensure user is authenticated if user_id is required
- Check database permissions

### Issue: Quantity maxing at 99 unexpectedly

**Solution:**
- This is intentional (prevent bulk buys)
- Change in `CartService.php`: `MAX_QUANTITY` constant
- Adjust as needed for your business

### Issue: Cart disappearing on new tab/incognito

**Solution:**
- Session-based carts need cookies enabled
- Verify `SESSION_DOMAIN` in `.env`
- Check `SESSION_PATH` in `.env`
- Ensure `SESSION_ENCRYPT=false` for debugging

---

## Upgrading From Redis (if applicable)

The previous Redis implementation:
- Stored data in memory (temporary)
- Had 24-hour TTL (auto-expiring)
- Required running Redis service

The new SQL implementation:
- Stores data in database (persistent)
- Data persists indefinitely (unless manually deleted)
- Works on any database-supported hosting

**Important:** Old Redis carts will NOT migrate. Guests will get fresh carts.

---

## Scaling Considerations

### For Small Sites (< 1,000 users)
- Current setup is sufficient
- No additional optimization needed

### For Medium Sites (1,000 - 10,000 users)
- Monitor database performance
- Consider database backup strategy
- Implement query caching

### For Large Sites (> 10,000 users)
- Use database replication
- Implement read replicas
- Consider cache layer for reads
- Archive old carts regularly

---

## Maintenance Tasks

### Weekly
- Monitor disk space for database
- Check error logs

### Monthly
- Analyze slow queries: `SHOW PROCESSLIST;`
- Clean up abandoned carts (no activity > 30 days)

### Quarterly
- Verify backups are working
- Review database performance metrics
- Optimize indexes if needed

### Backup Old Carts

```sql
-- Archive carts older than 90 days
INSERT INTO cart_archives 
SELECT * FROM carts 
WHERE updated_at < DATE_SUB(NOW(), INTERVAL 90 DAY);

DELETE FROM carts 
WHERE updated_at < DATE_SUB(NOW(), INTERVAL 90 DAY);
```

---

## Integration with Frontend

### React/Vue Example

```javascript
// services/cartService.js
const API_BASE = 'http://yoursite.com/api';

export const cartAPI = {
    getCart: () => fetch(`${API_BASE}/cart`).then(r => r.json()),
    
    addItem: (variantId, quantity) =>
        fetch(`${API_BASE}/cart/add`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ product_variant_id: variantId, quantity })
        }).then(r => r.json()),
    
    updateQuantity: (itemId, quantity) =>
        fetch(`${API_BASE}/cart/update/${itemId}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ quantity })
        }).then(r => r.json()),
    
    removeItem: (itemId) =>
        fetch(`${API_BASE}/cart/remove/${itemId}`).then(r => r.json()),
    
    clearCart: () =>
        fetch(`${API_BASE}/cart/clear`, { method: 'POST' }).then(r => r.json())
};
```

---

## Support Resources

- **Cart Service:** `app/Services/CartService.php`
- **Cart Controller:** `app/Http/Controllers/Admin/CartController.php`
- **Documentation:** `SQL_CART_DOCUMENTATION.md`
- **API Tests:** `API_TEST_COMMANDS.md`
- **Database Schema:** `database/migrations/2025_12_23_000000_create_cart_tables.php`

---

## Next Steps

1. ✅ Run migration: `php artisan migrate`
2. ✅ Update `.env` (remove Redis, set SESSION_DRIVER=database)
3. ✅ Clear cache: `php artisan cache:clear`
4. ✅ Test endpoints with provided curl commands
5. ✅ Integrate with frontend using provided examples
6. ✅ Monitor performance in first week

---

*All Redis dependencies have been removed. This system is optimized for Hostinger shared hosting.*
