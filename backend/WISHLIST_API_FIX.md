# حل مشکل 403 Forbidden برای Wish-List API

## مشکل

خطای `403 Forbidden` برای endpoint `/api/v1/customer/wish-list` به دو دلیل است:

### 1. Authentication Token
این endpoint نیاز به `auth:api` middleware دارد و باید token در header ارسال شود.

### 2. Zone ID Header
مطابق کد `WishlistController` (خط 75-80)، این endpoint نیاز به header `zoneId` دارد.

## راه حل

### برای Frontend (React App):

```javascript
// 1. دریافت token از login
const loginResponse = await fetch('http://193.162.129.214/api/v1/auth/login', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
  },
  body: JSON.stringify({
    login_type: 'manual',
    email_or_phone: 'test@6ammart.com',
    password: '123456',
    field_type: 'email'
  })
});

const { token } = await loginResponse.json();

// 2. استفاده از token برای wish-list
const wishListResponse = await fetch('http://193.162.129.214/api/v1/customer/wish-list', {
  method: 'GET',
  headers: {
    'Authorization': `Bearer ${token}`,
    'zoneId': '[1]', // یا zone ID واقعی
    'Content-Type': 'application/json',
  }
});

const wishList = await wishListResponse.json();
```

### Headers مورد نیاز:

1. **Authorization**: `Bearer YOUR_TOKEN_HERE`
2. **zoneId**: `[1]` یا `[1,2,3]` (array of zone IDs as JSON string)

### مثال با curl:

```bash
# 1. Login و دریافت token
TOKEN=$(curl -s http://193.162.129.214/api/v1/auth/login \
  -X POST \
  -H "Content-Type: application/json" \
  -d '{
    "login_type": "manual",
    "email_or_phone": "test@6ammart.com",
    "password": "123456",
    "field_type": "email"
  }' | python3 -c "import sys, json; print(json.load(sys.stdin)['token'])")

# 2. استفاده از token برای wish-list
curl -X GET http://193.162.129.214/api/v1/customer/wish-list \
  -H "Authorization: Bearer $TOKEN" \
  -H "zoneId: [1]" \
  -H "Content-Type: application/json"
```

## بررسی مشکل در React App

اگر React app شما هنوز خطای 403 می‌دهد، بررسی کنید:

1. **Token Storage**: آیا token در localStorage/sessionStorage ذخیره شده است؟
2. **Token Expiry**: آیا token منقضی شده است؟
3. **Header Format**: آیا header به درستی ارسال می‌شود؟
   - باید `Authorization: Bearer TOKEN` باشد (با فاصله بعد از Bearer)
4. **Zone ID**: آیا zoneId header ارسال می‌شود؟

## Debug در Browser Console

```javascript
// بررسی token
console.log('Token:', localStorage.getItem('token'));

// بررسی request headers
fetch('http://193.162.129.214/api/v1/customer/wish-list', {
  method: 'GET',
  headers: {
    'Authorization': `Bearer ${localStorage.getItem('token')}`,
    'zoneId': '[1]',
  }
})
.then(res => {
  console.log('Status:', res.status);
  console.log('Headers:', res.headers);
  return res.json();
})
.then(data => console.log('Data:', data))
.catch(err => console.error('Error:', err));
```

## نکات مهم

1. **Module Check**: Route در `module-check` middleware است، اما `api/v1/customer*` در exception list است، پس نیازی به `moduleId` header نیست.

2. **Zone ID Format**: `zoneId` باید به صورت JSON array string باشد: `"[1]"` یا `"[1,2,3]"`

3. **Token Expiry**: Token های Passport معمولاً 1 سال اعتبار دارند، اما اگر مشکل دارید دوباره login کنید.

4. **CORS**: اگر از مرورگر درخواست می‌دهید، مطمئن شوید CORS درست تنظیم شده است.

