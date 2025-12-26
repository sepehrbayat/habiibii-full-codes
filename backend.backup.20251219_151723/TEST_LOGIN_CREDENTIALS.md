# اطلاعات لاگین برای تست

## کاربر تست (Test User)

### اطلاعات کاربر:
- **Email**: `test@6ammart.com`
- **Phone**: `09123456789`
- **Password**: `123456`
- **Status**: `1` (فعال)
- **User ID**: `3`

**نکته**: کاربر با موفقیت ایجاد شده و authentication در Laravel کار می‌کند. Passport personal access client نیز تنظیم شده است.

## نحوه لاگین از طریق API

### 1. لاگین با Email:

```bash
curl -X POST http://193.162.129.214/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "login_type": "manual",
    "email_or_phone": "test@6ammart.com",
    "password": "123456",
    "field_type": "email"
  }'
```

### 2. لاگین با Phone:

```bash
curl -X POST http://193.162.129.214/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "login_type": "manual",
    "email_or_phone": "09123456789",
    "password": "123456",
    "field_type": "phone"
  }'
```

## پارامترهای API

### login_type
مقادیر معتبر:
- `manual` - لاگین با ایمیل/شماره تلفن و رمز عبور
- `otp` - لاگین با OTP (کد یکبار مصرف)
- `social` - لاگین با شبکه‌های اجتماعی (Google, Facebook, Apple)

### field_type (برای manual login)
- `email` - استفاده از ایمیل
- `phone` - استفاده از شماره تلفن

## Response موفق

در صورت موفقیت، API یک token برمی‌گرداند:

```json
{
  "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
  "is_phone_verified": 1,
  "is_email_verified": 1,
  "is_personal_info": 1,
  "is_exist_user": null,
  "login_type": "manual",
  "email": "test@6ammart.com"
}
```

## استفاده از Token

بعد از دریافت token، آن را در header درخواست‌های بعدی استفاده کنید:

```bash
curl -X GET http://193.162.129.214/api/v1/customer/profile \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

## نکات مهم

1. **Password**: رمز عبور `123456` است
2. **User Status**: کاربر باید `status = 1` باشد (فعال) - ✅ تنظیم شده
3. **Token**: Token را در header درخواست‌های بعدی استفاده کنید
4. **API Endpoint**: `/api/v1/auth/login`
5. **login_type**: باید یکی از `manual`, `otp`, یا `social` باشد
6. **field_type**: برای `manual` login باید `email` یا `phone` باشد

## تست سریع

```bash
# تست لاگین
curl -s http://193.162.129.214/api/v1/auth/login \
  -X POST \
  -H "Content-Type: application/json" \
  -d '{"login_type":"manual","email_or_phone":"test@6ammart.com","password":"123456","field_type":"email"}' \
  | python3 -m json.tool
```

