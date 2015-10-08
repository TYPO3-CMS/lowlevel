<?php
defined('TYPO3_MODE') or die();

if (TYPO3_MODE === 'BE') {
    // Setting up scripts that can be run from the cli_dispatch.phpsh script.
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['GLOBAL']['cliKeys']['lowlevel_refindex'] = array(
        function () {
            // Call the functionality
            if (in_array('-e', $_SERVER['argv']) || in_array('-c', $_SERVER['argv'])) {
                $testOnly = in_array('-c', $_SERVER['argv']);
                $refIndexObj = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Database\ReferenceIndex::class);
                list($headerContent, $bodyContent) = $refIndexObj->updateIndex($testOnly, !in_array('-s', $_SERVER['argv']));
                $bodyContent = str_replace('##LF##', LF, $bodyContent);
            } else {
                echo '
			Options:
			-c = Check refindex
			-e = Update refindex
			-s = Silent
			';
                die;
            }
        },
        '_CLI_lowlevel'
    );
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['GLOBAL']['cliKeys']['lowlevel_cleaner'] = array(
        function () {
            $cleanerObj = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Lowlevel\CleanerCommand::class);
            $cleanerObj->cli_main($_SERVER['argv']);
        },
        '_CLI_lowlevel'
    );
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['GLOBAL']['cliKeys']['lowlevel_admin'] = array(
        function () {
            $adminObj = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Lowlevel\AdminCommand::class);
            $adminObj->cli_main($_SERVER['argv']);
        },
        '_CLI_lowlevel'
    );
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['lowlevel']['cleanerModules']['missing_files'] = array(\TYPO3\CMS\Lowlevel\MissingFilesCommand::class);
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['lowlevel']['cleanerModules']['missing_relations'] = array(\TYPO3\CMS\Lowlevel\MissingRelationsCommand::class);
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['lowlevel']['cleanerModules']['double_files'] = array(\TYPO3\CMS\Lowlevel\DoubleFilesCommand::class);
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['lowlevel']['cleanerModules']['rte_images'] = array(\TYPO3\CMS\Lowlevel\RteImagesCommand::class);
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['lowlevel']['cleanerModules']['lost_files'] = array(\TYPO3\CMS\Lowlevel\LostFilesCommand::class);
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['lowlevel']['cleanerModules']['orphan_records'] = array(\TYPO3\CMS\Lowlevel\OrphanRecordsCommand::class);
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['lowlevel']['cleanerModules']['deleted'] = array(\TYPO3\CMS\Lowlevel\DeletedRecordsCommand::class);
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['lowlevel']['cleanerModules']['versions'] = array(\TYPO3\CMS\Lowlevel\VersionsCommand::class);
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['lowlevel']['cleanerModules']['cleanflexform'] = array(\TYPO3\CMS\Lowlevel\CleanFlexformCommand::class);
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['lowlevel']['cleanerModules']['syslog'] = array(\TYPO3\CMS\Lowlevel\SyslogCommand::class);
}
