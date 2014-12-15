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

use MetaModels\IMetaModel;

/**
 * Unit tests to test class Country.
 */
abstract class TestCase extends \PHPUnit_Framework_TestCase {

    /**
     * Mock a MetaModel.
     *
     * @return IMetaModel
     */
    protected function mockMetaModel($language, $fallbackLanguage)
    {
        $metaModel = $this->getMock('MetaModels\MetaModel', array(), array(
            array() 
        ));
        
        $metaModel->expects($this->any())->method('getTableName')->will($this->returnValue('mm_unittest'));
        
        $metaModel->expects($this->any())->method('getActiveLanguage')->will($this->returnValue($language));
        
        $metaModel->expects($this->any())->method('getFallbackLanguage')->will($this->returnValue($fallbackLanguage));
        
        return $metaModel;
    }
}
