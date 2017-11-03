<?php

/**
 * This file is part of MetaModels/attribute_country.
 *
 * (c) 2012-2017 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels
 * @subpackage AttributeCountry
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Cliff Parnitzky <github@cliff-parnitzky.de>
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @copyright  2012-2017 The MetaModels team.
 * @license    https://github.com/MetaModels/attribute_country/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace MetaModels\AttributeCountryBundle\Attribute;

use Doctrine\DBAL\Connection;
use MetaModels\Attribute\AbstractSimpleAttributeTypeFactory;
use MetaModels\Helper\TableManipulator;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Attribute type factory for country attributes.
 */
class AttributeTypeFactory extends AbstractSimpleAttributeTypeFactory
{
    /**
     * The event dispatcher.
     *
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * Construct.
     *
     * @param Connection               $connection       Database connection.
     * @param TableManipulator         $tableManipulator Table manipulator.
     * @param EventDispatcherInterface $eventDispatcher  Event dispatcher.
     */
    public function __construct(
        Connection $connection,
        TableManipulator $tableManipulator,
        EventDispatcherInterface $eventDispatcher
    ) {
        parent::__construct($connection, $tableManipulator);

        $this->typeName        = 'country';
        $this->typeIcon        = 'bundles/metamodelsattributecountry/country.png';
        $this->typeClass       = 'MetaModels\Attribute\Country\Country';
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * {@inheritDoc}
     */
    public function createInstance($information, $metaModel)
    {
        return new $this->typeClass(
            $metaModel,
            $information,
            $this->connection,
            $this->tableManipulator,
            $this->eventDispatcher
        );
    }
}
