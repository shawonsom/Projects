# Installing Zimbra Open Source Edition (OSE) on Ubuntu 22.04 
To run Zimbra **without a license**, you must use a **Community OSE Build** (such as those from Maldua or Intalio) because the "Official" version 10 binaries are usually locked behind a support portal.

Minimum Hardware Specifications

For a single-server installation (handling roughly 10–50 users), these are the requirements:

| Resource | Absolute Minimum (Testing) | Recommended (Production) |
| --- | --- | --- |
| **CPU** | 64-bit 2.0 GHz (2 Cores) | 64-bit Quad-Core (4+ vCPUs) |
| **RAM** | **8 GB** | **16 GB or more** |
| **Disk Space** | 10 GB (Software only) | 50 GB to 250 GB+ (includes mail) |
| **Swap Space** | 4 GB | Equal to RAM (up to 16GB) |

> **Warning:** Attempting to run Zimbra with less than 8 GB of RAM will likely cause the "mailbox" service to crash or fail to start, as the Java heap size alone is often pre-configured to consume several gigabytes.

---

### 2. Network & OS Requirements

Beyond the physical hardware, your Ubuntu 22.04 environment must be pre-configured to avoid "Dependency" or "Service Conflict" errors:

* **Static IP Address:** Your server (`172.17.64.26`) must be static. Zimbra will fail if the IP changes via DHCP.
* **DNS Records:** You must have a valid **A record** (mail.shimantosom.org) and an **MX record** (shimantosom.org) pointing to your IP. Zimbra checks these during installation.
* **Disabled Services:**
* **AppArmor:** Must be stopped/disabled (it interferes with Zimbra's process permissions).
* **Systemd-resolved:** Must be disabled to free up port 53.
* **Postfix/Exim:** Any existing mail transfer agents must be removed.

------------------

### Phase 1: System Preparation
Zimbra is very sensitive to hostname and DNS settings. Your current hostname `mail.shimantosom.org` is perfect.

#### 1. Configure the Hosts File
Ensure your local IP is mapped correctly.

```bash
nano /etc/hosts
```

Add or modify the line to look like this (using your `eth0` IP):
`172.17.64.26 mail.shimantosom.org mail`

#### 2. Disable Conflicting Services

Ubuntu 22.04 runs `systemd-resolved` and often a default `postfix` instance, both of which will block Zimbra.

```bash
# Stop and disable Postfix
systemctl stop postfix
systemctl disable postfix

# Disable systemd-resolved (Zimbra manages its own DNS cache)
systemctl disable systemd-resolved
systemctl stop systemd-resolved

# Delete the symlink and create a static resolv.conf
rm /etc/resolv.conf
echo "nameserver 8.8.8.8" > /etc/resolv.conf
echo "nameserver 1.1.1.1" >> /etc/resolv.conf

```

#### 3. Update & Install Dependencies

```bash
apt update && apt upgrade -y
apt install libgmp10 libperl5.34 unzip pax sysstat sqlite3 wget netcat-openbsd -y

```

---

### Phase 2: Download & Install Zimbra 10 OSE

Since there is no "official" public download link for a free Zimbra 10 binary on the main site, we use the reputable **Maldua Community Build** which is specifically compiled for Ubuntu 22.04 FOSS users.

#### 1. Download the Installer

```bash
cd /opt
# Download the latest OSE build for Ubuntu 22
wget https://github.com/maldua/zimbra-foss/releases/download/zimbra-foss-build-ubuntu-22.04/10.1.10.p3/zcs-10.1.10_GA_4200003.UBUNTU22_64.20251107221239.tgz

# Extract
tar -xvf zcs-10.1.10_GA_4200003.UBUNTU22_64.20251107221239.tgz
mv zcs-10.1.10_GA_4200003.UBUNTU22_64.20251107221239 zcs-10.1.10
cd zcs-10.1.10

```

#### 2. Run the Installation Script

Use the `--skip-activation-check` flag to ensure the installer doesn't look for a Network Edition license.

```bash
./install.sh --skip-activation-check

```

* **Agreements:** Type `Y` for all license agreements.
* **Package Selection:** Select `Y` for all default components. (Note: `zimbra-dnscache` is usually recommended if you don't have a local DNS server).
* **System Modification:** Type `Y` to allow the script to modify the system.

---

### Phase 3: Configuration (The Menu)

During the install, you will see a menu with asterisk `*******` items that need attention.

1. **Address DNS Error:** If it asks "Change domain name?", select **Yes** and enter `shimantosom.org` (the root domain, not the hostname).
2. **Set Admin Password:** * Press `7` (zimbra-store).
* Press `4` (Admin Password).
* Type your desired password.
* Press `r` to go back.


3. **Apply:** Press `a` to apply configuration, then `y` to save and finish.

---

### Phase 4: Final Steps

Once the script finishes, switch to the `zimbra` user to check the status:

```bash
su - zimbra
zmcontrol status

```

> **Note:** It may take 2–5 minutes for all services (especially `mailbox`) to start fully.

### Access the Panels:

* **Admin Console:** `https://172.17.64.26:7071`
* **Webmail:** `https://172.17.64.26`

---

**Would you like me to show you how to configure the SPF and DKIM records for your domain so your emails don't go to spam?**
