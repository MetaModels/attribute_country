<?php

/**
 * This file is part of MetaModels/attribute_country.
 *
 * (c) 2012-2022 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/attribute_country
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Oliver Hoff <oliver@hofff.com>
 * @author     Cliff Parnitzky <github@cliff-parnitzky.de>
 * @author     Tim Becker <tb@westwerk.ac>
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2022 The MetaModels team.
 * @license    https://github.com/MetaModels/attribute_country/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\AttributeCountryBundle\Attribute;

use Contao\System;
use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\System\LoadLanguageFileEvent;
use Doctrine\DBAL\Connection;
use MetaModels\Attribute\BaseSimple;
use MetaModels\Helper\TableManipulator;
use MetaModels\IMetaModel;
use MetaModels\Render\Template;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\String\UnicodeString;

/**
 * This is the MetaModelAttribute class for handling country fields.
 */
class Country extends BaseSimple
{
    /**
     * Local lookup cache for country names in a given language.
     *
     * @var array
     */
    protected $countryCache = [];

    /**
     * Event dispatcher.
     *
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * Instantiate an MetaModel attribute.
     *
     * Note that you should not use this directly but use the factory classes to instantiate attributes.
     *
     * @param IMetaModel                    $objMetaModel     The MetaModel instance this attribute belongs to.
     *
     * @param array                         $arrData          The information array, for attribute information, refer to
     *                                                        documentation of table tl_metamodel_attribute and
     *                                                        documentation of the certain attribute classes for
     *                                                        information what values are understood.
     *
     * @param Connection                    $connection       The database connection.
     *
     * @param TableManipulator              $tableManipulator Table manipulator instance.
     *
     * @param EventDispatcherInterface|null $eventDispatcher  Event dispatcher.
     */
    public function __construct(
        IMetaModel $objMetaModel,
        array $arrData = [],
        Connection $connection = null,
        TableManipulator $tableManipulator = null,
        EventDispatcherInterface $eventDispatcher = null
    ) {
        parent::__construct($objMetaModel, $arrData, $connection, $tableManipulator);

        if (null === $eventDispatcher) {
            // @codingStandardsIgnoreStart Silencing errors is discouraged
            @trigger_error(
                'Event dispatcher is missing. It has to be passed in the constructor. Fallback will be dropped.',
                E_USER_DEPRECATED
            );
            // @codingStandardsIgnoreEnd
            $eventDispatcher = System::getContainer()->get('event_dispatcher');
        }

        $this->eventDispatcher = $eventDispatcher;
    }

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
        return 'varchar(2) NULL';
    }

    /**
     * {@inheritDoc}
     */
    public function getAttributeSettingNames()
    {
        return \array_merge(
            parent::getAttributeSettingNames(),
            [
                'countries',
                'filterable',
                'searchable',
                'mandatory',
                'includeBlankOption'
            ]
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
        include(TL_ROOT . '/vendor/contao/core-bundle/src/Resources/contao/config/countries.php');
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
        // FIXME: do we need a language with '_' or '-' here?????
        $event = new LoadLanguageFileEvent('countries', $language, true);
        $this->eventDispatcher->dispatch($event, ContaoEvents::SYSTEM_LOAD_LANGUAGE_FILE);

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
    protected function restoreLanguage(string $lastLoadedLanguage)
    {
        // Switch back to the original FE language to not disturb the frontend.
        if ($lastLoadedLanguage != \str_replace('-', '_', $GLOBALS['TL_LANGUAGE'])) {
            $event = new LoadLanguageFileEvent('countries', null, true);
            $this->eventDispatcher->dispatch($event, ContaoEvents::SYSTEM_LOAD_LANGUAGE_FILE);
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
        $keys           = \array_keys($countries);
        $aux            = [];
        $real           = [];

        // Fetch real language values.
        foreach ($keys as $key) {
            if (isset($languageValues[$key])) {
                $aux[$key]  = (new UnicodeString($languageValues[$key]))->ascii()->toString();
                $real[$key] = $languageValues[$key];
            }
        }

        // Add needed fallback values.
        $keys = \array_diff($keys, \array_keys($aux));
        $fallbackLanguage = null;
        if ($keys) {
            $fallbackLanguage = $this->getMetaModel()->getFallbackLanguage();
            $fallbackValues = $this->getCountryNames($fallbackLanguage);
            foreach ($keys as $key) {
                if (isset($fallbackValues[$key])) {
                    $aux[$key]  = Utf8::toAscii($fallbackValues[$key]);
                    $real[$key] = $fallbackValues[$key];
                }
            }
        }

        $keys = \array_diff($keys, \array_keys($aux));
        if ($keys) {
            foreach ($keys as $key) {
                $aux[$key]  = $countries[$key];
                $real[$key] = $countries[$key];
            }
        }

        \asort($aux);
        $return = [];
        foreach (\array_keys($aux) as $key) {
            $return[$key] = $real[$key];
        }

        if (null !== $fallbackLanguage) {
            $this->restoreLanguage($fallbackLanguage);
        }

        $this->countryCache[$loadedLanguage] = $return;

        return $return;
    }

    /**
     * {@inheritDoc}
     */
    public function getFieldDefinition($arrOverrides = [])
    {
        $arrFieldDef                   = parent::getFieldDefinition($arrOverrides);
        $arrFieldDef['inputType']      = 'select';
        $arrFieldDef['eval']['chosen'] = true;
        $arrFieldDef['options']        = $this->getCountries();

        $arrSelectable = \deserialize($this->get('countries'), true);
        if ($arrSelectable) {
            $arrFieldDef['options'] = \array_intersect_key($arrFieldDef['options'], \array_flip($arrSelectable));
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

        // Sort the result, see #11
        \asort($options, SORT_LOCALE_STRING);

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
        $statement = $this->connection->createQueryBuilder()
            ->select('t.' . $this->getColName() . ' AS country, t.id')
            ->from($metaModel->getTableName(), 't')
            ->where('t.id IN (:ids)')
            ->setParameter('ids', $idList, Connection::PARAM_INT_ARRAY)
            ->execute();

        $sorted = [];
        while ($lookup = $statement->fetch(\PDO::FETCH_OBJ)) {
            $country            = isset($countries[$lookup->country]) ? $countries[$lookup->country] : $lookup->country;
            $sorted[$country][] = $lookup->id;
        }

        if ($strDirection === 'DESC') {
            \krsort($sorted);
        } else {
            \ksort($sorted);
        }

        return \call_user_func_array('array_merge', $sorted);
    }

    /**
     * {@inheritDoc}
     *
     * This is needed for compatibility with MySQL strict mode.
     */
    public function serializeData($value)
    {
        return $value === '' ? null : $value;
    }
}
