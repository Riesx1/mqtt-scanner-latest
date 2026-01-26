# 🚀 Quick Start - Test Your MQTT Scanner NOW

## Current Status ✅

All services are **RUNNING**:

- ✅ Docker: mosq_insecure (port 1883) + mosq_secure (port 8883)
- ✅ Flask API: http://127.0.0.1:5000
- ✅ Laravel Dashboard: http://127.0.0.1:8000

## Step 1: Test ESP32 Sensors (2 minutes)

Open a **NEW PowerShell terminal** and run:

```powershell
cd "S:\Unikl\SEM 6\IPB49906 - FINAL YEAR PROJECT 2\Develop\MQTTScanner"
python test_esp32_sensors.py
```

### ✅ Expected Output:

```
[01:45:03] 🔒 SECURE - sensors/[USERNAME]/dht_secure
  🌡️ Temp: 31.8°C, Humidity: 64.0%

[01:45:03] 🔒 SECURE - sensors/[USERNAME]/ldr_secure
  💡 Light: 47.3% (Raw: 1938)

[01:45:03] ⚠️ INSECURE - sensors/[USERNAME]/pir_insecure
  👁️ Motion: DETECTED ⚠️
```

**Wait 30 seconds**, then check the final summary:

```
VERIFICATION:
✅ DHT sensor is publishing
✅ LDR sensor is publishing
✅ PIR sensor is publishing
```

### ❌ If No Messages Appear:

1. **Check ESP32 is powered on** and connected to WiFi
2. **Check ESP32 serial monitor** - should show:
    ```
    [SECURE DHT] ✓ Published: {"temp_c":31.8,"hum_pct":64.0}
    [SECURE LDR] ✓ Published: {"ldr_pct":47.3,"ldr_raw":1938}
    [INSECURE PIR] ✓ Published: {"pir":1}
    ```
3. **Verify IP address** in esp32_mixed_security.ino:
    - WiFi IP should match your network
    - MQTT server should be `XXX.XXX.X.X`

---

## Step 2: Test Dashboard (1 minute)

1. **Open browser**: http://127.0.0.1:8000

2. **Login** (if not already logged in)

3. **Go to Dashboard** (you should already be there)

4. **Fill in the scan form**:
    - Target: `XXX.XXX.X.X`
    - Username: `[USERNAME]`
    - Password: `[PASSWORD]`

5. **Click "Start Scan"**

6. **Wait 5-10 seconds** - You should see:
    - Progress bar
    - "Scan completed!" message
    - Summary cards updating
    - Results table appearing

---

## Step 3: Verify Results

### Expected Results Table:

You should see **2-3 rows** showing:

#### Row 1: DHT Sensor (Secure)

- **IP**: XXX.XXX.XXX.XX
- **Port**: 8883
- **Security**: 🔒 Secure (TLS)
- **Sensor Type**: 🌡️ DHT
- **Publisher**: sensors/[USERNAME]/dht_secure
- **Sensor Data**:
    - Temp: 31.8°C
    - Humidity: 64.0%
- **TLS Details**: TLS 1.2+, Self-signed cert

#### Row 2: LDR Sensor (Secure)

- **IP**: XXX.XXX.XXX.XX
- **Port**: 8883
- **Security**: 🔒 Secure (TLS)
- **Sensor Type**: 💡 LDR
- **Publisher**: sensors/[USERNAME]/ldr_secure
- **Sensor Data**: Light: 47.3%
- **TLS Details**: TLS 1.2+, Self-signed cert

#### Row 3: PIR Sensor (Insecure)

- **IP**: XXX.XXX.XXX.XX
- **Port**: 1883
- **Security**: ⚠️ Insecure
- **Sensor Type**: 👁️ PIR
- **Publisher**: sensors/[USERNAME]/pir_insecure
- **Sensor Data**: Motion: DETECTED (or "No Motion")
- **TLS Details**: ⚠️ Unencrypted

### Summary Cards Should Show:

```
┌────────────────┐  ┌────────────────┐  ┌────────────────┐
│ Total Scanned  │  │ Open Brokers   │  │ Auth Failures  │
│       2        │  │       2        │  │       0        │
└────────────────┘  └────────────────┘  └────────────────┘
```

---

## ✅ Success Criteria

- [ ] test_esp32_sensors.py shows all 3 sensors
- [ ] Dashboard scan completes without errors
- [ ] See 2 secure sensors (DHT + LDR) on port 8883
- [ ] See 1 insecure sensor (PIR) on port 1883
- [ ] Sensor data shows actual values (not "N/A")
- [ ] Can export CSV with results

---

## 🐛 Quick Troubleshooting

### Problem: "No sensors detected" in dashboard

**Solution**: Increase listen duration

- The scanner listens for 5 seconds by default
- If ESP32 publishes every 3 seconds, you might miss it
- Try scanning again (ESP32 uses retained messages, so it should work)

### Problem: Port 8883 shows "Not Authorized"

**Solution**: Check credentials

- Make sure Username = `[USERNAME]`
- Make sure Password = `[PASSWORD]`
- These are case-sensitive

### Problem: Port 1883 shows "Closed/Unreachable"

**Solution**: Check Docker

```powershell
docker ps
# Should show mosq_insecure container running
docker logs mosq_insecure
# Check for errors
```

---

## 📊 What the Dashboard Actually Shows

The scanner detects MQTT publishers (your ESP32) by:

1. **Connecting** to both ports (1883 and 8883)
2. **Subscribing** to all topics (`#`)
3. **Listening** for 5 seconds
4. **Recording** any messages received
5. **Parsing** the topics to identify sensor types

**Note**: If ESP32 publishes with `retained=true` (which it does), the scanner will see the last message immediately, even if ESP32 stopped publishing!

---

## 🎯 Next Steps After Testing

If everything works:

1. ✅ **Take screenshots** of the results
2. ✅ **Export CSV** (click "Download CSV" button)
3. ✅ **Review security analysis** in the results
4. ✅ **Test multiple times** to verify consistency
5. ✅ **Try different targets** (e.g., scan a /24 subnet)

---

## 📁 Important Files Created

1. **test_esp32_sensors.py** - Verifies ESP32 is publishing
2. **SYSTEM_DOCUMENTATION.md** - Complete system documentation
3. **TEST_INSTRUCTIONS.md** - Detailed testing guide
4. **THIS FILE (QUICK_START.md)** - You are here!

---

## 💡 Pro Tips

1. **Retained Messages**: Your ESP32 uses `retained=true`, which means:
    - Last message is stored by the broker
    - Scanner will see it immediately
    - Even if ESP32 is offline!

2. **Real-time Data**: To see live updates:
    - Keep scanning every 30 seconds
    - Or modify dashboard.blade.php to auto-refresh

3. **Security Analysis**: The dashboard shows:
    - TLS certificate details
    - Security score (0-100)
    - Vulnerabilities detected
    - Recommendations

---

## 🆘 Still Having Issues?

1. **Check ALL services**:

    ```powershell
    docker ps  # Both MQTT brokers
    netstat -ano | findstr :5000  # Flask
    netstat -ano | findstr :8000  # Laravel
    ```

2. **Check ESP32 serial monitor** - Should show "Published" messages

3. **Check Flask logs** - Look at terminal where `python app.py` is running

4. **Check Laravel logs**:

    ```powershell
    cat storage\logs\laravel.log
    ```

5. **Read full documentation**: `SYSTEM_DOCUMENTATION.md`

---

## 🎉 Expected Final Result

After running the test, your dashboard should clearly show:

- ✅ **2 Secure connections** (port 8883 with TLS)
- ✅ **1 Insecure connection** (port 1883 plain text)
- ✅ **3 Sensor types** (DHT, LDR, PIR)
- ✅ **Real sensor data** with actual values
- ✅ **Security warnings** for insecure connection

This demonstrates your **Mixed Security MQTT Scanner** working correctly!

---

**Ready? Start with Step 1 above! 🚀**
