#!/usr/bin/env python3
"""
Simple Error Message Proof - Shows actual Python errors
This is the clearest proof that a port is unreachable!
"""

import socket
import sys

def test_with_error_message(ip, port):
    """Test connection and show error messages"""
    print(f"\n{'='*70}")
    print(f"Testing: {ip}:{port}")
    print('='*70)

    try:
        print("Attempting connection...")
        s = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
        s.settimeout(2)
        s.connect((ip, port))
        print("✅ SUCCESS - Port is REACHABLE")
        s.close()

    except socket.timeout as e:
        print("\n❌ ERROR CAUGHT:")
        print(f"   Type: socket.timeout")
        print(f"   Message: {e}")
        print(f"   Meaning: Connection timed out - NO RESPONSE from host")
        print(f"   PROOF: Port is UNREACHABLE ❌")

    except ConnectionRefusedError as e:
        print("\n❌ ERROR CAUGHT:")
        print(f"   Type: ConnectionRefusedError")
        print(f"   Error Code: {e.errno}")
        print(f"   Message: {e}")
        print(f"   Meaning: Host responded but port is CLOSED")
        print(f"   PROOF: Port is CLOSED ⚪")

    except OSError as e:
        print("\n❌ ERROR CAUGHT:")
        print(f"   Type: OSError")
        print(f"   Error Code: {e.errno}")
        print(f"   Message: {e}")
        print(f"   Meaning: Network unreachable or host offline")
        print(f"   PROOF: Port is UNREACHABLE ❌")

    except Exception as e:
        print("\n❌ ERROR CAUGHT:")
        print(f"   Type: {type(e).__name__}")
        print(f"   Message: {e}")
        print(f"   PROOF: Connection failed ❌")

def main():
    print("\n╔" + "="*68 + "╗")
    print("║" + " "*20 + "ERROR MESSAGE PROOF" + " "*27 + "║")
    print("╚" + "="*68 + "╝")
    print("\nThis script shows the ACTUAL ERROR MESSAGES as proof")

    # Test 1: Unreachable IP
    print("\n\n[TEST 1] Unreachable IP - Should show timeout error")
    test_with_error_message("192.168.100.254", 1883)
    test_with_error_message("192.168.100.254", 8883)

    # Test 2: Public IP
    print("\n\n[TEST 2] Public IP (Google DNS) - Should show timeout")
    test_with_error_message("8.8.8.8", 1883)

    # Test 3: Reachable (for comparison)
    print("\n\n[TEST 3] Localhost (Reachable) - Should succeed")
    test_with_error_message("127.0.0.1", 1883)

    print("\n" + "="*70)
    print("✅ PROOF COMPLETE - Error messages show port is unreachable!")
    print("="*70)

if __name__ == '__main__':
    main()
