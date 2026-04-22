# Quick Reference Guide - Smart Agriculture API

## 🚀 Quick Start

### 1. Register as Engineer
```bash
POST /api/register
{
  "first_name": "Ahmed",
  "last_name": "Hassan",
  "email": "engineer@example.com",
  "phone": "01234567890",
  "password": "SecurePassword123!",
  "password_confirmation": "SecurePassword123!",
  "role": "engineer"
}
```
**Response**: Returns `token` for use in subsequent requests

### 2. Register as Farmer (under Engineer)
```bash
POST /api/register
{
  "first_name": "Mohamed",
  "last_name": "Ali",
  "email": "farmer@example.com",
  "phone": "01234567891",
  "password": "SecurePassword123!",
  "password_confirmation": "SecurePassword123!",
  "role": "farmer",
  "engineer_id": 1
}
```

---

## 📍 API Endpoints Summary

### 🌾 Farm Management (CRUD + Access Control)
| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---|
| GET | `/api/farms` | List accessible farms | ✅ |
| POST | `/api/farms` | Create farm | ✅ |
| GET | `/api/farms/{id}` | View farm details | ✅ |
| PUT | `/api/farms/{id}` | Update farm | ✅ |
| DELETE | `/api/farms/{id}` | Delete farm | ✅ |
| POST | `/api/farms/{id}/grant-access` | Share farm with user | ✅ |
| POST | `/api/farms/{id}/revoke-access` | Remove access | ✅ |
| GET | `/api/farms/{id}/access-list` | See who has access | ✅ |

### 📚 Blog System
| Method | Endpoint | Description | Requirements |
|--------|----------|-------------|---|
| GET | `/api/blogs` | View all blogs | ✅ Auth |
| POST | `/api/blogs` | Create blog | ✅ Engineer only |
| GET | `/api/blogs/{id}` | View blog | ✅ Auth |
| PUT | `/api/blogs/{id}` | Update blog | ✅ Author only |
| DELETE | `/api/blogs/{id}` | Delete blog | ✅ Author only |
| POST | `/api/blogs/{id}/comments` | Add comment | ✅ Auth |
| POST | `/api/blogs/{id}/reactions` | Like/Unlike | ✅ Auth |
| GET | `/api/blogs/{id}/comments` | Get comments | ✅ Auth |
| GET | `/api/blogs/{id}/reactions` | Get reactions | ✅ Auth |

### 💬 Chat System
| Method | Endpoint | Description | Special Logic |
|--------|----------|-------------|---|
| POST | `/api/chats/get-or-create` | Create or get chat | Farmers auto-target supervisor |
| GET | `/api/chats` | List user's chats | Participants only |
| GET | `/api/chats/{id}` | Get chat details | Participants only |
| POST | `/api/chats/{id}/send-message` | Send message | Participants only |
| POST | `/api/chats/{id}/send-message-attachment` | Send with file | Max 10MB |
| GET | `/api/chats/{id}/messages` | Get messages | Participants only |

### 👥 Staff Management (Engineers Only)
| Method | Endpoint | Description | Restriction |
|--------|----------|-------------|---|
| GET | `/api/staff` | List staff | Engineer only |
| POST | `/api/staff/assign-farm` | Assign farm to farmer | Engineer only |
| POST | `/api/staff/remove-farm` | Remove farm from farmer | Engineer only |
| GET | `/api/staff/farms` | View farms with staff | Engineer only |

---

## 🔐 Authorization Levels

### Farm Access
- **Owner**: Full access (create, read, update, delete)
- **Editor**: Can read and update only
- **Viewer**: Read-only access

### Blog Creation
- **Engineer**: Can create blogs ✅
- **Farmer**: Cannot create blogs ❌

### Chat
- **All Users**: Can initiate chats
- **Farmers**: Auto-chat with supervisor (engineer)
- **Only Participants**: Can send messages

### Staff Management
- **Engineers**: Can manage their staff and assign farms
- **Farmers**: Cannot manage staff

---

## 📋 Common Request/Response Examples

### Create a Farm
**Request:**
```bash
POST /api/farms
Authorization: Bearer {token}
Content-Type: application/json

{
  "name": "Green Fields Farm",
  "location": "Cairo, Egypt",
  "area": 100.50,
  "soil_type": "loamy"
}
```

**Success Response (201):**
```json
{
  "message": "Farm created successfully",
  "errors": {},
  "data": {
    "farm": {
      "id": 1,
      "name": "Green Fields Farm",
      "location": "Cairo, Egypt",
      "area": "100.50",
      "soil_type": "loamy",
      "user_id": 1,
      "created_at": "2026-04-17T12:00:00Z"
    }
  }
}
```

### Grant Farm Access to Farmer
**Request:**
```bash
POST /api/farms/1/grant-access
Authorization: Bearer {engineer_token}
Content-Type: application/json

{
  "user_id": 5,
  "role": "editor"
}
```

**Success Response (200):**
```json
{
  "message": "Access granted successfully",
  "errors": {},
  "data": {
    "farm": {
      "id": 1,
      "name": "Green Fields Farm",
      ...
    }
  }
}
```

### Create Blog (Engineer Only)
**Request:**
```bash
POST /api/blogs
Authorization: Bearer {engineer_token}
Content-Type: application/json

{
  "title": "Sustainable Farming Practices",
  "content": "Best practices for modern sustainable agriculture..."
}
```

**Success Response (201):**
```json
{
  "message": "Blog created successfully",
  "errors": {},
  "data": {
    "blog": {
      "id": 1,
      "title": "Sustainable Farming Practices",
      "content": "...",
      "user_id": 1,
      "created_at": "2026-04-17T12:00:00Z"
    }
  }
}
```

### Create Chat & Send Message
**Step 1 - Get or Create Chat:**
```bash
POST /api/chats/get-or-create
Authorization: Bearer {farmer_token}

{}  # Farmer auto-targets supervisor
# OR for engineer
{
  "receiver_id": 5
}
```

**Step 2 - Send Message:**
```bash
POST /api/chats/1/send-message
Authorization: Bearer {token}
Content-Type: application/json

{
  "content": "When should we irrigate the north field?"
}
```

### Farmer Gets Assigned to Farm
**Request (Engineer):**
```bash
POST /api/staff/assign-farm
Authorization: Bearer {engineer_token}
Content-Type: application/json

{
  "farmer_id": 5,
  "farm_id": 1,
  "role": "editor"
}
```

---

## ❌ Error Responses

### 401 Unauthorized
```json
{
  "message": "Unauthenticated",
  "errors": {},
  "data": {}
}
```
**Cause**: Missing or invalid bearer token

### 403 Forbidden
```json
{
  "message": "Unauthorized",
  "errors": {
    "role": ["Only engineers can create blogs"]
  },
  "data": {}
}
```
**Cause**: User lacks required permissions

### 404 Not Found
```json
{
  "message": "No query results found for model",
  "errors": {},
  "data": {}
}
```
**Cause**: Resource doesn't exist

### 422 Unprocessable Entity
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "name": ["The name field is required."],
    "area": ["The area must be at least 0.01."]
  },
  "data": {}
}
```
**Cause**: Validation failed

---

## 💡 Key Features

### 🎯 Smart Farmer Supervision
When a farmer creates a chat, it **automatically targets their supervisor** (engineer):
```php
// Farmer registers
$farmer = User::create([
  'engineer_id' => 1,  // Supervised by engineer 1
  'role' => 'farmer'
]);

// Farmer creates chat (no receiver_id needed)
POST /api/chats/get-or-create
// Automatically creates chat with engineer_id=1
```

### 🔑 Role-Based Farm Access
Engineers can assign their farmers to specific farms with different permissions:
```
Engineer 1 (Owner of Farm 1)
├── Farmer A (role: editor)  ← Can view & update
├── Farmer B (role: viewer)  ← Can only view
└── Farmer C (no access)
```

### 📱 Multi-level Pagination
- **Blogs**: 15 per page
- **Chats**: 10 per page  
- **Messages**: 25 per page
- **Comments**: 10 per page

### 📎 File Attachments
- Message attachments: 10MB max
- Image uploads: 2MB max
- Supported formats: jpeg, png, jpg, gif, webp

---

## 🧪 Testing Workflow

### 1. As Engineer
```bash
# Register
POST /api/register (role: engineer)

# Create farm
POST /api/farms

# Create blog
POST /api/blogs

# List staff
GET /api/staff

# Assign farm to farmer
POST /api/staff/assign-farm
```

### 2. As Farmer
```bash
# Register under engineer
POST /api/register (role: farmer, engineer_id: 1)

# View accessible farms
GET /api/farms

# Chat with supervisor
POST /api/chats/get-or-create

# Comment on blog
POST /api/blogs/1/comments

# React to blog
POST /api/blogs/1/reactions
```

---

## 🔗 Related Files

- **Controllers**: `app/Http/Controllers/{FarmController, BlogController, ChatController, StaffController}.php`
- **Policies**: `app/Policies/{FarmPolicy, BlogPolicy, ChatPolicy}.php`
- **Models**: `app/Models/{Farm, Blog, Chat, User}.php`
- **Routes**: `routes/api.php`
- **Complete Docs**: `API_DOCUMENTATION.md`
- **Implementation Details**: `IMPLEMENTATION_SUMMARY.md`

---

## 📞 Troubleshooting

### Issue: "Only engineers can create blogs"
**Solution**: Register with `role: engineer` in registration

### Issue: "This farmer is not under your supervision"
**Solution**: Farmer must have your `user_id` as their `engineer_id`

### Issue: "This farm is not under your supervision" (when assigning)
**Solution**: Engineer must own the farm or have access to it

### Issue: Farmer chat goes to wrong person
**Solution**: Verify farmer's `engineer_id` matches supervisor's `id`

### Issue: Cannot create chat as farmer
**Solution**: Just call `POST /api/chats/get-or-create` with empty body, it auto-targets supervisor

---

## ✅ Verification Commands

```bash
# Check all routes
php artisan route:list --path=api

# Check policies are registered
php artisan route:list | grep policy

# Test registration
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{...}'

# Get user info
curl -X GET http://localhost:8000/api/user \
  -H "Authorization: Bearer {token}"
```

---

**Last Updated**: April 17, 2026  
**API Version**: v1  
**Status**: ✅ Production Ready
