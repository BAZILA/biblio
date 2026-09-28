<?php
/*
copyright 2009 Fiona Coulter http://www.iswebdesign.co.uk

This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.

*/
// no direct access
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Helper\RouteHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\Utilities\ArrayHelper;

use Joomla\CMS\Access\Access;
use Joomla\CMS\Component\Componenthelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\Component\Content\Site\Model\ArticlesModel;
use Joomla\Registry\Registry;

use Joomla\CMS\Date\Date;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Image\Image;
$language = Factory::getApplication()->getLanguage()->getTag();


error_reporting(E_ALL ^ E_NOTICE ^ E_STRICT ^ E_DEPRECATED ^ E_WARNING);


//require_once JPATH_SITE.'/components/com_content/helpers/route.php';


class modArticlesCountHelper
{

	public static function getList(&$params)
	{
	

		$db			= Factory::getDBO();
		$nullDate	= $db->getNullDate();
		$dateFormat = $db->getDateFormat();

		$date = Factory::getDate();
		$now = $date->format($date);
		$menuitemid = trim($params->get('menuitemid',''));
		$showEmpty = true;
		if($params->get('show_empty','yes') == 'no' && $params->get('show_tree','yes') == 'no')
		{
			$showEmpty = false;
		}
		
		

		
		$where = '(cc.published = 1)';
		$wherePublished = ""; //we use these to allow for tables that use state/published
		$whereState = "";
		$whereArticles = "";
		$whereAccess = "";
		
		
		
		if ($params->get('show_unpublished','no')=='no')
		{
			if($showEmpty)
			{
				$whereState	.= ' AND (a.state = 1 OR a.state IS NULL)';
				$wherePublished .= ' AND (a.published = 1 OR a.published IS NULL)';
				$whereArticles .= ' AND (a.publish_up = '.$db->Quote($nullDate).' OR a.publish_up <= '.$db->Quote($now).' OR a.publish_up IS NULL)'
				. ' AND (a.publish_down = '.$db->Quote($nullDate).' OR a.publish_down >= '.$db->Quote($now).' OR a.publish_down IS NULL )';
			}
			else
			{
				$whereState	.= ' AND (a.state = 1)';
				$wherePublished	.= ' AND (a.published = 1)';
				$whereArticles .= ' AND (a.publish_up = '.$db->Quote($nullDate).' OR a.publish_up <= '.$db->Quote($now).')'
				. ' AND (a.publish_down = '.$db->Quote($nullDate).' OR a.publish_down >= '.$db->Quote($now).')';				
			}
		}
		
		$user	= Factory::getUser();
		$groups	= implode(',', $user->getAuthorisedViewLevels());
		
		if($params->get('show_no_access_categories','no')=='no')
		{
	        $where .= ' AND (cc.access IN ('.$groups.')) ';
		}
		
		if($params->get('show_no_access_articles','no')=='no')
		{
			if($showEmpty)
			{
			  $whereAccess .= ' AND (a.access IN ('.$groups.') OR a.access IS NULL)';
			}
			else
			{
			  $whereAccess .= ' AND (a.access IN ('.$groups.')) ';
				
			}
		}
		
		  
	   $lists = new StdClass();
	   $lists->artcount = array();
	   $lists->names = array();
	   
	   $catid = '';
	   
	   
	   
	   $catCondition = '';
	   $extensions = (array)$params->get('extension', array('com_content'));
       $input = Factory::getApplication()->input;
	   
	   $option = $input->get('option','');
	   $view = $input->get('view');
	   if ($view == 'category') {
		  $id = intval($input->get('id',0,'int'));
	   }
	   else {
		  $id = intval($input->get('catid',0,'int'));				
	    }
	   
	   
	   if( $params->get('use_request','no') == 'yes' && in_array($option, $extensions) && $id > 0)
	   {
		    $ids = array($id);
			
			if($params->get('show_parent','no') == 'yes')
			{
				$ids = self::getParent($ids);
			}
			
			
			if($params->get('show_child','no') == 'yes')
			{
				  $catCondition = ' AND (cc.id IN('.implode(',',$ids).') OR cc.parent_id IN('.implode(',',$ids).'))';					
			}
			else
			{
				  $catCondition = ' AND (cc.id IN('.implode(',',$ids).'))';
			}
			  
			
	   
	   }	
		else 
		{
			$ids = array();
			
			$paramNames = array('catid', 'contact_catid', 'newsfeeds_catid', 'weblinks_catid');
			
			foreach($paramNames as $paramName)
			{
				$catParam = (array)$params->get($paramName,array(0));
				ArrayHelper::toInteger( $catParam );
				
				if(! in_array(0,$catParam))
				{
					$ids = array_merge($ids, $catParam);
				}
			}
			
			
			  
			if (count($ids))
			{
				if($params->get('show_child','no') == 'yes')
				{
				  $catCondition = ' AND (cc.id IN('.implode(',',$ids).') OR cc.parent_id IN('.implode(',',$ids).'))';					
				}
				else
				{
				  $catCondition = ' AND (cc.id IN('.implode(',',$ids).'))';
				}
			}
		}
	   
		$groupby = 'cc.id';
		$extensions = (array)$params->get('extension', array('com_content'));
		$rows = array();
		
		foreach($extensions as $extension)
		{
			$query = $db->getQuery(true);
			$query->select('COUNT(a.id) AS artcount, cc.id AS catid, cc.title AS cattitle, cc.parent_id, cc.level, cc.extension');
			$query->from('#__categories AS cc');
			
			$whereExtension = ' AND (cc.extension = '.$db->quote($extension).')';
			
			switch($extension)
			{
				case 'com_content': 
					$query->leftJoin('#__content AS a ON cc.id = a.catid');
					$query->where($where.$whereState.$whereArticles.$whereAccess.$catCondition.$whereExtension);
					
					break;
				case 'com_contact':
					$query->leftJoin('#__contact_details AS a ON cc.id = a.catid');
					$query->where($where.$wherePublished.$whereArticles.$whereAccess.$catCondition.$whereExtension);
					
					break;
				case 'com_newsfeeds':
					$query->leftJoin('#__newsfeeds AS a ON cc.id = a.catid');
					$query->where($where.$wherePublished.$whereArticles.$whereAccess.$catCondition.$whereExtension);
					
					break;
				case 'com_weblinks':
					$query->leftJoin('#__weblinks AS a ON cc.id = a.catid');
					$query->where($where.$whereState.$whereArticles.$whereAccess.$catCondition.$whereExtension);
					
					break;								
			}
			$query->group($groupby);
			$query->order('cc.lft');
			
			try{
				$db->setQuery($query);
				if ($temprows = $db->loadObjectList() )
				{
					$rows = array_merge($rows, $temprows);
				}
				
				
			} 
			catch(Exception $e)
			{
				echo $db->getErrorMsg();
			}
			
			
		}


		  $prevParent = 0;
		  $prevLevel = 0;
		  $prevId = 0;
		  $currTag = "";
		  $lists->items = array();
		  $depth = 0;
		  
		  foreach ($rows as $row)
		  {
			   $item = new stdClass();
			   $item->name = $row->cattitle;	
			   $item->subTotal = 0;
			   $item->artcount = $row->artcount;
			   
			   $item->parent_id = $row->parent_id;
			   $item->children = array();
			   $item->id = $row->catid;
			   
			   if($params->get('link_cat','yes')=='yes' || $params->get('show_tree','yes') == 'yes')
			   {
				  if($row->catid > 0)
				  {
					  if($row->extension == "com_content")
					  {
						  $link = RouteHelper::getCategoryRoute(
    (int) $row->catid,
    $language,
    'com_content'
);
					  }
					  else
					  {
						  $link = 'index.php?option='.$row->extension.'&view=category&id='.$row->catid;
					  }
					  if(($menuitemid != '') && (strpos($link,'Itemid=') === FALSE))
					  {
						$link .= '&Itemid='.$menuitemid;
					  }
					  
					  $link = Route::_($link);
					  
					  $item->startLink = '<a href="'.$link.'" id="artcountlink_'.$row->catid.'">';
					  $item->endLink = '</a>';
				  }
				  else
				  {
					  $item->startLink = "";
					  $item->endLink = "";
					  $link = "";
				  }
				  
				   
				   //$item->name = '<a href="'. $link .'">'.$row->cattitle.'</a>';
			   }
			   else
			   {
				   $item->startLink = "";
				   $item->endLink = "";				   
			   }
			   
			   $item->startTag = $currTag . '<li>';
			   //$currTag = '</li>';
			   if($prevParent > 0 && $prevParent != $row->parent_id)
			   {
				 if($row->parent_id == $prevId)
				 {
					 $item->startTag = '<ul><li>';
					 $depth++;
				 }
				 else //its not a child so close up
				 {
					 //new level must be lower
					 $levels = $prevLevel - $row->level;
					 $item->startTag = str_repeat('</li></ul>',$levels).'</li><li>';
					 $depth = $depth - $levels;
				 }
			   }
			   $currTag = '</li>';   
			   
			   
			   $lists->items[$row->catid] = $item;
			   
			   $prevParent = $row->parent_id;
			   $prevLevel = $row->level;
			   $prevId = $row->catid;
		  
		  }
		  if($depth>0)
		  {
			$lists->closeTag = str_repeat('</li></ul>',$depth).'</li>';
		  }
		  else
		  {
			 $lists->closeTag = '</li>'; 
		  }
		  $lists->id = uniqid('artcount');
		  $lists->layout = 'default';
		  if( $params->get('show_tree','yes') == 'yes')
		  {
			JHTML::_('behavior.tree',$lists->id, array(),array('text'=>Text::_('ARTCOUNTROOTNAME'),'data'=>array('url'=>'')));
			$lists->layout = 'tree';
		  }
		  
		  if($params->get('show_cumul','no') == 'yes')
		  {
			  //sort out parent child relationships
			  foreach($lists->items as $item)
			  {
				  if($item->id > 0 && isset($lists->items[$item->parent_id]))
				  {
					 $lists->items[$item->parent_id]->children[] = $item->id;
				  }
			  }
			  
			  foreach($lists->items as $item)
			  {
				  if(count($item->children) == 0)
				  {
					  $item->subTotal = $item->artcount;
					  self::addTotal($lists,$item, $item->artcount);
				  }
			  }
			  
			  foreach($lists->items as $item)
			  {
				 $item->artcountText = sprintf(Text::_('ARTCOUNTTEXT'),$item->subTotal);
				 if($params->get('show_zero_blank','yes') == 'yes' && $item->subTotal == 0)
				 {
					$item->artcountText = ""; 
				 }
			  }
		  }
		  else
		  {
			  foreach($lists->items as $item)
			  {
				 $item->artcountText = sprintf(Text::_('ARTCOUNTTEXT'),$item->artcount);
				 if($params->get('show_zero_blank','yes') == 'yes' && $item->artcount == 0)
				 {
					$item->artcountText = ""; 
				 }
			  }
		  }
		  
		  return $lists;		
			
		
    }
	
	private static function addTotal(&$lists, &$item, $amount)
	{
		 if($item->parent_id > 0 && isset($lists->items[$item->parent_id]))
		 {
			 $lists->items[$item->parent_id]->subTotal += $amount;
			 self::addTotal($lists, $lists->items[$item->parent_id], $amount);
		 }
	}
	
	private static function getParent(&$ids = array())
	{
		if(empty($ids))
		{
			return array();
		}
		
		$db = Factory::getDBO();
		$query = $db->getQuery(true);
		ArrayHelper::toInteger($ids);
		$query->select($db->quoteName('parent_id'))->from($db->quoteName('#__categories'))->where('id IN('.implode(',',$ids).')');
		$db->setQuery($query);
		if($results = $db->loadColumn())
		{
			foreach($results as $result)
			{
				if((int)$result > 0)
				{
					$ids[] = $result;
				}
			}
		}
		
		return $ids;
	}


}
