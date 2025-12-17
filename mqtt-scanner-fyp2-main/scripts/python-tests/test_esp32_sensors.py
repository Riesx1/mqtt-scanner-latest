#!/usr/bin/env python3
"""
Simple MQTT Subscriber - Test if ESP32 is publishing
Listens to all sensor topics and displays data
"""
import paho.mqtt.client as mqtt
import json
import time
from datetime import datetime

# MQTT Configuration
BROKER_IP = "192.168.100.56"
TOPICS = {
    "sensors/faris/dht_secure": {"name": "DHT (Temp/Humidity)", "port": 8883, "secure": True},
    "sensors/faris/ldr_secure": {"name": "LDR (Light)", "port": 8883, "secure": True},
    "sensors/faris/pir_insecure": {"name": "PIR (Motion)", "port": 1883, "secure": False}
}

# Track messages
message_count = {"dht": 0, "ldr": 0, "pir": 0}
last_data = {}

def on_connect(client, userdata, flags, rc, properties=None):
    """Callback when connected to MQTT broker"""
    if rc == 0:
        print(f"✅ Connected to MQTT broker at {BROKER_IP}:{userdata['port']}")
        print(f"   Security: {'🔒 Secure (TLS)' if userdata['secure'] else '⚠️ Insecure'}")

        # Subscribe to all topics for this connection
        for topic, info in TOPICS.items():
            if info['port'] == userdata['port']:
                client.subscribe(topic, qos=0)
                print(f"   📡 Subscribed to: {topic}")
    else:
        print(f"❌ Connection failed with code {rc}")

def on_message(client, userdata, msg):
    """Callback when message is received"""
    topic = msg.topic
    timestamp = datetime.now().strftime("%H:%M:%S")

    try:
        # Parse JSON payload
        payload = json.loads(msg.payload.decode())

        # Identify sensor type
        if 'dht' in topic.lower():
            sensor_type = 'dht'
            temp = payload.get('temp_c', 'N/A')
            humidity = payload.get('hum_pct', 'N/A')
            data_str = f"🌡️ Temp: {temp}°C, Humidity: {humidity}%"
        elif 'ldr' in topic.lower():
            sensor_type = 'ldr'
            light_pct = payload.get('ldr_pct', 'N/A')
            light_raw = payload.get('ldr_raw', 'N/A')
            data_str = f"💡 Light: {light_pct}% (Raw: {light_raw})"
        elif 'pir' in topic.lower():
            sensor_type = 'pir'
            motion = payload.get('pir', 0)
            motion_str = "DETECTED ⚠️" if motion == 1 else "None ✓"
            data_str = f"👁️ Motion: {motion_str}"
        else:
            sensor_type = 'unknown'
            data_str = str(payload)

        # Update counter
        message_count[sensor_type] = message_count.get(sensor_type, 0) + 1
        last_data[sensor_type] = payload

        # Display message
        security = "🔒 SECURE" if TOPICS.get(topic, {}).get('secure', False) else "⚠️ INSECURE"
        print(f"\n[{timestamp}] {security} - {topic}")
        print(f"  {data_str}")
        print(f"  JSON: {json.dumps(payload)}")

    except json.JSONDecodeError:
        print(f"\n[{timestamp}] ⚠️ Non-JSON message on {topic}: {msg.payload}")
    except Exception as e:
        print(f"\n[{timestamp}] ❌ Error processing message: {e}")

def on_disconnect(client, userdata, rc, properties=None):
    """Callback when disconnected"""
    print(f"\n⚠️ Disconnected from {BROKER_IP}:{userdata['port']} (rc={rc})")

print("=" * 70)
print("ESP32 Sensor Monitor - Real-time MQTT Data")
print("=" * 70)
print("\nExpected Sensors:")
for topic, info in TOPICS.items():
    security_icon = "🔒" if info['secure'] else "⚠️"
    print(f"  {security_icon} {info['name']}: {topic} (Port {info['port']})")
print("\n" + "-" * 70)

# Create two MQTT clients (one for each port)
clients = []

try:
    # Client 1: Secure broker (port 8883) - DHT and LDR
    client_secure = mqtt.Client(
        client_id="test-secure-sub",
        callback_api_version=mqtt.CallbackAPIVersion.VERSION2,
        userdata={"port": 8883, "secure": True}
    )
    client_secure.username_pw_set("testuser", "testpass")

    # Configure TLS to accept self-signed certificate
    import ssl
    ssl_context = ssl.create_default_context()
    ssl_context.check_hostname = False
    ssl_context.verify_mode = ssl.CERT_NONE
    client_secure.tls_set_context(ssl_context)

    client_secure.on_connect = on_connect
    client_secure.on_message = on_message
    client_secure.on_disconnect = on_disconnect

    print("\n🔒 Connecting to SECURE broker (port 8883)...")
    client_secure.connect(BROKER_IP, 8883, keepalive=60)
    clients.append(client_secure)

    # Client 2: Insecure broker (port 1883) - PIR
    client_insecure = mqtt.Client(
        client_id="test-insecure-sub",
        callback_api_version=mqtt.CallbackAPIVersion.VERSION2,
        userdata={"port": 1883, "secure": False}
    )
    client_insecure.on_connect = on_connect
    client_insecure.on_message = on_message
    client_insecure.on_disconnect = on_disconnect

    print("⚠️ Connecting to INSECURE broker (port 1883)...")
    client_insecure.connect(BROKER_IP, 1883, keepalive=60)
    clients.append(client_insecure)

    # Start both clients
    for client in clients:
        client.loop_start()

    print("\n" + "=" * 70)
    print("✅ Listening for sensor data... (Press Ctrl+C to stop)")
    print("=" * 70)

    # Run for 30 seconds or until interrupted
    start_time = time.time()
    while time.time() - start_time < 30:
        time.sleep(1)

        # Show summary every 10 seconds
        if int(time.time() - start_time) % 10 == 0:
            print("\n" + "-" * 70)
            print("📊 MESSAGE COUNT:")
            print(f"   DHT: {message_count.get('dht', 0)} messages")
            print(f"   LDR: {message_count.get('ldr', 0)} messages")
            print(f"   PIR: {message_count.get('pir', 0)} messages")
            print("-" * 70)

    print("\n" + "=" * 70)
    print("FINAL SUMMARY:")
    print("=" * 70)
    print(f"Total messages received: {sum(message_count.values())}")
    print(f"  🌡️ DHT: {message_count.get('dht', 0)}")
    print(f"  💡 LDR: {message_count.get('ldr', 0)}")
    print(f"  👁️ PIR: {message_count.get('pir', 0)}")

    if last_data:
        print("\nLast received data:")
        for sensor, data in last_data.items():
            print(f"  {sensor.upper()}: {json.dumps(data)}")

    # Verify expected results
    print("\n" + "=" * 70)
    print("VERIFICATION:")
    print("=" * 70)

    success = True
    if message_count.get('dht', 0) > 0:
        print("✅ DHT sensor is publishing")
    else:
        print("❌ DHT sensor NOT detected")
        success = False

    if message_count.get('ldr', 0) > 0:
        print("✅ LDR sensor is publishing")
    else:
        print("❌ LDR sensor NOT detected")
        success = False

    if message_count.get('pir', 0) > 0:
        print("✅ PIR sensor is publishing")
    else:
        print("❌ PIR sensor NOT detected")
        success = False

    print("=" * 70)
    if success:
        print("✅ ALL SENSORS WORKING! Ready to test Flask scanner.")
    else:
        print("⚠️ Some sensors not detected. Check ESP32 connection.")
    print("=" * 70)

except KeyboardInterrupt:
    print("\n\n⏹️ Stopping listener...")
except Exception as e:
    print(f"\n❌ Error: {e}")
    import traceback
    traceback.print_exc()
finally:
    # Clean disconnect
    for client in clients:
        client.loop_stop()
        client.disconnect()
    print("\n👋 Disconnected. Test complete.")
