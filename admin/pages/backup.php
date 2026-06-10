<?php
if (isset($_GET['download']) && $_GET['download'] === 'true') {
    $tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
    $sql = "-- INFRIZO SYSTEM BACKUP\n-- Generated: " . date('Y-m-d H:i:s') . "\n\n";

    foreach ($tables as $table) {
        $sql .= "DROP TABLE IF EXISTS `$table`;\n";
        $createStmt = $pdo->query("SHOW CREATE TABLE `$table`")->fetch(PDO::FETCH_ASSOC);
        $sql .= $createStmt['Create Table'] . ";\n\n";

        $rows = $pdo->query("SELECT * FROM `$table`")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as $row) {
            $keys = array_keys($row);
            $values = array_values($row);
            $valuesEscaped = array_map(function($val) use ($pdo) {
                return is_null($val) ? 'NULL' : $pdo->quote($val);
            }, $values);
            $sql .= "INSERT INTO `$table` (`" . implode("`, `", $keys) . "`) VALUES (" . implode(", ", $valuesEscaped) . ");\n";
        }
        $sql .= "\n";
    }

    header('Content-Type: application/sql');
    header('Content-Disposition: attachment; filename="infrizo_backup_' . date('Y_m_d_His') . '.sql"');
    echo $sql;
    exit;
}
?>

<div class="max-w-2xl mx-auto text-center py-20">
    <div class="text-6xl text-indigo-500 mb-6">💽</div>
    <h2 class="text-3xl font-robot font-bold text-slate-800 mb-4">Database Core Backup</h2>
    <p class="text-slate-500 mb-8 max-w-md mx-auto text-sm">Download a complete SQL dump of the system's database matrix. Ensure this file is stored securely.</p>
    
    <a href="?page=backup&download=true" class="btn-cyber btn-cyber-solid px-10 py-4 text-sm inline-flex items-center gap-2">
        <span>[↓]</span> INITIATE_DOWNLOAD_PROTOCOL
    </a>
</div>