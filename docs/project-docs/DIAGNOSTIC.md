# DIAGNOSTIC CHECKLIST - Run this first!

## Step 1: Check if ESP32 is Running

Open ESP32 serial monitor and look for these messages:

```
[SECURE DHT] ✓ Published: {"temp_c":31.8,"hum_pct":64.0}
[SECURE LDR] ✓ Published: {"ldr_pct":47.3,"ldr_raw":1938}
[INSECURE PIR] ✓ Published: {"pir":1}
```

**If you DON'T see these messages:**

-   ESP32 is not running or not connected to WiFi
-   Check WiFi credentials in esp32_mixed_security.ino
-   Reupload the code to ESP32

---

## Step 2: Verify MQTT Broker IP

Check esp32_mixed_security.ino line 28:

```cpp
const char* mqtt_server = "192.168.100.56";
```

**This MUST match your PC's IP address where Docker is running!**

To find your IP:

```powershell
ipconfig
# Look for "IPv4 Address" on your active network adapter
```

If your IP is different, update the ESP32 code and reupload.

---

## Step 3: Check Docker Containers

```powershell
docker ps
```

You should see:

-   `mosq_insecure` on port 1883
-   `mosq_secure` on port 8883

**If containers are not running:**

```powershell
cd mqtt-brokers
docker-compose up -d
```

---

## Step 4: Test MQTT Traffic

```powershell
python quick_test_mqtt.py
```

This will test BOTH ports and tell you if ANY messages are being received.

**Expected output:**

```
✅ Port 1883 (insecure): TRAFFIC DETECTED
✅ Port 8883 (secure): TRAFFIC DETECTED
```

**If NO TRAFFIC detected:**

-   ESP32 is not publishing
-   Check steps 1 and 2 above

---

## Step 5: Test Fixed Python Script

```powershell
python test_esp32_sensors.py
```

This should now work without SSL errors.

**Expected:** You see messages from all 3 sensors every 3 seconds.

---

## Step 6: Test Dashboard

1. Make sure Flask is running:

    ```powershell
    cd mqtt-scanner
    python app.py
    ```

2. Make sure Laravel is running:

    ```powershell
    php artisan serve
    ```

3. Open browser: http://127.0.0.1:8000/dashboard

4. Enter scan parameters:

    - **Target**: `192.168.100.56` (NOT 192.168.100.140!)
    - **Username**: `testuser`
    - **Password**: `testpass`

5. Click "Start Scan"

6. Wait 10-15 seconds

---

## Common Issues & Solutions

### Issue: "SSL: CERTIFICATE_VERIFY_FAILED"

**Fixed!** The test script now accepts self-signed certificates.

### Issue: "No messages received"

**Cause:** ESP32 is not publishing
**Solution:**

1. Check ESP32 serial monitor
2. Verify ESP32 WiFi connection
3. Verify MQTT broker IP matches your PC's IP

### Issue: "500 Internal Server Error" on dashboard

**Fixed!** Routes now use Flask scanner instead of direct PHP MQTT.

### Issue: Dashboard shows "Maximum execution time exceeded"

**Cause:** Scanning wrong IP (127.0.0.1 instead of 192.168.100.56)
**Solution:** Always scan `192.168.100.56` (where Docker containers are)

### Issue: Port 8883 shows "Not Authorized"

**Cause:** Missing credentials
**Solution:** Always enter username `testuser` and password `testpass` in the dashboard

---

## Critical Configuration

### ESP32 Configuration (esp32_mixed_security.ino)

```cpp
const char* mqtt_server = "192.168.100.56";  // ← YOUR PC'S IP!
const char* mqtt_user = "testuser";
const char* mqtt_pass = "testpass";
```

### Dashboard Scan Target

-   ✅ Correct: `192.168.100.56` (your PC where Docker runs)
-   ❌ Wrong: `127.0.0.1` (localhost - Docker can't route to itself)
-   ❌ Wrong: `192.168.100.140` (ESP32 IP - we're scanning the broker, not ESP32)

---

## What to Scan?

**You are scanning the MQTT BROKERS, not the ESP32!**

-   ESP32 IP: `192.168.100.140` ← Device that PUBLISHES sensor data
-   Broker IP: `192.168.100.56` ← Server that RECEIVES data (what we scan)

The scanner connects to the brokers and listens for any publishers (ESP32).

---

## Testing Order

1. ✅ Run `python quick_test_mqtt.py` - Verify MQTT traffic exists
2. ✅ Run `python test_esp32_sensors.py` - Verify all 3 sensors detected
3. ✅ Test dashboard with target `192.168.100.56` and credentials

---

## Success Criteria

✅ quick_test_mqtt.py detects traffic on both ports  
✅ test_esp32_sensors.py shows all 3 sensors  
✅ Dashboard scan completes without 500 error  
✅ Dashboard shows 2 secure + 1 insecure connection  
✅ Sensor data displays with actual values

If all above pass → **SYSTEM IS WORKING!** 🎉
