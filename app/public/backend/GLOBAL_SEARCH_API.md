# Global Search API Documentation

## Overview

The Global Search API provides comprehensive fuzzy search capabilities across your product database. It can search for products, variants, SKUs, and product IDs with intelligent relevance scoring.

## Endpoints

### 1. Global Search (Main Endpoint)

**Endpoint:** `GET /api/search`

**Description:** Performs a global fuzzy search across products, variants, and product IDs.

**Query Parameters:**
- `search` (required, string): Search term (minimum 2 characters)

**Example Request:**
```bash
curl --location 'http://127.0.0.1:8000/api/search?search=pressure'
```

**Example Response:**
```json
{
  "success": true,
  "search_term": "pressure",
  "data": [
    {
      "id": 1,
      "name": "SUMMIT OUTERLID PLAIN PRIME",
      "slug": "summit-outerlid",
      "description": "The Summit Outerlid 1.5L Prime Pressure Cooker...",
      "short_description": "short desc",
      "category": {
        "id": 2,
        "name": "Outer Lid",
        "slug": "outer-lid"
      },
      "variants": [
        {
          "id": 4,
          "product_id": 2,
          "sku": "SO1.5P",
          "mrp": 2100,
          "price": 1050,
          "stock": 10,
          "image": "https://s3.amazonaws.com/..."
        },
        {
          "id": 5,
          "product_id": 2,
          "sku": "SO2P",
          "mrp": 2000,
          "price": 1160,
          "stock": 30,
          "image": "https://s3.amazonaws.com/..."
        }
      ],
      "variant_count": 2,
      "match_type": "product"
    }
  ],
  "total": 1
}
```

**Response Structure:**
- `success` (boolean): Operation success status
- `search_term` (string): The search term used
- `data` (array): Array of matching products with variants
- `total` (number): Total number of results

**Product Object Structure:**
```json
{
  "id": number,
  "name": string,
  "slug": string,
  "description": string,
  "short_description": string,
  "category": {
    "id": number,
    "name": string,
    "slug": string
  },
  "variants": [
    {
      "id": number,
      "product_id": number,
      "sku": string,
      "mrp": number,
      "price": number,
      "stock": number,
      "image": string (presigned URL)
    }
  ],
  "variant_count": number,
  "match_type": string
}
```

---

### 2. Search Suggestions (Autocomplete)

**Endpoint:** `GET /api/search/suggestions`

**Description:** Returns autocomplete suggestions for the search bar.

**Query Parameters:**
- `q` (required, string): Partial search term (minimum 2 characters)

**Example Request:**
```bash
curl --location 'http://127.0.0.1:8000/api/search/suggestions?q=pres'
```

**Example Response:**
```json
{
  "suggestions": [
    {
      "text": "SUMMIT OUTERLID PLAIN PRIME",
      "type": "product"
    },
    {
      "text": "SO1.5P",
      "type": "sku"
    },
    {
      "text": "Pressure Cooker",
      "type": "category"
    }
  ]
}
```

**Response Structure:**
- `suggestions` (array): Array of suggestion objects
  - `text` (string): The suggestion text
  - `type` (string): Type of suggestion - "product", "sku", or "category"

---

### 3. Advanced Search with Filters

**Endpoint:** `GET /api/search/advanced`

**Description:** Performs advanced search with optional filtering by category and price range.

**Query Parameters:**
- `search` (required, string): Search term (minimum 2 characters)
- `category_id` (optional, number): Filter by category ID
- `min_price` (optional, number): Minimum price filter
- `max_price` (optional, number): Maximum price filter
- `per_page` (optional, number): Results per page (default: 20, max: 100)
- `page` (optional, number): Page number for pagination (default: 1)

**Example Requests:**

Basic advanced search:
```bash
curl --location 'http://127.0.0.1:8000/api/search/advanced?search=pressure'
```

With category filter:
```bash
curl --location 'http://127.0.0.1:8000/api/search/advanced?search=pressure&category_id=1'
```

With price range filter:
```bash
curl --location 'http://127.0.0.1:8000/api/search/advanced?search=pressure&min_price=1000&max_price=2000'
```

Combined filters with pagination:
```bash
curl --location 'http://127.0.0.1:8000/api/search/advanced?search=pressure&category_id=1&min_price=1000&max_price=2000&per_page=10&page=1'
```

**Example Response:**
```json
{
  "success": true,
  "search_term": "pressure",
  "data": [
    {
      "id": 2,
      "name": "SUMMIT OUTERLID PLAIN PRIME",
      "slug": "summit-outerlid",
      "category_name": "Outer Lid",
      "sku": "SO1.5P",
      "price": 1050,
      "mrp": 2100
    },
    {
      "id": 2,
      "name": "SUMMIT OUTERLID PLAIN PRIME",
      "slug": "summit-outerlid",
      "category_name": "Outer Lid",
      "sku": "SO2P",
      "price": 1160,
      "mrp": 2000
    }
  ],
  "total": 2,
  "pagination": {
    "current_page": 1,
    "total_pages": 1,
    "per_page": 20
  }
}
```

**Response Structure:**
- `success` (boolean): Operation success status
- `search_term` (string): The search term used
- `data` (array): Array of matching products with variant details
- `total` (number): Total number of results
- `pagination` (object): Pagination information
  - `current_page` (number): Current page number
  - `total_pages` (number): Total number of pages
  - `per_page` (number): Results per page

---

## Search Capabilities

### 1. Product Name Search
Searches across product names with relevance scoring:
```bash
curl 'http://127.0.0.1:8000/api/search?search=summit'
```

### 2. Product Description Search
Searches product descriptions and short descriptions:
```bash
curl 'http://127.0.0.1:8000/api/search?search=aluminium'
```

### 3. SKU Search
Searches product variant SKUs:
```bash
curl 'http://127.0.0.1:8000/api/search?search=SO1.5P'
```

### 4. Product ID Search
Search by numeric product ID:
```bash
curl 'http://127.0.0.1:8000/api/search?search=2'
```

### 5. Variant ID Search
Search by numeric variant ID:
```bash
curl 'http://127.0.0.1:8000/api/search?search=4'
```

---

## Relevance Scoring

The search API uses relevance scoring to order results:

### For Products:
- **Score 3:** Product name contains search term
- **Score 2:** Description contains search term
- **Score 1:** Short description contains search term
- **Exact Match:** Product ID matches search term (numeric)

### For Variants:
- **Score 3:** SKU contains search term
- **Score 2:** Product name contains search term
- **Exact Match:** Variant ID or Product ID matches (numeric)

Results are ordered by relevance score (highest first), then by result type.

---

## Error Handling

### Minimum Search Length Error
**Status Code:** 400

```json
{
  "message": "Search term must be at least 2 characters",
  "data": []
}
```

### No Results
**Status Code:** 200

```json
{
  "success": true,
  "search_term": "xyz",
  "data": [],
  "total": 0
}
```

---

## Feature Highlights

### 1. Fuzzy Matching
- Supports partial matches and typo tolerance
- Case-insensitive searching
- Handles whitespace properly

### 2. Multi-Field Search
- Searches product names, descriptions, SKUs, and IDs
- Cross-references products and variants
- Returns comprehensive product information including all variants

### 3. Intelligent Grouping
- Groups variants under parent product
- Avoids duplicate results
- Provides variant count for quick reference

### 4. Presigned URLs
- Automatically generates S3 presigned URLs for images
- Ensures secure image access without exposing direct S3 credentials
- URLs valid for 60 minutes

### 5. Pagination Support
- Advanced search supports pagination
- Configurable results per page (max 100)
- Includes pagination metadata in response

---

## Integration Examples

### JavaScript/Frontend Example

```javascript
// Simple Search
async function globalSearch(searchTerm) {
  const response = await fetch(`/api/search?search=${encodeURIComponent(searchTerm)}`);
  const data = await response.json();
  console.log(data);
  return data.data; // Array of products with variants
}

// Autocomplete Suggestions
async function getSearchSuggestions(term) {
  const response = await fetch(`/api/search/suggestions?q=${encodeURIComponent(term)}`);
  const data = await response.json();
  return data.suggestions; // Array of suggestion objects
}

// Advanced Search with Filters
async function advancedSearch(searchTerm, filters = {}) {
  const params = new URLSearchParams({
    search: searchTerm,
    ...filters // { category_id, min_price, max_price, per_page, page }
  });
  const response = await fetch(`/api/search/advanced?${params}`);
  const data = await response.json();
  return data;
}

// Usage
globalSearch('pressure').then(products => {
  products.forEach(product => {
    console.log(`${product.name} (${product.variant_count} variants)`);
    product.variants.forEach(variant => {
      console.log(`  - ${variant.sku}: ₹${variant.price}`);
    });
  });
});
```

### PHP/Laravel Example

```php
use Illuminate\Support\Facades\Http;

// Simple search
$response = Http::get('http://127.0.0.1:8000/api/search', [
    'search' => 'pressure'
]);
$results = $response->json()['data'];

// Advanced search with filters
$response = Http::get('http://127.0.0.1:8000/api/search/advanced', [
    'search' => 'pressure',
    'category_id' => 1,
    'min_price' => 1000,
    'max_price' => 2000,
    'per_page' => 20,
    'page' => 1
]);
$results = $response->json();
```

---

## Performance Notes

- **Limit:** Maximum 50 results per search to maintain performance
- **Caching:** Consider implementing Redis cache for frequently searched terms
- **Indexing:** For large product catalogs, consider adding database indexes on:
  - `products.name`
  - `products.description`
  - `product_variants.sku`
  - `product_variants.product_id`

---

## Future Enhancements

1. **Full-Text Search:** Migrate to MySQL FULLTEXT indexes for better fuzzy matching
2. **Search Analytics:** Track popular search terms and conversion rates
3. **Typo Correction:** Implement Levenshtein distance for typo tolerance
4. **Filters:** Add attribute filters (material, color, capacity, etc.)
5. **Boost:** Custom relevance boosting by category or popularity
6. **Personalization:** User search history and recommendations

