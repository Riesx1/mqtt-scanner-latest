# 🔒 Authentication Failures Detection - Documentation

## Overview

The **Auth Failures** card is a security metric displayed on the dashboard that shows how many MQTT brokers rejected connection attempts due to authentication failures. This feature helps identify:

-   Secured brokers that require credentials
-   Misconfigured credentials
-   Unauthorized access attempts
-   Security posture of MQTT brokers on the network

---

## 📊 What is Auth Failures?

**Auth Failures** counts the number of MQTT broker connections that failed specifically due to **authentication errors** (not authorized). This happens when:

1. **Wrong credentials provided** - Incorrect username or password
2. **No credentials provided** - Trying to connect to a broker that requires authentication
3. **Expired credentials** - Credentials that are no longer valid

---

## 🎯 How It Works

### Detection Process

When the scanner attempts to connect to an MQTT broker:

```
1. Scanner connects to broker (e.g., 192.168.100.56:8883)
2. Provides credentials (if any)
3. Broker responds with connection result code:
   - rc=0 or "Success" → Connection accepted ✅
   - rc=5 or "Not authorized" → Authentication failed ❌
```

### Classification Logic

The scanner classifies each broker connection:

| Connection Result      | Classification              | Counted As       |
| ---------------------- | --------------------------- | ---------------- |
| Connected successfully | `open_or_auth_ok`           | **Open Broker**  |
| Authentication failed  | `not_authorized`            | **Auth Failure** |
| Connection timeout     | `unreachable_or_firewalled` | Neither          |
| Connection refused     | `unreachable_or_firewalled` | Neither          |

---

## 🧪 Testing Auth Failures

### Test 1: Wrong Password (Should Show Auth Failures = 1)

**Steps:**

1. Open dashboard at http://127.0.0.1:8000
2. Enter scan details:
    - **Target IP:** `192.168.100.0/24`
    - **Username:** `faris02@gmail.com`
    - **Password:** `wrongpassword` ← **Intentionally wrong**
3. Click **"Start Scan"**

**Expected Result:**

```
📊 Total Scanned: 2
🔓 Open Brokers: 1 (PIR sensor on port 1883)
🔒 Auth Failures: 1 (Secure broker on port 8883)
```

**Scan Results Table:**
| IP:Port | Security | Sensor | Status |
|---------|----------|--------|--------|
| 192.168.100.56:1883 | Plain | PIR (Motion) | ✅ Connected |
| 192.168.100.56:8883 | TLS | Access Denied | ❌ **Authentication Failed** |

---

### Test 2: No Credentials (Should Show Auth Failures = 1)

**Steps:**

1. Clear the credential fields:
    - **Username:** _(leave empty)_
    - **Password:** _(leave empty)_
2. Click **"Start Scan"**

**Expected Result:**

```
📊 Total Scanned: 2
🔓 Open Brokers: 1
🔒 Auth Failures: 1
```

**Why:** The secure broker (port 8883) requires authentication, so connecting without credentials fails.

---

### Test 3: Correct Credentials (Should Show Auth Failures = 0)

**Steps:**

1. Enter correct credentials:
    - **Username:** `faris02@gmail.com`
    - **Password:** `faris123` ← **Correct password**
2. Click **"Start Scan"**

**Expected Result:**

```
📊 Total Scanned: 2-3 (depending on ESP32 status)
🔓 Open Brokers: 2-3
🔒 Auth Failures: 0 ← All authenticated successfully
```

**Scan Results Table (if ESP32 is publishing):**
| IP:Port | Security | Sensor | Status |
|---------|----------|--------|--------|
| 192.168.100.56:1883 | Plain | PIR (Motion) | ✅ Motion Detected |
| 192.168.100.56:8883 | TLS | DHT11 (Temp/Humidity) | ✅ Temp: 28.5°C, Hum: 65% |
| 192.168.100.56:8883 | TLS | LDR (Light) | ✅ Light: 30% |

---

## 🔐 Security Implications

### What Auth Failures Tell You

#### 🟢 Auth Failures = 0 (All Scans Successful)

-   **Good:** All brokers are accessible with provided credentials
-   **Neutral:** If no credentials provided and failures = 0, brokers allow anonymous access
-   **Action:** Verify if anonymous access is intentional

#### 🟡 Auth Failures = 1-2 (Some Failures)

-   **Expected:** Some brokers require authentication, others don't
-   **Mixed Security:** Indicates brokers with different security levels
-   **Action:** Review which brokers require authentication

#### 🔴 Auth Failures = All Scanned

-   **Issue:** No brokers are accessible with provided credentials
-   **Causes:** Wrong credentials, network issues, or all brokers secured
-   **Action:** Verify credentials and connectivity

---

## 📋 Real-World Scenarios

### Scenario 1: Development Environment

```
Total Scanned: 5
Open Brokers: 3 (anonymous brokers for testing)
Auth Failures: 2 (production-like secured brokers)
```

✅ **Expected behavior** - Mix of secured and unsecured for dev/test

---

### Scenario 2: Production Environment

```
Total Scanned: 10
Open Brokers: 0 (correct - scanning with no credentials)
Auth Failures: 10
```

✅ **Good security** - All brokers require authentication

---

### Scenario 3: Security Issue

```
Total Scanned: 8
Open Brokers: 8 (all connected without credentials)
Auth Failures: 0
```

❌ **Security Risk** - No brokers require authentication!

---

## 🛠️ Technical Details

### MQTT Connection Return Codes

| Code  | Name                   | Meaning                     | Scanner Action            |
| ----- | ---------------------- | --------------------------- | ------------------------- |
| 0     | Success                | Connection accepted         | Count as Open Broker      |
| 1     | Unacceptable protocol  | Protocol version mismatch   | Log error                 |
| 2     | Identifier rejected    | Client ID invalid           | Log error                 |
| 3     | Server unavailable     | Broker unavailable          | Mark unreachable          |
| 4     | Bad credentials format | Malformed username/password | Log error                 |
| **5** | **Not authorized**     | **Authentication failed**   | **Count as Auth Failure** |

### Paho MQTT Library Versions

The scanner handles both v1.x and v2.x of paho-mqtt:

**Version 1.x:**

```python
def on_connect(client, userdata, flags, rc):
    if rc == 5:  # Numeric code
        print("Authentication failed")
```

**Version 2.x:**

```python
def on_connect(client, userdata, flags, rc, properties=None):
    if rc == "Not authorized":  # String code
        print("Authentication failed")
```

Our scanner detects both formats automatically.

---

## 🔍 Troubleshooting

### Issue: Auth Failures Always Shows 0

**Possible Causes:**

1. All brokers allow anonymous access
2. Scanner not reaching secure brokers (firewall)
3. Provided credentials are actually correct

**Solutions:**

```bash
# Test authentication manually
cd mqtt-scanner
python test_auth_failures.py
```

Expected output for working auth detection:

```
✅ CORRECT: Authentication failed
✅ Auth failure detected correctly!
```

---

### Issue: Auth Failures = Total Scanned

**Possible Causes:**

1. Wrong credentials provided
2. Username/password format incorrect
3. Broker password file corrupted

**Solutions:**

```bash
# Verify broker credentials
cd mqtt-scanner
python test_broker_auth.py
```

```bash
# Reset broker password (if needed)
cd mqtt-brokers
docker exec mosq_secure mosquitto_passwd -b /mosquitto/config/passwordfile faris02@gmail.com faris123
docker-compose restart secure
```

---

## 📈 Best Practices

### 1. Regular Security Audits

-   Scan network monthly with no credentials
-   **Target:** Auth Failures should equal Total Scanned
-   **Goal:** No anonymous brokers in production

### 2. Credential Management

-   Use strong, unique passwords for each broker
-   Rotate credentials every 90 days
-   Never use default credentials in production

### 3. Monitoring

-   Track Auth Failures over time
-   Alert if Auth Failures drops to 0 unexpectedly
-   Indicates someone may have disabled authentication

### 4. Testing Protocol

```
Before Deployment:
1. Scan without credentials → Expect high Auth Failures ✅
2. Scan with wrong credentials → Expect high Auth Failures ✅
3. Scan with correct credentials → Expect Auth Failures = 0 ✅
```

---

## 🎓 Understanding the Dashboard Cards

### The Three Metrics Relationship

```
┌─────────────────┐
│ Total Scanned   │ ← All MQTT brokers detected on network
│       10        │
└─────────────────┘
        |
        ├─────────────────────────────────┐
        ▼                                 ▼
┌─────────────────┐           ┌─────────────────┐
│ Open Brokers    │           │ Auth Failures   │
│       6         │           │       4         │
└─────────────────┘           └─────────────────┘
   Accessible                  Secured
   (Connected)                 (Rejected)

Open Brokers + Auth Failures ≈ Total Scanned
(Some brokers may be unreachable/offline)
```

---

## 📚 Related Documentation

-   [Security Documentation](../SECURITY_DOCUMENTATION.md)
-   [Testing Guide](../mqtt-scanner/TESTING_GUIDE.md)
-   [ESP32 Setup Guide](../ESP32_SETUP_GUIDE.md)
-   [Quick Start Guide](../docs/project-docs/starthere.md)

---

## 🆘 Support

If Auth Failures detection is not working:

1. **Check scanner version:**

    ```bash
    cd mqtt-scanner
    python -c "import paho.mqtt.client as mqtt; print(mqtt.__version__)"
    ```

2. **Run diagnostic tests:**

    ```bash
    python test_auth_failures.py
    python test_broker_auth.py
    ```

3. **Check broker logs:**

    ```bash
    cd mqtt-brokers
    docker-compose logs secure
    ```

4. **Verify ports are open:**
    ```bash
    netstat -an | findstr "1883 8883"
    ```

---

**Last Updated:** December 20, 2025  
**Version:** 1.0  
**Author:** MQTT Security Scanner Team
