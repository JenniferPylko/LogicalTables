<?php
declare(strict_types=1);

class LogicalTable {
    private $db;
    private $name;
    private $segments;
    public function __construct(mysqli $db, string $name) {
        $this->db = $db;
        $this->name = $name;
    }
    public function setupDB() {
        $this->db->begin_transaction();
        $this->db->query("CREATE TABLE IF NOT EXISTS LogicalTables (LogicalName varchar(127), SegmentName varchar(127))");
        $this->db->query("CREATE TABLE IF NOT EXISTS LogicalObjects (LogicalName varchar(127), SegmentID varchar(11))");
        $this->db->query("ALTER TABLE `LogicalTables` ADD UNIQUE (`LogicalName`, `SegmentName`");
        $this->db->query("ALTER TABLE `LogicalObjects` ADD UNIQUE (`LogicalName`, `ObjectID`");
        $this->db->commit();
    }
    public function addSegment(string $name) {
        $this->db->begin_transaction();
        $stmt = $this->db->prepare("INSERT INTO LogicalTables (LogicalName, SegmentName) VALUES (?, ?)");
        $stmt->bind_param("ss", $this->name, $name);
        $stmt->execute();
        $describe = $this->db->query("DESCRIBE $name");
        if ($describe) {
            $associated = false;
            while ($row = $describe->fetch_row()) {
                if ($row[0] = $this->name."_id") {
                    $associated = true;
                    break;
                }
            }
            if (!associated) {
                $this->db->query("ALTER TABLE $name ADD `{$this->name}_id` INT(11) NOT NULL AUTO_INCREMENT FIRST");
            }
        } else {
            $this->db->query("CREATE TABLE $name ({$this->name}_id, timestamp, pending)");
            $this->db->query("ALTER TABLE $name MODIFY `{$this->name}_id` int(11) NOT NULL AUTO_INCREMENT");
        }
        $this->db->query("ALTER TABLE $name ADD UNIQUE (`{$this->name}`)");
    }
    public function getSegments() {
        $this->segments = [];
        $stmt = $this->db->prepare("SELECT SegmentName FROM LogicalTables WHERE LogicalName=?");
        $stmt->bind_param("s", $this->name);
        $stmt->execute();
        $stmt->bind_result($segment);
        var_dump($segment);
        while ($stmt->fetch()) {
            $this->segments []= $segment;
        }
        return $this->segments;
    }
    public function addColumn(string $name, ?string $segment) {
        
    }
}
