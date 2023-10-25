<?php
class lemsi {
    private static $db;
    private static $table;
    private static $database;

    public static function connect($host, $username, $password, $database) {
        $dsn = 'mysql:host='.$host.';dbname='.$database;      
        self::$database = $database;  
        try {
            self::$db = new PDO($dsn, $username, $password);
            return true;
        }
        catch( PDOException $Exception ) {
            throw new MyDatabaseException( $Exception->getMessage( ) , (int)$Exception->getCode( ) );
        }
    }

    public static function select($table) {
        self::$table = $table;
        return new stdClass();
    }

    public static function save($data) {
        $fields = [];
        $values = [];
        $table = self::$table;
        foreach ($data as $key => $value) {
            $fields[] = $key;
            $values[] = ':' . $key;
        }
        $fields = implode(', ', $fields);
        $values = implode(', ', $values);

        $tableExists = self::checkTableExists();

        if (!$tableExists) {
            self::createTable();
        }

        $missingColumns = self::checkMissingColumns($data);


        if (!empty($missingColumns)) {
            self::addColumns($missingColumns);
        }

        $query = "INSERT INTO {$table} ({$fields}) VALUES ({$values})";
        $stmt = self::$db->prepare($query);

        foreach ($data as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }

        $stmt->execute();

        return true;
    }

    public static function checkTableExists() {
        $query = "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = :database AND table_name = :table";
        $stmt = self::$db->prepare($query);
        $stmt->bindValue(':database', self::$database);
        $stmt->bindValue(':table', self::$table);
        $stmt->execute();
        
        return $stmt->fetchColumn() > 0;
    }

    public static function createTable() {
        $query = "CREATE TABLE IF NOT EXISTS " . self::$table . " (
            id INT(11) AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            age INT(3) NOT NULL,
            email VARCHAR(255) NOT NULL
        )";
        $stmt = self::$db->prepare($query);
        
        return $stmt->execute();
    }

    public static function checkMissingColumns($data) {
        $query = "DESCRIBE " . self::$table;
        $stmt = self::$db->prepare($query);
        $stmt->execute();
        $existingColumns = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        $requiredColumns = ['id'];
        foreach($data as $key =>  $row) {
            array_push($requiredColumns, $key);
        }
        $missingColumns = array_diff($requiredColumns, $existingColumns);
        return $missingColumns;
    }

    public static function addColumns($data) {
        
        foreach ($data as $key => $column) {
            $type = gettype($column);
            if($type === 'integer') {
                $type = "INT";
                $k = '11';
            } else if($type === 'string') {
                $type = "VARCHAR";
                $k = '255';
            } else {
                $type = "VARCHAR";
                $k = '255';
            }
            $query = "ALTER TABLE " . self::$table . " ADD COLUMN " . $column . " ".$type."(".$k.")";
            $stmt = self::$db->prepare($query);
            $stmt->execute();
        }
        
        return true;
    }
    
    public static function find($id) {
        $table = self::$table;
        $query = "SELECT * FROM {$table} WHERE id = :id";
        $stmt = self::$db->prepare($query);
        $stmt->bindValue(':id', $id);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function update($id, $data) {
        $table = self::$table;
        $fields = [];
        foreach ($data as $key => $value) {
            $fields[] = "{$key} = :{$key}";
        }
        $fields = implode(', ', $fields);

        $query = "UPDATE {$table} SET {$fields} WHERE id = :id";
        $stmt = self::$db->prepare($query);

        $stmt->bindValue(':id', $id);
        foreach ($data as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }

        return $stmt->execute();
    }

    public static function delete($id) {
        $table = self::$table;
        $query = "DELETE FROM {$table} WHERE id = :id";
        $stmt = self::$db->prepare($query);
        $stmt->bindValue(':id', $id);

        return $stmt->execute();
    }
}