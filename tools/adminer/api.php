<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

header("Content-Type: application/json; charset=utf-8");

// Helper to get connection
function getDbConnection($server = '127.0.0.1:3306', $user = 'root', $pass = '', $db = '') {
    $host = '127.0.0.1';
    $port = 3306;
    if (strpos($server, ':') !== false) {
        list($host, $port) = explode(':', $server);
        $port = (int)$port;
    } elseif (!empty($server)) {
        $host = $server;
    }
    if ($host === 'localhost') {
        $host = '127.0.0.1';
    }

    $mysqli = @new mysqli($host, $user, $pass, $db, $port);
    if ($mysqli->connect_error) {
        http_response_code(500);
        echo json_encode(['error' => 'Connection failed: ' . $mysqli->connect_error, 'success' => false]);
        exit;
    }
    $mysqli->set_charset('utf8mb4');
    return $mysqli;
}

$server = $_REQUEST['server'] ?? '127.0.0.1:3306';
$username = $_REQUEST['username'] ?? 'root';
$password = $_REQUEST['password'] ?? '';
$action = $_REQUEST['action'] ?? 'ping';
$db = $_REQUEST['db'] ?? '';

// 1. PING / TEST CONNECTION
if ($action === 'ping') {
    $mysqli = getDbConnection($server, $username, $password, $db);
    echo json_encode([
        'success' => true,
        'server_info' => $mysqli->server_info,
        'host_info' => $mysqli->host_info,
        'message' => 'Connected successfully to ' . $server
    ]);
    $mysqli->close();
    exit;
}

// 2. LIST DATABASES
if ($action === 'databases') {
    $mysqli = getDbConnection($server, $username, $password);
    
    $res = $mysqli->query("SHOW DATABASES");
    $databases = [];
    
    if ($res) {
        while ($row = $res->fetch_array()) {
            $dbName = $row[0];
            // Skip internal system databases unless requested
            if (in_array($dbName, ['information_schema', 'performance_schema', 'sys'])) {
                continue;
            }
            
            // Get table count and total size
            $statsRes = $mysqli->query("
                SELECT COUNT(*) as table_count, 
                       IFNULL(SUM(data_length + index_length), 0) as total_size 
                FROM information_schema.TABLES 
                WHERE table_schema = '" . $mysqli->real_escape_string($dbName) . "'
            ");
            
            $tableCount = 0;
            $sizeStr = '0 KB';
            if ($statsRes && $statsRow = $statsRes->fetch_assoc()) {
                $tableCount = (int)$statsRow['table_count'];
                $bytes = (float)$statsRow['total_size'];
                if ($bytes >= 1073741824) {
                    $sizeStr = number_format($bytes / 1073741824, 1) . ' GB';
                } elseif ($bytes >= 1048576) {
                    $sizeStr = number_format($bytes / 1048576, 1) . ' MB';
                } else {
                    $sizeStr = number_format($bytes / 1024, 1) . ' KB';
                }
            }
            
            $databases[] = [
                'name' => $dbName,
                'tables' => $tableCount,
                'size' => $sizeStr,
                'engine' => 'InnoDB'
            ];
        }
    }
    
    echo json_encode(['success' => true, 'databases' => $databases]);
    $mysqli->close();
    exit;
}

// 3. LIST TABLES IN DATABASE
if ($action === 'tables') {
    if (!$db) {
        http_response_code(400);
        echo json_encode(['error' => 'Database name is required', 'success' => false]);
        exit;
    }
    
    $mysqli = getDbConnection($server, $username, $password, $db);

    // Get column count for each table
    $colCounts = [];
    $colRes = $mysqli->query("SELECT TABLE_NAME, COUNT(*) as col_count FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '" . $mysqli->real_escape_string($db) . "' GROUP BY TABLE_NAME");
    if ($colRes) {
        while ($colRow = $colRes->fetch_assoc()) {
            $colCounts[$colRow['TABLE_NAME']] = (int)$colRow['col_count'];
        }
    }

    $res = $mysqli->query("SHOW TABLE STATUS");
    $tables = [];
    
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $bytes = (float)($row['Data_length'] + $row['Index_length']);
            if ($bytes >= 1048576) {
                $sizeStr = number_format($bytes / 1048576, 1) . ' MB';
            } else {
                $sizeStr = number_format($bytes / 1024, 1) . ' KB';
            }
            
            $colCount = $colCounts[$row['Name']] ?? 0;
            
            $tables[] = [
                'name' => $row['Name'],
                'rows' => (int)$row['Rows'],
                'size' => $sizeStr,
                'engine' => $row['Engine'] ?? 'InnoDB',
                'createdAt' => $row['Create_time'] ? substr($row['Create_time'], 0, 10) : date('Y-m-d'),
                'collation' => $row['Collation'] ?? '',
                'comment' => $row['Comment'] ?? '',
                'columnCount' => $colCount,
                'columns' => array_fill(0, $colCount, ['name' => '', 'type' => '', 'nullable' => false, 'key' => '', 'default' => '', 'extra' => ''])
            ];
        }
    }
    
    echo json_encode(['success' => true, 'tables' => $tables]);
    $mysqli->close();
    exit;
}

// 4. GET TABLE FULL DETAILS (COLUMNS + DATA)
if ($action === 'table_details') {
    $tableName = $_REQUEST['table'] ?? '';
    if (!$db || !$tableName) {
        http_response_code(400);
        echo json_encode(['error' => 'Database and table names are required', 'success' => false]);
        exit;
    }
    
    $mysqli = getDbConnection($server, $username, $password, $db);
    
    // Get full columns
    $colRes = $mysqli->query("SHOW FULL COLUMNS FROM `" . $mysqli->real_escape_string($tableName) . "`");
    $columns = [];
    if ($colRes) {
        while ($col = $colRes->fetch_assoc()) {
            $columns[] = [
                'name' => $col['Field'],
                'type' => $col['Type'],
                'collation' => $col['Collation'],
                'nullable' => ($col['Null'] === 'YES'),
                'key' => $col['Key'],
                'default' => $col['Default'] === null ? 'NULL' : $col['Default'],
                'extra' => $col['Extra'],
                'comment' => $col['Comment']
            ];
        }
    }
    
    // Get total rows count
    $countRes = $mysqli->query("SELECT COUNT(*) as total FROM `" . $mysqli->real_escape_string($tableName) . "`");
    $totalRows = 0;
    if ($countRes && $countRow = $countRes->fetch_assoc()) {
        $totalRows = (int)$countRow['total'];
    }
    
    // Get table stats
    $statusRes = $mysqli->query("SHOW TABLE STATUS LIKE '" . $mysqli->real_escape_string($tableName) . "'");
    $engine = 'InnoDB';
    $sizeStr = '0 KB';
    $createdAt = date('Y-m-d');
    if ($statusRes && $statusRow = $statusRes->fetch_assoc()) {
        $engine = $statusRow['Engine'] ?? 'InnoDB';
        $bytes = (float)($statusRow['Data_length'] + $statusRow['Index_length']);
        $sizeStr = $bytes >= 1048576 ? number_format($bytes / 1048576, 1) . ' MB' : number_format($bytes / 1024, 1) . ' KB';
        $createdAt = $statusRow['Create_time'] ? substr($statusRow['Create_time'], 0, 10) : date('Y-m-d');
    }
    
    // Fetch data rows (limit 100)
    $dataRes = $mysqli->query("SELECT * FROM `" . $mysqli->real_escape_string($tableName) . "` LIMIT 100");
    $data = [];
    if ($dataRes) {
        while ($row = $dataRes->fetch_assoc()) {
            $data[] = $row;
        }
    }
    
    echo json_encode([
        'success' => true,
        'table' => [
            'name' => $tableName,
            'rows' => $totalRows,
            'size' => $sizeStr,
            'engine' => $engine,
            'createdAt' => $createdAt,
            'columns' => $columns,
            'data' => $data
        ]
    ]);
    $mysqli->close();
    exit;
}

// 5. EXECUTE SQL QUERY
if ($action === 'query') {
    $query = '';
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $body = file_get_contents('php://input');
        $json = json_decode($body, true);
        if ($json && isset($json['query'])) {
            $query = trim($json['query']);
        } elseif (isset($_POST['query'])) {
            $query = trim($_POST['query']);
        }
    } else {
        $query = trim($_GET['query'] ?? '');
    }
    
    if (!$query) {
        http_response_code(400);
        echo json_encode(['error' => 'Query cannot be empty', 'success' => false]);
        exit;
    }
    
    $mysqli = getDbConnection($server, $username, $password, $db);
    
    $startTime = microtime(true);
    $result = $mysqli->query($query);
    $endTime = microtime(true);
    $timeMs = number_format(($endTime - $startTime) * 1000, 2) . ' ms';
    
    if ($result === false) {
        echo json_encode([
            'success' => false,
            'error' => $mysqli->error,
            'time' => $timeMs,
            'affectedRows' => 0,
            'columns' => [],
            'rows' => []
        ]);
        $mysqli->close();
        exit;
    }
    
    if ($result === true) {
        // Non-SELECT query (INSERT, UPDATE, DELETE, CREATE, etc.)
        echo json_encode([
            'success' => true,
            'message' => 'Query executed successfully. ' . $mysqli->affected_rows . ' row(s) affected.',
            'affectedRows' => $mysqli->affected_rows,
            'time' => $timeMs,
            'columns' => [],
            'rows' => []
        ]);
        $mysqli->close();
        exit;
    }
    
    // SELECT query with rows
    $columns = [];
    $fields = $result->fetch_fields();
    foreach ($fields as $f) {
        $columns[] = $f->name;
    }
    
    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Query executed successfully (' . count($rows) . ' rows returned)',
        'affectedRows' => count($rows),
        'time' => $timeMs,
        'columns' => $columns,
        'rows' => $rows
    ]);
    
    $mysqli->close();
    exit;
}

// 6. IMPORT SQL SCRIPT (MULTI-QUERY)
if ($action === 'import_sql') {
    $sql = '';
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $body = file_get_contents('php://input');
        $json = json_decode($body, true);
        if ($json && isset($json['sql'])) {
            $sql = trim($json['sql']);
        } elseif (isset($_POST['sql'])) {
            $sql = trim($_POST['sql']);
        } else {
            $sql = trim($body);
        }
    }
    
    if (!$sql) {
        echo json_encode(['success' => false, 'error' => 'SQL script is empty']);
        exit;
    }
    
    $mysqli = getDbConnection($server, $username, $password, $db);
    $startTime = microtime(true);
    $count = 0;
    $errors = [];
    
    if ($mysqli->multi_query($sql)) {
        do {
            $count++;
            if ($result = $mysqli->store_result()) {
                $result->free();
            }
            if ($mysqli->errno) {
                $errors[] = $mysqli->error;
            }
        } while ($mysqli->more_results() && $mysqli->next_result());
    } else {
        $errors[] = $mysqli->error;
    }
    
    $timeMs = number_format((microtime(true) - $startTime) * 1000, 2) . ' ms';
    $isSuccess = count($errors) === 0;
    
    echo json_encode([
        'success' => $isSuccess,
        'statementsCount' => $count,
        'time' => $timeMs,
        'errors' => $errors,
        'message' => $isSuccess ? "Executed $count statements successfully in $timeMs" : ("Executed $count statement(s) with " . count($errors) . " error(s): " . implode('; ', $errors))
    ]);
    $mysqli->close();
    exit;
}

// 7. EXPORT SQL DUMP
if ($action === 'export_sql') {
    if (!$db) {
        echo json_encode(['success' => false, 'error' => 'Database name is required']);
        exit;
    }
    
    $mysqli = getDbConnection($server, $username, $password, $db);
    $tablesParam = $_REQUEST['tables'] ?? '';
    $mode = $_REQUEST['mode'] ?? 'both'; // both, structure, data
    
    $selectedTables = [];
    if (!empty($tablesParam)) {
        $selectedTables = array_map('trim', explode(',', $tablesParam));
    } else {
        $res = $mysqli->query("SHOW TABLES");
        if ($res) {
            while ($row = $res->fetch_array()) {
                $selectedTables[] = $row[0];
            }
        }
    }
    
    $dump = "-- LPS Database Manager SQL Dump\n";
    $dump .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
    $dump .= "-- Host: $server  Database: `$db`\n";
    $dump .= "-- Server version: " . $mysqli->server_info . "\n\n";
    $dump .= "SET NAMES utf8mb4;\n";
    $dump .= "SET FOREIGN_KEY_CHECKS = 0;\n\n";
    
    foreach ($selectedTables as $table) {
        $escapedTable = $mysqli->real_escape_string($table);
        
        // Structure
        if ($mode === 'both' || $mode === 'structure') {
            $dump .= "-- --------------------------------------------------------\n";
            $dump .= "-- Structure for table `$table`\n";
            $dump .= "-- --------------------------------------------------------\n";
            $dump .= "DROP TABLE IF EXISTS `$table`;\n";
            
            $createRes = $mysqli->query("SHOW CREATE TABLE `$escapedTable`");
            if ($createRes && $cRow = $createRes->fetch_assoc()) {
                $dump .= $cRow['Create Table'] . ";\n\n";
            }
        }
        
        // Data
        if ($mode === 'both' || $mode === 'data') {
            $dataRes = $mysqli->query("SELECT * FROM `$escapedTable`");
            if ($dataRes && $dataRes->num_rows > 0) {
                $dump .= "-- Dumping data for table `$table`\n";
                $fields = $dataRes->fetch_fields();
                $fieldNames = [];
                foreach ($fields as $f) {
                    $fieldNames[] = "`" . $f->name . "`";
                }
                $fieldList = implode(', ', $fieldNames);
                
                while ($row = $dataRes->fetch_assoc()) {
                    $values = [];
                    foreach ($row as $val) {
                        if ($val === null) {
                            $values[] = "NULL";
                        } else {
                            $values[] = "'" . $mysqli->real_escape_string($val) . "'";
                        }
                    }
                    $dump .= "INSERT INTO `$table` ($fieldList) VALUES (" . implode(', ', $values) . ");\n";
                }
                $dump .= "\n";
            }
        }
    }
    
    $dump .= "SET FOREIGN_KEY_CHECKS = 1;\n";
    $dump .= "-- Dump completed on " . date('Y-m-d H:i:s') . "\n";
    
    echo json_encode([
        'success' => true,
        'database' => $db,
        'filename' => $db . '_dump_' . date('Ymd_His') . '.sql',
        'sql' => $dump,
        'size' => strlen($dump)
    ]);
    $mysqli->close();
    exit;
}

echo json_encode(['error' => 'Invalid action', 'success' => false]);
