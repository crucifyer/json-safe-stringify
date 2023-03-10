<?php
/*
Made by shj at xenosi.de
License: MIT
https://github.com/crucifyer/json-safe-stringify
 */

namespace Xeno\Json;

class Json {
	private static $objkeys = [], $keynames = [];

	private static function objectSearch(&$obj) {
		foreach(self::$objkeys as $idx => &$key) {
			if($key === $obj) return $idx;
		}
		return false;
	}

	private static function _RemoveCircular(&$obj, $keyname) {
		if(!is_array($obj) && !is_object($obj)) return;
		foreach($obj as $x => &$o) {
			if(false !== $idx = self::objectSearch($o)) {
				if(is_array($obj)) {
					unset($obj[$x]);
					$obj[$x] = '__circularObject__'.self::$keynames[$idx];
				} else {
					unset($obj->{$x});
					$obj->{$x} = '__circularObject__'.self::$keynames[$idx];
				}
				continue;
			}
			self::$objkeys[] = &$o;
			self::$keynames[] = $keyname.':'.$x;
			self::_RemoveCircular($o, $keyname.':'.$x);
		}
	}
	public static function RemoveCircular(&$targetObject) {
		self::$objkeys = [$targetObject];
		self::$keynames = ['o'];

		self::_RemoveCircular($targetObject, 'o');

		return true;
	}

	private static function _RestoreCircular(&$targetObject, &$obj) {
		if(!is_array($obj) && !is_object($obj)) return;
		foreach($obj as $x => &$o) {
			if(is_string($o) && substr($o, 0, 18) == '__circularObject__') {
				$keys = explode(':', substr($o, 18));
				array_shift($keys);
				$tobj = &$targetObject;
				while(count($keys)) {
					$key = array_shift($keys);
					unset($tobj);
					if(is_array($obj)) {
						$tobj = &$obj[$key];
					} else {
						$tobj = &$obj->{$key};
					}
				}
				if(is_array($obj)) {
					unset($obj[$x]);
					$obj[$x] = &$tobj;
				} else {
					unset($obj->{$x});
					$obj->{$x} = &$tobj;
				}
				unset($tobj);
			} else {
				self::_RestoreCircular($targetObject, $o);
			}
		}
	}

	public static function RestoreCircular(&$targetObject) {

		self::_RestoreCircular($targetObject, $targetObject);

		return true;
	}

	public static function ObjectFullCopy($targetObject) {
		self::RemoveCircular($targetObject);
		$jsonText = json_encode($targetObject, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
		self::RestoreCircular($targetObject);
		$res = json_decode($jsonText);
		return self::RestoreCircular($res);
	}
}
