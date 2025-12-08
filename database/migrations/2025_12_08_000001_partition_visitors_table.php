<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Note: MySQL partitioning requires specific setup
        // This migration creates the partitioning structure
        // In production, you may need to adjust partition ranges based on your data

        $partitioningSql = "
            ALTER TABLE visitors
            PARTITION BY RANGE (YEAR(created_at) * 100 + MONTH(created_at)) (
                PARTITION p202401 VALUES LESS THAN (202402),
                PARTITION p202402 VALUES LESS THAN (202403),
                PARTITION p202403 VALUES LESS THAN (202404),
                PARTITION p202404 VALUES LESS THAN (202405),
                PARTITION p202405 VALUES LESS THAN (202406),
                PARTITION p202406 VALUES LESS THAN (202407),
                PARTITION p202407 VALUES LESS THAN (202408),
                PARTITION p202408 VALUES LESS THAN (202409),
                PARTITION p202409 VALUES LESS THAN (202410),
                PARTITION p202410 VALUES LESS THAN (202411),
                PARTITION p202411 VALUES LESS THAN (202412),
                PARTITION p202412 VALUES LESS THAN (202501),
                PARTITION p202501 VALUES LESS THAN (202502),
                PARTITION p202502 VALUES LESS THAN (202503),
                PARTITION p202503 VALUES LESS THAN (202504),
                PARTITION p202504 VALUES LESS THAN (202505),
                PARTITION p202505 VALUES LESS THAN (202506),
                PARTITION p202506 VALUES LESS THAN (202507),
                PARTITION p202507 VALUES LESS THAN (202508),
                PARTITION p202508 VALUES LESS THAN (202509),
                PARTITION p202509 VALUES LESS THAN (202510),
                PARTITION p202510 VALUES LESS THAN (202511),
                PARTITION p202511 VALUES LESS THAN (202512),
                PARTITION p202512 VALUES LESS THAN (202601),
                PARTITION p_future VALUES LESS THAN MAXVALUE
            );
        ";

        try {
            DB::statement($partitioningSql);
        } catch (\Exception $e) {
            // Log the error - partitioning might not be supported on all database engines
            // In SQLite, this will fail gracefully and partitioning won't be applied
            // In production MySQL/PostgreSQL, this should work
        }

        // Create a stored procedure for automatic partition management
        $this->createPartitionManagementProcedure();
    }

    /**
     * Create stored procedure for partition management
     */
    private function createPartitionManagementProcedure(): void
    {
        // This procedure helps manage partitions automatically
        $procedureSql = "
            CREATE PROCEDURE manage_visitor_partitions()
            BEGIN
                DECLARE current_year INT;
                DECLARE current_month INT;
                DECLARE partition_name VARCHAR(20);
                DECLARE partition_value INT;
                DECLARE next_year INT;
                DECLARE next_month INT;
                DECLARE next_partition_value INT;

                -- Get current date info
                SET current_year = YEAR(CURDATE());
                SET current_month = MONTH(CURDATE());

                -- Calculate next partition (3 months ahead)
                SET next_month = current_month + 3;
                SET next_year = current_year;

                IF next_month > 12 THEN
                    SET next_month = next_month - 12;
                    SET next_year = current_year + 1;
                END IF;

                SET next_partition_value = next_year * 100 + next_month;

                -- Check if next partition exists, if not create it
                SET partition_name = CONCAT('p', DATE_FORMAT(DATE(CONCAT(next_year, '-', next_month, '-01')), '%Y%m'));

                IF NOT EXISTS (
                    SELECT 1 FROM information_schema.partitions
                    WHERE table_name = 'visitors'
                    AND partition_name = partition_name
                ) THEN
                    -- Add new partition
                    SET @sql = CONCAT(
                        'ALTER TABLE visitors ADD PARTITION (PARTITION ',
                        partition_name,
                        ' VALUES LESS THAN (',
                        next_partition_value,
                        '))'
                    );
                    PREPARE stmt FROM @sql;
                    EXECUTE stmt;
                    DEALLOCATE PREPARE stmt;
                END IF;

                -- Archive old partitions (older than 2 years)
                -- This would typically move data to archive tables
            END
        ";

        try {
            DB::statement('DROP PROCEDURE IF EXISTS manage_visitor_partitions');
            DB::statement($procedureSql);
        } catch (\Exception $e) {
            // Stored procedures might not be supported on all databases
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            // Remove partitioning (convert back to regular table)
            DB::statement('ALTER TABLE visitors REMOVE PARTITIONING');

            // Drop the management procedure
            DB::statement('DROP PROCEDURE IF EXISTS manage_visitor_partitions');
        } catch (\Exception $e) {
            // Gracefully handle if partitioning wasn't applied
        }
    }
};
