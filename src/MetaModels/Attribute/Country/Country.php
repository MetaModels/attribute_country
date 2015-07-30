<?php
/**
 * The MetaModels extension allows the creation of multiple collections of custom items,
 * each with its own unique set of selectable attributes, with attribute extendability.
 * The Front-End modules allow you to build powerful listing and filtering of the
 * data in each collection.
 *
 * PHP version 5
 *
 * @package     MetaModels
 * @subpackage  AttributeCountry
 * @author      Oliver Hoff <oliver@hofff.com>
 * @author      Cliff Parnitzky <github@cliff-parnitzky.de>
 * @author      Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author      Tim Becker <tb@westwerk.ac>
 * @copyright   The MetaModels team.
 * @license     LGPL.
 * @filesource
 */

namespace MetaModels\Attribute\Country;

use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\System\LoadLanguageFileEvent;
use MetaModels\Attribute\BaseSimple;
use MetaModels\Render\Template;

/**
 * This is the MetaModelAttribute class for handling country fields.
 *
 * @package    MetaModels
 * @subpackage AttributeCountry
 */
class Country extends BaseSimple
{
    /**
     * Local lookup cache for country names in a given language.
     *
     * @var array
     */
    protected $countryCache = array();

    /**
     * {@inheritDoc}
     */
    protected function prepareTemplate(Template $objTemplate, $arrRowData, $objSettings)
    {
        parent::prepareTemplate($objTemplate, $arrRowData, $objSettings);
        $objTemplate->value = $this->getCountryLabel($arrRowData[$this->getColName()]);
    }

    /**
     * {@inheritDoc}
     */
    public function getSQLDataType()
    {
        return 'varchar(2) NOT NULL default \'\'';
    }

    /**
     * {@inheritDoc}
     */
    public function getAttributeSettingNames()
    {
        return array_merge(
            parent::getAttributeSettingNames(),
            array(
                'countries',
                'filterable',
                'searchable',
                'mandatory',
                'includeBlankOption'
            )
        );
    }

    /**
     * Include the TL_ROOT/system/config/countries.php file and return the contained $countries variable.
     *
     * @return string[]
     *
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    protected function getRealCountries()
    {
        // @codingStandardsIgnoreStart - Include is required here, can not switch to require_once.
        include(TL_ROOT . '/system/config/countries.php');
        // @codingStandardsIgnoreEnd
        /** @var string[] $countries */
        return $countries;
    }

    /**
     * Retrieve all country names in the given language.
     *
     * @param string $language The language key.
     *
     * @return string[]
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    protected function getCountryNames($language)
    {
        $dispatcher = $this->getMetaModel()->getServiceContainer()->getEventDispatcher();

        $event = new LoadLanguageFileEvent('countries', $language, true);
        $dispatcher->dispatch(ContaoEvents::SYSTEM_LOAD_LANGUAGE_FILE, $event);

        return $GLOBALS['TL_LANG']['CNT'];
    }

    /**
     * Restore the normal language values.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    protected function restoreLanguage()
    {
        // Switch back to the original FE language to not disturb the frontend.
        if ($this->getMetaModel()->getActiveLanguage() != $GLOBALS['TL_LANGUAGE']) {
            $dispatcher = $this->getMetaModel()->getServiceContainer()->getEventDispatcher();
            $event      = new LoadLanguageFileEvent('countries', null, true);

            $dispatcher->dispatch(ContaoEvents::SYSTEM_LOAD_LANGUAGE_FILE, $event);
        }
    }

    /**
     * Retrieve all country names.
     *
     * This method takes the fallback language into account.
     *
     * @return string[]
     *
     * @SuppressWarnings("PHPMD.CyclomaticComplexity")
     */
    protected function getCountries()
    {
        $loadedLanguage = $this->getMetaModel()->getActiveLanguage();
        if (isset($this->countryCache[$loadedLanguage])) {
            return $this->countryCache[$loadedLanguage];
        }

        $languageValues = $this->getCountryNames($loadedLanguage);
        $countries      = $this->getRealCountries();
        $keys           = array_keys($countries);
        $aux            = array();
        $real           = array();

        // Fetch real language values.
        foreach ($keys as $key) {
            if (isset($languageValues[$key])) {
                $aux[$key]  = utf8_romanize($languageValues[$key]);
                $real[$key] = $languageValues[$key];
            }
        }

        // Add needed fallback values.
        $keys = array_diff($keys, array_keys($aux));
        if ($keys) {
            $loadedLanguage = $this->getMetaModel()->getFallbackLanguage();
            $fallbackValues = $this->getCountryNames($loadedLanguage);
            foreach ($keys as $key) {
                if (isset($fallbackValues[$key])) {
                    $aux[$key]  = utf8_romanize($fallbackValues[$key]);
                    $real[$key] = $fallbackValues[$key];
                }
            }
        }

        $keys = array_diff($keys, array_keys($aux));
        if ($keys) {
            foreach ($keys as $key) {
                $aux[$key]  = $countries[$key];
                $real[$key] = $countries[$key];
            }
        }

        asort($aux);
        $return = array();
        foreach (array_keys($aux) as $key) {
            $return[$key] = $real[$key];
        }

        $this->restoreLanguage();

        $this->countryCache[$loadedLanguage] = $return;

        return $return;
    }

    /**
     * {@inheritDoc}
     */
    public function getFieldDefinition($arrOverrides = array())
    {
        $arrFieldDef                   = parent::getFieldDefinition($arrOverrides);
        $arrFieldDef['inputType']      = 'select';
        $arrFieldDef['eval']['chosen'] = true;
        $arrFieldDef['options']        = $this->getCountries();

        $arrSelectable = deserialize($this->get('countries'), true);
        if ($arrSelectable) {
            $arrFieldDef['options'] = array_intersect_key($arrFieldDef['options'], array_flip($arrSelectable));
        }

        return $arrFieldDef;
    }

    /**
     * Retrieve the label for a given country.
     *
     * @param string $strCountry The country for which the label shall be retrieved.
     *
     * @return string
     */
    public function getCountryLabel($strCountry)
    {
        $countries = $this->getCountries();

        return isset($countries[$strCountry]) ? $countries[$strCountry] : null;
    }

    /**
     * {@inheritDoc}
     *
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function getFilterOptions($idList, $usedOnly, &$arrCount = null)
    {
        $options = parent::getFilterOptions($idList, $usedOnly, $arrCount);

        foreach ($options as $k => $v) {
            $options[$k] = $this->getCountryLabel($k);
        }

        return $options;
    }

    /**
     * {@inheritdoc}
     *
     * This base implementation does a plain SQL sort by native value as defined by MySQL.
     */
    public function sortIds($idList, $strDirection)
    {
        $countries = $this->getCountries();
        $metaModel = $this->getMetaModel();
        $lookup    = $metaModel
            ->getServiceContainer()
            ->getDatabase()
            ->prepare(
                sprintf(
                    'SELECT %1$s AS country,id FROM %2$s WHERE id IN (%3$s)',
                    // @codingStandardsIgnoreStart
                    $this->getColName(),           // 1
                    $metaModel->getTableName(),    // 2
                    $this->parameterMask($idList)  // 3
                    // @codingStandardsIgnoreEnd
                )
            )
            ->execute($idList);

        $sorted = array();
        while ($lookup->next()) {
            $country            = isset($countries[$lookup->country]) ? $countries[$lookup->country] : $lookup->country;
            $sorted[$country][] = $lookup->id;
        }

        if ($strDirection === 'DESC') {
            krsort($sorted);
        } else {
            ksort($sorted);
        }

        return call_user_func_array('array_merge', $sorted);
    }
}
