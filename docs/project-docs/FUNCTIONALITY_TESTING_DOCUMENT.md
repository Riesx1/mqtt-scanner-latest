# Functionality Testing Document - MQTT Security Scanner

**Project:** MQTT Security Scanner  
**Version:** 1.0  
**Testing Date:** December 17, 2025  
**Tester:** [Your Name]  
**Environment:** Windows Development (Local)

---

## Table of Contents

1. [Testing Overview](#testing-overview)
2. [Test Environment Setup](#test-environment-setup)
3. [Authentication Testing](#authentication-testing)
4. [MQTT Scanner Testing](#mqtt-scanner-testing)
5. [Sensor Data Testing](#sensor-data-testing)
6. [Dashboard Testing](#dashboard-testing)
7. [Security Features Testing](#security-features-testing)
8. [Integration Testing](#integration-testing)
9. [Performance Testing](#performance-testing)
10. [Test Results Summary](#test-results-summary)

---

## Testing Overview

### Objectives

-   Verify all features work as intended
-   Ensure user workflows function correctly
-   Validate data accuracy and persistence
-   Test error handling and edge cases
-   Confirm security implementations

### Scope

-   ✅ User Authentication (Register, Login, Logout)
-   ✅ MQTT Broker Scanning
-   ✅ Sensor Data Collection and Display
-   ✅ PDF Report Generation
-   ✅ Profile Management
-   ✅ Security Features (Rate Limiting, CSRF, etc.)

### Out of Scope

-   Performance benchmarking beyond basic metrics
-   Load testing with >50 concurrent users
-   Mobile responsive testing
-   Cross-browser compatibility (focus on Chrome)

---

## Test Environment Setup

### Prerequisites Checklist

```powershell
# 1. Start MQTT Brokers
cd mqtt-brokers
docker-compose up -d
# Expected: Both containers running (mosq_secure, mosq_insecure)

# 2. Start Flask Scanner
cd mqtt-scanner
python app.py
# Expected: Running on http://127.0.0.1:5000

# 3. Start Laravel Server
php artisan serve
# Expected: Running on http://127.0.0.1:8000

# 4. Verify Database
# Check database/database.sqlite exists

# 5. Clear Cache
php artisan optimize:clear
```

### Test Data Requirements

**Test User Accounts:**

-   Email: test@example.com, Password: Test123!@#
-   Email: admin@example.com, Password: Admin123!@#

**MQTT Credentials:**

-   Secure Broker: mqtt@example.com / testpass
-   Target IP: 192.168.100.56 (or your local IP)

---

## Authentication Testing

### TC-AUTH-01: User Registration

**Objective:** Verify new users can successfully register

**Pre-conditions:**

-   User not already registered
-   Server running

**Test Steps:**

1. Navigate to `http://127.0.0.1:8000`
2. Click "Register" button
3. Fill in registration form:
    - Name: John Doe
    - Email: testuser1@example.com
    - Password: SecurePass123!
    - Confirm Password: SecurePass123!
4. Click "Register" button
5. Verify redirect to login page
6. Check success message displayed

**Expected Result:**

-   ✅ User redirected to login page
-   ✅ Success message: "Registration successful! Please log in with your credentials."
-   ✅ User stored in database
-   ✅ Password hashed (not plain text)

**Actual Result:** ************\_\_\_************

**Status:** ☐ Pass ☐ Fail ☐ Blocked

**Notes:** ************\_\_\_************

---

### TC-AUTH-02: User Login (Valid Credentials)

**Objective:** Verify registered users can log in

**Pre-conditions:** User registered in system

**Test Steps:**

1. Navigate to `http://127.0.0.1:8000/login`
2. Enter valid credentials:
    - Email: testuser1@example.com
    - Password: SecurePass123!
3. Check "Remember me" checkbox
4. Click "Log in" button
5. Verify redirect to dashboard

**Expected Result:**

-   ✅ Redirected to `/dashboard`
-   ✅ User name displayed in navigation
-   ✅ Dashboard content visible
-   ✅ Session cookie created

**Actual Result:** ************\_\_\_************

**Status:** ☐ Pass ☐ Fail ☐ Blocked

---

### TC-AUTH-03: User Login (Invalid Credentials)

**Objective:** Verify system rejects invalid login attempts

**Pre-conditions:** Server running

**Test Steps:**

1. Navigate to login page
2. Enter invalid credentials:
    - Email: testuser1@example.com
    - Password: WrongPassword123
3. Click "Log in"
4. Observe error message

**Expected Result:**

-   ✅ Login denied
-   ✅ Error message: "Incorrect password. You have X attempt(s) remaining."
-   ✅ User remains on login page
-   ✅ Attempt counter decremented

**Actual Result:** ************\_\_\_************

**Status:** ☐ Pass ☐ Fail ☐ Blocked

---

### TC-AUTH-04: Brute Force Protection

**Objective:** Verify account lockout after 5 failed attempts

**Pre-conditions:** Fresh user account

**Test Steps:**

1. Attempt login with wrong password 5 times
2. On 5th attempt, note error message
3. Wait for lockout period (60 seconds)
4. Try logging in again

**Expected Result:**

-   ✅ After 5 attempts: "Too many failed login attempts. Please try again later."
-   ✅ Further attempts blocked for 60 seconds
-   ✅ After timeout, login allowed again

**Actual Result:** ************\_\_\_************

**Status:** ☐ Pass ☐ Fail ☐ Blocked

---

### TC-AUTH-05: Logout

**Objective:** Verify user can successfully log out

**Pre-conditions:** User logged in

**Test Steps:**

1. Click "Logout" button in navigation
2. Verify redirect to welcome page
3. Try accessing `/dashboard` directly
4. Verify redirect to login

**Expected Result:**

-   ✅ User logged out
-   ✅ Redirected to welcome/home page
-   ✅ Protected routes no longer accessible
-   ✅ Session destroyed

**Actual Result:** ************\_\_\_************

**Status:** ☐ Pass ☐ Fail ☐ Blocked

---

### TC-AUTH-06: Registration Validation

**Objective:** Verify form validation on registration

**Test Cases:**

| Field            | Input         | Expected Error                         |
| ---------------- | ------------- | -------------------------------------- |
| Email            | invalid-email | Please enter a valid email address     |
| Email            | (empty)       | Please enter your email address        |
| Password         | 123           | Password must be at least 8 characters |
| Password         | (empty)       | Please enter your password             |
| Confirm Password | mismatch      | Passwords do not match                 |

**Status:** ☐ Pass ☐ Fail

---

## MQTT Scanner Testing

### TC-SCAN-01: Basic Network Scan (Single IP)

**Objective:** Scan a single MQTT broker IP

**Pre-conditions:**

-   User logged in
-   MQTT brokers running
-   Dashboard loaded

**Test Steps:**

1. Navigate to Dashboard
2. In target field, enter: `192.168.100.56`
3. Leave credentials empty
4. Click "Start Scan"
5. Wait for scan completion
6. Observe results table

**Expected Result:**

-   ✅ Scan initiated (button shows "Scanning...")
-   ✅ Progress indicator visible
-   ✅ Results display broker information:
    -   IP address
    -   Port number (1883/8883)
    -   Status (Open/Secured)
    -   Authentication requirement
-   ✅ Security badge displayed
-   ✅ Scan completes in <10 seconds

**Actual Result:** ************\_\_\_************

**Status:** ☐ Pass ☐ Fail ☐ Blocked

**Screenshot:** ☐ Attached

---

### TC-SCAN-02: Network Range Scan (CIDR)

**Objective:** Scan multiple IPs using CIDR notation

**Pre-conditions:** User logged in

**Test Steps:**

1. Enter target: `192.168.100.0/28` (16 IPs)
2. Click "Start Scan"
3. Monitor progress bar
4. Verify results for all IPs

**Expected Result:**

-   ✅ Scans all IPs in range
-   ✅ Progress bar updates incrementally
-   ✅ Results show all discovered brokers
-   ✅ Non-MQTT hosts shown as "No broker found"

**Actual Result:** ************\_\_\_************

**Status:** ☐ Pass ☐ Fail ☐ Blocked

---

### TC-SCAN-03: Scan with Credentials (Secure Broker)

**Objective:** Authenticate to secured MQTT broker

**Pre-conditions:**

-   Secure broker running on port 8883
-   Valid credentials available

**Test Steps:**

1. Enter target: `192.168.100.56`
2. Enter MQTT credentials:
    - Username: mqtt@example.com
    - Password: testpass
3. Click "Start Scan"
4. Check authentication status in results

**Expected Result:**

-   ✅ Broker detected on port 8883
-   ✅ Authentication successful
-   ✅ TLS/SSL status shown
-   ✅ "Secured" badge displayed
-   ✅ Topics accessible (if available)

**Actual Result:** ************\_\_\_************

**Status:** ☐ Pass ☐ Fail ☐ Blocked

---

### TC-SCAN-04: Invalid Target Format

**Objective:** Verify input validation on target field

**Test Cases:**

| Input                       | Expected Behavior    |
| --------------------------- | -------------------- |
| `<script>alert(1)</script>` | Validation error     |
| `192.168.1.1000`            | Invalid IP error     |
| `192.168.1.1/33`            | Invalid CIDR error   |
| `hello world`               | Invalid format error |
| (empty)                     | Required field error |

**Expected Result:**

-   ✅ All invalid inputs rejected
-   ✅ Clear error messages displayed
-   ✅ Scan not initiated

**Status:** ☐ Pass ☐ Fail

---

### TC-SCAN-05: Scan Rate Limiting

**Objective:** Verify 10 scans per minute limit

**Pre-conditions:** User logged in

**Test Steps:**

1. Perform 10 quick consecutive scans
2. Attempt 11th scan immediately
3. Note error message
4. Wait 60 seconds
5. Try scanning again

**Expected Result:**

-   ✅ First 10 scans succeed
-   ✅ 11th scan blocked with error: "Too many scan requests. Please wait before scanning again."
-   ✅ HTTP 429 status code
-   ✅ After timeout, scanning allowed

**Actual Result:** ************\_\_\_************

**Status:** ☐ Pass ☐ Fail ☐ Blocked

---

### TC-SCAN-06: Concurrent User Scans

**Objective:** Test multiple users scanning simultaneously

**Pre-conditions:** 2+ browser sessions with different users

**Test Steps:**

1. Login as User A in Chrome
2. Login as User B in Firefox/Incognito
3. Both users scan different targets
4. Verify results don't mix

**Expected Result:**

-   ✅ Both scans execute independently
-   ✅ Results don't interfere
-   ✅ Rate limits per-user (not global)

**Actual Result:** ************\_\_\_************

**Status:** ☐ Pass ☐ Fail ☐ Blocked

---

## Sensor Data Testing

### TC-SENSOR-01: Real-time Data Display

**Objective:** Verify sensor data appears on dashboard

**Pre-conditions:**

-   ESP32 connected OR test publisher running
-   Brokers running

**Test Steps:**

1. Start test publisher: `python test_publisher.py`
2. Navigate to Dashboard
3. Observe sensor data cards
4. Verify data updates

**Expected Result:**

-   ✅ Temperature reading displayed (e.g., 25.3°C)
-   ✅ Humidity reading displayed (e.g., 60.5%)
-   ✅ Motion status displayed (Detected/Not Detected)
-   ✅ Data updates in real-time
-   ✅ Timestamps shown

**Actual Result:** ************\_\_\_************

**Status:** ☐ Pass ☐ Fail ☐ Blocked

**Screenshot:** ☐ Attached

---

### TC-SENSOR-02: Data Persistence

**Objective:** Verify sensor data saved to database

**Pre-conditions:** Sensor data published

**Test Steps:**

1. Publish sensor data via test script
2. Refresh dashboard page
3. Verify data still displayed
4. Check database:
    ```powershell
    sqlite3 database/database.sqlite "SELECT * FROM sensor_readings ORDER BY created_at DESC LIMIT 5;"
    ```

**Expected Result:**

-   ✅ Data persists after page refresh
-   ✅ Database contains readings
-   ✅ Timestamps accurate
-   ✅ Values match published data

**Actual Result:** ************\_\_\_************

**Status:** ☐ Pass ☐ Fail ☐ Blocked

---

### TC-SENSOR-03: Clear Retained Messages

**Objective:** Verify clearing retained data works

**Pre-conditions:** Retained messages exist on brokers

**Test Steps:**

1. Note current sensor readings on dashboard
2. Run: `python clear_retained.py`
3. Wait for confirmation
4. Refresh dashboard
5. Perform new scan

**Expected Result:**

-   ✅ Script reports successful clearing
-   ✅ Dashboard shows no sensor data initially
-   ✅ Only new data appears after clearing

**Actual Result:** ************\_\_\_************

**Status:** ☐ Pass ☐ Fail ☐ Blocked

---

## Dashboard Testing

### TC-DASH-01: Dashboard Load Time

**Objective:** Verify dashboard loads quickly

**Pre-conditions:** User logged in

**Test Steps:**

1. Clear browser cache
2. Open DevTools > Network tab
3. Navigate to `/dashboard`
4. Record page load time
5. Check for errors in console

**Expected Result:**

-   ✅ Page loads in <2 seconds
-   ✅ No JavaScript errors
-   ✅ All assets load successfully
-   ✅ CSS styling applied

**Actual Result:**
Load Time: **\_\_\_** seconds

**Status:** ☐ Pass ☐ Fail ☐ Blocked

---

### TC-DASH-02: PDF Report Generation

**Objective:** Verify PDF export functionality

**Pre-conditions:**

-   Scan results available
-   Dashboard displaying data

**Test Steps:**

1. Perform a scan with results
2. Click "Download PDF" button
3. Wait for PDF generation
4. Open downloaded PDF file
5. Verify contents

**Expected Result:**

-   ✅ PDF downloads successfully
-   ✅ Filename format: `mqtt_scan_report_YYYYMMDD_HHMMSS.pdf`
-   ✅ PDF contains:
    -   Scan summary
    -   Results table
    -   Security information
    -   Timestamps
    -   Readable formatting

**Actual Result:** ************\_\_\_************

**Status:** ☐ Pass ☐ Fail ☐ Blocked

**PDF Saved:** ☐ Yes

---

### TC-DASH-03: Responsive Layout

**Objective:** Verify dashboard adapts to window size

**Test Steps:**

1. Open dashboard in full screen
2. Resize browser to mobile size (375px)
3. Check element visibility
4. Test menu navigation

**Expected Result:**

-   ✅ Layout adjusts to screen size
-   ✅ No horizontal scrolling
-   ✅ All elements accessible
-   ✅ Mobile menu functional

**Actual Result:** ************\_\_\_************

**Status:** ☐ Pass ☐ Fail ☐ Blocked

---

### TC-DASH-04: Navigation Menu

**Objective:** Verify all navigation links work

**Test Links:**

| Link         | Expected Destination  | Status        |
| ------------ | --------------------- | ------------- |
| MQTT Scanner | /dashboard            | ☐ Pass ☐ Fail |
| Profile      | /profile              | ☐ Pass ☐ Fail |
| Logout       | / (home) + logged out | ☐ Pass ☐ Fail |

**Overall Status:** ☐ Pass ☐ Fail

---

## Security Features Testing

### TC-SEC-01: CSRF Token Validation

**Objective:** Verify CSRF protection on forms

**Pre-conditions:** User logged in

**Test Steps:**

1. Open browser DevTools
2. Inspect login/scan form
3. Locate CSRF token in HTML
4. Attempt form submission via cURL without token:
    ```powershell
    curl -X POST http://127.0.0.1:8000/mqtt/scan `
      -H "Content-Type: application/json" `
      -d '{"target":"192.168.1.1"}'
    ```

**Expected Result:**

-   ✅ CSRF token present in form
-   ✅ Request without token rejected (419 or redirect)
-   ✅ Error logged

**Actual Result:** ************\_\_\_************

**Status:** ☐ Pass ☐ Fail ☐ Blocked

---

### TC-SEC-02: XSS Protection

**Objective:** Verify XSS attacks prevented

**Test Inputs:**

| Field    | XSS Payload                     | Expected Behavior |
| -------- | ------------------------------- | ----------------- |
| Target   | `<script>alert('XSS')</script>` | Escaped/Rejected  |
| Username | `<img src=x onerror=alert(1)>`  | Escaped           |
| Name     | `'; DROP TABLE users;--`        | Escaped           |

**Test Steps:**

1. For each payload, enter in respective field
2. Submit form
3. Check if executed or escaped

**Expected Result:**

-   ✅ All payloads escaped
-   ✅ No script execution
-   ✅ Data stored safely

**Status:** ☐ Pass ☐ Fail

---

### TC-SEC-03: SQL Injection Protection

**Objective:** Verify database queries are safe

**Test Steps:**

1. Login with username: `admin' OR '1'='1`
2. Try scanning target: `192.168.1.1' OR '1'='1`
3. Check for SQL errors

**Expected Result:**

-   ✅ No SQL injection successful
-   ✅ Invalid input rejected
-   ✅ No database errors

**Actual Result:** ************\_\_\_************

**Status:** ☐ Pass ☐ Fail ☐ Blocked

---

### TC-SEC-04: Authorization (Route Protection)

**Objective:** Verify protected routes require authentication

**Test Steps:**

1. Logout from application
2. Attempt to access protected URLs directly:
    - `http://127.0.0.1:8000/dashboard`
    - `http://127.0.0.1:8000/profile`
    - `http://127.0.0.1:8000/sensors`

**Expected Result:**

-   ✅ All requests redirect to `/login`
-   ✅ HTTP 302 status code
-   ✅ No data exposed

**Actual Result:** ************\_\_\_************

**Status:** ☐ Pass ☐ Fail ☐ Blocked

---

### TC-SEC-05: Security Headers

**Objective:** Verify security headers present

**Test Steps:**

1. Open browser DevTools > Network
2. Load dashboard page
3. Check response headers

**Expected Headers:**

| Header                  | Expected Value | Present?   |
| ----------------------- | -------------- | ---------- |
| X-Frame-Options         | DENY           | ☐ Yes ☐ No |
| X-Content-Type-Options  | nosniff        | ☐ Yes ☐ No |
| Content-Security-Policy | (present)      | ☐ Yes ☐ No |

**Status:** ☐ Pass ☐ Fail

---

## Integration Testing

### TC-INT-01: Laravel → Flask Communication

**Objective:** Verify frontend communicates with scanner API

**Pre-conditions:** Both servers running

**Test Steps:**

1. Run connectivity test:
    ```powershell
    cd scripts
    php test_flask_connection.php
    ```
2. Observe output

**Expected Result:**

-   ✅ Connection successful
-   ✅ API responds with valid data
-   ✅ Authentication works (API key validated)

**Actual Result:** ************\_\_\_************

**Status:** ☐ Pass ☐ Fail ☐ Blocked

---

### TC-INT-02: Flask → MQTT Brokers

**Objective:** Verify scanner connects to brokers

**Pre-conditions:**

-   Flask running
-   Brokers running

**Test Steps:**

1. Check Docker containers:
    ```powershell
    docker ps
    ```
2. Initiate scan from dashboard
3. Check Flask terminal output
4. Check broker logs:
    ```powershell
    docker-compose logs -f
    ```

**Expected Result:**

-   ✅ Flask logs show connection attempts
-   ✅ Broker logs show incoming connections
-   ✅ No connection errors

**Actual Result:** ************\_\_\_************

**Status:** ☐ Pass ☐ Fail ☐ Blocked

---

### TC-INT-03: ESP32 → MQTT → Dashboard

**Objective:** End-to-end IoT data flow

**Pre-conditions:**

-   ESP32 programmed and connected
-   OR test publisher running

**Test Steps:**

1. Start test publisher: `python test_publisher.py`
2. Wait 5 seconds
3. Refresh dashboard
4. Verify sensor data appears

**Expected Result:**

-   ✅ Data flows: ESP32/Script → Broker → Dashboard
-   ✅ Temperature, humidity, motion visible
-   ✅ Values accurate
-   ✅ Updates in real-time

**Actual Result:** ************\_\_\_************

**Status:** ☐ Pass ☐ Fail ☐ Blocked

---

## Performance Testing

### TC-PERF-01: Scan Speed

**Objective:** Measure scan performance

**Test Cases:**

| Target        | Expected Time | Actual Time  | Status        |
| ------------- | ------------- | ------------ | ------------- |
| Single IP     | <5 seconds    | ****\_\_**** | ☐ Pass ☐ Fail |
| /28 (16 IPs)  | <30 seconds   | ****\_\_**** | ☐ Pass ☐ Fail |
| /24 (256 IPs) | <5 minutes    | ****\_\_**** | ☐ Pass ☐ Fail |

**Status:** ☐ Pass ☐ Fail

---

### TC-PERF-02: Dashboard Load Time

**Objective:** Verify acceptable page load times

**Test Steps:**

1. Clear browser cache
2. Measure page load time (DevTools)
3. Record findings

**Expected Result:**

-   ✅ Dashboard loads in <2 seconds
-   ✅ Scan results appear in <1 second

**Actual Result:**

-   Dashboard: **\_\_\_** seconds
-   Results: **\_\_\_** seconds

**Status:** ☐ Pass ☐ Fail ☐ Blocked

---

### TC-PERF-03: Database Query Performance

**Objective:** Verify database queries are optimized

**Test Steps:**

1. Insert 1000 sensor readings
2. Load dashboard
3. Check query time in Laravel debug bar

**Expected Result:**

-   ✅ Queries execute in <100ms
-   ✅ No N+1 query problems
-   ✅ Proper indexing used

**Actual Result:** ************\_\_\_************

**Status:** ☐ Pass ☐ Fail ☐ Blocked

---

## Test Results Summary

### Test Execution Statistics

**Total Test Cases:** 30+  
**Executed:** **\_** / **\_**  
**Passed:** **\_** / **\_**  
**Failed:** **\_** / **\_**  
**Blocked:** **\_** / **\_**

**Pass Rate:** **\_**%

---

### Test Results by Category

| Category       | Total | Passed   | Failed   | Pass %    |
| -------------- | ----- | -------- | -------- | --------- |
| Authentication | 6     | \_\_\_\_ | \_\_\_\_ | \_\_\_\_% |
| MQTT Scanner   | 6     | \_\_\_\_ | \_\_\_\_ | \_\_\_\_% |
| Sensor Data    | 3     | \_\_\_\_ | \_\_\_\_ | \_\_\_\_% |
| Dashboard      | 4     | \_\_\_\_ | \_\_\_\_ | \_\_\_\_% |
| Security       | 5     | \_\_\_\_ | \_\_\_\_ | \_\_\_\_% |
| Integration    | 3     | \_\_\_\_ | \_\_\_\_ | \_\_\_\_% |
| Performance    | 3     | \_\_\_\_ | \_\_\_\_ | \_\_\_\_% |

---

### Critical Issues Found

**Priority 1 (Blocker):**

1. ***
2. ***

**Priority 2 (High):**

1. ***
2. ***

**Priority 3 (Medium):**

1. ***
2. ***

**Priority 4 (Low):**

1. ***
2. ***

---

### Known Limitations

1. **Network Speed:** Scan speed depends on network latency
2. **Browser Compatibility:** Tested primarily on Chrome
3. **Mobile:** Limited mobile device testing
4. **Load Testing:** Not tested with >20 concurrent users

---

### Recommendations

**Before Production:**

-   [ ] Fix all Priority 1 issues
-   [ ] Resolve 80%+ of Priority 2 issues
-   [ ] Complete security testing
-   [ ] Perform load testing
-   [ ] Test on multiple browsers
-   [ ] Conduct user acceptance testing

**Improvements:**

-   ***
-   ***
-   ***

---

## Bug Report Template

**Bug ID:** BUG-001  
**Test Case:** TC-XXX-XX  
**Severity:** ☐ Critical ☐ High ☐ Medium ☐ Low  
**Priority:** ☐ P1 ☐ P2 ☐ P3 ☐ P4

**Summary:**

---

**Steps to Reproduce:**

1. ***
2. ***
3. ***

**Expected Result:**

---

**Actual Result:**

---

**Environment:**

-   Browser: ******************\_\_\_******************
-   OS: ******************\_\_\_******************
-   Laravel Version: ******************\_\_\_******************

**Screenshots/Logs:**

---

**Workaround:**

---

**Status:** ☐ Open ☐ In Progress ☐ Fixed ☐ Won't Fix

---

## Approval

**Tester Name:** ************\_\_\_************  
**Tester Signature:** ************\_\_\_************  
**Date:** ************\_\_\_************

**Reviewer Name:** ************\_\_\_************  
**Reviewer Signature:** ************\_\_\_************  
**Date:** ************\_\_\_************

**Approved for Production:** ☐ Yes ☐ No ☐ Conditional

**Conditions (if any):**

---

---

---

## Appendix

### Test Data

**Test User Credentials:**

```
User 1: testuser1@example.com / SecurePass123!
User 2: testuser2@example.com / SecurePass123!
Admin: admin@example.com / Admin123!@#
```

**MQTT Test Credentials:**

```
Username: mqtt@example.com
Password: testpass
```

**Test Network Ranges:**

```
Single IP: 192.168.100.56
Small Range: 192.168.100.0/28 (16 IPs)
Medium Range: 192.168.100.0/24 (256 IPs)
```

### Useful Commands

```powershell
# Check system status
docker ps
curl http://127.0.0.1:5000/health
curl http://127.0.0.1:8000/up

# Clear test data
php artisan migrate:fresh
python clear_retained.py

# View logs
docker-compose logs -f
tail -f storage/logs/laravel.log

# Database queries
sqlite3 database/database.sqlite "SELECT COUNT(*) FROM users;"
sqlite3 database/database.sqlite "SELECT COUNT(*) FROM sensor_readings;"
```

---

**End of Functionality Testing Document**
