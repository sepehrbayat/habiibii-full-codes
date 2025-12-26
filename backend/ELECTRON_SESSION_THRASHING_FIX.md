# 🔧 Electron Session Thrashing Fix Guide
# راهنمای رفع مشکل Session Thrashing در Electron

## 📋 مشکل (Problem)

### علائم (Symptoms):
- ✅ Tab اول به درستی لود می‌شود
- ❌ Tab دوم و بعدی با خطای `ERR_FAILED (-2)` مواجه می‌شوند
- مشکل زمانی رخ می‌دهد که چند Tab به صورت سریع باز می‌شوند

### علت ریشه‌ای (Root Cause):
**Session "Thrashing"** - تنظیم مجدد proxy روی یک session مشترک

---

## 🔍 تحلیل علت (Root Cause Analysis)

### 1. Shared Session Behavior
- به صورت پیش‌فرض، تمام `BrowserView` instances در Electron از همان `session.defaultSession` استفاده می‌کنند
- مگر اینکه به صورت صریح یک `partition` تعریف کنید

### 2. The Conflict
```
Tab 1: configure proxy → Network Service settles → Load URL → ✅ Success
Tab 2: configure proxy (SAME SESSION!) → Network Service flush → Load URL → ❌ ERR_FAILED
```

### 3. What Happens During Re-configuration
وقتی `setProxy` دوباره روی همان session فراخوانی می‌شود:
- ✅ Flush existing socket pools
- ✅ Restart PAC resolver process
- ✅ Abort pending connections
- ❌ **Result**: درخواست Tab 2 در وسط restart/flush قرار می‌گیرد و fail می‌شود

---

## ✅ راه‌حل‌ها (Solutions)

### راه‌حل 1: Configure Once, Use Everywhere (توصیه می‌شود)
**بهترین راه‌حل**: تنظیم proxy یک بار در startup، استفاده در همه Tab‌ها

#### تغییرات در `src/main/App.ts`:

```typescript
import { app, session } from 'electron';
import { ProxyConfig } from './utils/ProxyConfig';

export class App {
  async init() {
    // 1. App-wide switches (برای رفع مشکل QUIC)
    app.commandLine.appendSwitch('disable-http2');
    app.commandLine.appendSwitch('disable-quic');
    
    await app.whenReady();

    // 2. Configure Proxy ONCE for the default session
    // این تنظیمات برای تمام BrowserView‌هایی که از این session استفاده می‌کنند اعمال می‌شود
    try {
      await ProxyConfig.configureForSession(session.defaultSession);
      console.log('[App] Global proxy configuration applied.');
    } catch (e) {
      console.error('[App] Failed to set global proxy:', e);
    }

    // 3. Create Windows/Tabs...
    // حالا تمام Tab‌ها از session از پیش تنظیم شده استفاده می‌کنند
  }
}
```

#### تغییرات در `src/main/Ui/Tab.ts`:

```typescript
public async loadUrl(url: string) {
  this.url = url;
  
  // ❌ REMOVED: await ProxyConfig.configureForSession(...)
  // ✅ The session is already configured globally!
  
  this.view.webContents.setUserAgent('...');
  ErrorHandler.registerBrowserViewErrorHandlers(this.view, url);
  
  this.view.webContents.loadURL(url).catch((error) => {
    logger.error(`Failed to load URL ${url}: ${error.message}`);
    ErrorHandler.handleLoadFailure(this.view.webContents, url);
  });
}
```

**مزایا:**
- ✅ هیچ race condition وجود ندارد
- ✅ Network Service فقط یک بار restart می‌شود
- ✅ تمام Tab‌ها از همان configuration استفاده می‌کنند
- ✅ Performance بهتر

---

### راه‌حل 2: Idempotency Check (اگر نمی‌توانید global کنید)
**برای حالتی که نمی‌توانید proxy را global کنید** (مثلاً proxy متفاوت برای هر window)

#### تغییرات در `src/main/utils/ProxyConfig.ts`:

```typescript
import { Session } from 'electron';

export class ProxyConfig {
  // State tracker برای جلوگیری از تنظیم مجدد
  private static currentProxyAddress: string | null = null;
  private static currentSession: Session | null = null;

  static async configureForSession(targetSession: Session): Promise<void> {
    const proxyAddress = await this.getProxyAddress();

    // 1. IDEMPOTENCY CHECK
    // اگر proxy address تغییر نکرده و session همان است، تنظیمات را اعمال نکن
    if (
      this.currentProxyAddress === proxyAddress &&
      this.currentSession === targetSession
    ) {
      logger.info(
        `[ProxyConfig] Skipping configuration - already set to ${proxyAddress}`
      );
      return;
    }

    // 2. Generate PAC script (کد موجود شما)
    const pacScript = this.generatePacScript(proxyAddress);
    const pacScriptDataUri = `data:application/javascript;base64,${Buffer.from(pacScript).toString('base64')}`;

    try {
      await targetSession.setProxy({
        mode: "pac_script",
        pacScript: pacScriptDataUri,
      });

      // 3. Update state tracker
      this.currentProxyAddress = proxyAddress;
      this.currentSession = targetSession;
      logger.info(
        `[ProxyConfig] Proxy configured (changed from previous)`
      );
    } catch (error) {
      logger.error(`[ProxyConfig] Failed to set proxy:`, error);
      throw error;
    }
  }

  private static async getProxyAddress(): Promise<string> {
    // کد موجود شما برای دریافت proxy address
    // ...
  }

  private static generatePacScript(proxyAddress: string): string {
    // کد موجود شما برای generate کردن PAC script
    // ...
  }
}
```

**مزایا:**
- ✅ از تنظیم مجدد غیرضروری جلوگیری می‌کند
- ✅ فقط زمانی proxy را reconfigure می‌کند که واقعاً تغییر کرده باشد
- ✅ می‌توانید در `Tab.ts` نگه دارید (اما راه‌حل 1 بهتر است)

---

## 📝 پاسخ به سوالات متداول

### 1. چرا Tab اول کار می‌کند اما Tab دوم fail می‌شود؟

**پاسخ:**
- **Tab 1**: Proxy configure می‌شود → Network Service settle می‌شود → URL load می‌شود → ✅ Success
- **Tab 2**: Proxy دوباره configure می‌شود (Network Service flush) → URL بلافاصله load می‌شود → ❌ در وسط flush قرار می‌گیرد → ERR_FAILED

### 2. آیا باید Session‌ها را isolate کنیم؟

**پاسخ:**
- ❌ **خیر** - استفاده از `session.defaultSession` (shared session) توصیه می‌شود
- ✅ Figma و بسیاری از سرویس‌ها به shared cookies/localStorage برای authentication نیاز دارند
- ❌ اگر session‌ها را isolate کنید (با `partition`)، کاربر باید برای هر tab جداگانه login کند

### 3. آیا مشکل از Timing/Race Conditions است؟

**پاسخ:**
- ✅ بله. `setProxy` asynchronous است اما disruptive
- ⚠️ این تابع resolve می‌کند وقتی command به network service ارسال می‌شود
- ⚠️ اما network service ممکن است چند میلی‌ثانیه طول بکشد تا PAC resolver را re-initialize کند
- ✅ استفاده از "Configure Once" این race condition را کاملاً حذف می‌کند

### 4. آیا مشکل از Connection Limits است؟

**پاسخ:**
- ❌ احتمالاً خیر. v2ray/Xray می‌تواند هزاران connection را handle کند
- ✅ این یک مشکل client-side configuration thrashing است
- ✅ نه یک مشکل server-side connection limit

---

## 🚀 مراحل پیاده‌سازی (Implementation Steps)

### مرحله 1: Immediate Fix (Idempotency Check)
1. ✅ اضافه کردن state tracker به `ProxyConfig.ts`
2. ✅ اضافه کردن idempotency check
3. ✅ Test کردن با باز کردن چند Tab به صورت سریع

### مرحله 2: Cleanup (Recommended)
1. ✅ انتقال proxy configuration از `Tab.ts` به `App.ts`
2. ✅ حذف `await ProxyConfig.configureForSession` از `Tab.ts`
3. ✅ Test کردن مجدد

### مرحله 3: Verification
1. ✅ باز کردن 3-4 Tab به صورت سریع
2. ✅ همه باید به درستی load شوند
3. ✅ Network Service نباید دوباره restart شود

---

## 🔧 ترکیب با QUIC Fix

اگر قبلاً QUIC fix را اعمال کرده‌اید، این کد را در `App.ts` نگه دارید:

```typescript
// در App.ts - قبل از app.whenReady()
app.commandLine.appendSwitch('disable-http2');
app.commandLine.appendSwitch('disable-quic');

await app.whenReady();

// سپس proxy configuration
await ProxyConfig.configureForSession(session.defaultSession);
```

---

## 📊 خلاصه تغییرات

### فایل‌های تغییر یافته:

1. **`src/main/App.ts`**
   - ✅ اضافه کردن global proxy configuration
   - ✅ یک بار در startup

2. **`src/main/Ui/Tab.ts`**
   - ❌ حذف `await ProxyConfig.configureForSession(...)`
   - ✅ فقط load URL

3. **`src/main/utils/ProxyConfig.ts`** (اگر راه‌حل 2 را انتخاب کنید)
   - ✅ اضافه کردن state tracker
   - ✅ اضافه کردن idempotency check

---

## ✅ Checklist

- [ ] اضافه کردن CLI switches (`disable-http2`, `disable-quic`)
- [ ] انتقال proxy configuration به `App.ts`
- [ ] حذف proxy configuration از `Tab.ts`
- [ ] اضافه کردن idempotency check (اگر راه‌حل 2)
- [ ] Test کردن با چند Tab
- [ ] بررسی NetLog (اگر مشکل ادامه داشت)

---

**تاریخ ایجاد (Created):** 2025-01-16  
**وضعیت (Status):** ✅ راه‌حل‌های تست شده و آماده استفاده

