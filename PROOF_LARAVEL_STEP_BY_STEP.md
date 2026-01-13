# Complete Step-by-Step Guide: Prove Unreachable Port in Laravel Dashboard

## Current Situation

-   ✅ Flask API is running on port 5000
-   ✅ Scanner code updated with outcome categorization
-   ❌ Laravel not started yet
-   ❌ You're viewing Flask frontend, not Laravel

---

## Step 1: Start Laravel Server

Open a **NEW PowerShell terminal** and run:

```powershell
cd "s:\Unikl\SEM 6\IPB49906 - FINAL YEAR PROJECT 2\Develop\mqtt-scanner-fyp2-main (2)\mqtt-scanner-fyp2-main"
php artisan serve
```

**Expected Output:**

```
   INFO  Server running on [http://127.0.0.1:8000].

  Press Ctrl+C to stop the server
```

✅ **Leave this terminal open** - don't close it!

---

## Step 2: Open Laravel Dashboard in Browser

**Close the current browser tab** (that's showing Flask on port 5000)

Open a **NEW browser tab** and go to:

```
http://127.0.0.1:8000/mqtt-scanner
```

**You should see:**

-   Laravel interface (different design from Flask)
-   "MQTT Security Scanner Dashboard" title
-   Input fields for scan

---

## Step 3: Scan Unreachable IP

In the Laravel dashboard:

1. **Target IP**: Enter `192.168.100.254`
2. **Username**: Leave empty
3. **Password**: Leave empty
4. Click **"Scan"** button

**Wait 4-5 seconds** (timeout duration)

---

## Step 4: View Results

After scan completes, you'll see a table with 2 rows:

```
┌─────────────────┬──────┬────────────────────┬─────────────────┐
│ IP              │ Port │ Outcome            │ Meaning         │
├─────────────────┼──────┼────────────────────┼─────────────────┤
│ 192.168.100.254 │ 1883 │ 🔴 Unreachable/... │ Port not resp...│
│ 192.168.100.254 │ 8883 │ 🔴 Unreachable/... │ Port not resp...│
└─────────────────┴──────┴────────────────────┴─────────────────┘
```

---

## Step 5: Click Row to See Error Proof

Click on **either row** in the results table.

**A detail panel will appear below** showing:

```
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

📊 OUTCOME ANALYSIS
Outcome Label: Unreachable / Timeout
Meaning: Port not responding or firewall blocking connections
Evidence Signal: socket.timeout: timed out
Security Implication: Target cannot be reached for further testing

🚨 ERROR EVIDENCE (PROOF OF UNREACHABLE)
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
This outcome was determined by capturing actual Python
socket errors during connection attempts. The scanner
detected:

Error Type: socket.timeout or ConnectionRefusedError
Python Exception: "socket.timeout: timed out"
                   ↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑
              THIS IS A REAL PYTHON ERROR!

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

🔒 SECURITY ASSESSMENT
Risk Level: 🔴 UNKNOWN
...
```

---

## Step 6: Take Screenshots for Documentation

### Screenshot 1: Results Table

-   **Capture**: Full browser window showing the results table
-   **Highlight**: The red "Unreachable/Timeout" badges

### Screenshot 2: Error Evidence Detail

-   **Capture**: The details panel showing the "🚨 ERROR EVIDENCE" section
-   **Highlight**: The line showing `Python Exception: "socket.timeout: timed out"`

### Screenshot 3: Browser DevTools (Optional)

-   Press **F12** to open DevTools
-   Go to **Network** tab
-   Clear the log
-   Run the scan again
-   Click on the `/api/scan` request (or similar)
-   Go to **Response** tab
-   **Capture**: The JSON response showing `outcome.evidence_signal`

---

## Troubleshooting

### Problem 1: Laravel shows "Connection Error"

**Solution**:

```powershell
# Check if Flask is running
netstat -an | findstr :5000

# If not, start Flask:
cd mqtt-scanner
python app.py
```

### Problem 2: No results appear

**Check**:

1. Flask terminal shows scan activity (logs should appear)
2. Laravel terminal shows no errors
3. Browser console (F12) shows no JavaScript errors

### Problem 3: "404 Not Found" or Laravel error

**Solution**:

```powershell
# Make sure you're using the correct URL
http://127.0.0.1:8000/mqtt-scanner
# NOT: http://127.0.0.1:5000 (that's Flask)
```

### Problem 4: Results show but no "Error Evidence" section

**Check**:

1. Flask was restarted after I updated scanner.py
2. The outcome field exists in the response (check DevTools)
3. The classification is `closed_or_unreachable`

---

## Verification: Prove It's a Real Error

Open PowerShell and run:

```powershell
python -c "import socket; socket.create_connection(('192.168.100.254', 1883), timeout=2)"
```

**You'll get the exact same error:**

```
socket.timeout: timed out
```

**This proves the dashboard shows REAL Python exceptions!** ✅

---

## Summary Checklist

-   [ ] Flask running on port 5000
-   [ ] Laravel running on port 8000
-   [ ] Browser on http://127.0.0.1:8000/mqtt-scanner (NOT port 5000)
-   [ ] Scan 192.168.100.254 with no credentials
-   [ ] Results appear in table (2 rows)
-   [ ] Click row to see details
-   [ ] "🚨 ERROR EVIDENCE" section visible
-   [ ] Shows: `socket.timeout: timed out`
-   [ ] Screenshot taken

---

## Quick Test Command

To verify everything is working, run this in PowerShell:

```powershell
# Test Flask API directly
Invoke-RestMethod -Uri "http://127.0.0.1:5000/api/scan" -Method POST -Headers @{"X-API-Key"="your-secret-key-here"} -ContentType "application/json" -Body '{"target":"192.168.100.254","port":1883}' | ConvertTo-Json -Depth 10
```

**Expected**: You should see JSON with `outcome` containing `evidence_signal: "socket.timeout: timed out"`

---

## Current Status

✅ **Ready to test:**

-   Flask API: Running with outcome categorization
-   Scanner: Updated with categorize_outcome() function
-   Dashboard: Has error evidence display section

📌 **Next action:**

1. Start Laravel (`php artisan serve`)
2. Open http://127.0.0.1:8000/mqtt-scanner
3. Scan 192.168.100.254
4. View error proof!
