<?php

/**
 * This file is part of MetaModels/attribute_country.
 *
 * (c) 2012-2024 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/attribute_country
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @copyright  2012-2024 The MetaModels team.
 * @license    https://github.com/MetaModels/attribute_country/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

use MetaModels\AttributeCountryBundle\Attribute\AttributeTypeFactory;
use MetaModels\AttributeCountryBundle\Attribute\Country;

// This hack is to load the "old locations" of the classes.
\spl_autoload_register(
    static function ($class) {
        static $classes = [
            'MetaModels\Attribute\Country\Country' => Country::class,
            'MetaModels\Attribute\Country\AttributeTypeFactory' => AttributeTypeFactory::class
        ];

        if (isset($classes[$class])) {
            // @codingStandardsIgnoreStart Silencing errors is discouraged
            @trigger_error('Class "' . $class . '" has been renamed to "' . $classes[$class] . '"', E_USER_DEPRECATED);
            // @codingStandardsIgnoreEnd

            if (!class_exists($classes[$class])) {
                \spl_autoload_call($class);
            }

            \class_alias($classes[$class], $class);
        }
    }
);
