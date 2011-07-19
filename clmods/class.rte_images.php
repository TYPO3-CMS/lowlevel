<?php
/***************************************************************
*  Copyright notice
*
*  (c) 1999-2011 Kasper Skårhøj (kasperYYYY@typo3.com)
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
 * Cleaner module: RTE magicc images
 * User function called from tx_lowlevel_cleaner_core configured in ext_localconf.php
 *
 * @author	Kasper Skårhøj <kasperYYYY@typo3.com>
 */
/**
 * Looking for RTE images integrity
 *
 * @author	Kasper Skårhøj <kasperYYYY@typo3.com>
 * @package TYPO3
 * @subpackage tx_lowlevel
 */
class tx_lowlevel_rte_images extends tx_lowlevel_cleaner_core {

	var $checkRefIndex = TRUE;

	/**
	 * Constructor
	 *
	 * @return	void
	 */
	function __construct()	{
		parent::__construct();

			// Setting up help:
		$this->cli_help['name'] = 'rte_images -- Looking up all occurencies of RTEmagic images in the database and check existence of parent and copy files on the file system plus report possibly lost files of this type.';
		$this->cli_help['description'] = trim('
Assumptions:
- a perfect integrity of the reference index table (always update the reference index table before using this tool!)
- that all RTEmagic image files in the database are registered with the soft reference parser "images"
- images found in deleted records are included (means that you might find lost RTEmagic images after flushing deleted records)

The assumptions are not requirements by the TYPO3 API but reflects the de facto implementation of most TYPO3 installations.
However, many custom fields using an RTE will probably not have the "images" soft reference parser registered and so the index will be incomplete and not listing all RTEmagic image files.
The consequence of this limitation is that you should be careful if you wish to delete lost RTEmagic images - they could be referenced from a field not parsed by the "images" soft reference parser!

Automatic Repair of Errors:
- Will search for double-usages of RTEmagic images and make copies as required.
- Lost files can be deleted automatically by setting the value "lostFiles" as an optional parameter to --AUTOFIX, but otherwise delete them manually if you do not recognize them as used somewhere the system does not know about.

Manual repair suggestions:
- Missing files: Re-insert missing files or edit record where the reference is found.
');

		$this->cli_help['examples'] = '/.../cli_dispatch.phpsh lowlevel_cleaner rte_images -s -r
Reports problems with RTE images';
	}

	/**
	 * Compatibility constructor.
	 *
	 * @deprecated since TYPO3 4.6 and will be removed in TYPO3 4.8. Use __construct() instead.
	 */
	public function tx_lowlevel_rte_images() {
		t3lib_div::logDeprecatedFunction();
			// Note: we cannot call $this->__construct() here because it would call the derived class constructor and cause recursion
			// This code uses official PHP behavior (http://www.php.net/manual/en/language.oop5.basic.php) when $this in the
			// statically called non-static method inherits $this from the caller's scope.
		tx_lowlevel_rte_images::__construct();
	}

	/**
	 * Analyse situation with RTE magic images. (still to define what the most useful output is).
	 * Fix methods: API in t3lib_refindex that allows to change the value of a reference (we could copy the files) or remove reference
	 *
	 * @return	array
	 */
	function main() {
			global $TYPO3_DB;

			// Initialize result array:
		$resultArray = array(
			'message' => $this->cli_help['name'].LF.LF.$this->cli_help['description'],
			'headers' => array(
				'completeFileList' => array('Complete list of used RTEmagic files','Both parent and copy are listed here including usage count (which should in theory all be "1"). This list does not exclude files that might be missing.',1),
				'RTEmagicFilePairs' => array('Statistical info about RTEmagic files','(copy used as index)',0),
				'doubleFiles' => array('Duplicate RTEmagic image files','These files are RTEmagic images found used in multiple records! RTEmagic images should be used by only one record at a time. A large amount of such images probably stems from previous versions of TYPO3 (before 4.2) which did not support making copies automatically of RTEmagic images in case of new copies / versions.',3),
				'missingFiles' => array('Missing RTEmagic image files','These files are not found in the file system! Should be corrected!',3),
				'lostFiles' => array('Lost RTEmagic files from uploads/','These files you might be able to delete but only if _all_ RTEmagic images are found by the soft reference parser. If you are using the RTE in third-party extensions it is likely that the soft reference parser is not applied correctly to their RTE and thus these "lost" files actually represent valid RTEmagic images, just not registered. Lost files can be auto-fixed but only if you specifically set "lostFiles" as parameter to the --AUTOFIX option.',2),
			),
			'RTEmagicFilePairs' => array(),
			'doubleFiles' => array(),
			'completeFileList' => array(),
			'missingFiles' => array(),
			'lostFiles' => array(),
		);

			// Select all RTEmagic files in the reference table (only from soft references of course)
		$recs = $TYPO3_DB->exec_SELECTgetRows(
			'*',
			'sys_refindex',
			'ref_table='.$TYPO3_DB->fullQuoteStr('_FILE', 'sys_refindex').
				' AND ref_string LIKE '.$TYPO3_DB->fullQuoteStr('%/RTEmagic%', 'sys_refindex').
				' AND softref_key='.$TYPO3_DB->fullQuoteStr('images', 'sys_refindex'),
			'',
			'sorting DESC'
		);

			// Traverse the files and put into a large table:
		if (is_array($recs)) {
			foreach($recs as $rec)	{
				$filename = basename($rec['ref_string']);
				if (t3lib_div::isFirstPartOfStr($filename,'RTEmagicC_'))	{
					$original = 'RTEmagicP_'.preg_replace('/\.[[:alnum:]]+$/','',substr($filename,10));
					$infoString = $this->infoStr($rec);

						// Build index:
					$resultArray['RTEmagicFilePairs'][$rec['ref_string']]['exists'] = @is_file(PATH_site.$rec['ref_string']);
					$resultArray['RTEmagicFilePairs'][$rec['ref_string']]['original'] = substr($rec['ref_string'],0,-strlen($filename)).$original;
					$resultArray['RTEmagicFilePairs'][$rec['ref_string']]['original_exists'] = @is_file(PATH_site.$resultArray['RTEmagicFilePairs'][$rec['ref_string']]['original']);
					$resultArray['RTEmagicFilePairs'][$rec['ref_string']]['count']++;
					$resultArray['RTEmagicFilePairs'][$rec['ref_string']]['usedIn'][$rec['hash']] = $infoString;

					$resultArray['completeFileList'][$resultArray['RTEmagicFilePairs'][$rec['ref_string']]['original']]++;
					$resultArray['completeFileList'][$rec['ref_string']]++;

						// Missing files:
					if (!$resultArray['RTEmagicFilePairs'][$rec['ref_string']]['exists'])	{
						$resultArray['missingFiles'][$rec['ref_string']] = $resultArray['RTEmagicFilePairs'][$rec['ref_string']]['usedIn'];
					}
					if (!$resultArray['RTEmagicFilePairs'][$rec['ref_string']]['original_exists'])	{
						$resultArray['missingFiles'][$resultArray['RTEmagicFilePairs'][$rec['ref_string']]['original']] = $resultArray['RTEmagicFilePairs'][$rec['ref_string']]['usedIn'];
					}
				}
			}

				// Searching for duplicates:
			foreach($resultArray['RTEmagicFilePairs'] as $fileName => $fileInfo) {
				if ($fileInfo['count']>1 && $fileInfo['exists'] && $fileInfo['original_exists']) 	{
					$resultArray['doubleFiles'][$fileName] = $fileInfo['usedIn'];
				}
			}
		}

			// Now, ask for RTEmagic files inside uploads/ folder:
		$cleanerModules = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['lowlevel']['cleanerModules'];
		$cleanerMode = t3lib_div::getUserObj($cleanerModules['lost_files'][0]);
		$resLostFiles = $cleanerMode->main(array(),FALSE,TRUE);
		if (is_array($resLostFiles['RTEmagicFiles']))	{
			foreach($resLostFiles['RTEmagicFiles'] as $fileName) {
				if (!isset($resultArray['completeFileList'][$fileName])) 	{
					$resultArray['lostFiles'][$fileName] = $fileName;
				}
			}
		}


		ksort($resultArray['RTEmagicFilePairs']);
		ksort($resultArray['completeFileList']);
		ksort($resultArray['missingFiles']);
		ksort($resultArray['doubleFiles']);
		ksort($resultArray['lostFiles']);
	#	print_r($resultArray);

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

		$limitTo = $this->cli_args['--AUTOFIX'][0];

		if (is_array($resultArray['doubleFiles']))	{
			if (!$limitTo || $limitTo==='doubleFiles')	{

				echo 'FIXING double-usages of RTE files in uploads/: '.LF;
				foreach($resultArray['RTEmagicFilePairs'] as $fileName => $fileInfo) {

						// Only fix something if there is a usage count of more than 1 plus if both original and copy exists:
					if ($fileInfo['count']>1 && $fileInfo['exists'] && $fileInfo['original_exists']) 	{

							// Traverse all records using the file:
						$c=0;
						foreach($fileInfo['usedIn'] as $hash => $recordID)	{
							if ($c==0)	{
								echo '	Keeping file '.$fileName.' for record '.$recordID.LF;
							} else {
									// CODE below is adapted from "class.tx_impexp.php" where there is support for duplication of RTE images:
								echo '	Copying file '.basename($fileName).' for record '.$recordID.' ';

									// Initialize; Get directory prefix for file and set the original name:
								$dirPrefix = dirname($fileName).'/';
								$rteOrigName = basename($fileInfo['original']);

									// If filename looks like an RTE file, and the directory is in "uploads/", then process as a RTE file!
								if ($rteOrigName && t3lib_div::isFirstPartOfStr($dirPrefix,'uploads/') && @is_dir(PATH_site.$dirPrefix))	{	// RTE:

										// From the "original" RTE filename, produce a new "original" destination filename which is unused.
									$fileProcObj = $this->getFileProcObj();
									$origDestName = $fileProcObj->getUniqueName($rteOrigName, PATH_site.$dirPrefix);

										// Create copy file name:
									$pI = pathinfo($fileName);
									$copyDestName = dirname($origDestName).'/RTEmagicC_'.substr(basename($origDestName),10).'.'.$pI['extension'];
									if (!@is_file($copyDestName) && !@is_file($origDestName)
										&& $origDestName===t3lib_div::getFileAbsFileName($origDestName) && $copyDestName===t3lib_div::getFileAbsFileName($copyDestName))	{

										echo ' to '.basename($copyDestName);

										if ($bypass = $this->cli_noExecutionCheck($fileName))	{
											echo $bypass;
										} else {
												// Making copies:
											t3lib_div::upload_copy_move(PATH_site.$fileInfo['original'],$origDestName);
											t3lib_div::upload_copy_move(PATH_site.$fileName,$copyDestName);
											clearstatcache();

											if (@is_file($copyDestName))	{
												$sysRefObj = t3lib_div::makeInstance('t3lib_refindex');
												$error = $sysRefObj->setReferenceValue($hash,substr($copyDestName,strlen(PATH_site)));
												if ($error)	{
													echo '	- ERROR:	t3lib_refindex::setReferenceValue(): '.$error.LF;
													exit;
												} else echo " - DONE";
											} else {
												echo '	- ERROR: File "'.$copyDestName.'" was not created!';
											}
										}
									} else echo '	- ERROR: Could not construct new unique names for file!';
								} else echo '	- ERROR: Maybe directory of file was not within "uploads/"?';
								echo LF;
							}
							$c++;
						}
					}
				}
			} else echo 'Bypassing fixing of double-usages since --AUTOFIX was not "doubleFiles"'.LF;
		}


		if (is_array($resultArray['lostFiles']))	{
			if ($limitTo==='lostFiles')	{
				echo 'Removing lost RTEmagic files from folders inside uploads/: '.LF;

				foreach($resultArray['lostFiles'] as $key => $value)	{
					$absFileName = t3lib_div::getFileAbsFileName($value);
					echo 'Deleting file: "'.$absFileName.'": ';
					if ($bypass = $this->cli_noExecutionCheck($absFileName))	{
						echo $bypass;
					} else {
						if ($absFileName && @is_file($absFileName))	{
							unlink($absFileName);
							echo 'DONE';
						} else {
							echo '	ERROR: File "'.$absFileName.'" was not found!';
						}
					}
					echo LF;
				}
			}
		} else echo 'Bypassing fixing of double-usages since --AUTOFIX was not "lostFiles"'.LF;
	}

	/**
	 * Returns file processing object, initialized only once.
	 *
	 * @return	object		File processor object
	 */
	function getFileProcObj() {
		if (!is_object($this->fileProcObj))	{
			$this->fileProcObj = t3lib_div::makeInstance('t3lib_extFileFunctions');
			$this->fileProcObj->init($GLOBALS['FILEMOUNTS'], $GLOBALS['TYPO3_CONF_VARS']['BE']['fileExtensions']);
			$this->fileProcObj->init_actionPerms($GLOBALS['BE_USER']->getFileoperationPermissions());
		}
		return $this->fileProcObj;
	}
}

?>