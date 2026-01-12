<?php
/**
 * Database Migrations Manager
 *
 * This file contains the Migrations class which manages database schema
 * versioning and migration execution for the Affiliate Product Showcase plugin.
 *
 * @package AffiliateProductShowcase
 * @subpackage Database
 * @since 1.0.0
 */

declare(strict_types=1);

namespace AffiliateProductShowcase\Database;

use Exception;

/**
 * Migrations Class
 *
 * Manages database schema versioning, migration execution,
 * rollback capability, and migration history logging.
 *
 * @package AffiliateProductShowcase
 * @subpackage Database
 */
class Migrations {
    
    /**
     * @var Database Database instance
     */
    private Database $db;
    
    /**
     * @var string Database version option name
     */
    private string $version_option = 'affiliate_products_db_version';
    
    /**
     * @var string Migration history option name
     */
    private string $history_option = 'affiliate_products_migration_history';
    
    /**
     * @var string Current database schema version
     */
    private string $current_version = '1.0.0';
    
    /**
     * @var array<mixed> Registered migrations
     */
    private array $migrations = [];
    
    /**
     * Constructor
     *
     * Initialize the migrations manager and register all migrations.
     *
     * @since 1.0.0
     * @param Database $db Database instance
     */
    public function __construct(Database $db) {
        $this->db = $db;
        $this->register_migrations();
    }
    
    /**
     * Register all database migrations
     *
     * Registers all available migrations in order of execution.
     *
     * @since 1.0.0
     * @return void
     */
    private function register_migrations(): void {
        $this->migrations = [
            '1.0.0' => [
                'up' => [$this, 'create_meta_table'],
                'down' => [$this, 'drop_meta_table'],
                'description' => 'Create affiliate products meta table',
            ],
            '1.0.1' => [
                'up' => [$this, 'create_submissions_table'],
                'down' => [$this, 'drop_submissions_table'],
                'description' => 'Create affiliate products submissions table',
            ],
        ];
    }
    
    /**
     * Get the current database version
     *
     * Returns the current schema version from the options table.
     *
     * @since 1.0.0
     * @return string Current database version
     */
    public function get_current_version(): string {
        $version = get_option($this->version_option, '0.0.0');
        return is_string($version) ? $version : '0.0.0';
    }
    
    /**
     * Get the latest available migration version
     *
     * Returns the latest migration version available.
     *
     * @since 1.0.0
     * @return string Latest migration version
     */
    public function get_latest_version(): string {
        $versions = array_keys($this->migrations);
        return !empty($versions) ? end($versions) : $this->current_version;
    }
    
    /**
     * Check if database needs migration
     *
     * Compares current version with latest version to determine if migration is needed.
     *
     * @since 1.0.0
     * @return bool True if migration is needed, false otherwise
     */
    public function needs_migration(): bool {
        return version_compare($this->get_current_version(), $this->get_latest_version(), '<');
    }
    
    /**
     * Run all pending migrations
     *
     * Executes all migrations that haven't been applied yet.
     *
     * @since 1.0.0
     * @return bool True on success, false on failure
     * @throws Exception If migration fails
     */
    public function run(): bool {
        $current_version = $this->get_current_version();
        $pending = $this->get_pending_migrations();
        
        if (empty($pending)) {
            return true;
        }
        
        foreach ($pending as $version => $migration) {
            if (!$this->execute_migration($version, $migration, 'up')) {
                throw new Exception("Migration $version failed to execute");
            }
        }
        
        $this->update_version($this->get_latest_version());
        
        return true;
    }
    
    /**
     * Rollback the last migration
     *
     * Reverts the last applied migration.
     *
     * @since 1.0.0
     * @param string|null $target_version Optional. Target version to rollback to. Default null (latest only)
     * @return bool True on success, false on failure
     * @throws Exception If rollback fails
     */
    public function rollback(?string $target_version = null): bool {
        $current_version = $this->get_current_version();
        $applied = $this->get_applied_migrations();
        
        if (empty($applied)) {
            throw new Exception('No migrations to rollback');
        }
        
        if ($target_version === null) {
            // Rollback only the last migration
            $version = end($applied);
            if (!isset($this->migrations[$version])) {
                throw new Exception("Migration $version not found");
            }
            
            $migration = $this->migrations[$version];
            if (!$this->execute_migration($version, $migration, 'down')) {
                throw new Exception("Rollback of migration $version failed");
            }
            
            $this->update_version(prev($applied) ?: '0.0.0');
        } else {
            // Rollback to specific version
            $to_rollback = array_filter($applied, function($v) use ($target_version) {
                return version_compare($v, $target_version, '>');
            });
            
            krsort($to_rollback);
            
            foreach ($to_rollback as $version) {
                if (!isset($this->migrations[$version])) {
                    throw new Exception("Migration $version not found");
                }
                
                $migration = $this->migrations[$version];
                if (!$this->execute_migration($version, $migration, 'down')) {
                    throw new Exception("Rollback of migration $version failed");
                }
            }
            
            $this->update_version($target_version);
        }
        
        return true;
    }
    
    /**
     * Get pending migrations
     *
     * Returns all migrations that haven't been applied yet.
     *
     * @since 1.0.0
     * @return array<string, mixed> Pending migrations
     */
    public function get_pending_migrations(): array {
        $current_version = $this->get_current_version();
        $pending = [];
        
        foreach ($this->migrations as $version => $migration) {
            if (version_compare($version, $current_version, '>')) {
                $pending[$version] = $migration;
            }
        }
        
        return $pending;
    }
    
    /**
     * Get applied migrations
     *
     * Returns all migrations that have been applied.
     *
     * @since 1.0.0
     * @return array<string> Applied migration versions
     */
    public function get_applied_migrations(): array {
        $current_version = $this->get_current_version();
        $applied = [];
        
        foreach ($this->migrations as $version => $migration) {
            if (version_compare($version, $current_version, '<=')) {
                $applied[] = $version;
            }
        }
        
        return $applied;
    }
    
    /**
     * Get migration history
     *
     * Returns the full migration history log.
     *
     * @since 1.0.0
     * @return array<mixed> Migration history
     */
    public function get_history(): array {
        $history = get_option($this->history_option, []);
        return is_array($history) ? $history : [];
    }
    
    /**
     * Execute a migration
     *
     * Executes a single migration up or down.
     *
     * @since 1.0.0
     * @param string $version Migration version
     * @param array<mixed> $migration Migration configuration
     * @param string $direction Migration direction ('up' or 'down')
     * @return bool True on success, false on failure
     */
    private function execute_migration(string $version, array $migration, string $direction): bool {
        if (!isset($migration[$direction]) || !is_callable($migration[$direction])) {
            return false;
        }
        
        $callback = $migration[$direction];
        $description = $migration['description'] ?? '';
        
        try {
            // Start transaction for atomic operation
            $this->db->start_transaction();
            
            // Execute migration
            $result = call_user_func($callback);
            
            if ($result === false) {
                $this->db->rollback();
                return false;
            }
            
            // Commit transaction
            $this->db->commit();
            
            // Log migration
            $this->log_migration($version, $description, $direction);
            
            return true;
        } catch (Exception $e) {
            $this->db->rollback();
            error_log(sprintf(
                'Migration %s failed: %s',
                $version,
                $e->getMessage()
            ));
            return false;
        }
    }
    
    /**
     * Update database version
     *
     * Updates the current database schema version.
     *
     * @since 1.0.0
     * @param string $version New version
     * @return void
     */
    private function update_version(string $version): void {
        update_option($this->version_option, $version);
    }
    
    /**
     * Log migration execution
     *
     * Logs migration execution to history.
     *
     * @since 1.0.0
     * @param string $version Migration version
     * @param string $description Migration description
     * @param string $direction Migration direction ('up' or 'down')
     * @return void
     */
    private function log_migration(string $version, string $description, string $direction): void {
        $history = $this->get_history();
        
        $history[] = [
            'version' => $version,
            'description' => $description,
            'direction' => $direction,
            'timestamp' => current_time('mysql'),
            'user_id' => get_current_user_id(),
        ];
        
        // Keep only last 100 migration logs
        if (count($history) > 100) {
            $history = array_slice($history, -100);
        }
        
        update_option($this->history_option, $history);
    }
    
    /**
     * Clean up old migration data
     *
     * Removes migration history and cleans up old data.
     *
     * @since 1.0.0
     * @return void
     */
    public function cleanup(): void {
        delete_option($this->history_option);
    }
    
    // ============================================================================
    // Migration Methods
    // ============================================================================
    
    /**
     * Migration 1.0.0: Create meta table (UP)
     *
     * Creates the affiliate products meta table for storing product metadata.
     *
     * @since 1.0.0
     * @return bool True on success, false on failure
     */
    private function create_meta_table(): bool {
        $table_name = $this->db->get_table_name('meta');
        $charset_collate = $this->db->get_charset_collate();
        
        $sql = "CREATE TABLE $table_name (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            product_id bigint(20) UNSIGNED NOT NULL,
            meta_key varchar(255) NOT NULL,
            meta_value longtext,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY product_id (product_id),
            KEY meta_key (meta_key(191))
        ) $charset_collate;";
        
        $result = $this->db->create_table('meta', $sql);
        
        if ($result) {
            // Create additional indexes
            $this->db->create_index('meta', 'idx_product_meta', 'product_id, meta_key', 191);
        }
        
        return $result;
    }
    
    /**
     * Migration 1.0.0: Drop meta table (DOWN)
     *
     * Drops the affiliate products meta table.
     *
     * @since 1.0.0
     * @return bool True on success, false on failure
     */
    private function drop_meta_table(): bool {
        return $this->db->drop_table('meta');
    }
    
    /**
     * Migration 1.0.1: Create submissions table (UP)
     *
     * Creates the affiliate products submissions table for storing form submissions.
     *
     * @since 1.0.0
     * @return bool True on success, false on failure
     */
    private function create_submissions_table(): bool {
        $table_name = $this->db->get_table_name('submissions');
        $charset_collate = $this->db->get_charset_collate();
        
        $sql = "CREATE TABLE $table_name (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            product_name varchar(255) NOT NULL,
            product_url varchar(500) NOT NULL,
            product_image varchar(500),
            price varchar(100),
            description text,
            category varchar(100),
            status varchar(20) NOT NULL DEFAULT 'pending',
            submitted_by bigint(20) UNSIGNED,
            submitted_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            reviewed_at datetime,
            reviewed_by bigint(20) UNSIGNED,
            notes text,
            PRIMARY KEY  (id),
            KEY status (status),
            KEY submitted_at (submitted_at),
            KEY submitted_by (submitted_by)
        ) $charset_collate;";
        
        $result = $this->db->create_table('submissions', $sql);
        
        if ($result) {
            // Create additional indexes
            $this->db->create_index('submissions', 'idx_status_date', 'status, submitted_at', 0);
        }
        
        return $result;
    }
    
    /**
     * Migration 1.0.1: Drop submissions table (DOWN)
     *
     * Drops the affiliate products submissions table.
     *
     * @since 1.0.0
     * @return bool True on success, false on failure
     */
    private function drop_submissions_table(): bool {
        return $this->db->drop_table('submissions');
    }
}
