<?php
function getAuthorName($author_id)
{
	$result = mysql_query('select name from author where id='.$author_id);
	if (!$result) {
	    return '';
	}

	while ($row = mysql_fetch_row($result))
	{
		$name = $row[0];
	}
	mysql_free_result($result);
	return htmlspecialchars($name);
}

function getPaperInfo($paper_id)
{
	$ret = array();
	//get title and book title
	$result = mysql_query('select title, booktitle,year,journal,abstract,type from paper where id='.$paper_id);
	if (!$result) {
	    die('Query failed: ' . mysql_error());
	}

	while ($row = mysql_fetch_row($result))
	{
		if($row[5] == "www") return array();
		$ret['title'] = $row[0];
		$ret['booktitle'] = $row[1];
		$ret['year'] = $row[2];
		$ret['journal'] = $row[3];
		$ret['abstract'] = $row[4];
	}
	mysql_free_result($result);
	
	//get author
	$ret['author'] = array();
	$result = mysql_query('select author_id from paper_author where paper_id='.$paper_id.' order by rank');
	if (!$result) {
	    die('Query failed: ' . mysql_error());
	}

	while ($row = mysql_fetch_row($result))
	{
		$ret['author'][$row[0]] = getAuthorName($row[0]);
	}
	mysql_free_result($result);
	return $ret;
}

function getRecommendedUsers($paper_id)
{
	$ret = array();
	$result = mysql_query("select a.user_id, b.email from user_paper_behavior a, user b where a.user_id=b.id and a.behavior=1 and a.paper_id=$paper_id order by a.created_at desc limit 100;");
	if(!$result) return $ret;
	
	while($row = mysql_fetch_row($result))
	{
		$tks = explode('@', $row[1]);
		$user_name = $tks[0];
		$ret[$row[0]] = $user_name;
	}
	mysql_free_result($result);
	return $ret;
}

function hasRecommended($user_id, $paper_id)
{
	$ret = array();
	$result = mysql_query("select * from recommend where user_id=$user_id and paper_id=$paper_id;");
	if(!$result) return 0;
	
	while($row = mysql_fetch_row($result))
	{
		return 1;
	}
	mysql_free_result($result);
	return 0;
}

function hasBookmarked($user_id, $paper_id)
{
	$ret = array();
	$result = mysql_query("select * from bookmark where user_id=$user_id and paper_id=$paper_id;");
	if(!$result) return 0;
	
	while($row = mysql_fetch_row($result))
	{
		return 1;
	}
	mysql_free_result($result);
	return 0;
}

function getDownLoadLink($paper_id)
{
	$ret = '';
	$result = mysql_query("select citeseer_key from paper_citeseer where paper_id=$paper_id;");
	if($result){
		while($row = mysql_fetch_row($result))
		{
			$ret = $row[0];
			break;
		}
	}
	mysql_free_result($result);
	if(strlen($ret) == 0)
	{
		$result = mysql_query("select link from paper_link where id=$paper_id;");
		while($row = mysql_fetch_row($result))
		{
			$ret = $row[0];
			break;
		}
		mysql_free_result($result);
	}
	return $ret;
}
?>
