<?php

/**
 * The MetaModels extension allows the creation of multiple collections of custom items,
 * each with its own unique set of selectable attributes, with attribute extendability.
 * The Front-End modules allow you to build powerful listing and filtering of the
 * data in each collection.
 *
 * @author     Oliver Hoff <oliver@hofff.com>
 * @copyright  The MetaModels team
 * @license    LGPL
 */

class MetaModelAttributeCountry extends MetaModelAttributeSimple {

	protected function prepareTemplate(MetaModelTemplate $objTemplate, $arrRowData, $objSettings = null)
	{
		parent::prepareTemplate($objTemplate, $arrRowData, $objSettings);
		$objTemplate->value = $this->getCountryLabel($arrRowData[$this->getColName()]);
	}

	public function getSQLDataType() {
		return 'varchar(2) NOT NULL default \'\'';
	}

	public function getAttributeSettingNames() {
		return array_merge(parent::getAttributeSettingNames(), array(
			'countries',
			'filterable',
			'searchable',
			'sortable',
			'flag',
			'mandatory',
			'includeBlankOption'
		));
	}

	public function getFieldDefinition($arrOverrides = array()) {
		$arrFieldDef = parent::getFieldDefinition($arrOverrides);
		$arrFieldDef['inputType'] = 'select';
		$arrFieldDef['options'] = MetaModelController::getInstance()->getCountries();
		$arrSelectable = (array) $this->get('countries');
		$arrSelectable && $arrFieldDef['options'] = array_intersect_key(
			$arrFieldDef['options'],
			array_flip($arrSelectable)
		);
		$arrFieldDef['eval']['chosen'] = true;
		return $arrFieldDef;
	}

	public function getCountryLabel($strCountry) {
		$strLanguage = $this->getMetaModel()->getActiveLanguage();
		MetaModelController::getInstance()->loadLanguageFile('languages', $strLanguage, true);

		if(strlen($GLOBALS['TL_LANG']['LNG'][$strCountry])) {
			$strLabel = $GLOBALS['TL_LANG']['LNG'][$strCountry];

		} else {
			$strLanguage = $this->getMetaModel()->getFallbackLanguage();
			MetaModelController::getInstance()->loadLanguageFile('languages', $strLanguage, true);

			if(strlen($GLOBALS['TL_LANG']['LNG'][$strCountry])) {
				$strResult = $GLOBALS['TL_LANG']['LNG'][$strCountry];

			} else {
				include(TL_ROOT . '/system/config/countries.php');
				$strResult = $countries[$strCountry];
			}
		}

		// switch back to the original FE language to not disturb the frontend.
		if($strLanguage != $GLOBALS['TL_LANGUAGE']) {
			MetaModelController::getInstance()->loadLanguageFile('languages', false, true);
		}

		return $strLabel;
	}

}
