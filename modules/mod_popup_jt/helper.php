<?php
 /**
 * @package SideScript Module for Joomla 3.0 By Joomlatema.net
 * @version $Id: mod_SideScript_jt1.php  2013-25-05  Joomlatema.Net $
 * @author Muratyil
 * @copyright (C) 2013- Muratyil
 * @license GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 **/
// no direct access
defined('_JEXEC') or die;
error_reporting(E_ALL &~E_WARNING &~E_DEPRECATED &~E_NOTICE);
require_once JPATH_SITE . DS . 'components' . DS . 'com_content' . DS . 'helpers' . DS . 'route.php';

if (!class_exists('LofPanelGroupBase')) {
    require_once(dirname(__FILE__) . DS . 'libs' . DS . 'group_base.php');
}

abstract class modPopupJTHelper {

    /**
     * get list articles
     */
    public static function getList($params) {
        if ($params->get('enable_cache')) {
            $cache = & JFactory::getCache('mod_popup_jt');
            $cache->setCaching(true);
            $cache->setLifeTime($params->get('cache_time', 15) * 60);
            return $cache->get(array('modPopupJTHelper ', 'getGroupObject'), array($params));
        } else {
            return self::getGroupObject($params);
        }
    }

    /**
     * get list articles
     */
    public static function getGroupObject($params) {
        $group = $params->get('group', 'file');
        $file = dirname(__FILE__) . DS . 'libs' . DS . 'groups' . DS . strtolower($group) . DS . strtolower($group) . '.php';

        if (file_exists($file)) {
            require_once($file);
            $className = 'SideScriptGroup' . ucfirst($group);
            if (class_exists($className)) {
                $object = new $className($group);
                $object->setCurrentPath(dirname(__FILE__) . DS . 'libs' . DS . 'groups' . DS . strtolower($group) . DS);
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
            $tPath = JPATH_BASE . DS . 'templates' . DS . $mainframe->getTemplate() . DS . 'html' . DS . $module->module . DS . $theme . DS . 'assets' . DS . 'style.css';

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
    public function renderItem(&$row, $params, $layout='_item') {
        $mainframe = JFactory::getApplication();
        $theme = $params->get('theme');

        $tPath = JPATH_BASE . DS . 'templates' . DS . $mainframe->getTemplate() . DS . 'html' . DS . 'mod_popup_jt' . DS . $theme . DS . $layout . '.php';
        $bPath = JPATH_BASE . DS . 'modules' . DS . 'mod_popup_jt' . DS . 'tmpl' . DS . $theme . DS . $layout . '.php';

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
        $tPath = JPATH_BASE . DS . 'templates' . DS . $mainframe->getTemplate() . DS . 'html' . DS . $module->module . DS . $theme . DS . 'default.php';
        $bPath = JPATH_BASE . DS . 'modules' . DS . $module->module . DS . 'tmpl' . DS . $theme . DS . 'default.php';

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

    private function getModuleById($id) {
        $db = &JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('module, title');
        $query->from('#__modules');
        $query->where('published=1 AND client_id=0 AND id=' . $db->quote($id));

        $db->setQuery($query);
        return $db->loadObject();
    }

  

}