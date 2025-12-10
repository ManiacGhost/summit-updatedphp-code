/**
 * Universal API Client for Summit Home Appliances
 * 
 * This client handles all communication with the backend API on 127.0.0.1:8000
 * Use this across all frontend components for consistency
 */

const API_BASE_URL = 'http://127.0.0.1:8000/api';

/**
 * Generic fetch wrapper with error handling
 */
async function fetchAPI(endpoint, options = {}) {
  try {
    const response = await fetch(`${API_BASE_URL}${endpoint}`, {
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        ...options.headers,
      },
      ...options,
    });

    if (!response.ok) {
      throw new Error(`API Error: ${response.status} ${response.statusText}`);
    }

    return await response.json();
  } catch (error) {
    console.error('API Request Error:', error);
    throw error;
  }
}

/**
 * Build query string from filter parameters
 */
function buildQueryString(params) {
  return Object.entries(params)
    .filter(([, value]) => value !== null && value !== undefined && value !== '')
    .map(([key, value]) => {
      if (Array.isArray(value)) {
        return `${key}=${value.join(',')}`;
      }
      return `${key}=${encodeURIComponent(value)}`;
    })
    .join('&');
}

// ===========================
// PRODUCTS API
// ===========================

/**
 * Fetch products with filters
 * 
 * @param {Object} filters - Filter options
 * @param {number} filters.page - Page number (default: 1)
 * @param {number} filters.per_page - Items per page (default: 10)
 * @param {number|string} filters.category_id - Category ID or comma-separated IDs
 * @param {number} filters.min_price - Minimum price
 * @param {number} filters.max_price - Maximum price
 * @param {string} filters.search - Search term
 * @param {string} filters.sort - Sort by: name, price, popularity, newest (default: newest)
 * @param {string} filters.order - Order: asc or desc (default: desc for newest, asc for others)
 * 
 * @returns {Promise<Object>} Products with pagination info
 * 
 * @example
 * // Get pressure cookers between 1000-5000 price
 * const products = await getProducts({
 *   category_id: 1,
 *   min_price: 1000,
 *   max_price: 5000,
 *   sort: 'price',
 *   order: 'asc',
 *   per_page: 12
 * });
 */
export async function getProducts(filters = {}) {
  const queryString = buildQueryString({
    page: filters.page || 1,
    per_page: filters.per_page || 10,
    category_id: filters.category_id,
    min_price: filters.min_price,
    max_price: filters.max_price,
    search: filters.search,
    sort: filters.sort || 'newest',
    order: filters.order || 'desc',
  });

  const endpoint = `/products${queryString ? '?' + queryString : ''}`;
  return fetchAPI(endpoint);
}

/**
 * Fetch single product by slug
 * 
 * @param {string} slug - Product slug
 * @returns {Promise<Object>} Product details with variants and related products
 */
export async function getProductBySlug(slug) {
  return fetchAPI(`/products/${slug}`);
}

// ===========================
// CATEGORIES API
// ===========================

/**
 * Fetch all categories
 * 
 * @returns {Promise<Array>} List of categories
 */
export async function getCategories() {
  return fetchAPI('/categories');
}

/**
 * Fetch single category by slug
 * 
 * @param {string} slug - Category slug
 * @returns {Promise<Object>} Category details
 */
export async function getCategoryBySlug(slug) {
  return fetchAPI(`/categories/${slug}`);
}

// ===========================
// CART API
// ===========================

/**
 * Fetch current cart
 * 
 * @returns {Promise<Object>} Cart items
 */
export async function getCart() {
  return fetchAPI('/cart');
}

/**
 * Add item to cart
 * 
 * @param {Object} item - Item to add
 * @param {number} item.variant_id - Product variant ID
 * @param {number} item.quantity - Quantity
 * @returns {Promise<Object>} Updated cart
 */
export async function addToCart(item) {
  return fetchAPI('/cart/add', {
    method: 'POST',
    body: JSON.stringify(item),
  });
}

/**
 * Remove item from cart
 * 
 * @param {number} itemId - Cart item ID
 * @returns {Promise<Object>} Updated cart
 */
export async function removeFromCart(itemId) {
  return fetchAPI(`/cart/remove/${itemId}`, {
    method: 'GET',
  });
}

/**
 * Update cart item quantity
 * 
 * @param {number} itemId - Cart item ID
 * @param {number} quantity - New quantity
 * @returns {Promise<Object>} Updated cart
 */
export async function updateCartQuantity(itemId, quantity) {
  return fetchAPI(`/cart/update/${itemId}`, {
    method: 'POST',
    body: JSON.stringify({ quantity }),
  });
}

/**
 * Clear entire cart
 * 
 * @returns {Promise<Object>} Empty cart response
 */
export async function clearCart() {
  return fetchAPI('/cart/clear', {
    method: 'POST',
  });
}

// ===========================
// VARIANTS API
// ===========================

/**
 * Fetch product variants by product ID
 * 
 * @param {number} productId - Product ID
 * @returns {Promise<Array>} Product variants
 */
export async function getVariants(productId) {
  return fetchAPI(`/variants/${productId}`);
}

export default {
  getProducts,
  getProductBySlug,
  getCategories,
  getCategoryBySlug,
  getCart,
  addToCart,
  removeFromCart,
  updateCartQuantity,
  clearCart,
  getVariants,
};
