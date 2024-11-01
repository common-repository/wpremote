<?php
if (!defined('ABSPATH') && !defined('MCDATAPATH')) exit;

if (!class_exists('WPRProtectIpstoreDB_V581')) :
class WPRProtectIpstoreDB_V581 {
		const TABLE_NAME = 'ip_store';

		const CATEGORY_FW = 3;
		const CATEGORY_LP = 4;

		#XNOTE: check this. 
		public static function blacklistedTypes() {
			return WPRProtectRequest_V581::blacklistedCategories();
		}

		public static function whitelistedTypes() {
			return WPRProtectRequest_V581::whitelistedCategories();
		}

		public static function uninstall() {
			WPRProtect_V581::$db->dropBVTable(WPRProtectIpstoreDB_V581::TABLE_NAME);
		}

		public function isLPIPBlacklisted($ip) {
			return $this->checkIPPresent($ip, self::blacklistedTypes(), WPRProtectIpstoreDB_V581::CATEGORY_LP);
		}

		public function isLPIPWhitelisted($ip) {
			return $this->checkIPPresent($ip, self::whitelistedTypes(), WPRProtectIpstoreDB_V581::CATEGORY_LP);
		}

		public function getTypeIfBlacklistedIP($ip) {
			return $this->getIPType($ip, self::blacklistedTypes(), WPRProtectIpstoreDB_V581::CATEGORY_FW);
		}

		public function isFWIPBlacklisted($ip) {
			return $this->checkIPPresent($ip, self::blacklistedTypes(), WPRProtectIpstoreDB_V581::CATEGORY_FW);
		}

		public function isFWIPWhitelisted($ip) {
			return $this->checkIPPresent($ip, self::whitelistedTypes(), WPRProtectIpstoreDB_V581::CATEGORY_FW);
		}

		private function checkIPPresent($ip, $types, $category) {
			$ip_category = $this->getIPType($ip, $types, $category);

			return isset($ip_category) ? true : false;
		}

		#XNOTE: getIPCategory or getIPType?
		private function getIPType($ip, $types, $category) {
			$table = WPRProtect_V581::$db->getBVTable(WPRProtectIpstoreDB_V581::TABLE_NAME);

			if (WPRProtect_V581::$db->isTablePresent($table)) {
				$binIP = WPRProtectUtils_V581::bvInetPton($ip);
				$is_v6 = WPRProtectUtils_V581::isIPv6($ip);

				if ($binIP !== false) {
					$query_str = "SELECT * FROM $table WHERE %s >= `start_ip_range` && %s <= `end_ip_range` && ";
					if ($category == WPRProtectIpstoreDB_V581::CATEGORY_FW) {
						$query_str .= "`is_fw` = true";
					} else {
						$query_str .= "`is_lp` = true";
					}
					$query_str .= " && `type` in (" . implode(',', $types) . ") && `is_v6` = %d LIMIT 1;";

					$query = WPRProtect_V581::$db->prepare($query_str, array($binIP, $binIP, $is_v6));

					return WPRProtect_V581::$db->getVar($query, 5);
				}
			}
		}
	}
endif;