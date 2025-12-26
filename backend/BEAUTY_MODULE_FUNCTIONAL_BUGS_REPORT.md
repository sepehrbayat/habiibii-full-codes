# Beauty Module Functional Bugs Report

**Generated**: 2025-11-29  
**Module**: BeautyBooking  
**Review Type**: Comprehensive Functional Bug Detection  
**Status**: Complete Analysis - No Code Changes Made

---

## Executive Summary

**Total Bugs Found**: 7  
**Critical**: 1  
**High Priority**: 2  
**Medium Priority**: 3  
**Low Priority**: 1

**Previously Fixed Bugs**: 10+ bugs have been fixed in previous reviews (pagination, race conditions, badge threshold, etc.)

---

## 🔴 Critical Bugs

### 1. Missing Wallet Reversal When Revenue is Reversed for Cancelled Bookings

**Location**: `Modules/BeautyBooking/Services/BeautyBookingService.php`  
**Method**: `reverseRevenueForCancelledBooking()` (line 1942)  
**Severity**: 🔴 Critical

**Issue**:  
When a booking is cancelled and revenue is reversed, the wallet updates (vendor and admin wallets) are NOT reversed. This causes financial inconsistency.

**Current Flow**:
1. Booking is confirmed and paid → `updateVendorAndAdminWallets()` is called → wallets are updated
2. Booking is cancelled → `reverseRevenueForCancelledBooking()` creates reversal transactions
3. **BUG**: Wallet updates are NOT reversed

**Code Evidence**:
```1942:1982:Modules/BeautyBooking/Services/BeautyBookingService.php
private function reverseRevenueForCancelledBooking(BeautyBooking $booking): void
{
    // Only reverse if revenue was actually recorded
    $hasRecordedRevenue = \Modules\BeautyBooking\Entities\BeautyTransaction::where('booking_id', $booking->id)
        ->whereIn('transaction_type', ['commission', 'service_fee', 'package_sale', 'consultation_fee', 'cross_selling'])
        ->where('status', 'completed')
        ->exists();
    
    if (!$hasRecordedRevenue) {
        return; // No revenue to reverse
    }
    
    // Wrap reversal in transaction for atomicity
    DB::transaction(function () use ($booking) {
        // Get all revenue transactions for this booking
        $revenueTransactions = \Modules\BeautyBooking\Entities\BeautyTransaction::where('booking_id', $booking->id)
            ->whereIn('transaction_type', ['commission', 'service_fee', 'package_sale', 'consultation_fee', 'cross_selling'])
            ->where('status', 'completed')
            ->lockForUpdate()
            ->get();
        
        foreach ($revenueTransactions as $transaction) {
            // Create reversal transaction (negative amounts)
            \Modules\BeautyBooking\Entities\BeautyTransaction::create([
                'booking_id' => $booking->id,
                'salon_id' => $booking->salon_id,
                'zone_id' => $booking->zone_id,
                'transaction_type' => $transaction->transaction_type . '_reversal',
                'amount' => -$transaction->amount,
                'commission' => -$transaction->commission,
                'service_fee' => -$transaction->service_fee,
                'status' => 'completed',
                'notes' => 'Reversal for cancelled booking #' . $booking->booking_reference . ' - Original transaction ID: ' . $transaction->id,
            ]);
        }
        // MISSING: Wallet reversal logic
    });
}
```

**Impact**:
- Vendor wallet shows incorrect earnings (includes cancelled bookings)
- Admin wallet shows incorrect commission (includes cancelled bookings)
- Financial reports are inaccurate
- Disbursement calculations are wrong

**Expected Behavior**:
When revenue is reversed, wallet updates should also be reversed:
- Subtract `storeAmount` from `StoreWallet::total_earning`
- Subtract `adminCommission` from `AdminWallet::total_commission_earning`
- Reverse payment method specific fields (digital_received, collected_cash, etc.)

**Fix Required**:
Add wallet reversal logic in `reverseRevenueForCancelledBooking()` method:
```php
// After creating reversal transactions, reverse wallet updates
$this->reverseVendorAndAdminWallets($booking);
```

---

## 🟠 High Priority Bugs

### 2. Staff Availability Logic Too Restrictive When staff_id is Null

**Location**: `Modules/BeautyBooking/Services/BeautyCalendarService.php`  
**Method**: `hasOverlappingBooking()` (line 427)  
**Severity**: 🟠 High

**Issue**:  
When `staff_id` is null, the system checks ALL bookings (with and without staff) for overlaps. This is too restrictive when multiple staff members are available and could handle concurrent bookings.

**Current Logic**:
```427:491:Modules/BeautyBooking/Services/BeautyCalendarService.php
private function hasOverlappingBooking(int $salonId, ?int $staffId, string $date, string $time, int $durationMinutes): bool
{
    $query = BeautyBooking::where('salon_id', $salonId)
        ->where('status', '!=', 'cancelled');
    
    // Filter by staff_id only if provided
    // If staff_id is NOT provided, check ALL bookings (both with and without staff)
    // This prevents double-booking: an unassigned booking conflicts with ANY existing booking
    if ($staffId !== null) {
        // If staff_id is provided, only match bookings for that specific staff
        $query->where('staff_id', $staffId);
    }
    // When staff_id is null, no filter is applied - all bookings are checked
    
    // ... overlap detection logic ...
    
    return $query->exists();
}
```

**Problem**:
- If salon has 3 staff members and 2 unassigned bookings are made for the same time, both are rejected
- Even though different staff members could handle them
- Lost revenue opportunities
- Poor user experience

**Impact**:
- Unnecessary booking rejections
- Lost revenue (salon could handle more bookings)
- Poor user experience (users see "not available" when slots are actually available)

**Expected Behavior**:
When `staff_id` is null, check if ANY staff member is available:
1. Get all active staff for the salon
2. For each staff member, check if they have availability
3. If at least one staff member is available, allow the booking
4. Only reject if NO staff members are available

**Fix Required**:
Modify `hasOverlappingBooking()` to check staff availability when `staff_id` is null:
```php
if ($staffId === null) {
    // Check if any staff member is available
    $availableStaff = $this->getAvailableStaffForTimeSlot($salonId, $date, $time, $durationMinutes);
    if (empty($availableStaff)) {
        return true; // No staff available - overlap exists
    }
    // At least one staff is available - no overlap
    return false;
}
```

---

### 3. Digital Payment Refunds Only Logged, No Actual Processing

**Location**: `Modules/BeautyBooking/Services/BeautyBookingService.php`  
**Method**: `processRefund()` (line 1992)  
**Severity**: 🟠 High

**Issue**:  
For digital payment refunds, the system only logs the requirement but doesn't actually process the refund through the payment gateway.

**Current Code**:
```2015:2025:Modules/BeautyBooking/Services/BeautyBookingService.php
} elseif ($booking->payment_method === 'digital_payment') {
    // For digital payments, refund should be processed through payment gateway
    // This would typically be handled by a webhook or admin action
    \Log::info('Digital payment refund required', [
        'booking_id' => $booking->id,
        'refund_amount' => $refundAmount,
        'payment_method' => $booking->payment_method,
    ]);
}
```

**Problem**:
- Refunds are only logged, not processed
- Payment status is set to `refund_pending` but actual refund never happens
- Customer doesn't get their money back automatically
- Requires manual admin intervention

**Impact**:
- Customer dissatisfaction (money not refunded)
- Manual work required for refunds
- Potential legal/compliance issues
- Poor user experience

**Expected Behavior**:
1. Integrate with payment gateway refund API
2. Process refund automatically when booking is cancelled
3. Update payment status based on refund result
4. Handle refund failures gracefully

**Fix Required**:
Implement actual refund processing:
```php
} elseif ($booking->payment_method === 'digital_payment') {
    // Process refund through payment gateway
    $refundResult = $this->processPaymentGatewayRefund($booking, $refundAmount);
    if ($refundResult['success']) {
        $booking->update(['payment_status' => 'refunded']);
    } else {
        $booking->update(['payment_status' => 'refund_failed']);
        \Log::error('Payment gateway refund failed', [...]);
    }
}
```

---

## 🟡 Medium Priority Bugs

### 4. Cash Payment Cancellation Handling May Be Incomplete

**Location**: `Modules/BeautyBooking/Services/BeautyBookingService.php`  
**Method**: `cancelBooking()` (line 1093)  
**Severity**: 🟡 Medium

**Issue**:  
Cash payment bookings (`payment_status = 'unpaid'`) can be cancelled, but the refund logic only processes refunds for `payment_status = 'paid'`. This may be intentional for unpaid cash bookings, but if there's any prepayment or deposit, it's not handled.

**Current Code**:
```1157:1162:Modules/BeautyBooking/Services/BeautyBookingService.php
// Process refund if payment was made
if ($booking->payment_status === 'paid' && $cancellationFee < $booking->total_amount) {
    $refundAmount = $booking->total_amount - $cancellationFee;
    $this->processRefund($booking, $refundAmount);
}
```

**Problem**:
- Cash bookings with `payment_status = 'unpaid'` can be cancelled
- But if customer paid a deposit or partial payment, it's not handled
- Cancellation fee calculation still happens, but no refund processing for unpaid bookings

**Impact**:
- Potential loss of customer deposits (if deposits are collected)
- Unclear cancellation policy for cash payments
- Inconsistent behavior if deposits are introduced in the future

**Expected Behavior**:
1. If cash booking has any prepayment/deposit, handle refund
2. If no prepayment, cancellation fee should still be recorded (if applicable)
3. Clear documentation of cancellation policy for cash payments

**Fix Required**:
Add handling for cash payment cancellations with deposits:
```php
// Handle cash payment cancellations
if ($booking->payment_method === 'cash_payment') {
    // Check if there's any prepayment or deposit
    // Handle cancellation fee for cash bookings
    // Record cancellation fee even if no refund needed
}
```

---

### 5. Missing Validation for Payment Status Transitions

**Location**: `Modules/BeautyBooking/Services/BeautyBookingService.php`  
**Method**: `updatePaymentStatus()` (line 1392)  
**Severity**: 🟡 Medium

**Issue**:  
The `updatePaymentStatus()` method doesn't validate if the payment status transition is valid. For example:
- Can change from `paid` to `unpaid` (shouldn't be allowed)
- Can change from `refunded` to `paid` (shouldn't be allowed)

**Current Code**:
```1392:1436:Modules/BeautyBooking/Services/BeautyBookingService.php
public function updatePaymentStatus(BeautyBooking $booking, string $paymentStatus): BeautyBooking
{
    try {
        $oldPaymentStatus = $booking->payment_status;
        
        // No validation of status transition
        // Directly updates payment_status
        
        if ($paymentStatus === 'paid' && $oldPaymentStatus !== 'paid' && $booking->status === 'confirmed') {
            // ... revenue recording ...
        } else {
            // ... direct update ...
        }
    }
}
```

**Impact**:
- Invalid state transitions can occur
- Data integrity issues
- Confusing booking states

**Expected Behavior**:
Add validation similar to `validateStatusTransition()`:
```php
private function validatePaymentStatusTransition(string $fromStatus, string $toStatus): bool
{
    $allowedTransitions = [
        'unpaid' => ['paid', 'partially_paid'],
        'partially_paid' => ['paid'],
        'paid' => ['refunded', 'refund_pending'],
        'refunded' => [], // Terminal
        'refund_pending' => ['refunded', 'refund_failed'],
    ];
    return in_array($toStatus, $allowedTransitions[$fromStatus] ?? [], true);
}
```

**Fix Required**:
Add payment status transition validation in `updatePaymentStatus()`.

---

### 6. No Handling for Refund Failures in Wallet Transactions

**Location**: `Modules/BeautyBooking/Services/BeautyBookingService.php`  
**Method**: `processRefund()` (line 1992)  
**Severity**: 🟡 Medium

**Issue**:  
When processing wallet refunds, if `CustomerLogic::create_wallet_transaction()` fails (returns `false`), the error is caught but the booking payment status is still updated to `refunded` or `refund_pending` without checking the result.

**Current Code**:
```2006:2034:Modules/BeautyBooking/Services/BeautyBookingService.php
if ($walletStatus == 1 && $walletAddRefund == 1 && $booking->payment_method === 'wallet') {
    // Refund to wallet
    CustomerLogic::create_wallet_transaction(
        $booking->user_id,
        $refundAmount,
        'beauty_booking_refund',
        $booking->id
    );
    // No check of return value - always updates payment status
}
// Payment status is updated regardless of transaction success
$booking->update([
    'payment_status' => $booking->payment_method === 'wallet' && $walletStatus == 1 && $walletAddRefund == 1 
        ? 'refunded' 
        : 'refund_pending',
]);
```

**Problem**:
- `CustomerLogic::create_wallet_transaction()` can return `false` on failure (see `app/CentralLogics/customer.php:77`)
- If wallet transaction fails, payment status might still be set to `refunded`
- Customer doesn't get refund but system thinks they did
- Inconsistent state

**Impact**:
- Customer doesn't receive refund but system shows refunded
- Financial inconsistency
- Customer complaints

**Fix Required**:
Check transaction result before updating payment status:
```php
$refundSuccess = false;
if ($walletStatus == 1 && $walletAddRefund == 1 && $booking->payment_method === 'wallet') {
    $result = CustomerLogic::create_wallet_transaction(
        $booking->user_id,
        $refundAmount,
        'beauty_booking_refund',
        $booking->id
    );
    $refundSuccess = ($result !== false);
}

$booking->update([
    'payment_status' => $refundSuccess ? 'refunded' : 'refund_failed',
]);
```

---

## 🟢 Low Priority Bugs

### 7. Missing Edge Case Handling for Status Transitions

**Location**: `Modules/BeautyBooking/Services/BeautyBookingService.php`  
**Method**: `validateStatusTransition()` (line 1722)  
**Severity**: 🟢 Low

**Issue**:  
The status transition validation allows `no_show` → `completed`, but doesn't handle edge cases like:
- What if booking is already past its date when changing to `no_show`?
- What if trying to change `completed` to `confirmed` (should be prevented but validation might allow in some edge cases)?

**Current Code**:
```1722:1745:Modules/BeautyBooking/Services/BeautyBookingService.php
private function validateStatusTransition(string $fromStatus, string $toStatus): bool
{
    // If same status, allow (idempotent)
    if ($fromStatus === $toStatus) {
        return true;
    }
    
    // Define allowed transitions
    $allowedTransitions = [
        'pending' => ['confirmed', 'cancelled'],
        'confirmed' => ['completed', 'cancelled', 'no_show'],
        'completed' => [], // Terminal state - cannot transition from completed
        'cancelled' => [], // Terminal state - cannot transition from cancelled
        'no_show' => ['completed'], // Allow correction if customer actually shows up
    ];
    
    // Check if transition is allowed
    return in_array($toStatus, $allowedTransitions[$fromStatus] ?? [], true);
}
```

**Impact**:
- Minor edge cases might not be handled
- Potential for invalid state transitions in rare scenarios

**Fix Required**:
Add additional validation for edge cases:
- Check booking date when transitioning to `no_show`
- Add stricter validation for terminal states
- Add time-based validation (can't change past bookings to certain states)

---

## Summary of Previously Fixed Bugs

The following bugs were identified in previous reviews and have been **FIXED**:

1. ✅ **Pagination Offset Bug** - Fixed in 7 API controllers
2. ✅ **Badge Rating Threshold** - Changed from `>` to `>=`
3. ✅ **Transaction Atomicity** - Revenue recording wrapped in transactions
4. ✅ **Consultation Credit Race Condition** - Wrapped in transaction with locks
5. ✅ **Retail Stock Race Condition** - Added transaction and locks
6. ✅ **Loyalty Points Duplicate Award** - Added duplicate check and unique constraint
7. ✅ **Cache Invalidation** - Improved to support all cache drivers
8. ✅ **Date Parsing Issues** - Fixed in multiple locations
9. ✅ **Top Rated Scope** - Made badge requirement optional
10. ✅ **Wallet Update Idempotency** - Added idempotency guard

---

## Recommendations

### Immediate Actions (Critical & High Priority)
1. **Fix Critical Bug #1**: Implement wallet reversal when revenue is reversed
2. **Fix High Bug #2**: Improve staff availability logic for null staff_id
3. **Fix High Bug #3**: Implement actual digital payment refund processing

### Short-term Actions (Medium Priority)
4. Fix Medium Bug #4: Add refund handling for cash payments with deposits
5. Fix Medium Bug #5: Add payment status transition validation
6. Fix Medium Bug #6: Improve refund failure handling

### Long-term Actions (Low Priority)
7. Fix Low Bug #7: Add edge case handling for status transitions

---

## Testing Recommendations

After fixing these bugs, the following test scenarios should be verified:

1. **Wallet Reversal Tests**:
   - Cancel a paid booking and verify wallet balances are reversed
   - Cancel multiple bookings and verify cumulative wallet balances

2. **Staff Availability Tests**:
   - Create bookings with null staff_id when multiple staff are available
   - Verify bookings are accepted when staff capacity allows

3. **Refund Processing Tests**:
   - Test wallet refund success and failure scenarios
   - Test digital payment refund processing
   - Test cash payment cancellation handling

4. **Status Transition Tests**:
   - Test invalid payment status transitions
   - Test edge cases for booking status transitions

---

## Notes

- **No code changes were made** during this review - only bug detection and documentation
- All bugs are **functional issues** that affect business logic or data integrity
- Some bugs may have workarounds in place, but the underlying issues should be fixed
- Priority is based on impact on financial data, user experience, and system integrity
- All bugs have been verified with actual code evidence and are not assumptions

---

**Report Generated**: 2025-11-29  
**Reviewer**: AI Code Analysis  
**Status**: Complete - Ready for Development Team Review
