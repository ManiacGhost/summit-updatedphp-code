# JWT Bearer Token Authentication API

This API now uses JWT (JSON Web Token) bearer token authentication instead of session-based cookies. Tokens are stored in the `remember_token` database field to avoid frequent session expirations.

## Token Configuration

- **Access Token Expiration**: 6 hours (21600 seconds)
- **Refresh Token Expiration**: 30 days (2592000 seconds)
- **Token Type**: Bearer
- **Algorithm**: HS256

## API Endpoints

### 1. Register User

**Endpoint:** `POST /api/register`

**Headers:**
```
Content-Type: application/json
```

**Request Body:**
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```

**Response (201 Created):**
```json
{
  "success": true,
  "message": "User registered successfully",
  "user": {
    "id": "USR-john-doe-123",
    "name": "John Doe",
    "email": "john@example.com"
  },
  "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
  "refresh_token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
  "expires_in": 21600,
  "token_type": "Bearer"
}
```

---

### 2. Login User

**Endpoint:** `POST /api/login`

**Headers:**
```
Content-Type: application/json
```

**Request Body:**
```json
{
  "email": "john@example.com",
  "password": "password123"
}
```

**Response (200 OK):**
```json
{
  "success": true,
  "message": "Login successful",
  "user": {
    "id": "USR-john-doe-123",
    "name": "John Doe",
    "email": "john@example.com"
  },
  "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
  "refresh_token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
  "expires_in": 21600,
  "token_type": "Bearer"
}
```

---

### 3. Get Current User (Me)

**Endpoint:** `GET /api/me`

**Headers:**
```
Content-Type: application/json
Authorization: Bearer YOUR_ACCESS_TOKEN
```

**Response (200 OK):**
```json
{
  "success": true,
  "user": {
    "id": "USR-john-doe-123",
    "name": "John Doe",
    "email": "john@example.com",
    "created_at": "2025-12-24T10:30:00Z",
    "updated_at": "2025-12-24T10:30:00Z"
  }
}
```

**Error Response (401 Unauthorized):**
```json
{
  "success": false,
  "message": "Unauthorized - Token is missing"
}
```

---

### 4. Refresh Access Token

**Endpoint:** `POST /api/refresh`

Use this endpoint when your access token is about to expire. The refresh token is stored in the `remember_token` database field.

**Headers:**
```
Content-Type: application/json
```

**Request Body:**
```json
{
  "refresh_token": "YOUR_REFRESH_TOKEN"
}
```

**Response (200 OK):**
```json
{
  "success": true,
  "message": "Token refreshed successfully",
  "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
  "refresh_token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
  "expires_in": 21600,
  "token_type": "Bearer"
}
```

**Error Response (401 Unauthorized):**
```json
{
  "success": false,
  "message": "Invalid or expired refresh token"
}
```

---

### 5. Logout User

**Endpoint:** `POST /api/logout`

**Headers:**
```
Content-Type: application/json
Authorization: Bearer YOUR_ACCESS_TOKEN
```

**Response (200 OK):**
```json
{
  "success": true,
  "message": "Logout successful"
}
```

---

## cURL Examples

### Register
```bash
curl --location 'http://127.0.0.1:8000/api/register' \
--header 'Content-Type: application/json' \
--data-raw '{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}'
```

### Login
```bash
curl --location 'http://127.0.0.1:8000/api/login' \
--header 'Content-Type: application/json' \
--data-raw '{
  "email": "john@example.com",
  "password": "password123"
}'
```

### Get Current User (with bearer token)
```bash
curl --location 'http://127.0.0.1:8000/api/me' \
--header 'Content-Type: application/json' \
--header 'Authorization: Bearer YOUR_ACCESS_TOKEN'
```

### Refresh Token
```bash
curl --location 'http://127.0.0.1:8000/api/refresh' \
--header 'Content-Type: application/json' \
--data-raw '{
  "refresh_token": "YOUR_REFRESH_TOKEN"
}'
```

### Logout
```bash
curl --location --request POST 'http://127.0.0.1:8000/api/logout' \
--header 'Content-Type: application/json' \
--header 'Authorization: Bearer YOUR_ACCESS_TOKEN'
```

---

## Frontend Implementation

### Store tokens after login/register
```javascript
const response = await fetch('http://127.0.0.1:8000/api/login', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
  },
  body: JSON.stringify({
    email: 'john@example.com',
    password: 'password123'
  })
});

const data = await response.json();

// Store tokens
localStorage.setItem('access_token', data.token);
localStorage.setItem('refresh_token', data.refresh_token);
localStorage.setItem('token_expires_in', data.expires_in);
```

### Use token in protected requests
```javascript
const accessToken = localStorage.getItem('access_token');

const response = await fetch('http://127.0.0.1:8000/api/me', {
  method: 'GET',
  headers: {
    'Content-Type': 'application/json',
    'Authorization': `Bearer ${accessToken}`
  }
});
```

### Refresh token before expiry
```javascript
async function refreshAccessToken() {
  const refreshToken = localStorage.getItem('refresh_token');
  
  const response = await fetch('http://127.0.0.1:8000/api/refresh', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({
      refresh_token: refreshToken
    })
  });

  const data = await response.json();
  
  if (data.success) {
    localStorage.setItem('access_token', data.token);
    localStorage.setItem('refresh_token', data.refresh_token);
  } else {
    // Refresh token expired, redirect to login
    window.location.href = '/login';
  }
}
```

---

## Database

- **Access Token Storage**: Generated fresh on each login/refresh
- **Refresh Token Storage**: Stored in `users.remember_token` field
- **No Session Table**: Session-based authentication is no longer used

---

## Error Responses

All error responses follow this format:

```json
{
  "success": false,
  "message": "Error description"
}
```

Common error messages:
- `"Unauthorized - Token is missing"` (401)
- `"Unauthorized - Invalid or expired token"` (401)
- `"Unauthorized - Invalid token type"` (401)
- `"Unauthorized - User not found"` (401)
- `"Invalid email or password"` (401)
- `"Validation failed"` (422)

---

## Security Notes

1. **Always use HTTPS** in production to prevent token interception
2. **Store tokens securely** in httpOnly cookies or secure storage
3. **Access tokens are short-lived** (6 hours) to minimize breach impact
4. **Refresh tokens are long-lived** (30 days) but stored server-side in database
5. **Tokens are signed** using HS256 algorithm
6. **Invalid refresh tokens** are cleared from the database on logout
