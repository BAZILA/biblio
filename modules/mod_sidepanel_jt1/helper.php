<?php
 /**
 * @package SidePanel Module for Joomla 2.5 By Joomlatema.net
 * @version $Id: mod_SidePanel_JT1.php  2012-07-07  Joomlatema.Net $
 * @author Muratyil
 * @copyright (C) 2010- Muratyil
 * @license GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
**/
// no direct access
defined('_JEXEC') or die;
error_reporting(E_ALL  &~E_WARNING &~E_DEPRECATED &~E_NOTICE);
require_once JPATH_SITE . '/components/com_content/helpers/route.php';

if (!class_exists('LofPanelGroupBase')) {
    require_once(dirname(__FILE__) . '/libs/group_base.php');
}

abstract class modSidePanelHelper {

    /**
     * get list articles
     */
    public static function getList($params) {
        if ($params->get('enable_cache')) {
            $cache = JFactory::getCache('mod_sidepanel_jt1');
            $cache->setCaching(true);
            $cache->setLifeTime($params->get('cache_time', 15) * 60);
            return $cache->get(array('modSidePanelHelper', 'getGroupObject'), array($params));
        } else {
            return self::getGroupObject($params);
        }
    }

    /**
     * get list articles
     */
    public static function getGroupObject($params) {
        $group = $params->get('group', 'file');
        $file = dirname(__FILE__) .'/libs/groups/'. strtolower($group) . '/' . strtolower($group) . '.php';

        if (file_exists($file)) {
            require_once($file);
            $className = 'SidePanelGroup' . ucfirst($group);
            if (class_exists($className)) {
                $object = new $className($group);
                $object->setCurrentPath(dirname(__FILE__) .'/libs/groups/'. strtolower($group) . '/');
            }
        }
        if ($object) {
            return $object->getListByParameters($params);
        } else {
            return array();
        }
    }

    /**
     * load css - javascript file.
     * 
     * @param JParameter $params;
     * @param JModule $module
     * @return void.
     */
    public static function loadMediaFiles($params, $module, $theme='') {
        $mainframe = JFactory::getApplication();
        // if the verion is equal 1.6.x
        JHTML::script('modules/' . $module->module . '/assets/script.js');

        if ($theme && $theme != -1) {
            $tPath = JPATH_BASE .'/templates/'. $mainframe->getTemplate() .'/html/'. $module->module . '/' . $theme .'/assets/style.css';

            if (file_exists($tPath)) {
                JHTML::stylesheet('templates/' . $mainframe->getTemplate() . '/html/' . $module->module . '/' . $theme . '/assets/style.css');
            } else {
                JHTML::stylesheet('modules/' . $module->module . '/tmpl/' . $theme . '/assets/style.css');
            }
        } else {
            JHTML::stylesheet('modules/' . $module->module . '/assets/style.css');
        }
    }

    /**
     *
     */
    public static function renderItem(&$row, $params, $layout='_item') {
        $mainframe = JFactory::getApplication();
        $theme = $params->get('theme');

        $tPath = JPATH_BASE .'/templates/'. $mainframe->getTemplate() .'/html/mod_sidepanel_jt1/'. $theme . '/' . $layout . '.php';
        $bPath = JPATH_BASE .'/modules/mod_sidepanel_jt1/tmpl/'. $theme . '/' . $layout . '.php';

        if (file_exists($tPath)) {
            require($tPath);
        } elseif (file_exists($bPath)) {
            require($bPath);
        }
    }

    /**
     * load theme
     */
    public static function getLayoutByTheme($module, $group, $theme= '') {
        $mainframe = JFactory::getApplication();
        // Build the template and base path for the layout
        $tPath = JPATH_BASE .'/templates/'. $mainframe->getTemplate() .'/html/'. $module->module . '/' . $theme .'/default.php';
        $bPath = JPATH_BASE .'/modules/'. $module->module .'/tmpl/'. $theme .'/default.php';

        // If the template has a layout override use it
        if (file_exists($tPath)) {
            return $tPath;
        } elseif (file_exists($bPath)) {
            return $bPath;
        }
    }

    public static function getModulesEnable($params) {
        $enableModules = array();
        for ($i = 0; $i <= 6; $i++) {
            $module = $params->get('file' . $i);
			if($module){
				if (intval($module->enable) == 1) {
					$enableModules[] = self::getModuleById($module->image);
				
				}
			}
        }
        return $enableModules;
    }

    private static function getModuleById($id) {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('module, title');
        $query->from('#__modules');
        $query->where('published=1 AND client_id=0 AND id=' . $db->quote($id));

        $db->setQuery($query);
        return $db->loadObject();
    }

    static function getReturnURL($params, $type) {
        $app = JFactory::getApplication();
        $router = $app->getRouter();
        $url = null;
        if ($itemid = $params->get($type)) {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);

            $query->select($db->quoteName('link'));
            $query->from($db->quoteName('#__menu'));
            $query->where($db->quoteName('published') . '=1');
            $query->where($db->quoteName('id') . '=' . $db->quote($itemid));

            $db->setQuery($query);
            if ($link = $db->loadResult()) {
                if ($router->getMode() == JROUTER_MODE_SEF) {
                    $url = 'index.php?Itemid=' . $itemid;
                } else {
                    $url = $link . '&Itemid=' . $itemid;
                }
            }
        }
        if (!$url) {
            // stay on the same page
            $uri = clone JFactory::getURI();
            $vars = $router->parse($uri);
            unset($vars['lang']);
            if ($router->getMode() == JROUTER_MODE_SEF) {
                if (isset($vars['Itemid'])) {
                    $itemid = $vars['Itemid'];
                    $menu = $app->getMenu();
                    $item = $menu->getItem($itemid);
                    unset($vars['Itemid']);
                    if (isset($item) && $vars == $item->query) {
                        $url = 'index.php?Itemid=' . $itemid;
                    } else {
                        $url = 'index.php?' . JURI::buildQuery($vars) . '&Itemid=' . $itemid;
                    }
                } else {
                    $url = 'index.php?' . JURI::buildQuery($vars);
                }
            } else {
                $url = 'index.php?' . JURI::buildQuery($vars);
            }
        }

        return base64_encode($url);
    }

}