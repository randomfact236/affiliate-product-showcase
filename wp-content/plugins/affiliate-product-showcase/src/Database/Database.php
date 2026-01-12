<?php
/**
 * Database Access Layer
 *
 * This file contains the Database class which provides a standardized
 * database access layer for the Affiliate Product Showcase plugin.
 * It uses WordPress $wpdb with proper prefix support and prepared statements.
 *
 * @package AffiliateProductShowcase
 * @subpackage Database
 * @since 1.0.0
 */

declare(strict_types=1);

namespace AffiliateProductShowcase\Database;

use Exception;
use wpdb;

/**
 * Database Class
 *
 * Provides a database access layer with standardized table naming,
 * safe queries, and helper methods for common operations.
 *
 * @package AffiliateProductShowcase
 * @subpackage Database
 */
class Database {
    
    /**
     * @var wpdb WordPress database object
     */
    private wpdb $wpdb;
    
    /**
     * @var string Custom table prefix for plugin tables
     */
    private string $table_prefix;
    
    /**
     * @var array Cache for table names to avoid repeated string operations
     */
    private array $table_cache = [];
    
    /**
     * Constructor
     *
     * Initialize the database layer with WordPress database object.
     *
     * @since 1.0.0
     */
    public function __construct() {
        global $wpdb;
        
        $this->wpdb = $wpdb;
        $this->table_prefix = $wpdb->prefix . 'affiliate_products_';
    }
    
    /**
     * Get the charset collate for table creation
     *
     * Returns the charset and collation to use when creating tables.
     *
     * @since 1.0.0
     * @return string The charset collate string
     */
    public function get_charset_collate(): string {
        return $this->wpdb->get_charset_collate();
    }
    
    /**
     * Get table name with proper prefix
     *
     * Returns the full table name including the WordPress prefix
     * and plugin-specific prefix.
     *
     * @since 1.0.0
     * @param string $table The table name without prefix
     * @return string The full table name
     */
    public function get_table_name(string $table): string {
        if (!isset($this->table_cache[$table])) {
            $this->table_cache[$table] = $this->table_prefix . $table;
        }
        
        return $this->table_cache[$table];
    }
    
    /**
     * Get all custom table names
     *
     * Returns an array of all custom table names for this plugin.
     *
     * @since 1.0.0
     * @return array<string> Array of table names
     */
    public function get_all_tables(): array {
        return [
            'meta' => $this->get_table_name('meta'),
            'submissions' => $this->get_table_name('submissions'),
        ];
    }
    
    /**
     * Prepare a SQL query for safe execution
     *
     * Wraps $wpdb->prepare() for consistent query preparation.
     *
     * @since 1.0.0
     * @param string $query The SQL query with placeholders
     * @param array<mixed>|mixed $args The values to replace placeholders
     * @return string The prepared SQL query
     */
    public function prepare(string $query, $args = null): string {
        if (!is_array($args)) {
            $args = array_slice(func_get_args(), 1);
        }
        
        return $this->wpdb->prepare($query, ...$args);
    }
    
    /**
     * Get results from a query
     *
     * Returns an array of results from the database.
     *
     * @since 1.0.0
     * @param string $query The SQL query
     * @param string $output Optional. Output type (OBJECT, ARRAY_A, or ARRAY_N). Default OBJECT
     * @return array<mixed>|object|null Query results
     */
    public function get_results(string $query, string $output = OBJECT) {
        return $this->wpdb->get_results($query, $output);
    }
    
    /**
     * Get a single row from a query
     *
     * Returns a single row from the database.
     *
     * @since 1.0.0
     * @param string $query The SQL query
     * @param string $output Optional. Output type (OBJECT, ARRAY_A, or ARRAY_N). Default OBJECT
     * @return object|array|null Single row or null
     */
    public function get_row(string $query, string $output = OBJECT) {
        return $this->wpdb->get_row($query, $output);
    }
    
    /**
     * Get a single variable from a query
     *
     * Returns a single variable from the database.
     *
     * @since 1.0.0
     * @param string $query The SQL query
     * @return string|null Single variable or null
     */
    public function get_var(string $query): ?string {
        $result = $this->wpdb->get_var($query);
        return $result === null ? null : (string) $result;
    }
    
    /**
     * Insert a row into a table
     *
     * Inserts a row into the specified table.
     *
     * @since 1.0.0
     * @param string $table The table name
     * @param array<string, mixed> $data Data to insert (column => value pairs)
     * @param array<string>|null $format Optional. Format array for values
     * @return int|false The inserted row ID or false on failure
     */
    public function insert(string $table, array $data, ?array $format = null) {
        $table_name = $this->get_table_name($table);
        return $this->wpdb->insert($table_name, $data, $format);
    }
    
    /**
     * Update rows in a table
     *
     * Updates rows in the specified table.
     *
     * @since 1.0.0
     * @param string $table The table name
     * @param array<string, mixed> $data Data to update (column => value pairs)
     * @param array<string, mixed> $where Where clause (column => value pairs)
     * @param array<string>|null $format Optional. Format array for data values
     * @param array<string>|null $where_format Optional. Format array for where values
     * @return int|false Number of rows updated or false on failure
     */
    public function update(
        string $table,
        array $data,
        array $where,
        ?array $format = null,
        ?array $where_format = null
    ) {
        $table_name = $this->get_table_name($table);
        return $this->wpdb->update($table_name, $data, $where, $format, $where_format);
    }
    
    /**
     * Delete rows from a table
     *
     * Deletes rows from the specified table.
     *
     * @since 1.0.0
     * @param string $table The table name
     * @param array<string, mixed> $where Where clause (column => value pairs)
     * @param array<string>|null $format Optional. Format array for where values
     * @return int|false Number of rows deleted or false on failure
     */
    public function delete(string $table, array $where, ?array $format = null) {
        $table_name = $this->get_table_name($table);
        return $this->wpdb->delete($table_name, $where, $format);
    }
    
    /**
     * Create a database table
     *
     * Creates a custom database table with proper prefix and charset.
     *
     * @since 1.0.0
     * @param string $table_name The table name (without prefix)
     * @param string $sql The SQL CREATE TABLE statement
     * @return bool True on success, false on failure
     */
    public function create_table(string $table_name, string $sql): bool {
        $table_name = $this->get_table_name($table_name);
        
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        
        $result = dbDelta($sql);
        
        return !empty($result);
    }
    
    /**
     * Create an index on a table
     *
     * Creates a database index on the specified table and column.
     * Note: MySQL doesn't support IF NOT EXISTS for indexes, so we check first.
     *
     * @since 1.0.0
     * @param string $table The table name (without prefix)
     * @param string $index_name The index name
     * @param string $column The column name (comma-separated for multi-column)
     * @param int $length Optional. Index length for string columns. Default 0 (full length)
     * @param string $type Optional. Index type (INDEX, UNIQUE, FULLTEXT, SPATIAL). Default INDEX
     * @return bool True on success, false on failure
     */
    public function create_index(
        string $table,
        string $index_name,
        string $column,
        int $length = 0,
        string $type = 'INDEX'
    ): bool {
        $table_name = $this->get_table_name($table);
        
        // Escape identifiers (table name, index name, column names)
        $table_name_safe = $this->escape_identifier($table_name);
        $index_name_safe = $this->escape_identifier($index_name);
        $column_safe = $this->escape_column_list($column, $length);
        
        // Check if index already exists
        if ($this->index_exists($table_name_safe, $index_name_safe)) {
            return true;
        }
        
        $sql = "CREATE $type $index_name_safe ON $table_name_safe $column_safe";
        
        return $this->query($sql) !== false;
    }
    
    /**
     * Check if an index exists on a table
     *
     * @since 1.0.0
     * @param string $table_name The table name (fully escaped)
     * @param string $index_name The index name (fully escaped)
     * @return bool True if index exists, false otherwise
     */
    private function index_exists(string $table_name, string $index_name): bool {
        $sql = "SHOW INDEX FROM $table_name WHERE Key_name = '$index_name'";
        $result = $this->get_var($sql);
        return $result !== null;
    }
    
    /**
     * Escape a SQL identifier (table name, column name, etc.)
     *
     * @since 1.0.0
     * @param string $identifier The identifier to escape
     * @return string The escaped identifier
     */
    private function escape_identifier(string $identifier): string {
        // Remove backticks and wrap in backticks
        $identifier = str_replace('`', '', $identifier);
        return '`' . $identifier . '`';
    }
    
    /**
     * Escape a column list for index creation
     *
     * Handles single columns or comma-separated multi-column lists.
     * Applies length specification to string columns.
     *
     * @since 1.0.0
     * @param string $columns Column list (comma-separated for multiple)
     * @param int $length Length to apply to string columns
     * @return string Escaped and formatted column list
     */
    private function escape_column_list(string $columns, int $length): string {
        // Split by comma for multi-column indexes
        $column_array = array_map('trim', explode(',', $columns));
        $escaped_columns = [];
        
        foreach ($column_array as $column) {
            $column_safe = $this->escape_identifier($column);
            
            // Apply length if specified and column doesn't already have length
            if ($length > 0 && false === strpos($column, '(')) {
                $column_safe .= "($length)";
            }
            
            $escaped_columns[] = $column_safe;
        }
        
        return '(' . implode(', ', $escaped_columns) . ')';
    }
    
    /**
     * Drop a table
     *
     * Drops (deletes) a custom database table.
     *
     * @since 1.0.0
     * @param string $table The table name (without prefix)
     * @return bool True on success, false on failure
     */
    public function drop_table(string $table): bool {
        $table_name = $this->get_table_name($table);
        $sql = "DROP TABLE IF EXISTS $table_name";
        return $this->query($sql) !== false;
    }
    
    /**
     * Check if a table exists
     *
     * Checks whether a custom table exists in the database.
     *
     * @since 1.0.0
     * @param string $table The table name (without prefix)
     * @return bool True if table exists, false otherwise
     */
    public function table_exists(string $table): bool {
        $table_name = $this->get_table_name($table);
        $sql = $this->prepare(
            "SHOW TABLES LIKE %s",
            $table_name
        );
        
        $result = $this->wpdb->get_var($sql);
        return $result === $table_name;
    }
    
    /**
     * Get the last insert ID
     *
     * Returns the ID of the last inserted row.
     *
     * @since 1.0.0
     * @return int The last insert ID
     */
    public function get_insert_id(): int {
        return (int) $this->wpdb->insert_id;
    }
    
    /**
     * Get the number of affected rows
     *
     * Returns the number of rows affected by the last query.
     *
     * @since 1.0.0
     * @return int Number of affected rows
     */
    public function get_affected_rows(): int {
        return (int) $this->wpdb->rows_affected;
    }
    
    /**
     * Execute a direct SQL query
     *
     * Executes a SQL query directly on the database.
     *
     * @since 1.0.0
     * @param string $query The SQL query
     * @return int|false Number of rows affected or false on failure
     */
    public function query(string $query) {
        return $this->wpdb->query($query);
    }
    
    /**
     * Get the last database error
     *
     * Returns the last database error message.
     *
     * @since 1.0.0
     * @return string Last error message or empty string
     */
    public function get_last_error(): string {
        return $this->wpdb->last_error;
    }
    
    /**
     * Begin a transaction
     *
     * Starts a database transaction for atomic operations.
     *
     * @since 1.0.0
     * @return bool True on success, false on failure
     */
    public function start_transaction(): bool {
        return $this->wpdb->query('START TRANSACTION') !== false;
    }
    
    /**
     * Commit a transaction
     *
     * Commits the current transaction.
     *
     * @since 1.0.0
     * @return bool True on success, false on failure
     */
    public function commit(): bool {
        return $this->wpdb->query('COMMIT') !== false;
    }
    
    /**
     * Rollback a transaction
     *
     * Rolls back the current transaction.
     *
     * @since 1.0.0
     * @return bool True on success, false on failure
     */
    public function rollback(): bool {
        return $this->wpdb->query('ROLLBACK') !== false;
    }
    
    /**
     * Escape a string for SQL
     *
     * Escapes a string for safe use in SQL queries.
     *
     * @since 1.0.0
     * @param string $text The text to escape
     * @return string The escaped text
     */
    public function escape(string $text): string {
        return $this->wpdb->_escape($text);
    }
}
