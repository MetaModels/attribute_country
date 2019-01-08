<?php

/**
 * This file is part of MetaModels/attribute_country.
 *
 * (c) 2012-2019 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/attribute_country
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2019 The MetaModels team.
 * @license    https://github.com/MetaModels/attribute_country/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

use MetaModels\Attribute\Country\AttributeTypeFactory;
use MetaModels\Attribute\Events\CreateAttributeFactoryEvent;
use MetaModels\Filter\Setting\Events\CreateFilterSettingFactoryEvent;
use MetaModels\MetaModelsEvents;

return array
(
    MetaModelsEvents::ATTRIBUTE_FACTORY_CREATE      => [
        function (CreateAttributeFactoryEvent $event) {
            $factory = $event->getFactory();
            $factory->addTypeFactory(new AttributeTypeFactory());
        }
    ],
    MetaModelsEvents::FILTER_SETTING_FACTORY_CREATE => [
        [
            function (CreateFilterSettingFactoryEvent $event) {
                $selectFilterFactory = $event->getFactory()->getTypeFactory('select');
                if ($selectFilterFactory) {
                    $selectFilterFactory->addKnownAttributeType('country');
                }
            },
            // Low priority to have select filter factory instantiated before we want to populate it.
            -200
        ]
    ]
);
