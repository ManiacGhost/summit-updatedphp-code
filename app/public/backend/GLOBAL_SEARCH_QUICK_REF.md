# Global Search API - Quick Reference

## Main Endpoints

### 1. Global Search
```
GET /api/search?search=pressure
```
Returns products with all variants matching the search term.

### 2. Autocomplete Suggestions
```
GET /api/search/suggestions?q=pres
```
Returns quick suggestions for autocomplete dropdown.

### 3. Advanced Search with Filters
```
GET /api/search/advanced?search=pressure&category_id=1&min_price=1000&max_price=2000
```
Returns filtered results with pagination.

---

## Common Use Cases

### Search by Product Name
```bash
curl 'http://127.0.0.1:8000/api/search?search=pressure'
```

### Search by SKU
```bash
curl 'http://127.0.0.1:8000/api/search?search=SO1.5P'
```

### Search by Product ID
```bash
curl 'http://127.0.0.1:8000/api/search?search=2'
```

### Search by Category with Price Filter
```bash
curl 'http://127.0.0.1:8000/api/search/advanced?search=cooker&category_id=1&min_price=1000'
```

### Autocomplete for Search Bar
```bash
curl 'http://127.0.0.1:8000/api/search/suggestions?q=pres'
```

---

## Response Format

### Search Response
```json
{
  "success": true,
  "search_term": "pressure",
  "total": 1,
  "data": [
    {
      "id": 2,
      "name": "Product Name",
      "slug": "product-slug",
      "description": "...",
      "short_description": "...",
      "category": { "id": 2, "name": "Category", "slug": "slug" },
      "variants": [
        {
          "id": 4,
          "sku": "SO1.5P",
          "price": 1050,
          "mrp": 2100,
          "stock": 10,
          "image": "https://..."
        }
      ],
      "variant_count": 2
    }
  ]
}
```

### Suggestions Response
```json
{
  "suggestions": [
    { "text": "SUMMIT OUTERLID", "type": "product" },
    { "text": "SO1.5P", "type": "sku" },
    { "text": "Pressure Cooker", "type": "category" }
  ]
}
```

---

## Key Features

✅ **Fuzzy Search** - Finds products even with partial/typo searches  
✅ **Multi-field Search** - Searches products, variants, SKUs, IDs  
✅ **Relevance Scoring** - Results ordered by relevance  
✅ **Autocomplete** - Quick suggestions for search bar  
✅ **Advanced Filters** - Category and price range filters  
✅ **Pagination** - Handle large result sets  
✅ **Presigned URLs** - Secure S3 image URLs  

---

## Implementation Tips

1. **Frontend:** Use `/api/search/suggestions` in your search input with debouncing
2. **Results:** Use `/api/search` for main search results with variants
3. **Filters:** Use `/api/search/advanced` when you need category/price filtering
4. **Caching:** Cache suggestions with 5-minute TTL for performance
5. **Debouncing:** Wait 300-500ms after user stops typing before making API calls

---

## Error Codes

| Code | Message | Solution |
|------|---------|----------|
| 400 | Search term must be at least 2 characters | Require minimum 2 characters |
| 200 | Empty data array | No results found, show "No results" message |
| 500 | Server error | Check server logs |

