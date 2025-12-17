# MQTT Scanner Testing Instructions

## System Architecture

```
ESP32 (192.168.100.140)
    ↓ publishes via MQTT
MQTT Brokers (192.168.100.56)
    - Port 1883 (insecure) ← PIR sensor
    - Port 8883 (secure/TLS) ← DHT + LDR sensors
    ↓ scanned by
Flask Scanner (http://127.0.0.1:5000)
    ↓ API calls
Laravel Dashboard (http://127.0.0.1:8000)
```

## Expected Sensor Data

The ESP32 publishes 3 sensors with different security profiles:

### 1. DHT11 (Secure - Port 8883)

-   **Topic**: `sensors/faris/dht_secure`
-   **Security**: TLS with authentication (testuser/testpass)
-   **Data Format**: `{"temp_c":31.8,"hum_pct":64.0}`
-   **Publish Interval**: Every 3 seconds

### 2. LDR Light Sensor (Secure - Port 8883)

-   **Topic**: `sensors/faris/ldr_secure`
-   **Security**: TLS with authentication (testuser/testpass)
-   **Data Format**: `{"ldr_pct":47.3,"ldr_raw":1938}`
-   **Publish Interval**: Every 3 seconds

### 3. PIR Motion Sensor (Insecure - Port 1883)

-   **Topic**: `sensors/faris/pir_insecure`
-   **Security**: No encryption, no authentication
-   **Data Format**: `{"pir":1}` (1=motion detected, 0=no motion)
-   **Publish Interval**: Every 3 seconds

## Prerequisites

1. ✅ Docker Desktop running with MQTT brokers:

    ```bash
    docker ps
    # Should show: mosq_insecure (port 1883) and mosq_secure (port 8883)
    ```

2. ✅ ESP32 connected and publishing sensor data

    - Check serial monitor for output like:

    ```
    [SECURE DHT] ✓ Published: {"temp_c":31.8,"hum_pct":64.0}
    [SECURE LDR] ✓ Published: {"ldr_pct":47.3,"ldr_raw":1938}
    [INSECURE PIR] ✓ Published: {"pir":1}
    ```

3. ✅ Flask MQTT Scanner running on port 5000
4. ✅ Laravel server running on port 8000

## Testing Steps

### Step 1: Verify MQTT Brokers

```powershell
# Check Docker containers
docker ps

# Test insecure broker (port 1883) - should allow anonymous connection
cd mqtt-scanner
python quick_sub.py 192.168.100.56 1883 sensors/faris/#

# Test secure broker (port 8883) - requires credentials
# You should see sensor data streaming
```

### Step 2: Test Flask Scanner API

```powershell
# Start Flask if not running
cd mqtt-scanner
python app.py

# In another terminal, test scan endpoint
$body = @{
    target = "192.168.100.56"
    creds = @{
        user = "testuser"
        pass = "testpass"
    }
    listen_duration = 5
    capture_all_topics = $true
} | ConvertTo-Json

Invoke-WebRequest -Uri "http://127.0.0.1:5000/api/scan" `
    -Method POST `
    -Headers @{
        "X-API-KEY" = "my-very-secret-flask-key-CHANGEME"
        "Content-Type" = "application/json"
    } `
    -Body $body

# Fetch results
Invoke-WebRequest -Uri "http://127.0.0.1:5000/api/results" `
    -Method GET `
    -Headers @{"X-API-KEY" = "my-very-secret-flask-key-CHANGEME"}
```

### Step 3: Test Laravel Dashboard

1. Open browser: http://127.0.0.1:8000
2. Login with your credentials
3. Go to Dashboard
4. Enter target: `192.168.100.56`
5. Enter MQTT credentials:
    - Username: `testuser`
    - Password: `testpass`
6. Click "Start Scan"

### Expected Results in Dashboard

The dashboard should display a table with **3 rows**:

| IP             | Port | Security        | Sensor Type | Publisher                  | Subscriber | Sensor Data                     | TLS Details    |
| -------------- | ---- | --------------- | ----------- | -------------------------- | ---------- | ------------------------------- | -------------- |
| 192.168.100.56 | 8883 | 🔒 Secure (TLS) | 🌡️ DHT      | sensors/faris/dht_secure   | Scanner    | Temp: 31.8°C<br>Humidity: 64.0% | TLS 1.2+       |
| 192.168.100.56 | 8883 | 🔒 Secure (TLS) | 💡 LDR      | sensors/faris/ldr_secure   | Scanner    | Light: 47.3%                    | TLS 1.2+       |
| 192.168.100.56 | 1883 | ⚠️ Insecure     | 👁️ PIR      | sensors/faris/pir_insecure | Scanner    | Motion: DETECTED                | ⚠️ Unencrypted |

### Summary Cards Should Show:

-   **Total Scanned**: 2 (two ports)
-   **Open Brokers**: 2 (both accessible)
-   **Auth Failures**: 0 (credentials correct)

## Troubleshooting

### Issue: No sensors detected

1. **Check ESP32 is publishing:**

    ```powershell
    cd mqtt-subscriber
    python subscriber.py
    ```

    You should see messages like:

    ```
    Topic: sensors/faris/dht_secure
    Message: {"temp_c":31.8,"hum_pct":64.0}
    ```

2. **Check MQTT broker connectivity:**

    ```powershell
    # Test with mosquitto_sub if installed
    mosquitto_sub -h 192.168.100.56 -p 1883 -t "sensors/faris/#" -v
    ```

3. **Check Docker logs:**
    ```powershell
    docker logs mosq_insecure
    docker logs mosq_secure
    ```

### Issue: Flask scanner returns empty results

-   Increase `listen_duration` in the scan request (try 10 seconds)
-   Verify ESP32 publish interval (should be 3 seconds)
-   Check Flask logs for errors

### Issue: TLS connection fails

-   Verify secure broker password file:

    ```powershell
    docker exec mosq_secure cat /mosquitto/config/passwordfile
    # Should show: testuser:$7$...encrypted...
    ```

-   Check certificates:
    ```powershell
    ls mqtt-brokers/secure/certs/
    # Should have: server.crt, server.key
    ```

### Issue: Dashboard shows "Authentication required"

-   Check .env file has:
    ```
    FLASK_API_KEY=my-very-secret-flask-key-CHANGEME
    ```
-   Verify you're logged into Laravel dashboard

## Debug Mode

Enable debug logging in Flask:

```python
# In mqtt-scanner/app.py
app.run(host='0.0.0.0', port=5000, debug=True)
```

Check Laravel logs:

```powershell
tail -f storage/logs/laravel.log
```

## Success Criteria

✅ **Scan completes in 5-10 seconds**  
✅ **3 sensor types detected (DHT, LDR, PIR)**  
✅ **2 secure connections on port 8883**  
✅ **1 insecure connection on port 1883**  
✅ **Sensor data displays with real values**  
✅ **TLS details show for port 8883**  
✅ **Security warnings show for port 1883**

## API Response Format

The Flask `/api/scan` endpoint returns:

```json
{
    "status": "ok",
    "results": [
        {
            "ip": "192.168.100.56",
            "port": 8883,
            "result": "connected",
            "classification": "open_or_auth_ok",
            "tls": true,
            "publishers": [
                {
                    "topic": "sensors/faris/dht_secure",
                    "payload_size": 35,
                    "qos": 0,
                    "retained": true
                }
            ],
            "topics_discovered": {
                "sensors/faris/dht_secure": {
                    "first_seen": "2025-11-05T01:45:00",
                    "message_count": 5
                }
            },
            "tls_analysis": {
                "has_tls": true,
                "cert_valid": true,
                "security_score": 70
            }
        }
    ]
}
```

## Next Steps After Successful Test

1. **Export CSV** - Click "Download CSV" button
2. **Review Security Summary** - Check the summary cards
3. **Analyze TLS Certificates** - Review certificate details
4. **Monitor Real-time** - Keep dashboard open to see live updates
