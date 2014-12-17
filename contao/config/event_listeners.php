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
 * @subpackage  AttributeColor
 * @author      Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright   The MetaModels team.
 * @license     LGPL.
 * @filesource
 */

use MetaModels\Attribute\Country\AttributeTypeFactory;
use MetaModels\Attribute\Events\CreateAttributeFactoryEvent;
use MetaModels\Filter\Setting\Events\CreateFilterSettingFactoryEvent;
use MetaModels\MetaModelsEvents;

return array
(
    MetaModelsEvents::ATTRIBUTE_FACTORY_CREATE => array(
        function (CreateAttributeFactoryEvent $event) {
            $factory = $event->getFactory();
            $factory->addTypeFactory(new AttributeTypeFactory());
        }
    ),
    MetaModelsEvents::FILTER_SETTING_FACTORY_CREATE => array(
        array(
            function (CreateFilterSettingFactoryEvent $event) {
                $factory = $event->getFactory();
                $factory->getTypeFactory('select')->addKnownAttributeType('country');
            },
            // Low priority to have filter factory instantiated before we want to populate it.
            - 200
        )
    )
);
