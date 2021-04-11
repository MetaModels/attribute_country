<?php

/**
 * This file is part of MetaModels/attribute_country.
 *
 * (c) 2012-2021 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/attribute_country
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2021 The MetaModels team.
 * @license    https://github.com/MetaModels/attribute_country/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\AttributeCountryBundle\Test;

use MetaModels\AttributeCountryBundle\Attribute\Country;
use MetaModels\AttributeCountryBundle\Attribute\AttributeTypeFactory;
use PHPUnit\Framework\TestCase;

/**
 * This class tests if the deprecated autoloader works.
 *
 * @covers \MetaModels\AttributeCountryBundle\Attribute\Country
 * @covers \MetaModels\AttributeCountryBundle\Attribute\AttributeTypeFactory
 */
class DeprecatedAutoloaderTest extends TestCase
{
    /**
     * Countryes of old classes to the new one.
     *
     * @var array
     */
    private static $classes = [
        'MetaModels\Attribute\Country\Country' => Country::class,
        'MetaModels\Attribute\Country\AttributeTypeFactory' => AttributeTypeFactory::class
    ];

    /**
     * Provide the alias class map.
     *
     * @return array
     */
    public function provideAliasClassMap()
    {
        $values = [];

        foreach (static::$classes as $country => $class) {
            $values[] = [$country, $class];
        }

        return $values;
    }

    /**
     * Test if the deprecated classes are aliased to the new one.
     *
     * @param string $oldClass Old class name.
     * @param string $newClass New class name.
     *
     * @dataProvider provideAliasClassMap
     */
    public function testDeprecatedClassesAreAliased($oldClass, $newClass)
    {
        self::assertTrue(class_exists($oldClass), sprintf('Class country "%s" is not found.', $oldClass));

        $oldClassReflection = new \ReflectionClass($oldClass);
        $newClassReflection = new \ReflectionClass($newClass);

        self::assertSame($newClassReflection->getFileName(), $oldClassReflection->getFileName());
    }
}
