<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2007-2011 Kasper Skårhøj (kasperYYYY@typo3.com)
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*  A copy is found in the textfile GPL.txt and important notices to the license
*  from the author is found in LICENSE.txt distributed with these scripts.
*
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
 * Cleaner module: syslog
 * User function called from tx_lowlevel_cleaner_core configured in ext_localconf.php
 *
 * @author	Kasper Skårhøj <kasperYYYY@typo3.com>
 */


/**
 * syslog
 *
 * @author	Kasper Skårhøj <kasperYYYY@typo3.com>
 * @package TYPO3
 * @subpackage tx_lowlevel
 */
class tx_lowlevel_syslog extends tx_lowlevel_cleaner_core {

	/**
	 * Constructor
	 *
	 * @return	void
	 */
	function __construct()	{
		parent::__construct();

		$this->cli_help['name'] = 'syslog -- Show entries from syslog';
		$this->cli_help['description'] = trim('
Showing last 25 hour entries from the syslog. More features pending. This is the most basic and can be useful for nightly check test reports.
');

		$this->cli_help['examples'] = '';
	}

	/**
	 * Compatibility constructor.
	 *
	 * @deprecated since TYPO3 4.6 and will be removed in TYPO3 4.8. Use __construct() instead.
	 */
	public function tx_lowlevel_syslog() {
		t3lib_div::logDeprecatedFunction();
			// Note: we cannot call $this->__construct() here because it would call the derived class constructor and cause recursion
			// This code uses official PHP behavior (http://www.php.net/manual/en/language.oop5.basic.php) when $this in the
			// statically called non-static method inherits $this from the caller's scope.
		tx_lowlevel_syslog::__construct();
	}

	/**
	 * Find syslog
	 *
	 * @return	array
	 */
	function main() {
		global $TYPO3_DB;

			// Initialize result array:
		$resultArray = array(
			'message' => $this->cli_help['name'].LF.LF.$this->cli_help['description'],
			'headers' => array(
				'listing' => array('','',1),
				'allDetails' => array('','',0),
			),
			'listing' => array(),
			'allDetails' => array()
		);

		$rows = $TYPO3_DB->exec_SELECTgetRows(
				'*',
				'sys_log',
				'tstamp>' . ($GLOBALS['EXEC_TIME'] - 25 * 3600)
			);
		foreach($rows as $r)	{
			$l = unserialize($r['log_data']);
			$explained = '#'.$r['uid'].' '.t3lib_BEfunc::datetime($r['tstamp']).' USER['.$r['userid'].']: '.sprintf($r['details'],$l[0],$l[1],$l[2],$l[3],$l[4],$l[5]);
			$resultArray['listing'][$r['uid']] = $explained;
			$resultArray['allDetails'][$r['uid']] = array($explained,t3lib_div::arrayToLogString($r,'uid,userid,action,recuid,tablename,recpid,error,tstamp,type,details_nr,IP,event_pid,NEWid,workspace'));
		}

		return $resultArray;
	}

	/**
	 * Mandatory autofix function
	 * Will run auto-fix on the result array. Echos status during processing.
	 *
	 * @param	array		Result array from main() function
	 * @return	void
	 */
	function main_autoFix($resultArray)	{
	}
}

?>