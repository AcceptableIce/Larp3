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
}
