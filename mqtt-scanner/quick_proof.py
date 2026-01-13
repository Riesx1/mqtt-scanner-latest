#!/usr/bin/env python3
"""
Quick Proof: Port Unreachable
Simple visual demonstration
"""

import socket
import time

def test_connection(ip, port, timeout=2):
    """Test if port is reachable"""
    print(f"\n{'='*60}")
    print(f"Testing: {ip}:{port}")
    print(f"Timeout: {timeout} seconds")
    print('='*60)

    start = time.time()
    try:
        s = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
        s.settimeout(timeout)
        print(f"⏳ Attempting connection...")

        result = s.connect_ex((ip, port))
        elapsed = time.time() - start

        if result == 0:
            print(f"✅ SUCCESS: Connected in {elapsed:.2f}s")
            print(f"Result: REACHABLE")
            s.close()
            return "REACHABLE"
        else:
            print(f"❌ FAILED: Error code {result} after {elapsed:.2f}s")
            print(f"Result: CLOSED/REFUSED")
            return "CLOSED"

    except socket.timeout:
        elapsed = time.time() - start
        print(f"⏰ TIMEOUT: No response after {elapsed:.2f}s")
        print(f"Result: UNREACHABLE")
        return "UNREACHABLE"

    except OSError as e:
        elapsed = time.time() - start
        print(f"❌ ERROR: {e} after {elapsed:.2f}s")
        print(f"Result: UNREACHABLE")
        return "UNREACHABLE"
    finally:
        try:
            s.close()
        except:
            pass

def main():
    print("\n" + "╔" + "="*58 + "╗")
    print("║" + " "*15 + "PROOF: PORT UNREACHABLE" + " "*20 + "║")
    print("╚" + "="*58 + "╝")

    # Test 1: Unreachable IP
    print("\n[TEST 1] Unreachable IP (Non-existent device)")
    result1_1883 = test_connection("192.168.100.254", 1883, timeout=2)
    result1_8883 = test_connection("192.168.100.254", 8883, timeout=2)

    # Test 2: Reachable IP (for comparison)
    print("\n[TEST 2] Reachable IP (Localhost - for comparison)")
    result2_1883 = test_connection("127.0.0.1", 1883, timeout=2)
    result2_8883 = test_connection("127.0.0.1", 8883, timeout=2)

    # Summary
    print("\n" + "="*60)
    print("SUMMARY - PROOF OF UNREACHABLE")
    print("="*60)

    print("\nUnreachable IP (192.168.100.254):")
    print(f"  Port 1883: {result1_1883} {'✅' if result1_1883 == 'UNREACHABLE' else '❌'}")
    print(f"  Port 8883: {result1_8883} {'✅' if result1_8883 == 'UNREACHABLE' else '❌'}")

    print("\nReachable IP (127.0.0.1):")
    print(f"  Port 1883: {result2_1883} {'✅' if result2_1883 == 'REACHABLE' else '❌'}")
    print(f"  Port 8883: {result2_8883} {'✅' if result2_8883 == 'REACHABLE' else '❌'}")

    print("\n" + "="*60)
    print("CONCLUSION:")
    if result1_1883 == "UNREACHABLE" and result1_8883 == "UNREACHABLE":
        print("✅ PROVEN: 192.168.100.254 is UNREACHABLE")
        print("   Evidence: Connection timeout on both MQTT ports")
    else:
        print("⚠️  Results inconclusive - check network configuration")
    print("="*60)

if __name__ == '__main__':
    main()
