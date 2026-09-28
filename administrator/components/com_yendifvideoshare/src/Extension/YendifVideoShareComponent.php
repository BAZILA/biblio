<?php
/**
 * @version    2.1.1
 * @package    Com_YendifVideoShare
 * @author     PluginsWare Interactive Pvt. Ltd <admin@yendifplayer.com>
 * @copyright  Copyright (c) 2012 - 2025 PluginsWare Interactive Pvt. Ltd. All Rights Reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace PluginsWare\Component\YendifVideoShare\Administrator\Extension;

\defined( '_JEXEC' ) or die;

use \Joomla\CMS\Application\SiteApplication;
use \Joomla\CMS\Association\AssociationServiceInterface;
use \Joomla\CMS\Association\AssociationServiceTrait;
use \Joomla\CMS\Categories\CategoryServiceTrait;
use \Joomla\CMS\Component\Router\RouterServiceInterface;
use \Joomla\CMS\Component\Router\RouterServiceTrait;
use \Joomla\CMS\Extension\BootableExtensionInterface;
use \Joomla\CMS\Extension\MVCComponent;
use \Joomla\CMS\HTML\HTMLRegistryAwareTrait;
use \Joomla\CMS\Tag\TagServiceTrait;
use \Psr\Container\ContainerInterface;

/**
 * Component class for YendifVideoShare
 *
 * @since  2.0.0
 */
class YendifVideoShareComponent extends MVCComponent implements RouterServiceInterface {
	
	use AssociationServiceTrait;
	use RouterServiceTrait;
	use HTMLRegistryAwareTrait;
	use CategoryServiceTrait, TagServiceTrait {
		CategoryServiceTrait::getTableNameForSection insteadof TagServiceTrait;
		CategoryServiceTrait::getStateColumnForSection insteadof TagServiceTrait;
	}

}