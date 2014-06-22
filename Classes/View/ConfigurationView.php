<?php
namespace TYPO3\CMS\Lowlevel\View;

/**
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Script class for the Config module
 *
 * @author Kasper Skårhøj <kasperYYYY@typo3.com>
 */
class ConfigurationView {

	/**
	 * @todo Define visibility
	 */
	public $MCONF = array();

	/**
	 * @todo Define visibility
	 */
	public $MOD_MENU = array();

	/**
	 * @todo Define visibility
	 */
	public $MOD_SETTINGS = array();

	/**
	 * Document template object
	 *
	 * @var \TYPO3\CMS\Backend\Template\DocumentTemplate
	 * @todo Define visibility
	 */
	public $doc;

	/**
	 * @todo Define visibility
	 */
	public $include_once = array();

	/**
	 * @todo Define visibility
	 */
	public $content;

	/**
	 * Constructor
	 */
	public function __construct() {
		$GLOBALS['LANG']->includeLLFile('EXT:lowlevel/config/locallang.xlf');
		$GLOBALS['BE_USER']->modAccess($GLOBALS['MCONF'], 1);
	}

	/**
	 * Initialization
	 *
	 * @return void
	 * @todo Define visibility
	 */
	public function init() {
		global $BACK_PATH;
		$this->MCONF = $GLOBALS['MCONF'];
		$this->menuConfig();
		$this->doc = GeneralUtility::makeInstance('TYPO3\\CMS\\Backend\\Template\\DocumentTemplate');
		$this->doc->backPath = $BACK_PATH;
		$this->doc->setModuleTemplate('EXT:lowlevel/Resources/Private/Templates/config.html');
		$this->doc->form = '<form action="" method="post">';
	}

	/**
	 * Menu Configuration
	 *
	 * @return void
	 * @todo Define visibility
	 */
	public function menuConfig() {
		global $TYPO3_CONF_VARS;
		// MENU-ITEMS:
		// If array, then it's a selector box menu
		// If empty string it's just a variable, that'll be saved.
		// Values NOT in this array will not be saved in the settings-array for the module.
		$this->MOD_MENU = array(
			'function' => array(
				0 => $GLOBALS['LANG']->getLL('typo3ConfVars', TRUE),
				1 => $GLOBALS['LANG']->getLL('tca', TRUE),
				2 => $GLOBALS['LANG']->getLL('tcaDescr', TRUE),
				3 => $GLOBALS['LANG']->getLL('loadedExt', TRUE),
				4 => $GLOBALS['LANG']->getLL('t3services', TRUE),
				5 => $GLOBALS['LANG']->getLL('tbemodules', TRUE),
				6 => $GLOBALS['LANG']->getLL('tbemodulesext', TRUE),
				7 => $GLOBALS['LANG']->getLL('tbeStyles', TRUE),
				8 => $GLOBALS['LANG']->getLL('beUser', TRUE),
				9 => $GLOBALS['LANG']->getLL('usersettings', TRUE)
			),
			'regexsearch' => '',
			'fixedLgd' => ''
		);
		// CLEANSE SETTINGS
		$this->MOD_SETTINGS = BackendUtility::getModuleData($this->MOD_MENU, GeneralUtility::_GP('SET'), $this->MCONF['name']);
	}

	/**
	 * [Describe function...]
	 *
	 * @return void
	 * @todo Define visibility
	 */
	public function main() {
		$arrayBrowser = GeneralUtility::makeInstance('TYPO3\\CMS\\Lowlevel\\Utility\\ArrayBrowser');
		$label = $this->MOD_MENU['function'][$this->MOD_SETTINGS['function']];
		$search_field = GeneralUtility::_GP('search_field');
		$this->content = $this->doc->header($GLOBALS['LANG']->getLL('configuration', TRUE));
		$this->content .= '<h2>' . $label . '</h2>';

		$this->content .= '<div id="lowlevel-config">
						<label for="search_field">' . $GLOBALS['LANG']->getLL('enterSearchPhrase', TRUE) . '</label>
						<input type="text" id="search_field" name="search_field" value="' . htmlspecialchars($search_field) . '"' . $GLOBALS['TBE_TEMPLATE']->formWidth(20) . ' />
						<input type="submit" name="search" id="search" value="' . $GLOBALS['LANG']->getLL('search', TRUE) . '" />';
		$this->content .= BackendUtility::getFuncCheck(0, 'SET[regexsearch]', $this->MOD_SETTINGS['regexsearch'], '', '', 'id="checkRegexsearch"') . '<label for="checkRegexsearch">' . $GLOBALS['LANG']->getLL('useRegExp', TRUE) . '</label>';
		$this->content .= BackendUtility::getFuncCheck(0, 'SET[fixedLgd]', $this->MOD_SETTINGS['fixedLgd'], '', '', 'id="checkFixedLgd"') . '<label for="checkFixedLgd">' . $GLOBALS['LANG']->getLL('cropLines', TRUE) . '</label>
						</div>';
		$this->content .= $this->doc->spacer(5);
		switch ($this->MOD_SETTINGS['function']) {
			case 0:
				$theVar = $GLOBALS['TYPO3_CONF_VARS'];
				GeneralUtility::naturalKeySortRecursive($theVar);
				$arrayBrowser->varName = '$TYPO3_CONF_VARS';
				break;
			case 1:
				$theVar = $GLOBALS['TCA'];
				GeneralUtility::naturalKeySortRecursive($theVar);
				$arrayBrowser->varName = '$TCA';
				break;
			case 2:
				$theVar = $GLOBALS['TCA_DESCR'];
				GeneralUtility::naturalKeySortRecursive($theVar);
				$arrayBrowser->varName = '$TCA_DESCR';
				break;
			case 3:
				$theVar = $GLOBALS['TYPO3_LOADED_EXT'];
				GeneralUtility::naturalKeySortRecursive($theVar);
				$arrayBrowser->varName = '$TYPO3_LOADED_EXT';
				break;
			case 4:
				$theVar = $GLOBALS['T3_SERVICES'];
				GeneralUtility::naturalKeySortRecursive($theVar);
				$arrayBrowser->varName = '$T3_SERVICES';
				break;
			case 5:
				$theVar = $GLOBALS['TBE_MODULES'];
				GeneralUtility::naturalKeySortRecursive($theVar);
				$arrayBrowser->varName = '$TBE_MODULES';
				break;
			case 6:
				$theVar = $GLOBALS['TBE_MODULES_EXT'];
				GeneralUtility::naturalKeySortRecursive($theVar);
				$arrayBrowser->varName = '$TBE_MODULES_EXT';
				break;
			case 7:
				$theVar = $GLOBALS['TBE_STYLES'];
				GeneralUtility::naturalKeySortRecursive($theVar);
				$arrayBrowser->varName = '$TBE_STYLES';
				break;
			case 8:
				$theVar = $GLOBALS['BE_USER']->uc;
				GeneralUtility::naturalKeySortRecursive($theVar);
				$arrayBrowser->varName = '$BE_USER->uc';
				break;
			case 9:
				$theVar = $GLOBALS['TYPO3_USER_SETTINGS'];
				GeneralUtility::naturalKeySortRecursive($theVar);
				$arrayBrowser->varName = '$TYPO3_USER_SETTINGS';
				break;
			default:
				$theVar = array();
		}
		// Update node:
		$update = 0;
		$node = GeneralUtility::_GET('node');
		// If any plus-signs were clicked, it's registred.
		if (is_array($node)) {
			$this->MOD_SETTINGS['node_' . $this->MOD_SETTINGS['function']] = $arrayBrowser->depthKeys($node, $this->MOD_SETTINGS['node_' . $this->MOD_SETTINGS['function']]);
			$update = 1;
		}
		if ($update) {
			$GLOBALS['BE_USER']->pushModuleData($this->MCONF['name'], $this->MOD_SETTINGS);
		}
		$arrayBrowser->depthKeys = $this->MOD_SETTINGS['node_' . $this->MOD_SETTINGS['function']];
		$arrayBrowser->regexMode = $this->MOD_SETTINGS['regexsearch'];
		$arrayBrowser->fixedLgd = $this->MOD_SETTINGS['fixedLgd'];
		$arrayBrowser->searchKeysToo = TRUE;

		// If any POST-vars are send, update the condition array
		if (GeneralUtility::_POST('search') && trim($search_field)) {
			$arrayBrowser->depthKeys = $arrayBrowser->getSearchKeys($theVar, '', $search_field, array());
		}
		// mask the encryption key to not show it as plaintext in the configuration module
		if ($theVar == $GLOBALS['TYPO3_CONF_VARS']) {
			$theVar['SYS']['encryptionKey'] = '***** (length: ' . strlen($GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey']) . ' characters)';
		}
		$tree = $arrayBrowser->tree($theVar, '', '');
		$this->content .= $this->doc->sectionEnd();
		// Variable name:
		if (GeneralUtility::_GP('varname')) {
			$line = GeneralUtility::_GP('_') ? GeneralUtility::_GP('_') : GeneralUtility::_GP('varname');
			// Write the line to extTables.php
			if (GeneralUtility::_GP('writetoexttables')) {
				// change value to $GLOBALS
				$length = strpos($line, '[');
				$var = substr($line, 0, $length);
				$changedLine = '$GLOBALS[\'' . substr($line, 1, ($length - 1)) . '\']' . substr($line, $length);
				// load current extTables.php
				$extTables = GeneralUtility::getUrl(PATH_typo3conf . TYPO3_extTableDef_script);
				if ($var === '$TCA') {
					// check if we are editing the TCA
					preg_match_all('/\\[\'([^\']+)\'\\]/', $line, $parts);
				}
				// insert line in extTables.php
				$extTables = preg_replace('/<\\?php|\\?>/is', '', $extTables);
				$extTables = '<?php' . (empty($extTables) ? LF : '') . $extTables . $changedLine . LF . '?>';
				$success = GeneralUtility::writeFile(PATH_typo3conf . TYPO3_extTableDef_script, $extTables);
				if ($success) {
					// show flash message
					$flashMessage = GeneralUtility::makeInstance(
						'TYPO3\\CMS\\Core\\Messaging\\FlashMessage',
						'',
						sprintf(
							$GLOBALS['LANG']->getLL('writeMessage', TRUE),
							TYPO3_extTableDef_script,
							'<br />',
							'<strong>' . nl2br(htmlspecialchars($changedLine)) . '</strong>'
						),
						\TYPO3\CMS\Core\Messaging\FlashMessage::OK
					);
				} else {
					// Error: show flash message
					$flashMessage = GeneralUtility::makeInstance(
						'TYPO3\\CMS\\Core\\Messaging\\FlashMessage',
						'',
						sprintf($GLOBALS['LANG']->getLL('writeMessageFailed', TRUE), TYPO3_extTableDef_script),
						\TYPO3\CMS\Core\Messaging\FlashMessage::ERROR
					);
				}
				$this->content .= $flashMessage->render();
			}
			$this->content .= '<div id="lowlevel-config-var">
				<strong>' . $GLOBALS['LANG']->getLL('variable', TRUE) . '</strong><br />
				<input type="text" name="_" value="' . trim(htmlspecialchars($line)) . '" size="120" /><br/>';
			if (TYPO3_extTableDef_script !== '' && ($this->MOD_SETTINGS['function'] === '1' || $this->MOD_SETTINGS['function'] === '4')) {
				// write only for $TCA and TBE_STYLES if  TYPO3_extTableDef_script is defined
				$this->content .= '<br /><input type="submit" name="writetoexttables" value="' . $GLOBALS['LANG']->getLL('writeValue', TRUE) . '" /></div>';
			} else {
				$this->content .= $GLOBALS['LANG']->getLL('copyPaste', TRUE) . LF . '</div>';
			}
		}
		$this->content .= '<div class="nowrap">' . $tree . '</div>';

		// Setting up the buttons and markers for docheader
		$docHeaderButtons = $this->getButtons();
		$markers = array(
			'CSH' => $docHeaderButtons['csh'],
			'FUNC_MENU' => $this->getFuncMenu(),
			'CONTENT' => $this->content
		);
		// Build the <body> for the module
		$this->content = $this->doc->moduleBody($this->pageinfo, $docHeaderButtons, $markers);
		// Renders the module page
		$this->content = $this->doc->render('Configuration', $this->content);
	}

	/**
	 * Print output to browser
	 *
	 * @return void
	 * @todo Define visibility
	 */
	public function printContent() {
		echo $this->content;
	}

	/**
	 * Create the panel of buttons for submitting the form or otherwise perform operations.
	 *
	 * @return array All available buttons as an assoc. array
	 */
	protected function getButtons() {
		$buttons = array(
			'csh' => '',
			'shortcut' => ''
		);
		// Shortcut
		if ($GLOBALS['BE_USER']->mayMakeShortcut()) {
			$buttons['shortcut'] = $this->doc->makeShortcutIcon('', 'function', $this->MCONF['name']);
		}
		return $buttons;
	}

	/**
	 * Create the function menu
	 *
	 * @return string HTML of the function menu
	 */
	protected function getFuncMenu() {
		$funcMenu = BackendUtility::getFuncMenu(0, 'SET[function]', $this->MOD_SETTINGS['function'], $this->MOD_MENU['function']);
		return $funcMenu;
	}

}
