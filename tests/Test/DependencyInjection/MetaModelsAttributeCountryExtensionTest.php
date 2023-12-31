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
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2024 The MetaModels team.
 * @license    https://github.com/MetaModels/attribute_country/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

declare(strict_types=1);

namespace MetaModels\AttributeCountryBundle\Test\DependencyInjection;

use MetaModels\AttributeCountryBundle\Attribute\AttributeTypeFactory;
use MetaModels\AttributeCountryBundle\DependencyInjection\MetaModelsAttributeCountryExtension;
use MetaModels\AttributeCountryBundle\Migration\AllowNullMigration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;

/**
 * This test case test the extension.
 *
 * @covers \MetaModels\AttributeCountryBundle\DependencyInjection\MetaModelsAttributeCountryExtension
 */
class MetaModelsAttributeCountryExtensionTest extends TestCase
{
    public function testInstantiation(): void
    {
        $extension = new MetaModelsAttributeCountryExtension();

        self::assertInstanceOf(MetaModelsAttributeCountryExtension::class, $extension);
        self::assertInstanceOf(ExtensionInterface::class, $extension);
    }

    public function testRegistersServices(): void
    {
        $container = new ContainerBuilder();

        $extension = new MetaModelsAttributeCountryExtension();
        $extension->load([], $container);

        self::assertTrue($container->hasDefinition('metamodels.attribute_country.factory'));
        $definition = $container->getDefinition('metamodels.attribute_country.factory');
        self::assertCount(1, $definition->getTag('metamodels.attribute_factory'));

        self::assertTrue($container->hasDefinition(AllowNullMigration::class));
        $definition = $container->getDefinition(AllowNullMigration::class);
        self::assertCount(1, $definition->getTag('contao.migration'));
    }
}
