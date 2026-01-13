# Laravel Error Proof Testing Guide

## Overview

This guide shows how to verify that unreachable/timeout errors are properly displayed in the Laravel dashboard with detailed error evidence.

## Changes Made

### 1. Added `getOutcomeBadge()` Function

**Location:** `resources/views/dashboard.blade.php` (around line 720)

This function creates colored badges based on the scan outcome:

-   🚫 Network Unreachable (Gray)
-   ⏱️ Connection Timeout (Red)
-   🛑 Connection Refused (Dark Red)
-   🔐 Auth Required (Yellow)
-   🔒 Auth Failed (Orange)
-   ✅ Anonymous Success (Green)

### 2. Added Outcome Section in Details Modal

**Location:** `resources/views/dashboard.blade.php` (in `showDetails()` function)

When you click "Details" on a scan result, you'll now see:

-   **🎯 SCAN OUTCOME ANALYSIS** section with:
    -   Outcome badge with color-coded status
    -   Meaning of the outcome
    -   Security implication
    -   **🚨 ERROR EVIDENCE** box (for errors only) showing the actual error message

## Testing Steps

### Step 1: Ensure Both Servers Are Running

```powershell
# Terminal 1: Flask API (Backend)
cd "s:\Unikl\SEM 6\IPB49906 - FINAL YEAR PROJECT 2\Develop\mqtt-scanner-fyp2-main (2)\mqtt-scanner-fyp2-main\mqtt-scanner"
python app.py

# Terminal 2: Laravel (Frontend)
cd "s:\Unikl\SEM 6\IPB49906 - FINAL YEAR PROJECT 2\Develop\mqtt-scanner-fyp2-main (2)\mqtt-scanner-fyp2-main"
php artisan serve
```

### Step 2: Login to Dashboard

1. Open: http://127.0.0.1:8000/dashboard
2. Login with:
    - Email: `faris02@gmail.com`
    - Password: `Faris02!`

### Step 3: Run Scan with Unreachable IP

1. In the scan form, enter:

    - **Target IP:** `192.168.100.254` (unreachable IP)
    - **Username:** `mqtt@example.com`
    - **Password:** `mqtt_secure_2024`

2. Click **"🚀 Start Scan"**

### Step 4: View Results

**Expected Results Table:**
| IP:Port | Security | Sensor Type | Data | Topic | Messages | Actions |
|---------|----------|-------------|------|-------|----------|---------|
| 192.168.100.254:1883 | Badge showing status | Unknown ❓ | - | - | 0 | **Details** button |
| 192.168.100.254:8883 | Badge showing status | Unknown ❓ | - | - | 0 | **Details** button |

### Step 5: Click "Details" Button

**Expected Details Modal:**

```
╔═════════════════════════════════════════════════╗
║         MQTT SECURITY SCAN REPORT              ║
╚═════════════════════════════════════════════════╝

📍 TARGET INFORMATION
IP Address: 192.168.100.254
Port: 1883 (Insecure MQTT)
Classification: closed_or_unreachable

🎯 SCAN OUTCOME ANALYSIS
Outcome: 🛑 Closed / Refused
Meaning: Port is closed or service is not listening
Security Implication: Lower exposure, MQTT not reachable on that port

🚨 ERROR EVIDENCE (PROOF OF UNREACHABLE)
Technical Error Signal Captured:
┌─────────────────────────────────────────────┐
│ Connection refused quickly                   │
└─────────────────────────────────────────────┘

⚠️ This proves the port is actively refusing
   connections (closed)
```

## What Each Outcome Type Shows

### 1. Connection Timeout

```
Outcome: ⏱️ Connection Timeout
Evidence: socket.timeout: timed out
Proof: This proves the port is not responding (filtered/timeout)
```

### 2. Connection Refused

```
Outcome: 🛑 Closed / Refused
Evidence: Connection refused quickly
Proof: This proves the port is actively refusing connections (closed)
```

### 3. Network Unreachable

```
Outcome: 🚫 Network Unreachable
Evidence: [Errno 101] Network is unreachable
Proof: This proves the port is unreachable from this network position
```

### 4. Auth Required

```
Outcome: 🔐 Auth Required
Evidence: CONNACK code=5 (Not authorized)
Note: No error box (this is expected behavior, not an error)
```

### 5. Anonymous Success

```
Outcome: ✅ Anonymous Success
Evidence: Connected successfully without credentials
Note: No error box (success case)
```

## Troubleshooting

### Issue: "0 results" shown

**Solution:**

1. Check if Flask API is running (http://127.0.0.1:5000)
2. Check browser console for errors (F12)
3. Check Flask terminal for request logs

### Issue: No outcome data in details

**Solution:**

1. Clear browser cache (Ctrl+Shift+Delete)
2. Hard refresh (Ctrl+F5)
3. Check if `sensor.outcome` exists in browser console

### Issue: Modal not showing

**Solution:**

1. Check browser console for JavaScript errors
2. Ensure `globalSensors` array is populated
3. Try clicking different result rows

## Browser Console Debugging

Open Developer Tools (F12) and run:

```javascript
// Check if outcomes are present
console.log(globalSensors);
// Should show array with outcome fields

// Check specific sensor outcome
console.log(globalSensors[0].outcome);
// Should show: {label, meaning, evidence_signal, security_implication}
```

## Expected API Response

The Flask API should return JSON like:

```json
{
    "results": [
        {
            "ip": "192.168.100.254",
            "port": 1883,
            "classification": "closed_or_unreachable",
            "outcome": {
                "label": "Closed / Refused",
                "meaning": "Port is closed or service is not listening",
                "evidence_signal": "Connection refused quickly",
                "security_implication": "Lower exposure, MQTT not reachable on that port"
            }
        }
    ]
}
```

## Verification Checklist

-   [ ] Both Flask and Laravel servers running
-   [ ] Logged into http://127.0.0.1:8000/dashboard
-   [ ] Scan completed successfully
-   [ ] Results table shows IP:Port combinations
-   [ ] Clicking "Details" opens modal
-   [ ] Modal shows "🎯 SCAN OUTCOME ANALYSIS" section
-   [ ] For errors: "🚨 ERROR EVIDENCE" box is visible
-   [ ] Error box shows actual error message (e.g., "Connection refused quickly")
-   [ ] Outcome badge has appropriate color and icon

## Success Criteria

✅ **Test Passed If:**

1. Unreachable IP scan returns results
2. Details modal opens when clicking "Details"
3. "🎯 SCAN OUTCOME ANALYSIS" section is visible
4. For error outcomes, "🚨 ERROR EVIDENCE" box appears
5. Error box contains the actual error message
6. Outcome badge is color-coded correctly

## Additional Test Cases

### Test Case 1: Valid ESP32 IP

```
IP: 192.168.100.57
Expected Outcome: ✅ Anonymous Success (port 1883) or 🔐 Auth Required (port 8883)
Should NOT show error evidence box
```

### Test Case 2: Google DNS (External)

```
IP: 8.8.8.8
Expected Outcome: ⏱️ Connection Timeout or 🛑 Closed / Refused
Should show error evidence box
```

### Test Case 3: Localhost

```
IP: 127.0.0.1
Expected Outcome: Depends on local MQTT broker
May show error evidence if no local broker
```

## Screenshots to Take

1. Login page
2. Dashboard with scan form
3. Scan in progress
4. Results table showing multiple results
5. Details modal - top section
6. Details modal - "🎯 SCAN OUTCOME ANALYSIS" section
7. Details modal - "🚨 ERROR EVIDENCE" box (for errors)

## Documentation Update

This error proof system is documented in:

-   `CHAPTER_4_SYSTEM_DEVELOPMENT.md` - Section 3.2 (Outcome Categorization)
-   `PROOF_LARAVEL_STEP_BY_STEP.md` - This guide
-   `TESTING_GUIDE.md` - Comprehensive testing procedures

## Developer Notes

**Code Locations:**

-   Outcome generation: `mqtt-scanner/scanner.py` lines 150-225 (`categorize_outcome()`)
-   Badge function: `dashboard.blade.php` line ~720 (`getOutcomeBadge()`)
-   Modal content: `dashboard.blade.php` line ~615 (outcome section in `showDetails()`)

**Data Flow:**

1. Scanner.py generates outcome dict
2. Flask API returns it in JSON
3. MqttScannerController proxies the response
4. dashboard.blade.php displayResults() processes it
5. sensors array includes outcome field
6. showDetails() displays outcome section
