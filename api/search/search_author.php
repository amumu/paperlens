<?php
header('Content-Type: text/xml');

require_once('../db.php');

$query = $_GET['query'];
$topN = $_GET['n'];

//$result = mysql_query("select * from sphinx  where query='@title \"" . $query . "\" | @name \"" .$query . "\";mode=any;sort=relevance;limit=".$topN. ";index=idx1';");
$result = mysql_query("select * from sphinx  where query='@name \"" . $query . "\";mode=extended;sort=extended:year desc, citations desc;limit=".$topN. ";index=idx1';");
if (!$result) {
    die('Query failed: ' . mysql_error());
}

echo "<result>";
$i = 0;
while ($row = mysql_fetch_row($result))
{
	++$i;
	$id = $row[0];
	echo file_get_contents('http://127.0.0.1/api/paper.php?id=' . $id) ;
}

echo "</result>";

?>