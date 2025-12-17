#!/usr/bin/env python3
"""
Quick MQTT Test - Check if any messages are being published
Tests BOTH ports to see which one has traffic
"""
import paho.mqtt.client as mqtt
import time
import ssl

BROKER_IP = "192.168.100.56"

def test_insecure():
    """Test insecure broker (port 1883)"""
    print("\n" + "="*70)
    print("Testing INSECURE broker (port 1883)")
    print("="*70)

    received_any = False

    def on_connect(client, userdata, flags, rc, properties=None):
        if rc == 0:
            print("✅ Connected successfully!")
            client.subscribe("#", qos=0)
            print("📡 Subscribed to all topics (#)")
            print("⏳ Listening for 10 seconds...\n")
        else:
            print(f"❌ Connection failed: {rc}")

    def on_message(client, userdata, msg):
        nonlocal received_any
        received_any = True
        print(f"📨 [{msg.topic}] {msg.payload.decode('utf-8', errors='replace')}")

    try:
        client = mqtt.Client(
            client_id="quick-test-insecure",
            callback_api_version=mqtt.CallbackAPIVersion.VERSION2
        )
        client.on_connect = on_connect
        client.on_message = on_message

        client.connect(BROKER_IP, 1883, keepalive=60)
        client.loop_start()

        time.sleep(10)

        client.loop_stop()
        client.disconnect()

        if not received_any:
            print("⚠️ No messages received on port 1883")

        return received_any

    except Exception as e:
        print(f"❌ Error: {e}")
        return False

def test_secure():
    """Test secure broker (port 8883)"""
    print("\n" + "="*70)
    print("Testing SECURE broker (port 8883)")
    print("="*70)

    received_any = False

    def on_connect(client, userdata, flags, rc, properties=None):
        if rc == 0:
            print("✅ Connected successfully with TLS!")
            client.subscribe("#", qos=0)
            print("📡 Subscribed to all topics (#)")
            print("⏳ Listening for 10 seconds...\n")
        else:
            print(f"❌ Connection failed: {rc}")

    def on_message(client, userdata, msg):
        nonlocal received_any
        received_any = True
        print(f"📨 [{msg.topic}] {msg.payload.decode('utf-8', errors='replace')}")

    try:
        client = mqtt.Client(
            client_id="quick-test-secure",
            callback_api_version=mqtt.CallbackAPIVersion.VERSION2
        )
        client.username_pw_set("testuser", "testpass")

        # Accept self-signed certificate
        ssl_context = ssl.create_default_context()
        ssl_context.check_hostname = False
        ssl_context.verify_mode = ssl.CERT_NONE
        client.tls_set_context(ssl_context)

        client.on_connect = on_connect
        client.on_message = on_message

        client.connect(BROKER_IP, 8883, keepalive=60)
        client.loop_start()

        time.sleep(10)

        client.loop_stop()
        client.disconnect()

        if not received_any:
            print("⚠️ No messages received on port 8883")

        return received_any

    except Exception as e:
        print(f"❌ Error: {e}")
        import traceback
        traceback.print_exc()
        return False

if __name__ == "__main__":
    print("="*70)
    print("QUICK MQTT TRAFFIC TEST")
    print("="*70)
    print(f"Broker: {BROKER_IP}")
    print(f"Testing if ANY messages are being published...")
    print("="*70)

    # Test both ports
    insecure_ok = test_insecure()
    secure_ok = test_secure()

    # Summary
    print("\n" + "="*70)
    print("TEST SUMMARY")
    print("="*70)

    if insecure_ok:
        print("✅ Port 1883 (insecure): TRAFFIC DETECTED")
    else:
        print("❌ Port 1883 (insecure): NO TRAFFIC")

    if secure_ok:
        print("✅ Port 8883 (secure): TRAFFIC DETECTED")
    else:
        print("❌ Port 8883 (secure): NO TRAFFIC")

    if not insecure_ok and not secure_ok:
        print("\n⚠️ NO MQTT TRAFFIC DETECTED ON EITHER PORT!")
        print("\nPossible causes:")
        print("  1. ESP32 is not connected or not running")
        print("  2. ESP32 is not publishing (check serial monitor)")
        print("  3. Wrong broker IP (check esp32_mixed_security.ino)")
        print("  4. ESP32 WiFi not connected")
        print("  5. Network/firewall blocking MQTT ports")
        print("\nNext steps:")
        print("  1. Check ESP32 serial monitor for 'Published' messages")
        print("  2. Verify MQTT broker IP in ESP32 code matches 192.168.100.56")
        print("  3. Check Docker logs: docker logs mosq_insecure")
        print("  4. Check Docker logs: docker logs mosq_secure")
    else:
        print("\n✅ SUCCESS! MQTT traffic detected. System is working!")

    print("="*70)
