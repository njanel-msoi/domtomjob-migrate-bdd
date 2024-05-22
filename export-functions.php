<?php

function get_table_names()
{
    $database = DB_NAME;
    global $wpdb;
    $result = $wpdb->get_results("SELECT TABLE_NAME FROM information_schema.tables WHERE table_schema = '$database' AND table_name LIKE '$wpdb->prefix%'");

    return array_map(function ($d) {
        return $d->TABLE_NAME;
    }, $result);
}

function generate_create_table_statement($table_name)
{
    global $wpdb;

    // Verify the table exists
    if ($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table_name)) != $table_name) {
        return "Table not found.";
    }

    // Retrieve the CREATE TABLE statement
    $create_table_query = $wpdb->get_row($wpdb->prepare("SHOW CREATE TABLE `$table_name`"), ARRAY_A);
    if (isset($create_table_query['Create Table'])) {
        return $create_table_query['Create Table'] . ';';
    } else {
        return "ERROR Could not nuild create statement";
    }
}

function generate_insert_statements($table_name)
{
    global $wpdb;

    // Verify the table exists
    if ($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table_name)) != $table_name) {
        return "Table not found.";
    }

    // Get table columns
    $columns_query = "SHOW COLUMNS FROM `$table_name`";
    $columns = $wpdb->get_results($columns_query, ARRAY_A);

    // Prepare column names for the INSERT statement
    $column_names = array();
    foreach ($columns as $column) {
        $column_names[] = '`' . $column['Field'] . '`';
    }
    $column_names_str = implode(', ', $column_names);

    // Get table data
    $data_query = "SELECT * FROM `$table_name`";
    $rows = $wpdb->get_results($data_query, ARRAY_A);

    // Prepare INSERT statements
    $insert_statements = array();
    foreach ($rows as $row) {
        $values = array();
        foreach ($row as $value) {
            $values[] = is_null($value) ? "NULL" : "'" . esc_sql($value) . "'";
        }
        $values_str = implode(', ', $values);
        $insert_statements[] = "INSERT INTO `$table_name` ($column_names_str) VALUES ($values_str);";
    }

    // Return the INSERT statements as a single string
    return implode("\n", $insert_statements);
}

function dump_tables($tables, $source, $target, $ishttps)
{
    $results = [];
    $results[] = "SET SQL_MODE='ALLOW_INVALID_DATES';";

    foreach ($tables as $table) {
        // DROP EXISTING
        $results[] = "DROP TABLE IF EXISTS $table;";
        // CREATE TABLE
        $results[] = generate_create_table_statement($table);
        // INSERT LINES
        $results[] = generate_insert_statements($table);
    }
    $str = join("\n", $results);
    return adapt_server_name($str, $source, $target, $ishttps);
}
function adapt_server_name($str, $source, $target, $ishttps)
{
    $str = str_replace($source, $target, $str);
    if ($ishttps)
        $str = str_replace('http://' . $target, 'https://' . $target, $str);
    return $str;
}
function execute_query($sql)
{
    global $wpdb;
    $wpdb->query('START TRANSACTION');
    $errors = false;
    foreach (explode("\n", $sql) as $line) {
        $query = trim($line);
        if (strlen($query) == 0) continue;
        $result = $wpdb->query($query);
        if (!$result) $errors = true;
    }
    if ($errors)
        $wpdb->query('COMMIT');
    else
        $wpdb->query('ROLLBACK');
}
