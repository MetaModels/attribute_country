<?php

/**
 * This file is part of MetaModels/attribute_country.
 *
 * (c) 2012-2016 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels
 * @subpackage AttributeCountry
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Cliff Parnitzky <github@cliff-parnitzky.de>
 * @copyright  2012-2016 The MetaModels team.
 * @license    https://github.com/MetaModels/attribute_country/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace MetaModels\Attribute\Country;

use MetaModels\Attribute\AbstractAttributeTypeFactory;

/**
 * Attribute type factory for country attributes.
 */
class AttributeTypeFactory extends AbstractAttributeTypeFactory
{
    /**
     * {@inheritDoc}
     */
    public function __construct()
    {
        parent::__construct();

        $this->typeName  = 'country';
        $this->typeIcon  = 'system/modules/metamodelsattribute_country/html/country.png';
        $this->typeClass = 'MetaModels\Attribute\Country\Country';
    }
}
