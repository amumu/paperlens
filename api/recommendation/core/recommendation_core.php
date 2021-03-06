<?php

function recommendationCore($features, $table_name, $topN, $feature_type = 'paper')
{
	$ret_weight = array();
	$ret_reason = array();
	foreach($features as $item=>$preference)
	{
		$related_items = array();
		if($feature_type=='paper') $related_items = GetRelatedItems($item, $table_name, $topN);
		else if($feature_type=='query') $related_items = GetRelatedItemsBySearch($item, $topN);
		if(count($related_items) == 0) continue;
		$max_sim = max(array_values($related_items));
		foreach($related_items as $related_item=>$similarity)
		{
			if(!array_key_exists($related_item, $ret_weight))
			{
				$ret_weight[$related_item] = 0;
				$ret_reason[$related_item] = array();
			}
			$ret_weight[$related_item] += $preference * $similarity / $max_sim;
			if(!array_key_exists($item, $ret_reason[$related_item]))
			{
				$ret_reason[$related_item][$item] = 0;
			}
			$ret_reason[$related_item][$item] += $preference * $similarity / $max_sim;
		}
	}
	$ret = array();
	array_push($ret, $ret_weight);
	array_push($ret, $ret_reason);
	return $ret;
}

?>