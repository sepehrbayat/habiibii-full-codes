# AgentOps Alerts Setup for BeautyBooking Module
# راه‌اندازی هشدارهای AgentOps برای ماژول BeautyBooking

## Overview
## بررسی کلی

This document provides instructions for setting up AgentOps alerts to monitor the BeautyBooking module in production.

این سند دستورالعمل‌هایی برای راه‌اندازی هشدارهای AgentOps برای نظارت بر ماژول BeautyBooking در تولید ارائه می‌دهد.

## Alert Configuration
## پیکربندی هشدار

The alert configuration is stored in `agentops-alerts-config.json`. This file contains:

پیکربندی هشدار در `agentops-alerts-config.json` ذخیره شده است. این فایل شامل:

- **8 Alert Rules**: Covering critical operations and error conditions
- **Monitoring Operations**: All key BeautyBooking operations
- **Key Attributes**: Important trace attributes to monitor

---

## Alert Rules
## قوانین هشدار

### 1. Booking Creation Errors
**Priority**: High  
**Condition**: Booking creation fails  
**Threshold**: 1 error in 5 minutes  
**Channels**: Email, Slack

### 2. Slow Booking Creation
**Priority**: Medium  
**Condition**: Booking creation takes >1 second  
**Threshold**: 5 occurrences in 10 minutes  
**Channels**: Email

### 3. Duplicate Commission Transactions
**Priority**: Critical  
**Condition**: Duplicate commission transactions detected  
**Threshold**: 1 occurrence in 1 minute  
**Channels**: Email, Slack, PagerDuty

### 4. Calendar Availability Check Failures
**Priority**: Medium  
**Condition**: Calendar availability checks fail  
**Threshold**: 10 failures in 5 minutes  
**Channels**: Email

### 5. Badge Calculation Errors
**Priority**: Medium  
**Condition**: Badge calculation fails  
**Threshold**: 3 errors in 10 minutes  
**Channels**: Email

### 6. Payment Status Update Failures
**Priority**: High  
**Condition**: Payment status updates fail  
**Threshold**: 5 failures in 5 minutes  
**Channels**: Email, Slack

### 7. Database Transaction Failures
**Priority**: Critical  
**Condition**: Database transactions fail in booking operations  
**Threshold**: 3 failures in 5 minutes  
**Channels**: Email, PagerDuty

### 8. High Error Rate
**Priority**: High  
**Condition**: Overall error rate >5%  
**Threshold**: 15 minutes  
**Channels**: Email, Slack

---

## Setup Methods
## روش‌های راه‌اندازی

### Method 1: AgentOps Dashboard (Recommended)
## روش 1: داشبورد AgentOps (توصیه شده)

1. **Log in to AgentOps Dashboard**
   - Navigate to https://app.agentops.ai
   - Authenticate with your API key

2. **Navigate to Alerts Section**
   - Click on "Alerts" in the sidebar
   - Click "Create New Alert"

3. **Create Alert Rules**
   - For each alert in `agentops-alerts-config.json`:
     - Click "New Alert Rule"
     - Enter alert name and description
     - Set condition (operation, status, duration, etc.)
     - Set threshold (count, time window)
     - Configure notification channels
     - Save alert

4. **Test Alerts**
   - Trigger test conditions
   - Verify alerts are received
   - Adjust thresholds if needed

### Method 2: AgentOps API
## روش 2: API AgentOps

```bash
# Create alert using AgentOps API
curl -X POST https://api.agentops.ai/v1/alerts \
  -H "Authorization: Bearer YOUR_API_KEY" \
  -H "Content-Type: application/json" \
  -d @agentops-alerts-config.json
```

### Method 3: AgentOps MCP Tools
## روش 3: ابزارهای AgentOps MCP

Use AgentOps MCP tools in Cursor AI to:
- Query traces for error conditions
- Set up automated monitoring scripts
- Create alert triggers based on trace analysis

---

## Notification Channels
## کانال‌های اعلان

### Email
- Configure email addresses in AgentOps dashboard
- Add team members who should receive alerts
- Set up email filters to avoid spam

### Slack
- Create Slack webhook in AgentOps dashboard
- Configure Slack channel for alerts
- Set up different channels for different alert severities

### PagerDuty
- Integrate PagerDuty with AgentOps
- Configure escalation policies
- Set up on-call schedules

---

## Monitoring Operations
## عملیات نظارت

The following operations are monitored:

عملیات زیر نظارت می‌شوند:

- `beauty.booking.create` - Booking creation
- `beauty.booking.updatePaymentStatus` - Payment status updates
- `beauty.booking.updateBookingStatus` - Booking status updates
- `beauty.booking.cancelBooking` - Booking cancellations
- `beauty.calendar.isTimeSlotAvailable` - Availability checks
- `beauty.calendar.getAvailableTimeSlots` - Time slot retrieval
- `beauty.badge.calculateAndAssignBadges` - Badge calculations
- `beauty.commission.calculateCommission` - Commission calculations
- `beauty.revenue.recordCommission` - Revenue recording

---

## Key Attributes to Monitor
## ویژگی‌های کلیدی برای نظارت

- `beauty.booking.user_id` - User ID
- `beauty.booking.salon_id` - Salon ID
- `beauty.booking.service_id` - Service ID
- `beauty.booking.staff_id` - Staff ID
- `beauty.booking.status` - Booking status
- `beauty.booking.payment_status` - Payment status

---

## Best Practices
## بهترین روش‌ها

1. **Start with High Priority Alerts**
   - Set up critical alerts first
   - Test thoroughly before enabling all alerts

2. **Adjust Thresholds**
   - Monitor alert frequency
   - Adjust thresholds based on actual traffic
   - Avoid alert fatigue

3. **Review Regularly**
   - Review alert effectiveness weekly
   - Update thresholds as needed
   - Remove unnecessary alerts

4. **Document Alert Responses**
   - Document how to respond to each alert
   - Create runbooks for common issues
   - Train team on alert handling

5. **Test in Staging**
   - Test all alerts in staging environment
   - Verify notification channels work
   - Ensure alerts are actionable

---

## Troubleshooting
## عیب‌یابی

### Alerts Not Firing
- Check AgentOps dashboard for trace data
- Verify alert conditions are correct
- Check notification channel configuration

### Too Many Alerts
- Increase thresholds
- Review alert conditions
- Consolidate similar alerts

### Missing Alerts
- Check trace generation
- Verify OpenTelemetry is enabled
- Review AgentOps connection

---

## Maintenance
## نگهداری

1. **Weekly Review**
   - Review alert frequency
   - Check false positive rate
   - Update thresholds as needed

2. **Monthly Audit**
   - Review all alert rules
   - Remove unused alerts
   - Add new alerts for new issues

3. **Quarterly Review**
   - Review alert effectiveness
   - Update documentation
   - Train team on changes

---

## Support
## پشتیبانی

For issues with AgentOps alerts:
- Check AgentOps documentation
- Contact AgentOps support
- Review trace data in dashboard

---

**Last Updated**: November 28, 2025  
**Module**: BeautyBooking  
**Status**: Ready for Production

