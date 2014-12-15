<?php
/**
 * The MetaModels extension allows the creation of multiple collections of custom items,
 * each with its own unique set of selectable attributes, with attribute extendability.
 * The Front-End modules allow you to build powerful listing and filtering of the
 * data in each collection.
 *
 * PHP version 5
 *
 * @package    MetaModels
 * @subpackage Tests
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Cliff Parnitzky <github@cliff-parnitzky.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace MetaModels\Test\Attribute\Country;

use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\System\LoadLanguageFileEvent;
use MetaModels\Attribute\Country\Country;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * Unit tests to test class Country.
 */
class TranslatedCountryTest extends TestCase {

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
     * Test a literal query.
     *
     * @return void
     */
    public function testNormal()
    {
        if (version_compare(PHP_VERSION, '5.4', '<')) {
            $this->markTestSkipped('Invalid test case for PHP 5.3');
            
            return;
        }
        
        $GLOBALS['container']['event-dispatcher'] = new EventDispatcher();
        $GLOBALS['TL_LANGUAGE'] = $GLOBALS['CURRENT_LANGUAGE'] = 'a';
        
        $GLOBALS['container']['event-dispatcher']->addListener(ContaoEvents::SYSTEM_LOAD_LANGUAGE_FILE, function (LoadLanguageFileEvent $event)
        {
            $GLOBALS['CURRENT_LANGUAGE'] = $event->getLanguage() ? $event->getLanguage() : 'a';
        });
        
        $mockModel = $this->mockMetaModel('a', 'b');
        $attribute = $this->getMockBuilder('MetaModels\Attribute\Country\Country')->setConstructorArgs(array(
            $mockModel 
        ))->setMethods(array(
            'getMetaModel',
            'getRealCountries',
            'getCountryNames' 
        ))->getMock();
        
        $attribute->expects($this->any())->method('getMetaModel')->will($this->returnValue($mockModel));
        
        $attribute->expects($this->any())->method('getRealCountries')->will($this->returnCallback(function ()
        {
            return static::$languageValues['base'];
        }));
        
        $attribute->expects($this->any())->method('getCountryNames')->will($this->returnCallback(function ($language)
        {
            return static::$languageValues[$language];
        }));
        
        /**
         * @var $attribute Country
         */
        $this->assertEquals($attribute->getCountryLabel('a'), static::$languageValues['a']['a']);
        $this->assertEquals($attribute->getCountryLabel('b'), static::$languageValues['b']['b']);
        $this->assertEquals($attribute->getCountryLabel('c'), static::$languageValues['base']['c']);
        $this->assertEquals('a', $GLOBALS['CURRENT_LANGUAGE']);
        $this->assertEquals('a', $GLOBALS['TL_LANGUAGE']);
    }
}
