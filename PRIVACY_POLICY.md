# PRIVACY POLICY

**Software Name:** Local Project Server (LPS) / LPS Core  
**Version:** 1.2.0  
**Effective Date:** November 2026  
**Publisher:** LPS Development Team  

---

### OUR PRIVACY COMMITMENT: ZERO TELEMETRY, 100% LOCAL PROCESSING

At the LPS Development Team, we firmly believe that your local development environment should remain private, confidential, and completely under your control. We designed **Local Project Server (LPS)** with an uncompromising **"Privacy by Design"** philosophy.

**LPS operates strictly on your local machine. We do not track you, we do not profile you, and we do not collect your data.**

Please read this Privacy Policy carefully to understand how LPS handles your local data, network connections, and privacy.

---

### 1. INFORMATION WE DO NOT COLLECT (ZERO TELEMETRY)

LPS is built without analytics trackers, telemetry daemons, or remote reporting mechanisms. Specifically:

1.1 **No Personal Data:** LPS does not collect your name, email address, physical location, username, or contact information. You are never required to create an account, log in, or register to use LPS.

1.2 **No Device or Hardware Identifiers:** LPS does not collect or transmit your MAC address, hardware UUIDs, CPU serial numbers, Windows Product ID, or hostname to any external server.

1.3 **No Usage Analytics or Behavioral Tracking:** LPS contains zero analytics SDKs (no Google Analytics, no Mixpanel, no Sentry, no telemetry pings). We do not track which features you use, which projects you create, what PHP versions you switch to, or how long you run servers.

1.4 **No Crash Reports or Log Uploads:** LPS does not automatically transmit crash dumps, panic traces, or error logs to the cloud. All system logs, PHP logs, Apache logs, and database logs remain exclusively inside your local project folders on your own hard drive.

---

### 2. LOCAL PROCESSING ARCHITECTURE

2.1 **Localhost Only (`127.0.0.1`):**  
All web servers, database engines, and background supervisors spawned by LPS bind exclusively to the local loopback network interface (`127.0.0.1` / `localhost`). They do not listen on public network interfaces unless you manually configure port forwarding or tunnels on your own operating system.

2.2 **Project Isolation:**  
Your source code, HTML/PHP files, WordPress themes and plugins, media uploads, and database tables are stored solely in the `projects\` folder on your local filesystem. LPS never scans, inspects, or uploads your project files.

---

### 3. NETWORK COMMUNICATIONS & OUTBOUND TRAFFIC

LPS only performs network operations when you explicitly request an action that requires internet access:

3.1 **User-Initiated Engine Downloads:**  
When you click **"Download"** in the "Engines & Packages" tab (e.g., to install PHP, MariaDB, Apache, or WordPress), LPS establishes a direct, encrypted HTTPS connection to the **official upstream open-source project mirrors**:
- **PHP:** `windows.php.net`
- **MariaDB:** `mariadb.org` / `downloads.mariadb.com`
- **Apache:** `apachelounge.com` / `apache.org`
- **WordPress:** `wordpress.org`
- **phpMyAdmin:** `phpmyadmin.net` / GitHub releases

3.2 **No Intermediate Proxy:**  
These download requests go directly from your computer to the official provider's download server. They never pass through any LPS proxy, analytics server, or intermediate broker.

3.3 **Version Check Manifests:**  
LPS retrieves static JSON release catalogs directly from official public release APIs to show you which versions of PHP, Apache, and WordPress are available. These checks do not send any identifiable information about you or your projects.

---

### 4. DATA STORAGE & SECURITY

4.1 **Local Storage Only:**  
All configuration settings (such as project ports, database names, and runtime selections) are saved locally in plain text JSON files within your `LPS\config\` directory.

4.2 **Database Security:**  
Your local MariaDB databases are initialized with standard local development credentials (listening only on `127.0.0.1:3306`). LPS never exposes your databases to external networks by default.

4.3 **Data Deletion:**  
Because LPS stores all data locally, deleting a project from the LPS dashboard or deleting the `LPS` folder permanently and irrevocably erases all associated files and databases from your computer. No data remains on any external cloud server because no data was ever sent there.

---

### 5. CHILDREN’S PRIVACY

LPS does not knowingly collect or solicit any personal information from children under the age of 13 (or under 16 in certain jurisdictions), nor does it have the technical capability to do so, as it collects no data from any user.

---

### 6. CHANGES TO THIS PRIVACY POLICY

We may update this Privacy Policy from time to time to reflect improvements or modifications to LPS. Any updates will be included directly in subsequent software releases in this `PRIVACY_POLICY.md` file with a revised "Effective Date".

---

### 7. CONTACT & INQUIRIES

If you have any questions, concerns, or inquiries regarding this Privacy Policy or the security architecture of Local Project Server, please review the documentation in `USER_MANUAL.md` or contact the LPS Development Team through our official repository channels.

---

**Copyright © 2026 LPS Development Team. All Rights Reserved.**  
Local Project Server (LPS) — Truly Local, Truly Private.
