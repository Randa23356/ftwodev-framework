<?php

namespace Projects\Migrations;

use Engine\MigrationBase;

class CreateUsersTable extends MigrationBase
{
    public function up()
    {
        $this->execute("CREATE TABLE users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(100) NOT NULL,
            email VARCHAR(200) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
    }

    public function down()
    {
        $this->execute("DROP TABLE users");
    }
}

