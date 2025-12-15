# Mega Menu API Documentation

## Overview
The new **Mega Menu API** consolidates all product categories with their hierarchical structure (categories → subcategories → materials/series → options/series names → sizes).

It fetches data from the new schema tables:
- `sm_product_categories` - Main categories
- `sm_subcategories` - Subcategories linked to products
- `sm_materials` - Materials (Aluminium, Stainless Steel, etc.)
- `sm_series` - Series/Options (Prime, Supreme, Fine, Elite, etc.)
- `sm_product_details` - Contains sizes (net_quantity)

---

## Endpoint

**GET** `/api/mega-menu`

Base URL: `http://localhost:8000` (or your actual domain)

Full URL: `http://localhost:8000/api/mega-menu`

---

## Response Format

```json
[
    {
        "id": "PC",
        "name": "Pressure Cooker",
        "sort_order": 10,
        "sub_categories": [
            {
                "id": "SI1F-SUB",
                "name": "Outerlid Pressure Cooker",
                "series": [
                    {
                        "id": "M1",
                        "name": "Aluminium",
                        "options": [
                            {
                                "id": "SI1F-SER",
                                "name": "Prime",
                                "series_id": "M1",
                                "sizes": [
                                    "1.5L",
                                    "2L",
                                    "3L",
                                    "5L"
                                ]
                            },
                            {
                                "id": "SI1IF-SER",
                                "name": "Supreme",
                                "series_id": "M1",
                                "sizes": [
                                    "1L",
                                    "1.5L",
                                    "2L",
                                    "3L",
                                    "5L"
                                ]
                            }
                        ]
                    },
                    {
                        "id": "M2",
                        "name": "Stainless Steel",
                        "options": [
                            {
                                "id": "SO1.5P-SER",
                                "name": "Desire",
                                "series_id": "M2",
                                "sizes": [
                                    "1.5L",
                                    "2L",
                                    "3L",
                                    "5L"
                                ]
                            }
                        ]
                    }
                ]
            },
            {
                "id": "SI1IF-SUB",
                "name": "Innerlid Pressure Cooker",
                "series": [
                    ...
                ]
            }
        ]
    },
    {
        "id": "IDLI",
        "name": "Idli / Multi Kadai",
        "sort_order": 60,
        "sub_categories": [
            ...
        ]
    },
    {
        "id": "MG",
        "name": "Mixer Grinder",
        "sort_order": 20,
        "sub_categories": [
            ...
        ]
    }
]
```

---

## Usage Examples

### Basic Request (Get all mega menu)
```bash
curl -X GET "http://localhost:8000/api/mega-menu"
```

### With Pretty Print (JSON formatting)
```bash
curl -X GET "http://localhost:8000/api/mega-menu" | json_pp
```

### Using Postman
1. Open Postman
2. Create new GET request
3. Paste URL: `http://localhost:8000/api/mega-menu`
4. Click Send

### Using JavaScript/Fetch
```javascript
fetch('http://localhost:8000/api/mega-menu')
    .then(response => response.json())
    .then(data => {
        console.log('Categories:', data);
        // data is an array of categories
        data.forEach(category => {
            console.log(`Category: ${category.name}`);
            category.sub_categories.forEach(subCat => {
                console.log(`  Sub-Category: ${subCat.name}`);
                subCat.series.forEach(material => {
                    console.log(`    Material: ${material.name}`);
                    material.options.forEach(option => {
                        console.log(`      Series: ${option.name} - Sizes: ${option.sizes.join(', ')}`);
                    });
                });
            });
        });
    })
    .catch(error => console.error('Error:', error));
```

### Using Axios
```javascript
import axios from 'axios';

axios.get('http://localhost:8000/api/mega-menu')
    .then(res => {
        console.log('Mega Menu Data:', res.data);
    })
    .catch(error => console.error('Error:', error));
```

---

## Response Structure Breakdown

| Field | Type | Description |
|-------|------|-------------|
| `id` | string | Category ID (e.g., "PC", "IDLI", "MG") |
| `name` | string | Category name (e.g., "Pressure Cooker") |
| `sort_order` | int | Display order (ascending) |
| `sub_categories` | array | Array of subcategories |
| `sub_categories[].id` | string | Subcategory ID |
| `sub_categories[].name` | string | Subcategory name |
| `sub_categories[].series` | array | Array of materials/series |
| `series[].id` | string | Material ID |
| `series[].name` | string | Material name (e.g., "Aluminium", "Stainless Steel") |
| `series[].options` | array | Array of series options |
| `options[].id` | string | Series/Option ID |
| `options[].name` | string | Series/Option name (e.g., "Prime", "Supreme") |
| `options[].series_id` | string | Reference to material ID |
| `options[].sizes` | array | Array of available sizes (e.g., ["1.5L", "2L", "3L"]) |

---

## Key Features

✅ **All Categories Included**: Pressure Cooker, Mixer Grinder, Gas Stove, Gas Tandoor, Idli/Multi Kadai, Ceiling Fan, Iron, Cookware, Rice Cooker, Combo Packs

✅ **Hierarchical Structure**: Proper nesting of categories → subcategories → materials → series → sizes

✅ **Sorted Output**: Categories ordered by `sort_order` field for consistent display

✅ **Dynamic Data**: Pulls data directly from the database tables; any updates automatically reflect in the API response

✅ **Distinct Values**: No duplicate materials, series, or sizes in the response

---

## Implementation Details

**Controller**: `app/Http/Controllers/Api/MegaMenuController.php`

**Route**: Defined in `routes/api.php` as `Route::get('/mega-menu', [MegaMenuController::class, 'index']);`

**Database Tables Used**:
- `sm_product_categories`
- `sm_subcategories`
- `sm_products_main`
- `sm_product_details`
- `sm_materials`
- `sm_series`

---

## Notes

- The API returns only categories that have at least one subcategory with data
- Sizes are sorted alphabetically and numerically when possible
- Empty sizes are excluded from the response
- The response is not paginated; all data is returned in a single response
- This API replaces the old `getMegaMenu.php` endpoint

---

## Troubleshooting

**Empty Response?**
- Ensure your database has data in `sm_product_categories` table
- Check that products are linked properly via `sm_products_main` with `category_id`
- Verify subcategories exist in `sm_subcategories`

**404 Error?**
- Ensure Laravel server is running: `php artisan serve`
- Check that the API prefix is correct (default: `/api`)
- Verify the route is registered in `routes/api.php`

**Database Connection Error?**
- Check `.env` file database credentials
- Run migrations: `php artisan migrate`
- Seed sample data if needed

---

## Sample Data

The schema includes sample data for:
- **Pressure Cooker (PC)**: Outerlid & Innerlid categories with Aluminium, Stainless Steel, Triply materials
- **Idli/Multi Kadai (IDLI)**: Multi-plate steam cookers
- **Mixer Grinder (MG)**: Grinding & blending appliances
- **Gas Tandoor (GT)**: Traditional tandoor cookers
- **Cookware (CK)**: Various cookware items
- **Other categories**: Ceiling Fan, Iron, Rice Cooker, Combo Packs

---

**Last Updated**: December 15, 2025
**API Version**: 1.0
