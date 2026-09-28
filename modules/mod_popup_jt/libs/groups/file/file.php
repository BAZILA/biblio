<?php 
/**
 * @package SidePanel Module for Joomla 2.5 By Joomlatema.net
 * @version $Id: mod_SidePanel_JT1.php  2012-07-07  Joomlatema.Net $
 * @author Muratyil
 * @copyright (C) 2010- Muratyil
 * @license GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
**/
// no direct access

defined( '_JEXEC' ) or die( 'Restricted access' );

if( !class_exists('SidePanelGroupFile') ){  
	class SidePanelGroupFile extends SidePanelGroupBase{
		/**
		 * @var string $__name;
		 *
		 * @access private
		 */
		var $__name = 'file';
 
		/**
		 * override method: get list image from articles.
		 */
		function getListByParameters($params){
			$maxFiles = 6;
			 $output = array(); 
			
			 for( $i=1; $i<=$maxFiles; $i++ ){
				$obj = $params->get('file'.$i);
				if( is_object($obj) && $obj->enable  ){
					$module = $this->getModule($obj->modid);
					$obj->title = $module->title;
					$obj->description = $module->content;
					$output[]=$obj;
			
				}
			 }
			return $output; 
		}
		function loadModuleById( $modId = 0)
			{
				$clean = array();
			
				$Itemid = JRequest::getInt('Itemid');
				$app	= JFactory::getApplication();
				$user	= JFactory::getUser();
				$groups	= implode(',', $user->getAuthorisedViewLevels());
				$db		= JFactory::getDbo();

				$query = $db->getQuery(true);
				$query->select('id, title, module, position, content, showtitle, params, mm.menuid');
				$query->from('#__modules AS m');
				$query->join('LEFT','#__modules_menu AS mm ON mm.moduleid = m.id');
				$query->where('m.published = 1 AND m.id ='.$modId);
					// Set the query
				$query->order('position, ordering');
				$clientid = (int) $app->getClientId();
				// Filter by language
				if ($app->isSite() && $app->getLanguageFilter()) {
					$query->where('m.language in (' . $db->Quote(JFactory::getLanguage()->getTag()) . ',' . $db->Quote('*') . ')');
				}

				// Set the query
				
				$db->setQuery($query);
				$modules = $db->loadObjectList();
				$cache 		= JFactory::getCache ('com_modules', 'callback');
				$cacheid 	= md5(serialize(array($Itemid, $groups, $clientid, JFactory::getLanguage()->getTag())));

				$modules = $cache->get(array($db, 'loadObjectList'), null, $cacheid, false);
				if (null === $modules)
				{
					JError::raiseWarning('SOME_ERROR_CODE', JText::sprintf('JLIB_APPLICATION_ERROR_MODULE_LOAD', $db->getErrorMsg()));
					$return = false;
					return $return;
				}

				// Apply negative selections and eliminate duplicates
				$negId	= $Itemid ? -(int)$Itemid : false;
				$dupes	= array();
				$clean	= array();
				for ($i = 0, $n = count($modules); $i < $n; $i++)
				{
					$module = &$modules[$i];

					// The module is excluded if there is an explicit prohibition, or if
					// the Itemid is missing or zero and the module is in exclude mode.
					$negHit	= ($negId === (int) $module->menuid)
							|| (!$negId && (int)$module->menuid < 0);

					if (isset($dupes[$module->id]))
					{
						// If this item has been excluded, keep the duplicate flag set,
						// but remove any item from the cleaned array.
						if ($negHit) {
							unset($clean[$module->id]);
						}
						continue;
					}
					$dupes[$module->id] = true;

					// Only accept modules without explicit exclusions.
					if (!$negHit)
					{
						//determine if this is a custom module
						$file				= $module->module;
						$custom				= substr($file, 0, 4) == 'mod_' ?  0 : 1;
						$module->user		= $custom;
						// Custom module name is given by the title field, otherwise strip off "com_"
						$module->name		= $custom ? $module->title : substr($file, 4);
						$module->style		= null;
						$module->position	= strtolower($module->position);
						$clean[$module->id]	= $module;
					}
				}
				unset($dupes);
				// Return to simple indexing that matches the query order.
				$clean = array_values($clean);

				return $clean;
		}
		function getModule( $modid = 0){
			if(!empty($modid)){
				$html = "";
				$modules = $this->loadModuleById($modid);
				if(!empty($modules)){
					$module = $modules[0];
					$module->showtitle = 1;
					$module->content = null;
					 if(isset($module) && @$module->id){
							ob_start();
							$content_module = JModuleHelper::renderModule($module);
							if(!empty($content_module)){
								echo $content_module;
							}
							$content_module = ob_get_contents();
							ob_end_clean();
							$search_text  = 'src="images';
							$replace_text = 'src="'.JURI::base( true ).'/images';
							$module->content  =  JString::str_ireplace( $search_text, $replace_text, $content_module);
							return $module;
						}
					}
				}
			return false;
		}
	}
}
?>