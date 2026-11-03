# Local Project Server (LPS) — Comprehensive User Manual & Setup Guide

**Version:** 1.2.0  
**Operating System:** Windows 10 / Windows 11 (64-bit AMD64)  
**License:** Proprietary Freeware (See `LICENSE.md`)  
**Privacy:** 100% Local & Zero Telemetry (See `PRIVACY_POLICY.md`)  

---

## 📖 Table of Contents
1. [Overview & What Makes LPS Different](#1-overview)
2. [Quick Start (Zero Installation)](#2-quick-start)
3. [Setting Up Runtimes (2 Easy Methods)](#3-setting-up-runtimes)
   - [Method A: Manual Setup via Official Websites (Recommended & Secure)](#method-a-manual-setup-via-official-websites-recommended--secure)
   - [Method B: 1-Click Automated Setup via Dashboard (Convenience Mode)](#method-b-1-click-automated-setup-via-dashboard-convenience-mode)
4. [Creating & Managing Projects](#4-creating--managing-projects)
5. [Running Sites & Independent Port Architecture](#5-running-sites--independent-port-architecture)
6. [WordPress Development: WP Admin vs Public Preview](#6-wordpress-development)
7. [Database Management with MariaDB Studio](#7-database-management)
8. [Directory Structure & Project File Storage](#8-directory-structure)
9. [Project Settings & Customizations](#9-project-settings--customizations)
10. [Troubleshooting & FAQs](#10-troubleshooting--faqs)

---

## 1. Overview & Feature Status

**Local Project Server (LPS)** is a lightweight, high-performance local web engineering platform for Windows. Designed as a modern alternative to monolithic tools like XAMPP and resource-heavy virtual machines like Docker, LPS is **project-centric**:

- **Independent Project Isolation:** Every project runs on its own dedicated port (`8081`, `8082`, `8083`...), with isolated logs and custom PHP versions.
- **Dual Server Engines:** Switch between ultra-fast PHP Built-in Server (for rapid prototyping) or full-featured Apache HTTP Server (with `mod_rewrite`, `.htaccess`, and CGI for WordPress).
- **Central Shared MariaDB:** All projects share a single high-efficiency MariaDB database daemon on port `3306`, saving system memory while keeping project databases completely isolated.
- **Zero-Dependency Portability:** LPS requires no installers, no system environment variables, and no registry changes. You can run it from any folder or USB drive.

### 🧪 Runtime Testing & Status Matrix

| Component / Runtime | Status | Operational Details |
| :--- | :--- | :--- |
| **PHP (8.1, 8.2, 8.3, 8.4)** | 🟢 **100% Tested & Stable** | Both Built-in server and Apache FCGI/CGI runners fully verified. Multi-PHP concurrent execution supported. |
| **WordPress Development** | 🟢 **100% Tested & Stable** | Automated scaffolding, dynamic `wp-config.php` port adaptation, database provisioning, and WP Admin routing verified. |
| **MariaDB & LPS Database Manager** | 🟢 **100% Tested & Stable** | 1-click passwordless database management, auto-cluster initialization (`mariadb-install-db.exe`), and port 3306 stability verified. |
| **Node.js & Python Runtimes** | 🟡 **Functional / Beta Preview** | Engine runners and environment loaders implemented; undergoing broader real-world community testing. |
| **Next.js & React+Vite Templates** | 🟡 **Functional / Beta Preview** | Scaffolding templates and supervisor process integration active; community feedback encouraged. |

---

## 2. Quick Start

1. **Extract the ZIP:** Extract the `LPS` folder to any directory (e.g. `C:\LPS`, `D:\LPS`, or desktop).
2. **Launch the Engine:** Double-click **`lps.exe`**.
3. **Open the Dashboard:** LPS will start and automatically open your default browser at:  
   👉 **`http://localhost:9999`**  
   *(If your browser does not open automatically, simply open Chrome, Edge, or Brave and navigate to `http://localhost:9999`).*

---

## 3. Setting Up Runtimes

LPS gives you two distinct ways to prepare runtime engines (PHP, MariaDB, Apache, WordPress). 

> [!IMPORTANT]
> ### 🛡️ Security & Long-Term Reliability Notice (Why Manual Setup is Recommended)
> In production and serious development environments, **Method A (Manual Setup via Official Portals)** is strongly recommended over in-app automated fetchers:
> 
> 1. **Upstream Link Immunity & Resilience:** In-app 1-click downloaders rely on specific upstream URLs and CDN mirrors configured during software development. Over time, open-source organizations (like PHP, Apache, and MariaDB) frequently restructure their download directories, migrate hosting servers, or deprecate older release URLs. If an upstream URL changes and your LPS build hasn't been updated, automated downloads may fail or encounter broken links.
> 2. **Cryptographic Authenticity & Security Patches:** By visiting the official upstream portals directly, you obtain the absolute latest, cryptographically signed binaries containing fresh CVE security fixes directly from primary maintainers.
> 3. **Complete Independence & Control:** You retain 100% control over the exact PHP version, MariaDB release, and Apache extension set running on your machine without relying on external network scripts.

---

### Method A: Manual Setup via Official Websites (Recommended & Secure)

For maximum stability, security, and future-proof offline independence, download clean packages directly from the official software publishers and place them into the designated LPS folders:

#### 1. PHP Runtimes (`php_server/`)
- **Official Portal:** [https://windows.php.net/download/](https://windows.php.net/download/)
- **Recommended Build:** Download the **VS16 x64 Non-Thread Safe (NTS)** or **Thread Safe (TS)** Zip archive for your chosen PHP version (e.g. PHP 8.4, 8.3, or 8.2).
- **Target Folder:** Create a folder inside `LPS\php_server\` named after the version string (e.g., `8.4.2-1`, `8.3.15-1`).
- **Placement:** Extract the ZIP so that `php.exe` is located at:
  ```
  LPS\php_server\<version>\php.exe
  ```
  *(Example: `LPS\php_server\8.3.15-1\php.exe`)*

#### 2. Apache HTTP Server (`web_server/`)
- **Official Portal:** [https://www.apachelounge.com/download/](https://www.apachelounge.com/download/)
- **Recommended Build:** Download Apache 2.4 (VS17 Win64) C++ build.
- **Target Folder:** Extract the archive so the `Apache24` folder contents reside in:
  ```
  LPS\web_server\apache\
  ```
  Ensure that `httpd.exe` is directly located at:
  ```
  LPS\web_server\apache\bin\httpd.exe
  ```

#### 3. MariaDB Database Server (`database_server/`)
- **Official Portal:** [https://mariadb.org/download/](https://mariadb.org/download/)
- **Recommended Build:** Select MariaDB Server (e.g. 12.3, 11.4, or 10.11), Package: **ZIP Archive**, OS: **Windows x86_64**.
- **Target Folder:** Extract the ZIP archive so that the binaries reside in:
  ```
  LPS\database_server\mysql\12.3\
  ```
  Ensure that `mariadbd.exe` and `mariadb-install-db.exe` are located at:
  ```
  LPS\database_server\mysql\12.3\bin\mariadbd.exe
  ```
- ⚠️ **Critical Database Note:** Do **NOT** copy, move, or manually create a `data` folder! When LPS launches, its supervisor automatically detects if a local database cluster exists. If not, it executes `mariadb-install-db.exe` in under 2 seconds to generate a fresh, secure database tailored strictly to your local machine.

#### 4. Database Administration Tools (`tools/`)
- **LPS Database Manager (Pre-bundled Zero-Config GUI):** Pre-installed and ready out of the box inside `LPS\tools\adminer\`. Offers instant 1-click passwordless database management.
- **phpMyAdmin (Optional Comprehensive Web GUI):** If you wish to add phpMyAdmin as an alternative, download the complete multi-language zip from [https://www.phpmyadmin.net/](https://www.phpmyadmin.net/), extract it, and place its contents at:
  ```
  LPS\tools\phpMyAdmin\index.php
  ```

#### 5. WordPress Core Release (`downloaded/`)
- **Official Portal:** [https://wordpress.org/download/](https://wordpress.org/download/)
- **Placement:** Download `latest.zip` (or any specific version such as `wordpress-6.7.1.zip`) directly and place it into:
  ```
  LPS\downloaded\
  ```
  LPS automatically scans `LPS\downloaded\` and offers your cached package in the project creation wizard for instant 1-second offline project creation.

---

### Method B: 1-Click Automated Setup via Dashboard (Convenience Mode)

If you are on an active internet connection and want rapid scaffolding for evaluation:

1. Launch **`lps.exe`** and open the dashboard at `http://localhost:9999`.
2. In the left navigation sidebar, click **"Engines & Packages"**.
3. Under the respective tabs:
   - **PHP Versions:** Click **"Download"** on your preferred PHP version.
   - **Server Runtimes & Tools:** Click **"Download"** next to **MariaDB**, **Apache**, or optional tools.
   - **WordPress Releases:** Click **"Download"** next to WordPress to cache it locally.
4. LPS connects directly to upstream servers, downloads the clean packages, verifies them, extracts them to their designated folders, and sets them up automatically.
> *Note:* Automated downloads depend on external upstream server availability and URLs recorded during LPS build time. If any upstream mirror undergoes restructuring or changes its download endpoints, simply use **Method A (Recommended)** to obtain packages directly from official vendor portals.

---

## 4. Creating & Managing Projects

1. Click **`+ New Project`** on the LPS top bar or home screen.
2. Enter your project specifications:
   - **Project Name:** (e.g. `Client Shop` or `My Blog`). LPS auto-generates a clean URL slug and database name.
   - **Project Type:**
     - **WordPress:** Scaffolds a full WordPress installation with auto-configured `wp-config.php`, security salts, and database connection.
     - **Plain PHP:** Creates a clean PHP workspace with an `index.php` entrypoint.
   - **PHP Version:** Select whichever installed PHP runtime you wish to assign to this specific project.
   - **Web Server Runner:** Select **Apache** (ideal for WordPress `.htaccess`) or **PHP Built-in** (lightweight).
   - **Database:** Enabled by default (auto-generates an isolated database name, e.g. `client_shop_db`).
3. Click **Create Project**. Within seconds, your project is created and listed on the dashboard.

---

## 5. Running Sites & Independent Port Architecture

Every project in LPS is assigned its own unique HTTP port (`8081`, `8082`, `8083`...):

- **Running a Project:** Click the **`▶ Start`** button on any project card. The status dot will turn green: **● Running**.
- **Running Multiple Projects Concurrently:** You can run 2, 5, or 10 projects at the exact same time! Project A runs on port `8081`, Project B runs on port `8082`, and so forth.
- **Stopping a Project:** Click **`⏹ Stop`** on the project card to halt that project's web server process.
- **Stop All:** Click the **`⏹ Stop All`** button in the top navigation bar to halt all running projects simultaneously.

---

## 6. WordPress Development: WP Admin vs Public Preview

For WordPress projects, LPS provides dedicated, labeled action buttons so you never have to guess URLs:

- **`⚡ WP Admin ↗` (Primary Blue Action):**  
  Opens the WordPress Admin Dashboard directly in your default browser:  
  `http://localhost:8082/wp-admin/`
- **`🌐 Site ↗` (Secondary Preview Action):**  
  Opens the public visitor-facing frontend of your WordPress site:  
  `http://localhost:8082`

> 💡 **Dynamic Host & Port Routing:** LPS injects dynamic host detection into your `wp-config.php`. If you ever change your project's port (e.g. from 8081 to 8085), WordPress automatically adapts and updates internal links without redirect loops!

---

## 7. Database Management with MariaDB Studio

LPS manages a central, high-performance MariaDB 12.3 daemon running on standard port `3306`:

- **Host:** `127.0.0.1` (or `localhost`)
- **Port:** `3306`
- **Username:** `root`
- **Password:** *(Blank / Empty)*

### Accessing Database Studio:
Click the **🗄️ Database** button on any project card or inside the Project Details action bar. You can choose between:
1. **LPS Database Manager:** Instant 1-click passwordless database browser.
2. **phpMyAdmin (Optional):** Advanced table schema editor, SQL console, query profiler, and import/export hub (if installed).

---

## 8. Directory Structure & Project File Storage

All your files, uploads, and assets reside directly inside your `LPS` folder:

```text
LPS\
├── lps.exe                  # Main LPS Executable & Dashboard Server
├── USER_MANUAL.md           # This user guide
├── LICENSE.md               # End User License Agreement
├── PRIVACY_POLICY.md        # Zero-telemetry privacy policy
│
├── projects\                # YOUR WEBSITES LIVE HERE!
│   └── my-portfolio\
│       ├── public\          # Webroot (Put themes, plugins, PHP scripts here)
│       │   ├── index.php
│       │   ├── wp-content\
│       │   └── wp-config.php
│       ├── logs\            # Project-specific error and access logs
│       └── project.json     # Project configuration file
│
├── php_server\              # Installed PHP binaries
├── database_server\         # MariaDB binaries & auto-generated data directory
├── web_server\              # Apache HTTP Server binaries
├── tools\                   # Integrated LPS Database Manager
├── downloaded\              # Cached WordPress zip packages
└── config\                  # LPS application registry
```

> 💡 **Quick Folder Access:** On any project card, click the **📁 folder icon** to open that project's folder directly in Windows File Explorer!

---

## 9. Project Settings & Customizations

From the Project Details page (click on any project card), you can customize:
- **Web Port:** Change the port from `8081` to any custom port (e.g., `8090`, `3000`).
- **PHP Version:** Switch PHP version with 1 click.
- **Web Server:** Toggle between PHP Built-in Server and Apache HTTP Server.
- **Webroot Directory:** Change webroot from `public` to any subfolder.
- **Console Logs:** View real-time stdout and stderr output from PHP and Apache.

---

## 10. Troubleshooting & FAQs

**Q: Port collision or "Port already occupied" alert?**  
A: LPS checks port availability before starting any server. If a port is occupied by another program (e.g. Skype, IIS, Docker), LPS alerts you. Simply change the port in the project's Settings tab to any free port.

**Q: Can I move the LPS folder to another computer or drive?**  
A: Yes! LPS is 100% portable. You can copy or move the entire `LPS` directory to another drive (e.g., from `C:\` to `D:\`) or a USB drive without reconfiguring anything.

**Q: How do I completely exit LPS?**  
A: Press `Ctrl + C` in the console window running `lps.exe`, or close the console window. LPS will safely stop all running PHP and Apache background processes.

---

*Thank you for developing with Local Project Server (LPS)!*
