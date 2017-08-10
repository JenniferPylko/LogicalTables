<!DOCTYPE html>
<html>
<head><title>LogicalTables test</title></head>
<body>
<?php
include_once("./lib/LogicalTable.php");
$db = new mysqli("localhost", "root", "root", "LogicalTables");
$test = new LogicalTable($db, "Test");
$test->setupDB();
$test->addSegment("test_segment_one");
?>
</body>
</html>
