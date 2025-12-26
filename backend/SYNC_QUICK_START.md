# 🚀 Quick Start: Sync Local with Server

## روش سریع (Quick Method)

### استفاده از اسکریپت خودکار (Using Automated Script)

```bash
# اجرای اسکریپت
cd /home/sepehr/Projects/6ammart-laravel
./sync-local-with-server.sh
```

این اسکریپت به صورت خودکار:
- ✅ Backup می‌گیرد
- ✅ فایل‌های React را sync می‌کند
- ✅ فایل‌های Laravel تغییر یافته را sync می‌کند
- ✅ گزارش ایجاد می‌کند

---

## روش دستی (Manual Method)

### استفاده از پرامپت Cursor AI

1. فایل `SYNC_LOCAL_WITH_SERVER_PROMPT.md` را باز کنید
2. محتوای آن را به Cursor AI بدهید
3. Cursor AI به صورت مرحله‌ای کارها را انجام می‌دهد

---

## مسیرهای پروژه

- **Laravel لوکال**: `/home/sepehr/Projects/6ammart-laravel/`
- **React لوکال**: `/home/sepehr/Projects/6ammart-react/`
- **Laravel سرور**: `/var/www/6ammart-laravel/`
- **React سرور**: `/var/www/6ammart-react/`

## فایل‌های ایجاد شده

1. **`SYNC_LOCAL_WITH_SERVER_PROMPT.md`**: پرامپت کامل برای Cursor AI
2. **`sync-local-with-server.sh`**: اسکریپت خودکار اجرا

---

## نکات مهم

- ⚠️ همیشه قبل از sync، backup گرفته می‌شود
- ⚠️ فایل‌های `.env` sync نمی‌شوند (برای امنیت)
- ⚠️ `node_modules` sync نمی‌شود (باید `npm install` بزنید)
- ✅ تمام فایل‌های React source sync می‌شوند
- ✅ فایل‌های Laravel تغییر یافته sync می‌شوند

---

## بعد از Sync

```bash
# اگر package.json تغییر کرد
cd /home/sepehr/Projects/6ammart-react
npm install

# بررسی import paths
grep -r "from '/var/www\|from '/home/sepehr" src --include="*.js" --include="*.jsx"
```

---

**آماده استفاده!** 🎉

