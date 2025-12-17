# 🔐 Auto MQTT User Registration System

## Overview

This system **automatically adds registered Laravel users to the MQTT broker's password file**, allowing them to connect to the secure MQTT broker immediately after registration.

---

## ✨ Features

### 1. **Automatic Registration**

-   When a user registers in Laravel, they are automatically added to the MQTT broker
-   Username = User's email address
-   Password = User's registration password
-   No manual configuration needed!

### 2. **Automatic Cleanup**

-   When a user deletes their account, they are automatically removed from MQTT broker
-   Keeps the password file clean and secure

### 3. **Manual Management**

-   Artisan commands available for manual user management
-   Useful for testing or fixing issues

---

## 🚀 How It Works

### Registration Flow:

```
User Registers
    ↓
Laravel Creates Account
    ↓
MqttUserService Generates Hashed Password
    ↓
User Added to /mqtt-brokers/secure/config/passwordfile
    ↓
Mosquitto Container Reloaded
    ↓
User Can Connect to Secure Broker! ✓
```

### Technical Details:

1. **Password Hashing**: Uses Mosquitto's `mosquitto_passwd` command via Docker
2. **File Format**: Standard Mosquitto password file format
    ```
    username:$7$101$hash...
    ```
3. **Container Reload**: Sends SIGHUP signal to reload config without downtime

---

## 📝 Manual User Management

### Add a User:

```bash
php artisan mqtt:user add user@example.com password123
```

### Remove a User:

```bash
php artisan mqtt:user remove user@example.com
```

### Add User with Prompt (secure password input):

```bash
php artisan mqtt:user add user@example.com
# Password will be prompted securely
```

---

## 🧪 Testing

### Test 1: Register New User

1. Go to http://127.0.0.1:8000/register
2. Register with:
    - Name: Test User
    - Email: newuser@test.com
    - Password: testpass123
3. Check password file:
    ```bash
    cat mqtt-brokers/secure/config/passwordfile
    ```
4. You should see `newuser@test.com` in the file!

### Test 2: Connect to MQTT Broker

1. On the dashboard, enter:
    - Broker IP: `127.0.0.1`
    - Secure Port: `8883`
    - Username: `newuser@test.com`
    - Password: `testpass123`
2. Click "Scan Network"
3. Should successfully connect! ✓

### Test 3: Manual Command

```bash
# Add test user
php artisan mqtt:user add manual@test.com mypassword

# Verify in file
cat mqtt-brokers/secure/config/passwordfile

# Remove test user
php artisan mqtt:user remove manual@test.com
```

---

## 📂 Files Modified

### New Files:

-   `app/Services/MqttUserService.php` - Service for MQTT user management
-   `app/Console/Commands/MqttUserCommand.php` - Artisan command for manual management

### Modified Files:

-   `app/Http/Controllers/Auth/RegisteredUserController.php` - Auto-add on registration
-   `app/Http/Controllers/ProfileController.php` - Auto-remove on account deletion

---

## 🔧 Configuration

### Password File Location:

```
mqtt-brokers/secure/config/passwordfile
```

### Docker Container Name:

```
mosq_secure
```

If you change the container name in `docker-compose.yml`, update it in `MqttUserService.php`:

```php
// Line ~126
protected function reloadMosquittoContainer(): void
{
    Process::run("docker exec YOUR_CONTAINER_NAME pkill -HUP mosquitto");
}
```

---

## 🛠️ Troubleshooting

### Problem: User added but can't connect

**Solution:**

1. Check if password file exists:
    ```bash
    ls mqtt-brokers/secure/config/passwordfile
    ```
2. Manually reload container:
    ```bash
    docker restart mosq_secure
    ```
3. Check logs:
    ```bash
    docker logs mosq_secure
    ```

### Problem: "Failed to add user" error

**Solution:**

1. Verify Docker container is running:
    ```bash
    docker ps | findstr mosq_secure
    ```
2. Check Laravel logs:
    ```bash
    tail -f storage/logs/laravel.log
    ```
3. Manually test Docker command:
    ```bash
    docker exec mosq_secure mosquitto_passwd -h
    ```

### Problem: Special characters in password

**Solution:**
The system automatically escapes special characters, but if issues occur:

1. Use simpler passwords for testing
2. Check `storage/logs/laravel.log` for errors
3. Manually add user with command:
    ```bash
    php artisan mqtt:user add user@test.com
    # Enter password when prompted
    ```

---

## 🔒 Security Notes

### Password Storage:

1. **Laravel Database**: Passwords are hashed using bcrypt
2. **MQTT Password File**: Passwords are hashed using Mosquitto's SHA512-PBKDF2
3. **Different Hashes**: The same password has different hashes in each system (this is normal and secure!)

### Important:

-   Users use their **plain text password** when connecting to MQTT
-   The system stores only **hashed versions**
-   Original passwords are never stored or logged

### Example:

```
User registers with password: "mypassword123"

In Laravel Database:
$2y$12$abcd1234... (bcrypt hash)

In MQTT Password File:
$7$101$xyz789... (SHA512-PBKDF2 hash)

User connects with:
Plain text: "mypassword123"
```

---

## 📊 Current Users

To see all MQTT users:

```bash
cat mqtt-brokers/secure/config/passwordfile
```

Or on Windows:

```powershell
Get-Content mqtt-brokers\secure\config\passwordfile
```

---

## 🎯 Best Practices

1. **Keep Docker Running**: MQTT broker must be running for auto-registration
2. **Monitor Logs**: Check `storage/logs/laravel.log` for MQTT registration issues
3. **Regular Backups**: Backup `passwordfile` before making manual changes
4. **Test After Changes**: Always test connection after adding users

---

## ✅ Success Indicators

After registration, you should see:

1. ✓ User created in Laravel database
2. ✓ User added to MQTT password file
3. ✓ Log entry: "New user registered and added to MQTT broker"
4. ✓ User can connect to secure broker (port 8883)
5. ✓ Scanner shows secure sensors when using credentials

---

## 🔄 Manual vs Automatic

| Method               | When to Use                                     |
| -------------------- | ----------------------------------------------- |
| **Automatic**        | Normal user registration (recommended)          |
| **Manual Command**   | Testing, fixing issues, adding service accounts |
| **Direct File Edit** | Emergency only, not recommended                 |

---

## 📞 Support

If you encounter issues:

1. Check logs: `storage/logs/laravel.log`
2. Verify Docker: `docker ps`
3. Test manually: `php artisan mqtt:user add test@test.com testpass`
4. Restart broker: `docker restart mosq_secure`

---

**🎉 You're all set!** Users can now register and immediately connect to the secure MQTT broker without any manual configuration!
