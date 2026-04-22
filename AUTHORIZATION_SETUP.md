# Authorization System - Setup & Troubleshooting Guide

**Status**: ✅ **FIXED & READY**  
**Date**: April 17, 2026

---

## 📋 What Was Fixed

### 1. ✅ Created AuthServiceProvider
**File**: `app/Providers/AuthServiceProvider.php`

```php
protected $policies = [
    Farm::class => FarmPolicy::class,
    Blog::class => BlogPolicy::class,
    Chat::class => ChatPolicy::class,
];
```

**What it does**: Registers all Policy classes so Laravel knows which policy to use for each model.

---

### 2. ✅ Updated User Model with Role Methods
**File**: `app/Models/User.php`

Added helper methods:
```php
public function isEngineer(): bool
public function isFarmer(): bool
public function hasRole(string $role): bool
public function hasAnyRole(array $roles): bool
```

**What it does**: Allows quick role checking in controllers and policies.

---

### 3. ✅ Enhanced ApiTrait
**File**: `app/Http/Traits/ApiTrait.php`

Added new method:
```php
public function unauthorizedResponse(string $message = 'This action is unauthorized.'): JsonResponse
```

**What it does**: Returns consistent 403 JSON response for authorization failures.

---

### 4. ✅ Exception Handler Configuration
**File**: `bootstrap/app.php`

```php
->withExceptions(function (Exceptions $exceptions) {
    $exceptions->render(function (AuthorizationException $e, $request) {
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([...], 403);
        }
    });
})
```

**What it does**: Automatically catches authorization exceptions and returns JSON instead of HTML error page.

---

### 5. ✅ Created Exception Handler (Optional)
**File**: `app/Exceptions/Handler.php`

Backup exception handling for custom scenarios.

---

## 🚀 How Authorization Works Now

### Flow Diagram
```
1. API Request
   ↓
2. Route Match (api.php)
   ↓
3. Controller Method Called
   ↓
4. $this->authorize('action', Model::class) ← Defined in Policy
   ↓
5. Policy Method Called (e.g., FarmPolicy::view())
   ↓
6. Policy Returns true/false
   ├─ If FALSE: AuthorizationException thrown
   │  ↓
   │  Exception Handler catches it
   │  ↓
   │  Returns JSON 403 response
   │
   └─ If TRUE: Method continues
      ↓
      Return Success Response
```

---

## 📚 Using Authorization in Controllers

### Pattern 1: Authorize Model Action
```php
public function update(Request $request, Farm $farm): JsonResponse
{
    // Checks FarmPolicy::update(User $user, Farm $farm)
    $this->authorize('update', $farm);
    
    // If you reach here, authorization passed
    $farm->update($request->validated());
    
    return $this->dataResponse(compact('farm'), 'Updated!');
}
```

### Pattern 2: Authorize Class Action
```php
public function store(Request $request): JsonResponse
{
    // Checks BlogPolicy::create(User $user)
    $this->authorize('create', Blog::class);
    
    $blog = Blog::create($request->validated());
    
    return $this->dataResponse(compact('blog'), 'Created!', 201);
}
```

### Pattern 3: Check User Role
```php
public function adminPanel(Request $request): JsonResponse
{
    if (!$request->user()->isEngineer()) {
        return $this->unauthorizedResponse('Only engineers can access this');
    }
    
    // Your logic here
}
```

### Pattern 4: Manual Check with Custom Message
```php
public function delete(Request $request, Farm $farm): JsonResponse
{
    if (!$request->user()->can('delete', $farm)) {
        return $this->unauthorizedResponse('Only the farm owner can delete');
    }
    
    $farm->delete();
    
    return $this->successResponse('Farm deleted!');
}
```

---

## 🔍 Policy Examples

### FarmPolicy (app/Policies/FarmPolicy.php)

**view()**: Check if user can view farm
```php
public function view(User $user, Farm $farm): bool
{
    // Owner always can view
    if ($farm->user_id === $user->id) {
        return true;
    }
    
    // Check if user has explicit access via pivot table
    return $farm->users()
        ->where('user_id', $user->id)
        ->exists();
}
```

**update()**: Check if user can update farm
```php
public function update(User $user, Farm $farm): bool
{
    // Owner can update
    if ($farm->user_id === $user->id) {
        return true;
    }
    
    // Users with 'editor' role can update
    return $farm->users()
        ->where('user_id', $user->id)
        ->where('role', 'editor')
        ->exists();
}
```

### BlogPolicy (app/Policies/BlogPolicy.php)

**create()**: Only engineers can create blogs
```php
public function create(User $user): bool
{
    return $user->role === 'engineer';
}
```

**update()**: Only author can update their blog
```php
public function update(User $user, Blog $blog): bool
{
    return $blog->user_id === $user->id;
}
```

---

## ✅ Verification Checklist

- [x] AuthServiceProvider created and policies registered
- [x] User model has role checking methods
- [x] ApiTrait has unauthorizedResponse() method
- [x] Exception handler catches AuthorizationException
- [x] bootstrap/app.php configured for JSON responses
- [x] All controllers use ApiTrait
- [x] Policies defined in app/Policies/

---

## 🧪 Testing Authorization

### Test 1: Engineer Can Create Blog
```bash
# Register as engineer
POST /api/register
{
  "first_name": "Ahmed",
  "last_name": "Hassan",
  "email": "engineer@test.com",
  "phone": "01234567890",
  "password": "SecurePass123!",
  "password_confirmation": "SecurePass123!",
  "role": "engineer"
}

# Create blog
POST /api/blogs
Authorization: Bearer {token}
{
  "title": "Blog Title",
  "content": "Blog content"
}

# Response: 201 Created ✅
```

### Test 2: Farmer Cannot Create Blog
```bash
# Register as farmer
POST /api/register
{
  "role": "farmer",
  "engineer_id": 1,
  ...
}

# Try to create blog
POST /api/blogs
Authorization: Bearer {farmer_token}
{
  "title": "Blog Title",
  "content": "Blog content"
}

# Response: 403 Forbidden ✅
{
  "message": "Unauthorized",
  "errors": {
    "authorization": "This action is unauthorized."
  },
  "data": {}
}
```

### Test 3: Non-Owner Cannot Update Farm
```bash
# User A owns farm 1
# User B tries to update it
PUT /api/farms/1
Authorization: Bearer {user_b_token}
{
  "name": "Hacked Farm"
}

# Response: 403 Forbidden ✅
```

### Test 4: Farmer with Editor Role Can Update Farm
```bash
# Engineer shares farm with farmer (role: editor)
POST /api/farms/1/grant-access
{
  "user_id": 5,
  "role": "editor"
}

# Farmer can now update
PUT /api/farms/1
Authorization: Bearer {farmer_token}
{
  "name": "Updated By Farmer"
}

# Response: 200 OK ✅
```

---

## 🐛 Troubleshooting

### Error: "Authorization not working"
**Solution**: Make sure AuthServiceProvider is registered in `config/app.php`
```bash
php artisan config:show app.providers | grep Auth
```

### Error: "HTML error page instead of JSON"
**Solution**: Ensure exception handler is in bootstrap/app.php
```bash
php artisan config:cache
```

### Error: "Policy method not found"
**Solution**: Check policy syntax and method exists
```bash
# View FarmPolicy
cat app/Policies/FarmPolicy.php

# Verify method names match authorize() calls
```

### Error: "Unauthenticated" on authorization check
**Solution**: Check auth middleware on route
```bash
php artisan route:list --path=api/farms
```

### Error: "isEngineer() method not found"
**Solution**: Ensure User model has the new methods
```bash
grep -n "isEngineer" app/Models/User.php
```

---

## 📋 Quick Reference

### Authorize Syntax
```php
// Model action
$this->authorize('view', $model);

// Class action
$this->authorize('create', ModelClass::class);

// Can method (returns boolean)
if ($user->can('delete', $resource)) { ... }

// Cannot method (opposite)
if ($user->cannot('delete', $resource)) { ... }
```

### User Role Methods
```php
$user->isEngineer()        // Is engineer?
$user->isFarmer()          // Is farmer?
$user->hasRole('engineer') // Has specific role?
$user->hasAnyRole(['engineer', 'admin']) // Has any of roles?
```

### Response Methods
```php
$this->dataResponse($data, $message, $code)
$this->successResponse($message, $code)
$this->errorResponse($errors, $message, $code)
$this->unauthorizedResponse($message)
```

---

## 🎯 Key Points

1. **Policies are automatically used** - Once registered in AuthServiceProvider, Laravel handles the rest
2. **JSON responses are automatic** - Exception handler catches auth failures and returns JSON
3. **Role methods make policies clean** - Use `$user->isEngineer()` instead of `$user->role === 'engineer'`
4. **Fallback to permissions method** - If `$this->authorize()` fails, exception is caught and JSON 403 returned
5. **All routes are protected** - `auth:sanctum` middleware on all API routes

---

## 📚 Related Files

- `app/Providers/AuthServiceProvider.php` - Policy registration
- `app/Models/User.php` - Role checking methods
- `app/Policies/{FarmPolicy, BlogPolicy, ChatPolicy}.php` - Authorization rules
- `app/Http/Traits/ApiTrait.php` - Response helpers
- `bootstrap/app.php` - Exception configuration
- `app/Http/Controllers/{FarmController, BlogController, ChatController, StaffController}.php` - Usage examples

---

## ✨ Summary

Your authorization system is now:
- ✅ Fully functional
- ✅ Returns JSON for all errors
- ✅ Has role checking methods
- ✅ Has police-based access control
- ✅ Has consistent error handling
- ✅ Follows Laravel best practices

**Ready for production!** 🚀
