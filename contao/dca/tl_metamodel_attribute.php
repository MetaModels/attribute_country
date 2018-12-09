<?php

/**
 * This file is part of MetaModels/attribute_country.
 *
 * (c) 2012-2018 The MetaModels team.
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
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2018 The MetaModels team.
 * @license    https://github.com/MetaModels/attribute_country/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

$GLOBALS['TL_DCA']['tl_metamodel_attribute']['metapalettes']['country extends _simpleattribute_'] = [
    '+display' => [
        'countries after description'
    ]
];

$GLOBALS['TL_DCA']['tl_metamodel_attribute']['fields']['countries'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_attribute']['countries'],
    'exclude'   => true,
    'inputType' => 'select',
    'options'   => $this->getCountries(),
    'sql'       => 'text NULL',
    'eval'      => [
        'chosen'     => true,
        'alwaysSave' => true,
        'multiple'   => true,
        'style'      => 'width: 100%'
    ]
];
