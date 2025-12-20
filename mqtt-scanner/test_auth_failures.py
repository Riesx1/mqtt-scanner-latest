#!/usr/bin/env python3
"""
Test authentication failure detection on port 8883
"""
import paho.mqtt.client as mqtt
import ssl
import time

HOST = "192.168.100.56"  # Your laptop IP
PORT = 8883

print("=" * 60)
print("Testing Authentication Failure on Secure Broker")
print(f"Target: {HOST}:{PORT}")
print("=" * 60)

# Test 1: Wrong password
print("\n--- Test 1: Wrong Password ---")
auth_failed = False
connected = False

def on_connect_wrong(client, userdata, flags, rc, properties=None):
    global auth_failed, connected
    # Handle both numeric and string return codes (paho-mqtt v1 vs v2)
    rc_value = rc if isinstance(rc, int) else str(rc)
    print(f"Connection result code: {rc_value}")
    if rc_value == 0 or rc == 0:
        connected = True
        print("❌ UNEXPECTED: Connected with wrong password!")
    elif rc_value == 5 or rc == 5 or str(rc).lower() == "not authorized":
        auth_failed = True
        print("✅ CORRECT: Authentication failed")
    else:
        print(f"⚠️  Connection failed with code {rc_value}")

try:
    if hasattr(mqtt, 'CallbackAPIVersion'):
        client1 = mqtt.Client(mqtt.CallbackAPIVersion.VERSION2, client_id="test-wrong-pass")
    else:
        client1 = mqtt.Client(client_id="test-wrong-pass")

    client1.username_pw_set("faris02@gmail.com", "WRONGPASSWORD")
    client1.tls_set(cert_reqs=ssl.CERT_NONE)
    client1.tls_insecure_set(True)
    client1.on_connect = on_connect_wrong

    client1.connect(HOST, PORT, 60)
    client1.loop_start()
    time.sleep(3)
    client1.loop_stop()
    client1.disconnect()

    if auth_failed:
        print("✅ Auth failure detected correctly!")
    elif connected:
        print("❌ Security issue: Broker accepted wrong password!")
    else:
        print("⚠️  Could not connect (timeout or network issue)")

except Exception as e:
    print(f"❌ Error: {e}")

# Test 2: No credentials
print("\n--- Test 2: No Credentials (Anonymous) ---")
auth_failed2 = False
connected2 = False

def on_connect_anon(client, userdata, flags, rc, properties=None):
    global auth_failed2, connected2
    # Handle both numeric and string return codes (paho-mqtt v1 vs v2)
    rc_value = rc if isinstance(rc, int) else str(rc)
    print(f"Connection result code: {rc_value}")
    if rc_value == 0 or rc == 0:
        connected2 = True
        print("❌ SECURITY ISSUE: Broker allows anonymous access!")
    elif rc_value == 5 or rc == 5 or str(rc).lower() == "not authorized":
        auth_failed2 = True
        print("✅ CORRECT: Authentication required")
    else:
        print(f"⚠️  Connection failed with code {rc_value}")

try:
    if hasattr(mqtt, 'CallbackAPIVersion'):
        client2 = mqtt.Client(mqtt.CallbackAPIVersion.VERSION2, client_id="test-anonymous")
    else:
        client2 = mqtt.Client(client_id="test-anonymous")

    # Don't set credentials
    client2.tls_set(cert_reqs=ssl.CERT_NONE)
    client2.tls_insecure_set(True)
    client2.on_connect = on_connect_anon

    client2.connect(HOST, PORT, 60)
    client2.loop_start()
    time.sleep(3)
    client2.loop_stop()
    client2.disconnect()

    if auth_failed2:
        print("✅ Auth failure detected correctly!")
    elif connected2:
        print("❌ Security issue: Broker allows anonymous access!")
    else:
        print("⚠️  Could not connect (timeout or network issue)")

except Exception as e:
    print(f"❌ Error: {e}")

print("\n" + "=" * 60)
print("Summary:")
print(f"  Wrong Password Test: {'✅ PASS' if auth_failed else '❌ FAIL'}")
print(f"  Anonymous Test: {'✅ PASS' if auth_failed2 else '❌ FAIL'}")
print("\nIf both tests passed, the scanner should detect 2 auth failures")
print("when you scan with wrong/no credentials.")
print("=" * 60)
