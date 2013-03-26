<?php

/**
 * The MetaModels extension allows the creation of multiple collections of custom items,
 * each with its own unique set of selectable attributes, with attribute extendability.
 * The Front-End modules allow you to build powerful listing and filtering of the
 * data in each collection.
 *
 * @author     Oliver Hoff <oliver@hofff.com>
 * @copyright  The MetaModels team
 * @license    LGPL
 */
if(!defined('TL_ROOT')) {
	die('You cannot access this file directly!');
}

$GLOBALS['TL_LANG']['tl_metamodel_attribute']['typeOptions']['country']	= 'Country';
$GLOBALS['TL_LANG']['tl_metamodel_attribute']['countries'] = array('Filter available countries', 'Filter the countries that will be available for selection. If none is selected, all countries will be available.');
