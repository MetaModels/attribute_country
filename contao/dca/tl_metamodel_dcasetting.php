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
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2019 The MetaModels team.
 * @license    https://github.com/MetaModels/attribute_country/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

$GLOBALS['TL_DCA']['tl_metamodel_dcasetting']['metasubselectpalettes']['attr_id']['country'] = [
    'presentation' => [
        'tl_class'
    ],
    'functions'    => [
        'mandatory',
        'includeBlankOption'
    ],
    'overview'     => [
        'filterable',
        'searchable',
    ]
];
