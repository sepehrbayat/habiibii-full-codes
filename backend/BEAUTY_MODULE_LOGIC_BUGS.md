# Beauty Booking Module - Logic Bugs Report
# گزارش باگ‌های منطق ماژول رزرو زیبایی

**Generated:** 2025-01-24  
**Module:** BeautyBooking  
**Severity Levels:** Critical, High, Medium, Low

---

## 🔴 Critical Bugs

### 1. Revenue Recording Race Condition - Duplicate Transaction Risk

**Location:** `Modules/BeautyBooking/Services/BeautyBookingService.php`

**Issue:** Revenue transactions can be recorded twice when both `updatePaymentStatus()` and `updateBookingStatus()` are called with conditions that trigger revenue recording.

**Affected Methods:**
- `updatePaymentStatus()` (lines 1277-1346)
- `updateBookingStatus()` (lines 1357-1466)

**Problem:**
Both methods check if commission was already recorded, but there's a race condition:
- If payment status changes to 'paid' when booking is 'confirmed', revenue is recorded
- If booking status changes to 'confirmed' when payment is 'paid', revenue is recorded
- If both conditions are met simultaneously (e.g., concurrent API calls), revenue can be recorded twice

**Code References:**
```1285:1335:Modules/BeautyBooking/Services/BeautyBookingService.php
if ($paymentStatus === 'paid' && $oldPaymentStatus !== 'paid' && $booking->status === 'confirmed') {
    // Check if commission was already recorded to avoid duplicates
    $existingCommission = \Modules\BeautyBooking\Entities\BeautyTransaction::where('booking_id', $booking->id)
        ->where('transaction_type', 'commission')
        ->exists();
    
    if (!$existingCommission) {
        // Record revenue...
    }
}
```

And separately:
```1375:1427:Modules/BeautyBooking/Services/BeautyBookingService.php
if ($status === 'confirmed' && $booking->payment_status === 'paid') {
    // Check if commission was already recorded to avoid duplicates
    $existingCommission = \Modules\BeautyBooking\Entities\BeautyTransaction::where('booking_id', $booking->id)
        ->where('transaction_type', 'commission')
        ->exists();
    
    if (!$existingCommission) {
        // Record revenue...
    }
}
```

**Impact:** 
- Financial data integrity issues
- Incorrect revenue reporting
- Commission calculated multiple times

**Fix Required:**
1. Use database transactions with row-level locks
2. Use a single consolidated method for revenue recording
3. Add unique constraint check at database level (already exists but need to handle it better)

---

### 2. Consultation Credit Double Application Risk

**Location:** `Modules/BeautyBooking/Services/BeautyBookingService.php`

**Issue:** Consultation credits can potentially be applied multiple times due to race conditions in `markConsultationCreditApplied()`.

**Affected Method:** `markConsultationCreditApplied()` (lines 538-662)

**Problem:**
- The method uses `lockForUpdate()` but re-fetches records multiple times
- When `main_service_id` is null, it re-fetches and updates, but if multiple bookings try to link to the same consultation simultaneously, credits might be applied multiple times
- The `remainingCredit` calculation might not be accurate across concurrent requests

**Code Reference:**
```583:641:Modules/BeautyBooking/Services/BeautyBookingService.php
foreach ($consultationBookings as $consultationBooking) {
    if ($remainingCredit <= 0) {
        break;
    }
    
    // ... complex update logic with re-fetching ...
}
```

**Impact:**
- Financial loss - customers get more credit than they should
- Consultation credits applied multiple times to different bookings

**Fix Required:**
1. Wrap entire credit application in a single transaction
2. Use database-level atomic updates
3. Add unique constraint or use advisory locks

---

## 🟠 High Priority Bugs

### 3. Overlapping Booking Check Logic When Staff is Null

**Location:** `Modules/BeautyBooking/Services/BeautyBookingService.php` and `BeautyCalendarService.php`

**Issue:** When `staff_id` is null, the code checks ALL bookings (with and without staff), which might be too restrictive.

**Problem:**
If a salon has multiple staff members and two unassigned bookings are made, they conflict with each other even though different staff members could handle them. The logic assumes "an unassigned slot can be fulfilled by any available staff member" but doesn't check if multiple staff are available.

**Code Reference:**
```186:193:Modules/BeautyBooking/Services/BeautyBookingService.php
// If staff_id is NOT provided, check ALL bookings (both with and without staff)
// This prevents double-booking: an unassigned booking conflicts with ANY existing booking
// because an unassigned slot can be fulfilled by any available staff member
if (isset($bookingData['staff_id']) && $bookingData['staff_id'] !== null) {
    $overlappingBooking->where('staff_id', $bookingData['staff_id']);
}
// When staff_id is null, no filter is applied - all bookings are checked
```

**Impact:**
- Unnecessary booking rejections
- Lost revenue opportunities
- Poor user experience

**Fix Required:**
1. Check if multiple staff members are available
2. Only reject if no staff capacity remains
3. Consider staff working hours and availability

---

### 4. Cancellation Fee Calculation Inconsistency

**Location:** `Modules/BeautyBooking/Services/BeautyBookingService.php`

**Issue:** The cancellation fee calculation in the service method uses config-based thresholds, but the model method uses hardcoded values.

**Problem:**
- Service method (`calculateCancellationFee()` at line 1184) uses config with defaults: 24h (no fee), 2h (partial fee threshold)
- Model method (`calculateCancellationFee()` in BeautyBooking.php at line 310) uses hardcoded: 24h, 2h
- If config is changed, model method won't reflect the change
- Both methods can return different results for the same booking

**Code Comparison:**

Service method:
```1237:1266:Modules/BeautyBooking/Services/BeautyBookingService.php
$noFeeHours = $timeThresholds['no_fee_hours'] ?? config('beautybooking.cancellation_fee.time_thresholds.no_fee_hours', 24);
$partialFeeHours = $timeThresholds['partial_fee_hours'] ?? config('beautybooking.cancellation_fee.time_thresholds.partial_fee_hours', 2);
```

Model method:
```337:349:Modules/BeautyBooking/Entities/BeautyBooking.php
if ($hoursUntilBooking < 2) {
    return $this->total_amount; // 100% fee
} elseif ($hoursUntilBooking < 24) {
    return $this->total_amount * 0.5; // 50% fee
} else {
    return 0.0; // No fee
}
```

**Impact:**
- Inconsistent cancellation fees
- Confusion for users
- Different results depending on which method is called

**Fix Required:**
1. Consolidate cancellation fee logic to use config consistently
2. Have model method call service method or vice versa
3. Use single source of truth for cancellation rules

---

### 5. Badge Calculation Rating Threshold

**Location:** `Modules/BeautyBooking/Services/BeautyBadgeService.php`

**Issue:** The Top Rated badge check uses strict greater than (`>`) for rating comparison, which means a salon with exactly 4.8 rating won't qualify.

**Problem:**
- Config default is `min_rating => 4.8`
- Code uses `$salon->avg_rating > $minRating` (strict greater than)
- A salon with exactly 4.8 rating won't get the badge
- The requirement says "بالاتر از ۴.۸" which could be interpreted as >= 4.8

**Code Reference:**
```45:45:Modules/BeautyBooking/Services/BeautyBadgeService.php
$hasMinRating = $salon->avg_rating > $minRating;
```

**Impact:**
- Salons with exactly 4.8 rating won't receive Top Rated badge
- Unfair exclusion of qualifying salons
- User confusion

**Fix Required:**
1. Clarify requirement: should it be `>=` or `>`
2. Update code to match requirement
3. Consider using `>=` for inclusive threshold

---

## 🟡 Medium Priority Bugs

### 6. Booking Date/Time Null Handling Inconsistency

**Location:** Multiple locations

**Issue:** Some places check for null `booking_date_time`, others don't handle it consistently.

**Problem:**
- `booking_date_time` is nullable in the database
- Some methods check for it, others assume it exists
- Inconsistent error handling when date/time is missing

**Impact:**
- Potential null pointer exceptions
- Inconsistent error messages
- Unclear validation failures

**Fix Required:**
1. Add consistent null checks throughout
2. Use the `getParsedBookingDateTime()` method from model consistently
3. Add validation at booking creation to ensure `booking_date_time` is always set

---

### 7. Package Usage Tracking Race Condition

**Location:** `Modules/BeautyBooking/Services/BeautyBookingService.php`

**Issue:** The `trackPackageUsage()` method has race condition protection, but the session number calculation might still have edge cases.

**Problem:**
- Method uses locks and transactions correctly
- However, if multiple bookings complete simultaneously for the same package, session numbers could potentially conflict
- The check for existing usage happens after locking, which is good, but the session number increment happens after getting the last usage, which could race

**Code Reference:**
```1547:1570:Modules/BeautyBooking/Services/BeautyBookingService.php
$lastUsage = BeautyPackageUsage::lockForUpdate()
    ->where('package_id', $package->id)
    ->where('user_id', $booking->user_id)
    ->where('status', 'used')
    ->orderByDesc('session_number')
    ->first();

$nextSessionNumber = $lastUsage ? $lastUsage->session_number + 1 : 1;
```

**Impact:**
- Potential duplicate session numbers
- Package usage tracking errors
- Booking completion failures

**Fix Required:**
1. Use database auto-increment or sequence for session numbers
2. Add unique constraint on (package_id, user_id, session_number)
3. Handle duplicate key errors gracefully

---

### 8. Availability Check Error Handling

**Location:** `Modules/BeautyBooking/Services/BeautyCalendarService.php`

**Issue:** The `isTimeSlotAvailable()` method catches all exceptions and returns false, which might hide important errors.

**Problem:**
- Database connection errors are re-thrown (good)
- But other exceptions return false silently
- This makes debugging difficult
- Could hide configuration errors or data issues

**Code Reference:**
```98:111:Modules/BeautyBooking/Services/BeautyCalendarService.php
} catch (\Exception $e) {
    // Expected business logic errors (invalid input, validation failures, etc.)
    \Log::warning('Time slot availability check failed (expected error): ' . $e->getMessage(), [
        'salon_id' => $salonId,
        'staff_id' => $staffId,
        'date' => $date,
        'time' => $time,
        'exception_type' => get_class($e),
        'severity' => 'warning',
    ]);
    // Return false for expected business logic errors
    return false;
}
```

**Impact:**
- Difficult to debug availability issues
- Silent failures
- Masked configuration problems

**Fix Required:**
1. Distinguish between expected and unexpected errors
2. Log unexpected errors at error level
3. Consider re-throwing unexpected errors

---

## 🟢 Low Priority Bugs / Code Quality Issues

### 9. Ranking Score Cache Invalidation

**Location:** `Modules/BeautyBooking/Services/BeautyRankingService.php`

**Issue:** Cache invalidation only works with Redis. Other cache drivers won't invalidate properly.

**Problem:**
- `invalidateSalonRankingCache()` uses Redis-specific pattern matching
- Other cache drivers (file, database, array) won't work
- Stale cache data might be served

**Code Reference:**
```594:604:Modules/BeautyBooking/Services/BeautyRankingService.php
if (config('cache.default') === 'redis') {
    $keys = Cache::getRedis()->keys($pattern);
    if (!empty($keys)) {
        Cache::getRedis()->del($keys);
    }
}
```

**Impact:**
- Stale ranking data
- Inconsistent search results
- Poor user experience

**Fix Required:**
1. Add support for other cache drivers
2. Use cache tags if available
3. Implement fallback invalidation strategy

---

### 10. Commission Calculation Top Rated Discount

**Location:** `Modules/BeautyBooking/Services/BeautyCommissionService.php`

**Issue:** The Top Rated discount calculation subtracts from percentage, which could result in negative commission.

**Problem:**
- Code does `max(0, $commissionPercentage - $topRatedDiscount)` which prevents negative values
- However, the discount is subtracted from percentage, which might not be the intended behavior
- Should discount be a percentage reduction or a fixed percentage reduction?

**Code Reference:**
```112:112:Modules/BeautyBooking/Services/BeautyCommissionService.php
$commissionPercentage = max(0, $commissionPercentage - $topRatedDiscount);
```

**Impact:**
- Potentially incorrect commission calculations
- Unclear discount mechanism

**Fix Required:**
1. Clarify discount mechanism (percentage points vs percentage of commission)
2. Document expected behavior
3. Add validation to ensure commission never goes below 0

---

## Summary

**Total Bugs Found:** 10
- 🔴 Critical: 2
- 🟠 High: 3
- 🟡 Medium: 3
- 🟢 Low: 2

**Recommendations:**
1. Implement database-level unique constraints with proper error handling
2. Consolidate revenue recording logic into a single transactional method
3. Add comprehensive transaction wrapping for critical operations
4. Use consistent config-based calculations throughout
5. Improve error handling and logging for better debugging
6. Add integration tests for concurrent operations
7. Document expected behavior for edge cases

---

**Next Steps:**
1. Prioritize fixing Critical and High priority bugs
2. Add unit tests for race condition scenarios
3. Review and update documentation
4. Consider adding database constraints for data integrity
5. Implement monitoring/alerting for duplicate transactions

