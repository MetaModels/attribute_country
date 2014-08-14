<?php

namespace MetaModels\Test\Attribute\Country;

use MetaModels\IMetaModel;

abstract class TestCase extends \PHPUnit_Framework_TestCase
{
	/**
	 * Mock a MetaModel.
	 *
	 * @return IMetaModel
	 */
	protected function mockMetaModel($language, $fallbackLanguage)
	{
		$metaModel = $this->getMock(
			'MetaModels\MetaModel',
			array(),
			array(array())
		);

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
}
