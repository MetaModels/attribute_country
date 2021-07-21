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
 * @author     Cliff Parnitzky <github@cliff-parnitzky.de>
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2019 The MetaModels team.
 * @license    https://github.com/MetaModels/attribute_country/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\AttributeCountryBundle\Test\Attribute;

use Doctrine\DBAL\Connection;
use MetaModels\AttributeCountryBundle\Attribute\Country;
use MetaModels\Helper\TableManipulator;
use MetaModels\IMetaModel;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use MetaModels\MetaModel;

/**
 * Unit tests to test class Country.
 *
 * @covers \MetaModels\AttributeCountryBundle\Attribute\Country
 */
class CountryTest extends TestCase
{
    /**
     * {@inheritDoc}
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        require_once __DIR__ . '/utf8_romanize.php';
    }

    /**
     * Test data.
     *
     * @var array
     */
    protected static $languageValues = [
        'base' => [
            'a' => 'A in base file',
            'b' => 'B in base file',
            'c' => 'C in base file'
        ],
        'a' => [
            'a' => 'A in language a'
        ],
        'b' => [
            'a' => 'A in language b',
            'b' => 'B in language b'
        ]
    ];

    /**
     * Mock a MetaModel.
     *
     * @param string $language         The language.
     * @param string $fallbackLanguage The fallback language.
     *
     * @return IMetaModel
     */
    protected function mockMetaModel($language, $fallbackLanguage)
    {
        $metaModel = $this->getMockBuilder(MetaModel::class)->setMethods([])->setConstructorArgs([[]])->getMock();

        $metaModel
            ->method('getTableName')
            ->willReturn('mm_unittest');

        $metaModel
            ->method('getActiveLanguage')
            ->willReturn($language);

        $metaModel
            ->method('getFallbackLanguage')
            ->willReturn($fallbackLanguage);

        return $metaModel;
    }

    /**
     * Mock the database connection.
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|Connection
     */
    private function mockConnection()
    {
        return $this->getMockBuilder(Connection::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * Mock the table manipulator.
     *
     * @param Connection $connection The database connection mock.
     *
     * @return TableManipulator|\PHPUnit_Framework_MockObject_MockObject
     */
    private function mockTableManipulator(Connection $connection)
    {
        return $this->getMockBuilder(TableManipulator::class)
            ->setConstructorArgs([$connection, []])
            ->getMock();
    }

    /**
     * Test a literal query.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function testNormal()
    {
        $GLOBALS['TL_LANGUAGE'] = $GLOBALS['CURRENT_LANGUAGE'] = 'a';

        $connection  = $this->mockConnection();
        $manipulator = $this->mockTableManipulator($connection);
        $dispatcher  = new EventDispatcher();

        $mockModel = $this->mockMetaModel('a', 'b');
        $attribute = $this->getMockBuilder(Country::class)
            ->setConstructorArgs(
                [
                    $mockModel,
                    [],
                    $connection,
                    $manipulator,
                    $dispatcher
                ]
            )
            ->setMethods(
                [
                'getMetaModel',
                'getRealCountries',
                'getCountryNames'
                ]
            )
            ->getMock();

        $attribute->method('getMetaModel')->willReturn($mockModel);

        $attribute->method('getRealCountries')->willReturnCallback(function () {
            return static::$languageValues['base'];
        });

        $attribute->method('getCountryNames')->willReturnCallback(function ($language) {
            return static::$languageValues[$language];
        });

        /** @var $attribute Country */
        $this->assertEquals($attribute->getCountryLabel('a'), static::$languageValues['a']['a']);
        $this->assertEquals($attribute->getCountryLabel('b'), static::$languageValues['b']['b']);
        $this->assertEquals($attribute->getCountryLabel('c'), static::$languageValues['base']['c']);
        $this->assertEquals('a', $GLOBALS['CURRENT_LANGUAGE']);
        $this->assertEquals('a', $GLOBALS['TL_LANGUAGE']);
    }
}
