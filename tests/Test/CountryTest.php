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
 * @subpackage Tests
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Cliff Parnitzky <github@cliff-parnitzky.de>
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @copyright  2012-2017 The MetaModels team.
 * @license    https://github.com/MetaModels/attribute_country/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace MetaModels\Test\Attribute\Country;

use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\System\LoadLanguageFileEvent;
use Doctrine\DBAL\Connection;
use MetaModels\Attribute\Country\Country;
use MetaModels\Helper\TableManipulator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * Unit tests to test class Country.
 */
class CountryTest extends TestCase
{
    /**
     * Test data.
     *
     * @var array
     */
    protected static $languageValues = array(
        'base' => array(
            'a' => 'A in base file',
            'b' => 'B in base file',
            'c' => 'C in base file'
        ),
        'a' => array(
            'a' => 'A in language a'
        ),
        'b' => array(
            'a' => 'A in language b',
            'b' => 'B in language b'
        )
    );
    
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
        $metaModel = $this->getMockForAbstractClass('MetaModels\IMetaModel');

        $metaModel
            ->expects($this->any())
            ->method('getTableName')
            ->will($this->returnValue('mm_unittest'));

        $metaModel
            ->expects($this->any())
            ->method('getActiveLanguage')
            ->will($this->returnValue($language));

        $metaModel
            ->expects($this->any())
            ->method('getFallbackLanguage')
            ->will($this->returnValue($fallbackLanguage));

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
     */
    public function testNormal()
    {
        $GLOBALS['TL_LANGUAGE'] = $GLOBALS['CURRENT_LANGUAGE'] = 'a';

        $connection  = $this->mockConnection();
        $manipulator = $this->mockTableManipulator($connection);
        $dispatcher  = new EventDispatcher();
        $dispatcher->addListener(
            ContaoEvents::SYSTEM_LOAD_LANGUAGE_FILE,
            function (LoadLanguageFileEvent $event) {
                $GLOBALS['CURRENT_LANGUAGE'] = $event->getLanguage() ? $event->getLanguage() : 'a';
            }
        );

        $mockModel = $this->mockMetaModel('a', 'b');
        $attribute = $this->getMockBuilder('MetaModels\Attribute\Country\Country')
            ->setConstructorArgs(
                array(
                    $mockModel,
                    [],
                    $connection,
                    $manipulator,
                    $dispatcher
                )
            )
            ->setMethods(
                array(
                'getMetaModel',
                'getRealCountries',
                'getCountryNames'
                )
            )
            ->getMock();
        
        $attribute->expects($this->any())->method('getMetaModel')->will($this->returnValue($mockModel));
        
        $attribute->expects($this->any())->method('getRealCountries')->will($this->returnCallback(function () {
            return static::$languageValues['base'];
        }));
        
        $attribute->expects($this->any())->method('getCountryNames')->will($this->returnCallback(function ($language) {
            return static::$languageValues[$language];
        }));
        
        /** @var $attribute Country */
        $this->assertEquals($attribute->getCountryLabel('a'), static::$languageValues['a']['a']);
        $this->assertEquals($attribute->getCountryLabel('b'), static::$languageValues['b']['b']);
        $this->assertEquals($attribute->getCountryLabel('c'), static::$languageValues['base']['c']);
        $this->assertEquals('a', $GLOBALS['CURRENT_LANGUAGE']);
        $this->assertEquals('a', $GLOBALS['TL_LANGUAGE']);
    }
}
