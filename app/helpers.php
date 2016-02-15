<?php
class Helpers {
	static function timestamp($timestamp) {
		$user = Auth::user();
		if($user) {
			$value = $user->getSettingValue('Timestamp Style');
			if($value && $value == "exact") return $timestamp->setTimezone('America/Chicago')->format('F jS g:i A');
		}
		return $timestamp->diffForHumans();
	}
	
	static function nl_join(array $list, $conjunction = 'and') {
		$last = array_pop($list);
		if ($list) {
			return implode(', ', $list) . ' ' . $conjunction . ' ' . $last;
			}
	  return $last;
	}
	
	static function extractByKey($key, $collection) {
		$list = []; 
		foreach($collection as $u) $list[] = $u->$key;
		return $list;
	}
}
