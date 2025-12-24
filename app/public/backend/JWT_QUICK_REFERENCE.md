# JWT Authentication Quick Reference

## Installation Complete ✅

- ✅ Firebase JWT package installed
- ✅ JWT Service class created
- ✅ Auth config updated
- ✅ AuthController updated with JWT
- ✅ JWT Middleware created
- ✅ API routes updated
- ✅ Environment variables configured

## Key Changes

### 1. Database Field
- **Tokens stored in**: `users.remember_token` field
- **Access token expiry**: 6 hours (21600 seconds)
- **Refresh token expiry**: 30 days

### 2. Files Created/Modified
- **Created**: `app/Services/JwtService.php` - JWT token generation and validation
- **Created**: `app/Http/Middleware/JwtMiddleware.php` - JWT verification middleware
- **Modified**: `app/Http/Controllers/Api/AuthController.php` - Updated to use JWT
- **Modified**: `config/auth.php` - Added JWT guard
- **Modified**: `bootstrap/app.php` - Registered JWT middleware
- **Modified**: `routes/api.php` - Updated auth routes
- **Modified**: `.env` - Added JWT configuration
- **Created**: `JWT_AUTH_API.md` - Full API documentation

### 3. New Endpoints
- `POST /api/register` - Register and get tokens
- `POST /api/login` - Login and get tokens
- `POST /api/refresh` - Refresh access token
- `POST /api/logout` - Logout and clear refresh token
- `GET /api/me` - Get current user (with bearer token)

### 4. Token Structure
```
Header Authorization: Bearer YOUR_ACCESS_TOKEN
```

## Quick Test

### 1. Register
```bash
curl --location 'http://127.0.0.1:8000/api/register' \
--header 'Content-Type: application/json' \
--data '{
  "name": "harsh1",
  "email": "harsh1@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}'
```

Response will include:
- `token` - Access token (6 hour expiry)
- `refresh_token` - Refresh token (30 day expiry)
- `expires_in` - 21600 seconds

### 2. Login
```bash
curl --location 'http://127.0.0.1:8000/api/login' \
--header 'Content-Type: application/json' \
--data '{
  "email": "harsh1@example.com",
  "password": "password123"
}'
```

### 3. Use Token
```bash
curl --location 'http://127.0.0.1:8000/api/me' \
--header 'Content-Type: application/json' \
--header 'Authorization: Bearer YOUR_ACCESS_TOKEN'
```

Replace `YOUR_ACCESS_TOKEN` with the token from login/register response.

### 4. Refresh Token
```bash
curl --location 'http://127.0.0.1:8000/api/refresh' \
--header 'Content-Type: application/json' \
--data '{
  "refresh_token": "YOUR_REFRESH_TOKEN"
}'
```

### 5. Logout
```bash
curl --location --request POST 'http://127.0.0.1:8000/api/logout' \
--header 'Content-Type: application/json' \
--header 'Authorization: Bearer YOUR_ACCESS_TOKEN'
```

## Token Flow Diagram

```
1. Register/Login
   ↓
   Returns: access_token + refresh_token
   
2. Store tokens
   ↓
   access_token → localStorage/memory
   refresh_token → localStorage (refreshes in DB)
   
3. Use access_token for API requests
   ↓
   Authorization: Bearer {access_token}
   
4. When access_token expires (6 hours)
   ↓
   Use refresh_token to get new access_token
   ↓
   POST /api/refresh with refresh_token
   
5. Continue using new access_token
   ↓
   (refresh_token remains valid for 30 days)
   
6. On Logout
   ↓
   refresh_token cleared from database
   ↓
   Client deletes tokens from storage
```

## Frontend Example

```javascript
// Store tokens after login
async function login(email, password) {
  const res = await fetch('/api/login', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ email, password })
  });
  const data = await res.json();
  localStorage.setItem('access_token', data.token);
  localStorage.setItem('refresh_token', data.refresh_token);
  return data;
}

// Use token in requests
async function apiCall(url, options = {}) {
  const token = localStorage.getItem('access_token');
  const res = await fetch(url, {
    ...options,
    headers: {
      ...options.headers,
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json'
    }
  });
  
  // If 401, try to refresh token
  if (res.status === 401) {
    await refreshToken();
    return apiCall(url, options); // Retry
  }
  return res;
}

// Refresh token
async function refreshToken() {
  const refreshToken = localStorage.getItem('refresh_token');
  const res = await fetch('/api/refresh', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ refresh_token: refreshToken })
  });
  const data = await res.json();
  localStorage.setItem('access_token', data.token);
  localStorage.setItem('refresh_token', data.refresh_token);
}

// Logout
async function logout() {
  const token = localStorage.getItem('access_token');
  await fetch('/api/logout', {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json'
    }
  });
  localStorage.removeItem('access_token');
  localStorage.removeItem('refresh_token');
}
```

## Configuration

Edit `.env` to change token expiry times:

```env
# 6 hours in seconds (default)
JWT_ACCESS_TOKEN_EXPIRY=21600

# 30 days in seconds (default)
JWT_REFRESH_TOKEN_EXPIRY=2592000
```

Then update in `app/Services/JwtService.php`:
```php
private int $accessTokenExpiry = 21600;  // Change this
private int $refreshTokenExpiry = 2592000;  // Or this
```

## Troubleshooting

### "Unauthorized - Token is missing"
- Make sure to include the `Authorization: Bearer {token}` header

### "Invalid or expired token"
- Token has expired, use refresh endpoint to get new token
- Or the token signature is invalid (use correct token)

### "Invalid token type"
- Using refresh token as access token
- Access tokens for API calls, refresh tokens for `/api/refresh` only

## Benefits

✅ No session management needed  
✅ Stateless authentication (scalable)  
✅ Tokens stored in database (`remember_token` field)  
✅ No XSRF/CSRF token complexity  
✅ Works perfectly with mobile apps  
✅ 6-hour access token prevents long-lived breaches  
✅ 30-day refresh token for convenience  
✅ Can validate tokens on any server  
✅ No cookie/session state issues  
