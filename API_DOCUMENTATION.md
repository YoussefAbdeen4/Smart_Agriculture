# Smart Agriculture API Documentation

## Overview
This API documentation covers the complete implementation of the Smart Agriculture Management System including Farm Management, Blog & Community System, Chat System, and Staff Management with authorization logic.

---

## Authentication
All API endpoints (except `/api/auth/*`) require:
- **Header**: `Authorization: Bearer {token}`
- **Token obtained from**: `/api/auth/register` or `/api/auth/login`

---

## 1. FARM MANAGEMENT API

### Policies
**FarmPolicy** (`app/Policies/FarmPolicy.php`)
- **Owner**: Full access (view, update, delete)
- **Engineer**: Can grant access to farmers under their supervision
- **Farmers**: Can access farms they've been granted access to
- **Access Levels**:
  - `editor`: Can view and update farm/plant data
  - `viewer`: Read-only access

### Endpoints

#### Get All Farms (Accessible to User)
```
GET /api/farms
```
**Response**: 200 OK
```json
{
  "message": "Farms retrieved successfully",
  "errors": {},
  "data": {
    "farms": [
      {
        "id": 1,
        "name": "North Farm",
        "location": "Alexandria",
        "area": "50.50",
        "soil_type": "loamy",
        "user_id": 1,
        "created_at": "2026-04-17T10:00:00Z",
        "updated_at": "2026-04-17T10:00:00Z"
      }
    ]
  }
}
```

#### Create Farm
```
POST /api/farms
Content-Type: application/json

{
  "name": "West Farm",
  "location": "Giza",
  "area": 75.25,
  "soil_type": "sandy",
  "img": null
}
```
**Response**: 201 Created

#### Get Farm Details
```
GET /api/farms/{id}
```
**Response**: 200 OK
- Returns farm with plants, plans, and shared users

#### Update Farm
```
PUT /api/farms/{id}
Content-Type: application/json

{
  "name": "Updated Farm Name",
  "area": 80.00
}
```
**Response**: 200 OK

#### Delete Farm
```
DELETE /api/farms/{id}
```
**Response**: 200 OK
- Only farm owner can delete

#### Grant Access to Farm
```
POST /api/farms/{id}/grant-access
Content-Type: application/json

{
  "user_id": 5,
  "role": "editor"
}
```
**Response**: 200 OK
- `role`: `editor` or `viewer`
- Can only be done by farm owner or engineer

#### Revoke Access from Farm
```
POST /api/farms/{id}/revoke-access
Content-Type: application/json

{
  "user_id": 5
}
```
**Response**: 200 OK

#### Get Access List for Farm
```
GET /api/farms/{id}/access-list
```
**Response**: 200 OK
```json
{
  "message": "Access list retrieved successfully",
  "data": {
    "users": [
      {
        "id": 5,
        "first_name": "Ahmed",
        "last_name": "Ali",
        "email": "ahmed@example.com",
        "role": "editor"
      }
    ]
  }
}
```

---

## 2. BLOG & COMMUNITY API

### Policies
**BlogPolicy** (`app/Policies/BlogPolicy.php`)
- **Only Engineers** can create blogs
- **Authors** can update/delete their own blogs
- **All users** can view, comment, and react on blogs

### Endpoints

#### Get All Blogs
```
GET /api/blogs?page=1
```
**Response**: 200 OK
- Paginated (15 per page)
- Includes comments and reactions

#### Create Blog (Engineers Only)
```
POST /api/blogs
Content-Type: application/json

{
  "title": "Best Farming Practices 2026",
  "content": "Article content here..."
}
```
**Response**: 201 Created
- Authorization check: `role = 'engineer'`

#### Get Blog Details
```
GET /api/blogs/{id}
```
**Response**: 200 OK
- Includes comments, reactions, and attachments

#### Update Blog
```
PUT /api/blogs/{id}
Content-Type: application/json

{
  "title": "Updated Title",
  "content": "Updated content..."
}
```
**Response**: 200 OK
- Only author can update

#### Delete Blog
```
DELETE /api/blogs/{id}
```
**Response**: 200 OK
- Only author can delete

#### Add Comment to Blog
```
POST /api/blogs/{id}/comments
Content-Type: application/json

{
  "name": "John Doe",
  "content": "Great article!"
}
```
**Response**: 201 Created
- `name` is optional (uses user's first_name if not provided)

#### Toggle Reaction (Like/Unlike)
```
POST /api/blogs/{id}/reactions
Content-Type: application/json

{
  "is_like": true
}
```
**Response**: 201 Created or 200 OK (if removed)
- Toggles reaction: same request removes if exists

#### Get Blog Comments
```
GET /api/blogs/{id}/comments?page=1
```
**Response**: 200 OK
- Paginated (10 per page)

#### Get Blog Reactions
```
GET /api/blogs/{id}/reactions
```
**Response**: 200 OK
```json
{
  "message": "Reactions retrieved successfully",
  "data": {
    "reactions": [
      {
        "id": 1,
        "is_like": true,
        "user": {
          "id": 3,
          "first_name": "Fatima",
          "last_name": "Hassan"
        }
      }
    ]
  }
}
```

---

## 3. CHAT SYSTEM API

### Policies
**ChatPolicy** (`app/Policies/ChatPolicy.php`)
- Only chat participants can view/send messages
- Farmers automatically chat with their supervisor (engineer)

### Endpoints

#### Get or Create Chat
```
POST /api/chats/get-or-create
Content-Type: application/json

{
  "receiver_id": 2
}
```
**Response**: 201 Created or 200 OK
- For farmers: `receiver_id` optional (auto-targets supervisor)
- For engineers: `receiver_id` required

```json
{
  "message": "Chat retrieved successfully",
  "data": {
    "chat": {
      "id": 1,
      "sender_id": 1,
      "receiver_id": 2,
      "sender": {...},
      "receiver": {...},
      "messages": [...]
    }
  }
}
```

#### Get All User Chats
```
GET /api/chats?page=1
```
**Response**: 200 OK
- Paginated (10 per page)
- Shows latest message for each chat

#### Get Chat Details with Messages
```
GET /api/chats/{id}
```
**Response**: 200 OK
- Includes all messages and attachments

#### Send Message
```
POST /api/chats/{id}/send-message
Content-Type: application/json

{
  "content": "Hello! I need help with irrigation planning."
}
```
**Response**: 201 Created

#### Send Message with Attachment
```
POST /api/chats/{id}/send-message-attachment
Content-Type: multipart/form-data

{
  "content": "Here's the farm report",
  "attachment": <file>
}
```
**Response**: 201 Created
- Max file size: 10MB
- Stored in: `storage/app/public/message_attachments/`

#### Get Chat Messages (Paginated)
```
GET /api/chats/{id}/messages?page=1
```
**Response**: 200 OK
- Paginated (25 per page)
- Ordered by latest first

---

## 4. STAFF MANAGEMENT API (Engineers Only)

### Endpoints

#### List Engineer's Staff
```
GET /api/staff
```
**Response**: 200 OK
- Must be engineer
- Returns all farmers under this engineer

```json
{
  "message": "Staff retrieved successfully",
  "data": {
    "staff": [
      {
        "id": 5,
        "first_name": "Ahmed",
        "last_name": "Ali",
        "email": "ahmed@example.com",
        "phone": "01234567890",
        "role": "farmer"
      }
    ]
  }
}
```

#### Assign Farm to Farmer
```
POST /api/staff/assign-farm
Content-Type: application/json

{
  "farmer_id": 5,
  "farm_id": 1,
  "role": "editor"
}
```
**Response**: 200 OK
- `role`: `editor` or `viewer`
- Farmer must be under engineer's supervision
- Engineer must own the farm or have access

#### Remove Farmer from Farm
```
POST /api/staff/remove-farm
Content-Type: application/json

{
  "farmer_id": 5,
  "farm_id": 1
}
```
**Response**: 200 OK

#### Get Farms with Assigned Staff
```
GET /api/staff/farms
```
**Response**: 200 OK
```json
{
  "message": "Farms with staff retrieved successfully",
  "data": {
    "farms": [
      {
        "id": 1,
        "name": "North Farm",
        "location": "Alexandria",
        "users": [
          {
            "id": 5,
            "first_name": "Ahmed",
            "last_name": "Ali",
            "role": "editor"
          }
        ]
      }
    ]
  }
}
```

---

## Error Responses

### Validation Error (422)
```json
{
  "message": "The name field is required.",
  "errors": {
    "name": ["The name field is required."]
  },
  "data": {}
}
```

### Authorization Error (403)
```json
{
  "message": "Unauthorized",
  "errors": {
    "role": ["Only engineers can create blogs"]
  },
  "data": {}
}
```

### Not Found Error (404)
```json
{
  "message": "Resource not found.",
  "errors": {},
  "data": {}
}
```

### Success Response
```json
{
  "message": "Farm created successfully",
  "errors": {},
  "data": {...}
}
```

---

## Data Models & Relationships

### Farm Model
```php
Farm
├── user() -> User (owner)
├── plants() -> Plant (has many)
├── plans() -> Plan (has many)
└── users() -> User (many-to-many via farm_user with role)
```

### Blog Model
```php
Blog
├── user() -> User (author)
├── comments() -> Comment (has many)
├── reactions() -> React (has many)
└── attachments() -> AttachmentBlog (has many)
```

### Chat Model
```php
Chat
├── sender() -> User
├── receiver() -> User
└── messages() -> Message (has many)
```

### User Model
```php
User
├── supervisor() -> User (engineer)
├── staff() -> User (farmers under this engineer)
├── farms() -> Farm (owned farms)
├── sentChats() -> Chat
├── receivedChats() -> Chat
├── messages() -> Message
├── blogs() -> Blog
└── ...
```

---

## Best Practices

1. **Always include `Authorization` header** for protected endpoints
2. **Use pagination** for list endpoints with `?page=N`
3. **Check response status code** - 201 for creation, 200 for success, 422 for validation errors
4. **Handle errors gracefully** - Check `errors` object for issues
5. **Eager load relationships** - All controllers use `with()` for optimization
6. **Respect policies** - Authorization violations return 403

---

## Security Notes

- All routes require `auth:sanctum` middleware
- Farm access is controlled via policies and pivot table
- Only engineers can create blogs
- Chat is restricted to participants
- Staff management only for engineers with their own staff
- File uploads limited to 10MB for messages, 2MB for images

---

## Testing with cURL

### Register
```bash
curl -X POST http://localhost:8000/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "first_name": "Ahmed",
    "last_name": "Ali",
    "email": "ahmed@example.com",
    "phone": "01234567890",
    "password": "password",
    "password_confirmation": "password",
    "role": "farmer",
    "engineer_id": null
  }'
```

### Create Farm
```bash
curl -X POST http://localhost:8000/api/farms \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "My Farm",
    "location": "Cairo",
    "area": 50,
    "soil_type": "loamy"
  }'
```

### Add Comment
```bash
curl -X POST http://localhost:8000/api/blogs/1/comments \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "content": "Great article!"
  }'
```
