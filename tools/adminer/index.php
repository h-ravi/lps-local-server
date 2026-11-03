<?php
/**
 * LPS Database Manager (Database Manager)
 * 100% Standalone, Zero-Branding, Modern Local Database Manager.
 */

// Purge any lingering Adminer donation/logout flash messages from session
if (session_status() === PHP_SESSION_NONE && !empty($_COOKIE)) {
    $sess_name = session_name();
    if (isset($_COOKIE[$sess_name])) {
        @session_start();
        if (!empty($_SESSION["messages"])) {
            foreach ($_SESSION["messages"] as $k => $m) {
                $s = strtolower((string)$m);
                if (strpos($s, 'adminer') !== false || strpos($s, 'donat') !== false || strpos($s, 'thanks for using') !== false) {
                    unset($_SESSION["messages"][$k]);
                }
            }
        }
        @session_write_close();
    }
}

function adminer_object() {
    if (class_exists('\\Adminer\\Adminer', false)) {
        class LPS_Adminer extends \Adminer\Adminer {
            function name() {
                return '<span class="lps-brand-badge">LPS</span> Database Manager';
            }
            function head($Tb = null) {
                ?>
                <link rel="stylesheet" href="adminer.css">
                <script>
                document.addEventListener('DOMContentLoaded', () => {
                    // 1. Rename document title
                    if (document.title.indexOf('Adminer') !== -1) {
                        document.title = document.title.replace(/Adminer/gi, 'LPS Database Manager');
                    } else if (!document.title.includes('LPS')) {
                        document.title = 'LPS Database Manager - ' + document.title;
                    }

                    // 2. Remove all external Adminer links
                    document.querySelectorAll('a[href*="adminer.org"]').forEach(el => el.remove());
                    
                    // 3. Remove version elements and language selector
                    document.querySelectorAll('#version, .version, #lang').forEach(el => el.remove());

                    // 4. Remove donation / Adminer logout notifications completely
                    document.querySelectorAll('.message, #content > .message').forEach(el => {
                        const txt = (el.textContent || '').toLowerCase();
                        if (txt.includes('adminer') || txt.includes('donat') || txt.includes('thanks for using')) {
                            el.remove();
                        } else if (txt.includes('no tables')) {
                            el.classList.add('lps-empty-card');
                            const params = new URLSearchParams(window.location.search);
                            const srv = encodeURIComponent(params.get('server') || '');
                            const usr = encodeURIComponent(params.get('username') || '');
                            const db = encodeURIComponent(params.get('db') || '');
                            el.innerHTML = '<div class="lps-empty-graphic">' +
                                '<svg width="46" height="46" viewBox="0 0 24 24" fill="none" stroke="#818cf8" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">' +
                                '<ellipse cx="12" cy="5" rx="9" ry="3"></ellipse>' +
                                '<path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"></path>' +
                                '<path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"></path>' +
                                '</svg></div>' +
                                '<h3 class="lps-empty-title">No tables found in this database</h3>' +
                                '<p class="lps-empty-desc">Create your first database table or import an existing SQL dump file to start managing data.</p>' +
                                '<div class="lps-empty-actions">' +
                                '<a href="?server=' + srv + '&username=' + usr + '&db=' + db + '&create=" class="lps-empty-btn lps-empty-btn-primary">+ Create Table</a>' +
                                '<a href="?server=' + srv + '&username=' + usr + '&db=' + db + '&import=" class="lps-empty-btn lps-empty-btn-sec">Import SQL</a>' +
                                '</div>';
                        } else {
                            setTimeout(() => {
                                el.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
                                el.style.opacity = '0';
                                el.style.transform = 'translateY(-10px)';
                                setTimeout(() => el.remove(), 400);
                            }, 4500);
                        }
                    });

                    // 5. Clean up breadcrumbs
                    const breadcrumb = document.getElementById('breadcrumb');
                    if (breadcrumb) {
                        breadcrumb.childNodes.forEach(node => {
                            if (node.nodeType === Node.TEXT_NODE && node.textContent.includes('»')) {
                                node.textContent = node.textContent.replace(/»/g, ' / ');
                            }
                        });
                    }
                });
                </script>
                <?php
                return true;
            }
            function login($login, $password) {
                return true;
            }
            function credentials() {
                $server = !empty($_POST['auth']['server']) ? $_POST['auth']['server'] : (isset($_GET['server']) && $_GET['server'] !== '' ? $_GET['server'] : (defined('SERVER') && SERVER ? SERVER : '127.0.0.1:3306'));
                $user = !empty($_POST['auth']['username']) ? $_POST['auth']['username'] : (isset($_GET['username']) && $_GET['username'] !== '' ? $_GET['username'] : 'root');
                $pass = isset($_POST['auth']['password']) ? $_POST['auth']['password'] : (isset($_GET['password']) ? $_GET['password'] : '');
                return array($server, $user, $pass);
            }
            function database() {
                if (!empty($_POST['auth']['db'])) {
                    return $_POST['auth']['db'];
                }
                return isset($_GET['db']) ? $_GET['db'] : '';
            }
            function loginForm() {
                $server = isset($_GET['server']) && $_GET['server'] !== '' ? $_GET['server'] : (defined('SERVER') && SERVER ? SERVER : '127.0.0.1:3306');
                if (empty($server)) $server = '127.0.0.1:3306';
                $username = isset($_GET['username']) && $_GET['username'] !== '' ? $_GET['username'] : 'root';
                $db = isset($_GET['db']) ? $_GET['db'] : '';
                ?>
                <style>
                    #menu, #foot, #breadcrumb, .logout, .message, p.message, .error, p.error, #content > h2 { display: none !important; }
                    #content { margin: 0 !important; padding: 0 !important; width: 100% !important; min-height: 100vh !important; background: #090d16 !important; }
                    body { background: #090d16 !important; overflow-x: hidden !important; }
                </style>
                <div class="lps-split-container">
                    <!-- LEFT PANEL: Hero & Branding (Inspired by Reference Design) -->
                    <div class="lps-hero-panel">
                        <div class="lps-hero-glow"></div>
                        <div class="lps-hero-inner">
                            <div class="lps-hero-icon-box">
                                <svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <ellipse cx="12" cy="5" rx="9" ry="3"></ellipse>
                                    <path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"></path>
                                    <path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"></path>
                                </svg>
                            </div>
                            <h1 class="lps-hero-title">LPS Database Manager</h1>
                            <p class="lps-hero-subtitle">High-speed, zero-config local database management built for WordPress &amp; PHP projects.</p>
                            
                            <div class="lps-hero-pills">
                                <span class="lps-pill"><span class="lps-pill-dot"></span>Zero-Config</span>
                                <span class="lps-pill"><span class="lps-pill-dot"></span>MariaDB 10.11</span>
                                <span class="lps-pill"><span class="lps-pill-dot"></span>100% Local</span>
                            </div>

                            <div class="lps-hero-card">
                                <p class="lps-hero-quote">&ldquo;Instant local database access with zero memory overhead and seamless project switching.&rdquo;</p>
                                <div class="lps-hero-author">
                                    <div class="lps-hero-avatar">LPS</div>
                                    <div>
                                        <div class="lps-author-name">Local Project Server</div>
                                        <div class="lps-author-sub">Development Environment</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- RIGHT PANEL: Sleek Auth Form -->
                    <div class="lps-form-panel">
                        <div class="lps-form-box">
                            <div class="lps-form-header">
                                <h2 class="lps-form-title">Sign in to your database</h2>
                                <p class="lps-form-sub">Enter your credentials to manage tables and execute SQL</p>
                            </div>

                            <div class="lps-fields">
                                <div class="lps-group">
                                    <label class="lps-label">DATABASE SYSTEM</label>
                                    <div class="lps-system-badge">
                                        <span class="lps-system-dot"></span>
                                        <span>MySQL / MariaDB (Local)</span>
                                        <input type="hidden" name="auth[driver]" value="server">
                                    </div>
                                </div>

                                <div class="lps-group">
                                    <label class="lps-label" for="lps_server">SERVER HOST &amp; PORT</label>
                                    <div class="lps-input-wrap">
                                        <span class="lps-input-icon">
                                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <rect x="2" y="2" width="20" height="8" rx="2" ry="2"></rect>
                                                <rect x="2" y="14" width="20" height="8" rx="2" ry="2"></rect>
                                                <line x1="6" y1="6" x2="6.01" y2="6"></line>
                                                <line x1="6" y1="18" x2="6.01" y2="18"></line>
                                            </svg>
                                        </span>
                                        <input type="text" id="lps_server" name="auth[server]" value="<?php echo htmlspecialchars($server); ?>" placeholder="127.0.0.1:3306" required autocomplete="off">
                                    </div>
                                </div>

                                <div class="lps-group">
                                    <label class="lps-label" for="username">USERNAME</label>
                                    <div class="lps-input-wrap">
                                        <span class="lps-input-icon">
                                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                                <circle cx="12" cy="7" r="4"></circle>
                                            </svg>
                                        </span>
                                        <input type="text" id="username" name="auth[username]" value="<?php echo htmlspecialchars($username); ?>" placeholder="root" required autocomplete="username">
                                    </div>
                                </div>

                                <div class="lps-group">
                                    <label class="lps-label" for="lps_password">PASSWORD</label>
                                    <div class="lps-input-wrap">
                                        <span class="lps-input-icon">
                                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                                            </svg>
                                        </span>
                                        <input type="password" id="lps_password" name="auth[password]" placeholder="Default: empty for local root" autocomplete="current-password">
                                    </div>
                                </div>

                                <div class="lps-group">
                                    <label class="lps-label" for="lps_db">DATABASE (OPTIONAL)</label>
                                    <div class="lps-input-wrap">
                                        <span class="lps-input-icon">
                                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <ellipse cx="12" cy="5" rx="9" ry="3"></ellipse>
                                                <path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"></path>
                                                <path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"></path>
                                            </svg>
                                        </span>
                                        <input type="text" id="lps_db" name="auth[db]" value="<?php echo htmlspecialchars($db); ?>" placeholder="e.g. 13_db (or choose after login)" autocomplete="off">
                                    </div>
                                </div>
                            </div>

                            <div class="lps-options">
                                <label class="lps-checkbox">
                                    <input type="checkbox" name="auth[permanent]" value="1" checked>
                                    <span>Remember connection</span>
                                </label>
                            </div>

                            <div class="lps-submit-row">
                                <button type="submit" class="lps-btn-login">
                                    <span>Sign In</span>
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                        <line x1="5" y1="12" x2="19" y2="12"></line>
                                        <polyline points="12 5 19 12 12 19"></polyline>
                                    </svg>
                                </button>
                            </div>

                            <div class="lps-footer-note">
                                <p>&copy; <?php echo date('Y'); ?> LPS &bull; Local Project Server &bull; <span>Database Manager</span></p>
                            </div>
                        </div>
                    </div>
                </div>
                <?php
            }
        }
    } else {
        class LPS_Adminer extends \Adminer {
            function name() {
                return '<span class="lps-brand-badge">LPS</span> Database Manager';
            }
            function head($Tb = null) {
                ?>
                <link rel="stylesheet" href="adminer.css">
                <script>
                document.addEventListener('DOMContentLoaded', () => {
                    if (document.title.indexOf('Adminer') !== -1) {
                        document.title = document.title.replace(/Adminer/gi, 'LPS Database Manager');
                    } else if (!document.title.includes('LPS')) {
                        document.title = 'LPS Database Manager - ' + document.title;
                    }
                    document.querySelectorAll('a[href*="adminer.org"]').forEach(el => el.remove());
                    document.querySelectorAll('#version, .version, #lang').forEach(el => el.remove());
                    document.querySelectorAll('.message, #content > .message').forEach(el => {
                        const txt = (el.textContent || '').toLowerCase();
                        if (txt.includes('adminer') || txt.includes('donat') || txt.includes('thanks for using')) {
                            el.remove();
                        } else if (txt.includes('no tables')) {
                            el.classList.add('lps-empty-card');
                            const params = new URLSearchParams(window.location.search);
                            const srv = encodeURIComponent(params.get('server') || '');
                            const usr = encodeURIComponent(params.get('username') || '');
                            const db = encodeURIComponent(params.get('db') || '');
                            el.innerHTML = '<div class="lps-empty-graphic">' +
                                '<svg width="46" height="46" viewBox="0 0 24 24" fill="none" stroke="#818cf8" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">' +
                                '<ellipse cx="12" cy="5" rx="9" ry="3"></ellipse>' +
                                '<path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"></path>' +
                                '<path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"></path>' +
                                '</svg></div>' +
                                '<h3 class="lps-empty-title">No tables found in this database</h3>' +
                                '<p class="lps-empty-desc">Create your first database table or import an existing SQL dump file to start managing data.</p>' +
                                '<div class="lps-empty-actions">' +
                                '<a href="?server=' + srv + '&username=' + usr + '&db=' + db + '&create=" class="lps-empty-btn lps-empty-btn-primary">+ Create Table</a>' +
                                '<a href="?server=' + srv + '&username=' + usr + '&db=' + db + '&import=" class="lps-empty-btn lps-empty-btn-sec">Import SQL</a>' +
                                '</div>';
                        } else {
                            setTimeout(() => {
                                el.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
                                el.style.opacity = '0';
                                el.style.transform = 'translateY(-10px)';
                                setTimeout(() => el.remove(), 400);
                            }, 4500);
                        }
                    });
                    const breadcrumb = document.getElementById('breadcrumb');
                    if (breadcrumb) {
                        breadcrumb.childNodes.forEach(node => {
                            if (node.nodeType === Node.TEXT_NODE && node.textContent.includes('»')) {
                                node.textContent = node.textContent.replace(/»/g, ' / ');
                            }
                        });
                    }
                });
                </script>
                <?php
                return true;
            }
            function login($login, $password) {
                return true;
            }
            function credentials() {
                $server = !empty($_POST['auth']['server']) ? $_POST['auth']['server'] : (isset($_GET['server']) && $_GET['server'] !== '' ? $_GET['server'] : (defined('SERVER') && SERVER ? SERVER : '127.0.0.1:3306'));
                $user = !empty($_POST['auth']['username']) ? $_POST['auth']['username'] : (isset($_GET['username']) && $_GET['username'] !== '' ? $_GET['username'] : 'root');
                $pass = isset($_POST['auth']['password']) ? $_POST['auth']['password'] : (isset($_GET['password']) ? $_GET['password'] : '');
                return array($server, $user, $pass);
            }
            function database() {
                if (!empty($_POST['auth']['db'])) {
                    return $_POST['auth']['db'];
                }
                return isset($_GET['db']) ? $_GET['db'] : '';
            }
            function loginForm() {
                $server = isset($_GET['server']) && $_GET['server'] !== '' ? $_GET['server'] : (defined('SERVER') && SERVER ? SERVER : '127.0.0.1:3306');
                if (empty($server)) $server = '127.0.0.1:3306';
                $username = isset($_GET['username']) && $_GET['username'] !== '' ? $_GET['username'] : 'root';
                $db = isset($_GET['db']) ? $_GET['db'] : '';
                ?>
                <style>
                    #menu, #foot, #breadcrumb, .logout, .message, p.message, .error, p.error, #content > h2 { display: none !important; }
                    #content { margin: 0 !important; padding: 0 !important; width: 100% !important; min-height: 100vh !important; background: #090d16 !important; }
                    body { background: #090d16 !important; overflow-x: hidden !important; }
                </style>
                <div class="lps-split-container">
                    <div class="lps-hero-panel">
                        <div class="lps-hero-glow"></div>
                        <div class="lps-hero-inner">
                            <div class="lps-hero-icon-box">
                                <svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <ellipse cx="12" cy="5" rx="9" ry="3"></ellipse>
                                    <path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"></path>
                                    <path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"></path>
                                </svg>
                            </div>
                            <h1 class="lps-hero-title">LPS Database Manager</h1>
                            <p class="lps-hero-subtitle">High-speed, zero-config local database management built for WordPress &amp; PHP projects.</p>
                            
                            <div class="lps-hero-pills">
                                <span class="lps-pill"><span class="lps-pill-dot"></span>Zero-Config</span>
                                <span class="lps-pill"><span class="lps-pill-dot"></span>MariaDB 10.11</span>
                                <span class="lps-pill"><span class="lps-pill-dot"></span>100% Local</span>
                            </div>

                            <div class="lps-hero-card">
                                <p class="lps-hero-quote">&ldquo;Instant local database access with zero memory overhead and seamless project switching.&rdquo;</p>
                                <div class="lps-hero-author">
                                    <div class="lps-hero-avatar">LPS</div>
                                    <div>
                                        <div class="lps-author-name">Local Project Server</div>
                                        <div class="lps-author-sub">Development Environment</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="lps-form-panel">
                        <div class="lps-form-box">
                            <div class="lps-form-header">
                                <h2 class="lps-form-title">Sign in to your database</h2>
                                <p class="lps-form-sub">Enter your credentials to manage tables and execute SQL</p>
                            </div>

                            <div class="lps-fields">
                                <div class="lps-group">
                                    <label class="lps-label">DATABASE SYSTEM</label>
                                    <div class="lps-system-badge">
                                        <span class="lps-system-dot"></span>
                                        <span>MySQL / MariaDB (Local)</span>
                                        <input type="hidden" name="auth[driver]" value="server">
                                    </div>
                                </div>

                                <div class="lps-group">
                                    <label class="lps-label" for="lps_server">SERVER HOST &amp; PORT</label>
                                    <div class="lps-input-wrap">
                                        <span class="lps-input-icon">
                                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <rect x="2" y="2" width="20" height="8" rx="2" ry="2"></rect>
                                                <rect x="2" y="14" width="20" height="8" rx="2" ry="2"></rect>
                                                <line x1="6" y1="6" x2="6.01" y2="6"></line>
                                                <line x1="6" y1="18" x2="6.01" y2="18"></line>
                                            </svg>
                                        </span>
                                        <input type="text" id="lps_server" name="auth[server]" value="<?php echo htmlspecialchars($server); ?>" placeholder="127.0.0.1:3306" required autocomplete="off">
                                    </div>
                                </div>

                                <div class="lps-group">
                                    <label class="lps-label" for="username">USERNAME</label>
                                    <div class="lps-input-wrap">
                                        <span class="lps-input-icon">
                                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                                <circle cx="12" cy="7" r="4"></circle>
                                            </svg>
                                        </span>
                                        <input type="text" id="username" name="auth[username]" value="<?php echo htmlspecialchars($username); ?>" placeholder="root" required autocomplete="username">
                                    </div>
                                </div>

                                <div class="lps-group">
                                    <label class="lps-label" for="lps_password">PASSWORD</label>
                                    <div class="lps-input-wrap">
                                        <span class="lps-input-icon">
                                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                                            </svg>
                                        </span>
                                        <input type="password" id="lps_password" name="auth[password]" placeholder="Default: empty for local root" autocomplete="current-password">
                                    </div>
                                </div>

                                <div class="lps-group">
                                    <label class="lps-label" for="lps_db">DATABASE (OPTIONAL)</label>
                                    <div class="lps-input-wrap">
                                        <span class="lps-input-icon">
                                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <ellipse cx="12" cy="5" rx="9" ry="3"></ellipse>
                                                <path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"></path>
                                                <path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"></path>
                                            </svg>
                                        </span>
                                        <input type="text" id="lps_db" name="auth[db]" value="<?php echo htmlspecialchars($db); ?>" placeholder="e.g. 13_db (or choose after login)" autocomplete="off">
                                    </div>
                                </div>
                            </div>

                            <div class="lps-options">
                                <label class="lps-checkbox">
                                    <input type="checkbox" name="auth[permanent]" value="1" checked>
                                    <span>Remember connection</span>
                                </label>
                            </div>

                            <div class="lps-submit-row">
                                <button type="submit" class="lps-btn-login">
                                    <span>Sign In</span>
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                        <line x1="5" y1="12" x2="19" y2="12"></line>
                                        <polyline points="12 5 19 12 12 19"></polyline>
                                    </svg>
                                </button>
                            </div>

                            <div class="lps-footer-note">
                                <p>&copy; <?php echo date('Y'); ?> LPS &bull; Local Project Server &bull; <span>Database Manager</span></p>
                            </div>
                        </div>
                    </div>
                </div>
                <?php
            }
        }
    }
    return new LPS_Adminer();
}

include __DIR__ . '/adminer.php';
