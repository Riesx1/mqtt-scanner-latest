# Laravel Dashboard - Error Proof Display Guide

## Overview

This guide shows how the MQTT Scanner Laravel dashboard displays **actual Python socket errors** as proof that ports are unreachable/timeout. The error messages you see in the web interface are **real exceptions** caught by the Python scanner, not simulated.

---

## How It Works

### 1. **Architecture Flow**

```
User Browser
    → Laravel MqttScannerController
    → Flask API (Port 5000)
    → Python scanner.py
    → Captures socket.timeout exception
    → Returns error to Flask
    → Laravel displays in dashboard
```

### 2. **Error Capture in scanner.py**

The Python scanner catches real socket errors:

```python
def try_mqtt_connect(host, port, timeout=2):
    try:
        # Attempt TCP connection
        sock = socket.create_connection((host, port), timeout=timeout)
        sock.close()

        # Attempt MQTT connection
        client = mqtt.Client()
        client.connect(host, port, keepalive=10)

    except socket.timeout:
        # REAL ERROR CAUGHT HERE ✅
        evidence = "socket.timeout: timed out"
        classification = "closed_or_unreachable"

    except ConnectionRefusedError:
        # REAL ERROR CAUGHT HERE ✅
        evidence = "Connection refused - no broker listening"
        classification = "closed_or_unreachable"
```

**Key Point**: These are **genuine Python exceptions**, not fabricated messages.

---

## Testing in Laravel Dashboard

### Step 1: Start Flask API

```powershell
cd "s:\Unikl\SEM 6\IPB49906 - FINAL YEAR PROJECT 2\Develop\mqtt-scanner-fyp2-main (2)\mqtt-scanner-fyp2-main\mqtt-scanner"
python app.py
```

Expected output:

```
 * Running on http://127.0.0.1:5000
```

### Step 2: Start Laravel Server

Open a new terminal:

```powershell
cd "s:\Unikl\SEM 6\IPB49906 - FINAL YEAR PROJECT 2\Develop\mqtt-scanner-fyp2-main (2)\mqtt-scanner-fyp2-main"
php artisan serve
```

Expected output:

```
Server running on [http://127.0.0.1:8000]
```

### Step 3: Open Dashboard

Navigate to: `http://127.0.0.1:8000/mqtt-scanner`

### Step 4: Test Unreachable IP

In the scan form, enter:

-   **Target IP**: `192.168.100.254` (unreachable)
-   **Port**: `1883`
-   Click **"Start Scan"**

### Step 5: View Error Proof

Click on the result row to see detailed report. You will see:

```
🚨 ERROR EVIDENCE (PROOF OF UNREACHABLE)
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
This outcome was determined by capturing actual Python
socket errors during connection attempts. The scanner
detected:

Error Type: socket.timeout or ConnectionRefusedError
Python Exception: "socket.timeout: timed out"
What This Proves: The target IP/port combination is
                  genuinely unreachable (no response
                  within 2 seconds timeout period).

Technical Details:
• Scanner uses socket.create_connection() with 2s timeout
• No TCP handshake completion = socket.timeout exception
• No MQTT broker listening = Connection refused
• This is the same error Python's paho-mqtt library throws

Verification Command (run in Python):
    import socket
    socket.create_connection(('192.168.100.254', 1883), timeout=2)
    # This will raise: socket.timeout: timed out
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
```

---

## Testing Different Error Types

### Test 1: Timeout Error (No Host)

**Target**: `192.168.100.254` (non-existent IP)  
**Expected Error**: `socket.timeout: timed out`  
**Proof**: No host responds → TCP handshake never completes

### Test 2: Connection Refused (Host Up, Port Closed)

**Target**: `127.0.0.1:9999` (closed port)  
**Expected Error**: `Connection refused`  
**Proof**: Host responds with RST packet → active refusal

### Test 3: External Timeout

**Target**: `8.8.8.8:1883` (Google DNS, no MQTT)  
**Expected Error**: `socket.timeout: timed out`  
**Proof**: Host ignores port → no response within 2 seconds

---

## Laravel Controller Implementation

The Laravel controller (`app/Http/Controllers/MqttScannerController.php`) handles this automatically:

```php
public function scan(Request $request)
{
    // Send request to Flask API
    $response = Http::timeout(30)->post('http://127.0.0.1:5000/api/scan', [
        'target' => $target,
        'port' => $port,
    ]);

    // Return JSON response (includes outcome with error evidence)
    return response()->json($response->json());
}
```

**No extra code needed** - the outcome data (including error evidence) flows through automatically!

---

## Dashboard Display Code

The dashboard's `showDetails()` function checks for unreachable outcomes:

```javascript
// 🚨 SPECIAL SECTION: Show Error Evidence for Unreachable/Timeout
if (
    r.outcome.label.toLowerCase().includes("unreachable") ||
    r.outcome.label.toLowerCase().includes("timeout") ||
    r.classification === "closed_or_unreachable"
) {
    text += `🚨 ERROR EVIDENCE (PROOF OF UNREACHABLE)
Error Type: socket.timeout or ConnectionRefusedError
Python Exception: "${r.outcome.evidence_signal}"
...
`;
}
```

---

## Verification Methods

### Method 1: Browser Developer Tools

1. Open browser DevTools (F12)
2. Go to **Network** tab
3. Scan an unreachable IP
4. Click on the `/api/scan` request
5. View **Response** tab
6. You'll see JSON with `outcome.evidence_signal` containing the actual Python error

### Method 2: Laravel Logs

```powershell
tail -f storage/logs/laravel.log
```

Scan an unreachable IP, and you'll see:

```
MQTT Scanner: Target 192.168.100.254:1883 -> closed_or_unreachable
```

### Method 3: Flask Console Output

The Flask terminal will show:

```
Classification: closed_or_unreachable
Evidence: socket.timeout: timed out
```

---

## Common Questions

### Q1: Are these real errors or just messages?

**A**: Real Python exceptions. The scanner uses `try/except` blocks that catch actual `socket.timeout` and `ConnectionRefusedError` exceptions from the Python socket library.

### Q2: Can I verify this independently?

**A**: Yes! Open Python and run:

```python
import socket
socket.create_connection(('192.168.100.254', 1883), timeout=2)
```

You'll get the exact same error: `socket.timeout: timed out`

### Q3: What if the error doesn't show in Laravel?

**Check**:

1. Flask API is running (`python app.py`)
2. Network tab in browser shows `/api/scan` response
3. Response JSON contains `outcome.evidence_signal`
4. Dashboard JavaScript isn't throwing errors (check Console tab)

### Q4: Can I customize the error display?

**A**: Yes! Edit the `showDetails()` function in `mqtt-scanner/templates/dashboard_pretty.html` (lines 357-395). You can change formatting, add colors, or modify the text.

---

## Screenshot Locations for Documentation

To capture proof for your FYP report:

### Screenshot 1: Dashboard Scan Results

-   **Action**: Scan `192.168.100.254:1883`
-   **Capture**: Results table showing red "Unreachable/Timeout" badge
-   **Purpose**: Shows outcome classification in main view

### Screenshot 2: Error Evidence Section

-   **Action**: Click on unreachable result to open details
-   **Capture**: The entire "🚨 ERROR EVIDENCE" section
-   **Purpose**: Shows actual Python exception message as proof

### Screenshot 3: Browser Network Tab

-   **Action**: Scan unreachable IP with DevTools open
-   **Capture**: Response JSON showing `outcome.evidence_signal`
-   **Purpose**: Technical proof that real errors are being captured

### Screenshot 4: Side-by-Side Comparison

-   **Left**: Python terminal running `show_errors.py` with exception
-   **Right**: Laravel dashboard showing same error message
-   **Purpose**: Proves Laravel displays the same real errors as Python script

---

## Troubleshooting

### Issue: No Error Evidence Section Appears

**Solution**:

-   Check if `r.outcome` exists in the response
-   Verify `r.outcome.label` contains "Unreachable" or "Timeout"
-   Look for JavaScript errors in browser console

### Issue: Flask API Not Responding

**Solution**:

```powershell
# Check if Flask is running
netstat -an | findstr :5000

# Restart Flask
cd mqtt-scanner
python app.py
```

### Issue: Laravel Shows "Connection Error"

**Solution**:

-   Flask API must be running on port 5000
-   Check firewall isn't blocking localhost:5000
-   Verify `MqttScannerController` timeout is sufficient (default 30s)

---

## Summary

✅ **Real Errors**: Dashboard displays actual Python socket exceptions  
✅ **Automatic**: No extra code needed - errors flow through API  
✅ **Verifiable**: Can reproduce same errors in standalone Python  
✅ **Visual Proof**: Clear "🚨 ERROR EVIDENCE" section in details view  
✅ **Documentation Ready**: Screenshots prove error detection works

**The Laravel dashboard successfully proves unreachable ports by showing real Python socket.timeout errors!** 🎉
