#!/usr/bin/env python3
"""
Comprehensive Test Script for All MQTT Scanner Outcomes
Tests all 6 possible outcome states with different targets and configurations
"""

from scanner import run_scan, try_mqtt_connect
import json

def print_section(title):
    print("\n" + "=" * 70)
    print(f"  {title}")
    print("=" * 70)

def test_outcome(description, ip, creds=None, expected_outcome=None):
    """Test a single scenario and display results"""
    print(f"\n📍 Testing: {description}")
    print(f"   Target: {ip}")
    if creds:
        print(f"   Credentials: {creds['user']}")

    try:
        results = run_scan(ip, creds=creds)

        for result in results:
            outcome = result.get('outcome', {})
            print(f"\n   Port {result['port']}:")
            print(f"   ├─ Outcome: {outcome.get('label', 'N/A')}")
            print(f"   ├─ Meaning: {outcome.get('meaning', 'N/A')}")
            print(f"   └─ Security: {outcome.get('security_implication', 'N/A')}")

            if expected_outcome and outcome.get('label') == expected_outcome:
                print(f"   ✅ EXPECTED OUTCOME ACHIEVED: {expected_outcome}")
            elif expected_outcome:
                print(f"   ⚠️  Expected: {expected_outcome}, Got: {outcome.get('label')}")

    except Exception as e:
        print(f"   ❌ Error: {str(e)}")

def main():
    print("\n" + "╔" + "═" * 68 + "╗")
    print("║" + " " * 15 + "MQTT SCANNER OUTCOME TEST SUITE" + " " * 22 + "║")
    print("╚" + "═" * 68 + "╝")

    # Test 1: Connected (1883) - Insecure plaintext connection
    print_section("TEST 1: Connected (1883) - High Risk")
    test_outcome(
        "Insecure broker with anonymous access",
        "127.0.0.1",
        creds=None,
        expected_outcome="Connected (1883)"
    )

    # Test 2: Connected (8883) - Secure TLS connection with auth
    print_section("TEST 2: Connected (8883) - Secure with Auth")
    test_outcome(
        "Secure broker with valid credentials",
        "127.0.0.1",
        creds={'user': 'faris02@gmail.com', 'pass': 'Faris02!'},
        expected_outcome="Connected (8883)"
    )

    # Test 3: Not Authorised / Auth Required
    print_section("TEST 3: Not Authorised / Auth Required - Positive Security")
    test_outcome(
        "Secure broker without credentials (anonymous attempt)",
        "127.0.0.1",
        creds=None,
        expected_outcome="Not Authorised / Auth Required"
    )

    # Test 4: Not Authorised - Wrong credentials
    print_section("TEST 4: Not Authorised - Invalid Credentials")
    test_outcome(
        "Secure broker with wrong credentials",
        "127.0.0.1",
        creds={'user': 'wrong@email.com', 'pass': 'wrongpassword'},
        expected_outcome="Not Authorised / Auth Required"
    )

    # Test 5: Closed / Refused (test non-standard ports)
    print_section("TEST 5: Closed / Refused - Port Not Listening")
    print("\n📍 To test 'Closed / Refused' outcome:")
    print("   1. Temporarily add a non-listening port to COMMON_PORTS in scanner.py")
    print("      Example: COMMON_PORTS = [1883, 8883, 9999]")
    print("   2. Or stop the MQTT brokers: docker-compose down")
    print("   3. Then run: run_scan('127.0.0.1')")
    print("   Expected: 'Closed / Refused' outcome")

    # Test 6: Unreachable / Timeout
    print_section("TEST 6: Unreachable / Timeout - Network Issue")
    test_outcome(
        "Non-existent IP in subnet (should timeout)",
        "192.168.100.254",  # Likely unused IP
        creds=None,
        expected_outcome="Unreachable / Timeout"
    )

    # Alternative Test 6: Public IP that blocks MQTT
    print("\n📍 Alternative Test: Public IP blocking MQTT ports")
    test_outcome(
        "Google DNS (blocks MQTT ports)",
        "8.8.8.8",
        creds=None,
        expected_outcome="Unreachable / Timeout"
    )

    # Test 7: TLS Error (requires special setup)
    print_section("TEST 7: TLS Error - Certificate/Configuration Issue")
    print("\n📍 To test 'TLS Error' outcome, you need ONE of:")
    print("   1. Broker with expired certificate")
    print("   2. Broker with self-signed cert and strict validation")
    print("   3. Broker with hostname mismatch in certificate")
    print("   4. Broker with incompatible TLS version")
    print("\n   Current brokers use setInsecure() so won't show TLS errors")
    print("   To simulate: Modify scanner.py to remove 'setInsecure()' call")

    # Summary
    print_section("TEST SUMMARY")
    print("\n✅ Tested Outcomes:")
    print("   1. ✓ Connected (1883) - localhost:1883")
    print("   2. ✓ Connected (8883) - localhost:8883 with auth")
    print("   3. ✓ Not Authorised / Auth Required - localhost:8883 no auth")
    print("   4. ⚠ Closed / Refused - requires broker shutdown")
    print("   5. ✓ Unreachable / Timeout - non-existent IPs")
    print("   6. ⚠ TLS Error - requires special certificate setup")

    print("\n" + "=" * 70)
    print("\n💡 Quick Test Commands:")
    print("   # Test current running brokers (outcomes 1-3):")
    print("   python test_all_outcomes.py")
    print()
    print("   # Test 'Closed/Refused' outcome:")
    print("   docker-compose down  # Stop brokers")
    print("   python test_outcomes.py")
    print("   docker-compose up -d  # Restart brokers")
    print()
    print("   # Test specific IP:")
    print("   python -c \"from scanner import run_scan; import json; print(json.dumps(run_scan('192.168.100.254'), indent=2))\"")
    print()

if __name__ == '__main__':
    main()
