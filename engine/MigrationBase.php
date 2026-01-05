<?php

namespace Engine;

use PDO;

abstract class MigrationBase
{
    protected $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    abstract public function up();

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    abstract public function down();

    /**
     * Execute a SQL query.
     *
     * @param string $sql
     * @return void
     */
    protected function execute($sql)
    {
        $this->db->exec($sql);
    }
}
