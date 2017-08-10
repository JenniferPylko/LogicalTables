Overview
========
This is sort of an implementation of SQL views in PHP/SQL, but as far as I know no one has created a system quite like this before.

Logical tables, besides the 3 tables that are needed for the system, are composed of 1-n actual tables (called "segments" in the library), where n is the number of data columns in the logical 
table (data columns being columns where information not managed by the library is stored). There could theoretically be more segments added, but they would slow down data retrieval and not provide any 
benefit.

Logical tables can share segments. For example, if a website has Administrators and Users, each could have their own logical table, but share a segment containing information relevant to both, such as 
username.



Logical objects are the equivalent of rows in a normal database, but with some additional features:

1. Built-in record of changes. This can be retrieved to give a log of the entire history of the row. History can be selectively removed or thinned out to reduce DB size.
2. Storage of pending changes. For example, this can be used to create requests awaiting approval, or save data while it is being edited to be stored later.



Segments can be used to automatically reduce database size and load by distributing columns across multiple tables, despite the complete record of changes being stored.

Columns can be manually assigned to certain segments to optimize the database further, or to allow sharing of segments between logical tables.

The more logical tables a segment is shared between, the more columns the segment's table in the database will have. This could cause increased load and size if the segment is frequently updated.

API
===
Because the code is far from complete, this will likely change. Here's a rough idea of what it will look like:

class LogicalTable
------------------
__construct(mysqli $db, string $name)

* Associates with the mysqli object $db, and uses $name for the logical table name

setupDB()

* Creates any needed tables if they don't exist and sets up indices

addSegment(string $name)

* Creates a segment in this logical table if it does not exist
* If the table exists, it links itself to the segment. A segment can be shared between logical tables by calling addSegment() on both of them with the same name provided

removeSegment(string $name)

* Removes a segment from this logical table
* It does not delete the segment's table in the database
* The segment can be reassociated with no data loss by calling addSegment($name)

getSegments()

* Retrieves the list of segments linked to this logical table

addColumn(string $name, ?string $segment)

* Adds a column with the specified name
* If $segment is null, it automatically chooses the segment with the fewest columns

LogicalObject
-------------

__construct(mysqli $db, LogicalTable $table, int $id)

* Associates with the mysqli instance $db, and retrieves the logical object from the logical table $table with id $id
* If $id is <= 0, it creates a new logical object in the logical table

static query(mysqli $db, LogicalTable $table, array $query)

* Returns a the id of a logical object where the data matches the query
* $query is an associative array in the format $column => $value
* If $query matches multiple objects, it returns the one most recently updated

addChanges(array $changes)

* Adds the specified changes to the changes waiting to be committed
* This does not make any database changes
* $changes is an associative array in the format $column => $value

getChanges()

* Returns the changes that have been applied to the object, but not committed to the database

commitChanges()

* Saves changes added with addChanges() to the database

setPendingChanges(array $changes)

* Sets the object's pending changes to $changes
* This immediately updates the database
* $changes is an associative array in the format $column => value

getPendingChanges()

* Returns an array with the currently pending changes on the object

commitPendingChanges()

* Writes the pending changes to the object's history
* The changes will no longer be returned by getPendingChanges()

linkSegment(string $segmentName, LogicalTable $otherTable, int otherObjectID)

* When 2 logical tables share a segment, that means they both store data in the segment, but objects of the tables remain separate
* Linking segments together in an object means that the 2 objects share data, and if one is updated, the other will also have access to the new data
* If data in the 2 segments is different, this object will overwrite the other object's data

unlinkSegment(string $segmentName)

* Removes any links in this segment to other objects
* No data is lost, but the histories will now diverge as changes are made

getHistoryByDate($startDate, $endDate)

* Returns an array of rows between $startDate and $endDate, inclusive
* if $startDate is null, it is assumed to be the beginning of time
* if $endDate is null, it is assumed to be the end of time

getHistory(int $numRecords, int $skip)

* Returns an array of rows of length $numRecords, starting $skip records from the object's current data
* If $skip is < 1, it starts from the object's oldest data
