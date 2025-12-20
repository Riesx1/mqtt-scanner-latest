#!/usr/bin/env python3
"""
Quick test to verify broker credentials work
"""
import paho.mqtt.client as mqtt
import ssl
import sys

HOST = "127.0.0.1"
PORT = 8883

# Test different credentials
credentials = [
    ("faris02@gmail.com", "faris123"),
    ("faris@gmail.com", "faris123"),
    ("testuser", "testpass"),
]

def test_auth(username, password):
    print(f"\nTesting: {username} / {'*' * len(password)}")

    connected = False
    auth_failed = False

    def on_connect(client, userdata, flags, rc, properties=None):
        nonlocal connected, auth_failed
        if rc == 0:
            connected = True
            print(f"  ✅ SUCCESS! Connected with {username}")
        elif rc == 5:
            auth_failed = True
            print(f"  ❌ AUTH FAILED! Wrong username/password")
        else:
            print(f"  ❌ Connection failed with code {rc}")

    try:
        if hasattr(mqtt, 'CallbackAPIVersion'):
            client = mqtt.Client(mqtt.CallbackAPIVersion.VERSION2, client_id=f"test-{username}")
        else:
            client = mqtt.Client(client_id=f"test-{username}")

        client.username_pw_set(username, password)
        client.tls_set(cert_reqs=ssl.CERT_NONE)
        client.tls_insecure_set(True)
        client.on_connect = on_connect

        client.connect(HOST, PORT, 60)
        client.loop_start()

        import time
        time.sleep(2)

        client.loop_stop()
        client.disconnect()

        return connected

    except Exception as e:
        print(f"  ❌ Error: {e}")
        return False

print("=" * 60)
print("Testing MQTT Broker Credentials")
print(f"Broker: {HOST}:{PORT}")
print("=" * 60)

working_creds = []
for username, password in credentials:
    if test_auth(username, password):
        working_creds.append((username, password))

print("\n" + "=" * 60)
if working_creds:
    print("✅ Working credentials found:")
    for username, password in working_creds:
        print(f"   - {username} / {password}")
    print("\n🔧 Use these credentials in:")
    print("   1. ESP32 code (mqtt_user and mqtt_pass)")
    print("   2. Scanner form (username and password fields)")
else:
    print("❌ No working credentials found!")
    print("\nTo add new user to broker:")
    print("1. cd mqtt-brokers/secure/config")
    print("2. docker exec mqtt-brokers-secure-1 mosquitto_passwd -b /mosquitto/config/passwordfile faris02@gmail.com faris123")
    print("3. docker-compose restart")
print("=" * 60)
