# MQTT Scanner - Testing Guide for Different Outcomes

## Quick Reference Table

| Outcome                   | Test Method                            | IP to Use                       | Port            | Credentials                      | Expected Result       |
| ------------------------- | -------------------------------------- | ------------------------------- | --------------- | -------------------------------- | --------------------- |
| **Connected (1883)**      | Scan running insecure broker           | `127.0.0.1` or `192.168.100.57` | 1883            | None                             | 🔴 High Risk          |
| **Connected (8883)**      | Scan with valid credentials            | `127.0.0.1` or `192.168.100.57` | 8883            | `faris02@gmail.com` / `Faris02!` | 🟡 Verify Certificate |
| **Not Authorised**        | Scan secure broker without credentials | `127.0.0.1` or `192.168.100.57` | 8883            | None                             | 🟢 Good Security      |
| **Not Authorised**        | Scan with wrong credentials            | `127.0.0.1`                     | 8883            | Wrong username/password          | 🟢 Good Security      |
| **TLS Error**             | Broker with cert issues                | Custom setup                    | 8883            | Any                              | 🟠 Config Issue       |
| **Closed / Refused**      | Stopped broker or unused port          | `127.0.0.1`                     | Any closed port | Any                              | ⚪ Port Closed        |
| **Unreachable / Timeout** | Non-existent IP                        | `192.168.100.254`               | Any             | Any                              | ⚪ Network Issue      |
| **Unreachable / Timeout** | Firewalled public IP                   | `8.8.8.8`                       | 1883/8883       | Any                              | ⚪ Blocked/Timeout    |

---

## Detailed Test Commands

### 1. Test Connected (1883) - Insecure

```bash
cd mqtt-scanner
python -c "from scanner import run_scan; import json; print(json.dumps(run_scan('127.0.0.1'), indent=2))"
```

**Expected:** Port 1883 shows "Connected (1883)"

---

### 2. Test Connected (8883) - Secure with Auth

```bash
python -c "from scanner import run_scan; import json; print(json.dumps(run_scan('127.0.0.1', creds={'user': 'faris02@gmail.com', 'pass': 'Faris02!'}), indent=2))"
```

**Expected:** Port 8883 shows "Connected (8883)"

---

### 3. Test Not Authorised - Anonymous Attempt

```bash
python -c "from scanner import run_scan; import json; print(json.dumps(run_scan('127.0.0.1'), indent=2))"
```

**Expected:** Port 8883 shows "Not Authorised / Auth Required"

---

### 4. Test Not Authorised - Wrong Credentials

```bash
python -c "from scanner import run_scan; import json; print(json.dumps(run_scan('127.0.0.1', creds={'user': 'wrong@email.com', 'pass': 'wrongpass'}), indent=2))"
```

**Expected:** Port 8883 shows "Not Authorised / Auth Required"

---

### 5. Test Closed / Refused - Stop Brokers

```bash
# Stop the MQTT brokers
cd ../mqtt-brokers
docker-compose down

# Now scan
cd ../mqtt-scanner
python -c "from scanner import run_scan; import json; print(json.dumps(run_scan('127.0.0.1'), indent=2))"

# Restart brokers when done
cd ../mqtt-brokers
docker-compose up -d
```

**Expected:** Both ports show "Closed / Refused"

---

### 6. Test Unreachable / Timeout - Non-existent IP

```bash
# Test an IP that doesn't exist in your network
python -c "from scanner import run_scan; import json; print(json.dumps(run_scan('192.168.100.254'), indent=2))"
```

**Expected:** Both ports show "Unreachable / Timeout"

**Alternative - Public IP:**

```bash
python -c "from scanner import run_scan; import json; print(json.dumps(run_scan('8.8.8.8'), indent=2))"
```

---

### 7. Test TLS Error - Certificate Issues

**Method 1: Use expired certificate (requires broker reconfiguration)**

1. Generate an expired certificate for your broker
2. Update `mqtt-brokers/secure/certs/` with expired cert
3. Restart broker: `docker-compose restart mosquitto_secure`
4. Scan: `run_scan('127.0.0.1')`

**Method 2: Enable strict certificate validation**
Modify `scanner.py` line ~215:

```python
# Change from:
ctx.verify_mode = ssl.CERT_NONE

# To:
ctx.verify_mode = ssl.CERT_REQUIRED
ctx.check_hostname = True
```

Then scan: Your self-signed certificate will fail validation.

**Method 3: Test against known bad SSL server**

```bash
python -c "from scanner import try_mqtt_connect; import json; print(json.dumps(try_mqtt_connect('expired.badssl.com', 8883, use_tls=True), indent=2))"
```

---

## Complete Test Script

Run all tests at once:

```bash
cd mqtt-scanner
python test_all_outcomes.py
```

---

## Practical Testing Scenarios

### Scenario A: Test Your Local Setup (3 outcomes)

```bash
# This tests your current running brokers
python test_outcomes.py
```

Results:

-   ✅ Connected (1883) - port 1883
-   ✅ Connected (8883) - port 8883 with credentials
-   ✅ Not Authorised - port 8883 without credentials

---

### Scenario B: Test Network Issues (2 outcomes)

```bash
# Test unreachable IPs
python -c "
from scanner import run_scan
import json

# Non-existent IP
results = run_scan('192.168.100.254')
print('Non-existent IP:', json.dumps(results, indent=2))

# Public IP blocking MQTT
results = run_scan('8.8.8.8')
print('Public IP:', json.dumps(results, indent=2))
"
```

Results:

-   ✅ Unreachable / Timeout

---

### Scenario C: Test Port Closure (1 outcome)

```bash
# Stop brokers
docker-compose -f ../mqtt-brokers/docker-compose.yml down

# Scan
python -c "from scanner import run_scan; import json; print(json.dumps(run_scan('127.0.0.1'), indent=2))"

# Restart
docker-compose -f ../mqtt-brokers/docker-compose.yml up -d
```

Results:

-   ✅ Closed / Refused

---

## Environment-Specific IPs

### Your Current Setup:

-   **Local Broker:** `127.0.0.1` or `192.168.100.57`
-   **Insecure Port:** `1883`
-   **Secure Port:** `8883`
-   **Valid Credentials:** `faris02@gmail.com` / `Faris02!`

### Test Different Networks:

-   **Non-existent IP:** `192.168.100.254` (change last octet to unused IP)
-   **Another subnet:** `192.168.1.1` (if you're on 192.168.100.x)
-   **Public DNS:** `8.8.8.8` (Google), `1.1.1.1` (Cloudflare)

---

## Troubleshooting

### If you don't see expected outcomes:

**Problem:** All scans show "Connected"

-   ✅ Your brokers are running correctly
-   ❌ To test other outcomes, try different IPs or stop brokers

**Problem:** All scans show "Unreachable / Timeout"

-   ❌ Brokers may be down: `docker-compose ps`
-   ❌ Wrong IP address
-   ❌ Firewall blocking connections

**Problem:** "TLS Error" never appears

-   Your scanner uses `setInsecure()` which bypasses certificate validation
-   To test TLS errors, modify scanner.py to enable strict validation

---

## Summary: Easiest Way to Test Each Outcome

| Outcome          | Easiest Method                                                                 |
| ---------------- | ------------------------------------------------------------------------------ |
| Connected (1883) | `run_scan('127.0.0.1')` - check port 1883                                      |
| Connected (8883) | `run_scan('127.0.0.1', creds={'user':'faris02@gmail.com', 'pass':'Faris02!'})` |
| Not Authorised   | `run_scan('127.0.0.1')` - check port 8883                                      |
| Closed / Refused | `docker-compose down` then `run_scan('127.0.0.1')`                             |
| Unreachable      | `run_scan('192.168.100.254')` or `run_scan('8.8.8.8')`                         |
| TLS Error        | Modify scanner.py to enable strict cert validation                             |
