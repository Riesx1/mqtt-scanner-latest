#!/usr/bin/env python3
"""
Test if ESP32 is actually publishing to the secure broker.
Run this to verify ESP32 is connected and publishing.
"""
import paho.mqtt.client as mqtt
import ssl
import time

HOST = "127.0.0.1"  # Change if testing remotely
SECURE_PORT = 8883
USERNAME = "faris02@gmail.com"
PASSWORD = "Faris02!"

messages_received = []

def on_connect(client, userdata, flags, rc, properties=None):
    if rc == 0:
        print(f"✅ Connected to secure broker {HOST}:{SECURE_PORT}")
        print(f"   Using credentials: {USERNAME}")
        client.subscribe("sensors/#")
        print("📡 Subscribed to sensors/#")
        print("\n⏳ Waiting for messages from ESP32...")
        print("   (ESP32 publishes every 3 seconds)")
        print("   If you see nothing after 10 seconds, ESP32 is NOT publishing!\n")
    else:
        print(f"❌ Connection failed with code {rc}")
        if rc == 5:
            print("   → Authentication failed! Check credentials.")
        client.disconnect()

def on_message(client, userdata, msg):
    messages_received.append(msg)
    print(f"\n✅ Message received!")
    print(f"   Topic: {msg.topic}")
    print(f"   Payload: {msg.payload.decode('utf-8', errors='replace')}")
    print(f"   Retained: {msg.retain}")
    print(f"   QoS: {msg.qos}")

# Create client
if hasattr(mqtt, 'CallbackAPIVersion'):
    client = mqtt.Client(mqtt.CallbackAPIVersion.VERSION2, client_id="test-esp32-connection")
else:
    client = mqtt.Client(client_id="test-esp32-connection")

client.username_pw_set(USERNAME, PASSWORD)

# Setup TLS
client.tls_set(cert_reqs=ssl.CERT_NONE)
client.tls_insecure_set(True)

client.on_connect = on_connect
client.on_message = on_message

print("=" * 60)
print("ESP32 Connection Test")
print("=" * 60)
print(f"Testing: {HOST}:{SECURE_PORT}")
print(f"Username: {USERNAME}")
print(f"Password: {'*' * len(PASSWORD)}")
print("=" * 60 + "\n")

try:
    client.connect(HOST, SECURE_PORT, 60)
    client.loop_start()

    # Wait 15 seconds to receive messages
    time.sleep(15)

    print("\n" + "=" * 60)
    if messages_received:
        print(f"✅ SUCCESS! Received {len(messages_received)} message(s)")
        print("   ESP32 is connected and publishing!")
        print("\nTopics detected:")
        for msg in messages_received:
            print(f"   - {msg.topic}")
    else:
        print("❌ NO MESSAGES RECEIVED!")
        print("\nPossible issues:")
        print("1. ESP32 is not connected to WiFi")
        print("2. ESP32 code was not uploaded after changes")
        print("3. ESP32 credentials don't match broker")
        print("4. ESP32 is not running (check Serial Monitor)")
        print("5. Firewall is blocking connection")
        print("\nTroubleshooting steps:")
        print("1. Open Arduino IDE Serial Monitor (115200 baud)")
        print("2. Check for 'Connected to MQTT (Secure)' message")
        print("3. Check for '[SECURE DHT] ✓ Published' messages")
        print(f"4. Verify ESP32 uses: {USERNAME}")
        print("5. Upload the updated ESP32 code if needed")
    print("=" * 60)

    client.loop_stop()
    client.disconnect()

except Exception as e:
    print(f"\n❌ Error: {e}")
    print("\nCheck:")
    print("1. Docker containers are running: docker-compose ps")
    print("2. Port 8883 is not blocked by firewall")
