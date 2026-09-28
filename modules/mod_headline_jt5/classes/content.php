<?php
/**
 * @package Headline JT5 Module for Joomla!
 * @version $Id: 1.0 
 * @author muratyil
 * @Copyright (C) 2013- muratyil
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
**/
// no direct access
defined('_JEXEC') or die('Restricted access');

if(!defined('DS')){
    define('DS',DIRECTORY_SEPARATOR);
}

use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Access\Access;
use Joomla\CMS\Categories\Categories;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Helper\TagsHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\Component\Content\Site\Helper\RouteHelper as ContentHelperRoute;
use Joomla\Utilities\ArrayHelper;
use Joomla\String\StringHelper;

require_once 'jtsource.php';

/**
 * Ensures an image src is always an absolute URL.
 * On localhost subfolders (e.g. localhost/amitza/), Joomla stores images as
 * relative paths like "images/foo.jpg". Without the base URL prefix the browser
 * resolves them against the domain root, missing the subfolder entirely.
 */
if (!function_exists('jt5_to_abs_url')) {
    function jt5_to_abs_url($src) {
        if (!$src) return '';
        // Already absolute
        if (strpos($src, 'http://') === 0 || strpos($src, 'https://') === 0) {
            return $src;
        }
        // Already root-relative (starts with /)
        if (strpos($src, '/') === 0) {
            return $src;
        }
        // Relative path — prepend Joomla base URL (e.g. http://localhost/amitza/)
        return rtrim(Uri::base(), '/') . '/' . $src;
    }
}

/**
 * class JtContentDataSource
 */
if(!class_exists('JtContentDataSource')){
class JtContentDataSource extends JT5Source {

    function getList() {
        $params = &$this->_params;

        $formatter = $params->get('style_displaying', 'title');
        $titleMaxChars = $params->get('title_max_chars', '100');
        $limit_title_by = $params->get('limit_title_by', 'char');
        $limit_title_by2 = $params->get('limit_title_by2', 'char');
        $titleMaxChars2 = $params->get('title_max_chars2', '100');
        $descriptionMaxChars = $params->get('description_max_chars', 100);
        $limitDescriptionBy = $params->get('limit_description_by', 'char');
        $descriptionMaxChars2 = $params->get('description_max_chars2', 100);
        $limitDescriptionBy2 = $params->get('limit_description_by2', 'char');
        $isThumb = $params->get('image_thumb', 1);
		$thumbWidth = (int) $params->get('thumbnail_width', 280);
		$thumbHeight = (int) $params->get('thumbnail_height', 150);
		$quality = (int) $params->get('image_quality', 80);
		$showimage = $params->get('show_image', 1);
        $replacer = $params->get('replacer');
        $isStrips = $params->get("auto_strip_tags", 1);
		$use_linka = $params->get('use_linka',0);
		$use_introimg = $params->get('use_introimg',1);
		$use_caption = $params->get('use_caption',0);

        $stringtags = '';
		if ($isStrips) {
			$allow_tags = $params->get("allow_tags", '');
			$stringtags = '';
			if(!is_array($allow_tags)){
				$allow_tags = explode(',',$allow_tags);
			}
			foreach ($allow_tags as $tag) {
				$stringtags .= '<' . $tag . '>';
			}
		}

        if (!$params->get('default_thumb', 1)) {
            $this->_defaultThumb = '';
        }

        $ordering = $params->get('ordering', 'created-asc');

        $db = Factory::getDbo();
        $app = Factory::getApplication();

        // Get an instance of the generic articles model using MVC Factory
        $mvcFactory = $app->bootComponent('com_content')->getMVCFactory();
        $model = $mvcFactory->createModel('Articles', 'Site', ['ignore_request' => true]);

        // Set application parameters in model
        $appParams = $app->getParams();
        $model->setState('params', $appParams);

        $model->setState('list.select', 'a.urls, a.images, a.fulltext, a.id, a.title, a.alias, a.introtext, a.state, a.catid, a.created, a.created_by, a.created_by_alias,' . ' a.modified, a.modified_by,a.publish_up, a.publish_down, a.attribs, a.metadata, a.metakey, a.metadesc, a.access,' . ' a.hits, a.featured,' . ' LENGTH(a.fulltext) AS readmore');
        
        // Set the filters based on the module params
        $model->setState('list.start', 0);
        
		$slider = $params->get('layout_type') == 'slider';
		if ($slider) {
			$limit_items = $params->get('limit_items');
		} else {
			$limit_items = $params->get('limit_items');
		}
        $model->setState('list.limit', (int) $limit_items);
        $model->setState('filter.published', 1);

        // Access filter
		$userId = Factory::getUser()->get('id');
        $access = !ComponentHelper::getParams('com_content')->get('show_noauth');
        $authorised = Access::getAuthorisedViewLevels($userId);
        $model->setState('filter.access', $access);

        $source = trim($params->get('source', 'category'));

        // User filter
        switch ($params->get('user_id')) {
            case 'by_me':
                $model->setState('filter.author_id', (int) $userId);
                break;
            case 'not_me':
                $model->setState('filter.author_id', $userId);
                $model->setState('filter.author_id.include', false);
                break;
            case 0:
                break;
            default:
                $model->setState('filter.author_id', (int) $params->get('user_id'));
                break;
        }

        // Filter by language
        $model->setState('filter.language', $app->getLanguageFilter());

        // Featured switch
        switch ($params->get('show_featured')) {
            case 3:
                $model->setState('filter.featured', 'only');
                break;
            case 2:
                $model->setState('filter.featured', 'hide');
                break;
            default:
                $model->setState('filter.featured', 'show');
                break;
        }

        // Set ordering
		$ordering = explode('-', $ordering);
		if (trim($ordering[0]) == 'rand') {
			$model->setState('list.ordering', ' RAND() ');
		} else if (trim($ordering[0]) == 'featured') {
			$model->setState('list.ordering', 'fp.ordering');
			$model->setState('list.direction', 'asc');
		} else {
			$model->setState('list.ordering', 'a.' . $ordering[0]);
			$model->setState('list.direction', $ordering[1]);
		}

        // Get items based on source
        $items = array();
        if ($source == 'category') {
            $category_ids = self::getCategoryIds();
            if (!empty($category_ids) && $params->get('limit_items_for_each')) {
				$db->setQuery('SELECT id from #__categories where id in ('.implode(',', $category_ids).') order by lft');
				$category_ids = $db->loadColumn();
                foreach ($category_ids as $category_id) {
                    $model->setState('filter.category_id', array($category_id));
                    $itemsPerCategory = $model->getItems();
                    $items = array_merge($items, $itemsPerCategory);
                }
            } else {
                // Category filter
                $model->setState('filter.category_id', $category_ids);
                $items = $model->getItems();
            }
        } else if($source == 'article_ids'){
            $ids = preg_split('/,/', $params->get('article_ids', ''));
            $tmp = array();
            foreach ($ids as $id) {
                $tmp[] = (int) trim($id);
            }
            $model->setState('filter.article_id', $tmp);
            $items = $model->getItems();
        } else {
			$tagsHelper = new TagsHelper();
			$tagIds = $this->_params->get('joomla_tags');
			if($tagIds){
				$query = $tagsHelper->getTagItemsQuery(implode(',', $tagIds));
				$db->setQuery($query);
				$rs = $db->loadObjectList();
				if(!$rs){
					$items = $model->getItems();
				} else {
					$articleIds = array();
					foreach($rs as $article){
						if($article->type_alias != 'com_content.article') continue;
						$articleIds[] = $article->content_item_id;
					}
					$model->setState('filter.article_id', $articleIds);
					$items = $model->getItems();
				}
			} else {
				$items = $model->getItems();
			}	
		}
		
        foreach ($items as &$item) {
			// setting for route link
            $item->slug = $item->id . ':' . $item->alias;
            $item->catslug = $item->catid . ':' . $item->category_alias;

			// item link
			$item->link = '';
            if ($access || in_array($item->access, $authorised)) {
				// We know that user has the privilege to view the article
				if($use_linka && $item->urls){
					$item->urls = json_decode($item->urls);
					$item->link = $item->urls->urla;
				}
				if(!$item->link) {
					$item->link = Route::_(ContentHelperRoute::getArticleRoute($item->slug, $item->catslug, Factory::getLanguage()->getTag()));
				}
			} else {
				$item->link = Route::_('index.php?option=com_users&view=login');
			}

			// format date
			$date_format = $params->get('date_format');
            $item->date = HTMLHelper::_('date', $item->created, $date_format);

			// thumbnail & caption
			$item->thumbnail = '';
			$item->mainImage = '';
			if(($use_introimg || $use_caption) && $item->images){
				$item->images = json_decode($item->images);
			}
            if ($showimage) {
				if($item->images && $use_introimg){
					$imgLink = $item->images->image_intro;
					if($imgLink){
						// Always store as absolute URL so templates work on
						// localhost subfolders (e.g. localhost/amitza/) as well
						// as live servers without any template changes needed.
						$item->mainImage = jt5_to_abs_url($imgLink);
						if ($isThumb){
							// renderThumb returns a path relative to site root;
							// normalise it to absolute URL too.
							$item->thumbnail = jt5_to_abs_url(
								self::renderThumb($imgLink, $thumbWidth, $thumbHeight, $isThumb, $quality)
							);
						} else {
							$item->thumbnail = jt5_to_abs_url($imgLink);
						}
					}
				}
				if(!$item->thumbnail){
					// generateImages also returns relative paths; normalise.
					$item = $this->generateImages($item, $isThumb, $quality);
					if ($item->thumbnail) {
						$item->thumbnail = jt5_to_abs_url($item->thumbnail);
					}
					if ($item->mainImage) {
						$item->mainImage = jt5_to_abs_url($item->mainImage);
					}
				}
			}
			
			// change introtext with caption
			if($use_caption && $item->images){
				$caption = $item->images->image_intro_caption;
				if(!$caption){
					$caption = $item->images->image_fulltext_caption;
				}
				if($caption){
					$item->introtext = $caption;
				}
			}
			
			// set category link
            $item->categoryLink = Route::_(ContentHelperRoute::getCategoryRoute($item->catid, Factory::getLanguage()->getTag()));

			// cut title
            if ($limit_title_by == 'word' && $titleMaxChars > 0) {
                $item->title_cut = self::substrword($item->title, $titleMaxChars, $replacer, $isStrips);
            } else if ($limit_title_by == 'char' && $titleMaxChars > 0) {
                $item->title_cut = self::substring($item->title, $titleMaxChars, $replacer, $isStrips);
            }
			
			// cut title2
            if ($limit_title_by2 == 'word' && $titleMaxChars2 > 0) {
                $item->title_cut2 = self::substrword($item->title, $titleMaxChars2, $replacer, $isStrips);
            } else if ($limit_title_by2 == 'char' && $titleMaxChars2 > 0) {
                $item->title_cut2 = self::substring($item->title, $titleMaxChars2, $replacer, $isStrips);
            }
            
			// escape html characters
			$item->title = htmlspecialchars($item->title);

			// import content prepare plugin
            if($params->get('content_plugin')){
                $item->introtext = HTMLHelper::_('content.prepare', $item->introtext);
            }

			// cut description
            if ($limitDescriptionBy == 'word' && $descriptionMaxChars > 0) {
                $item->description = self::substrword($item->introtext, $descriptionMaxChars, $replacer, $isStrips, $stringtags);
            } elseif ($limitDescriptionBy == 'char' && $descriptionMaxChars > 0) {
                $item->description = self::substring($item->introtext, $descriptionMaxChars, $replacer, $isStrips, $stringtags);
            }
            
			// cut description2
            if ($limitDescriptionBy2 == 'word' && $descriptionMaxChars2 > 0) {
                $item->description2 = self::substrword($item->introtext, $descriptionMaxChars2, $replacer, $isStrips, $stringtags);
            } elseif ($limitDescriptionBy2 == 'char' && $descriptionMaxChars2 > 0) {
                $item->description2 = self::substring($item->introtext, $descriptionMaxChars2, $replacer, $isStrips, $stringtags);
            }

            // set authorlink empty
            $item->authorLink = "#";
        }
        return $items;
    }
    
	function getCategoryIds(){
		$db = Factory::getDbo();
		$app = Factory::getApplication();
		$input = $app->input;
		
		$category = array();
		
		if($this->_params->get('auto_category') && $input->get('option') == 'com_content'){
			$catid = $input->getInt('catid');
			if(!$catid && $input->get('view') == 'category') {
				$catid = $input->getInt('id');
			}
			if($catid) {
				$category = array($catid);
			} else {
				if($input->get('view') == 'article'){
					$itemid = $input->getInt('id');
					$db->setQuery('SELECT catid FROM #__content WHERE id=' . (int)$itemid);
					$catid = $db->loadResult();
					if($catid) {
						$category = array($catid);
					}
				}
			}
		}
		
		if(empty($category)){
			$category = $this->_params->get('category', array());
		}
		
		// since 2.4.2 - include subcategories
		if($this->_params->get('sub_categories', 0) && count($category)){
			$parents = $category;
			foreach($parents as $c){
				$instance = Categories::getInstance('Content');
				$cat = $instance->get($c);
				if($cat) {
					$children = $cat->getChildren();
					if(count($children)){
						foreach($children as $ch){
							if(!in_array($ch->id, $category)){
								$category[] = $ch->id;
							}
						}
					}
				}
			}
		}
		
		$exCategories = str_replace(' ', '', $this->_params->get('exclude_categories', ''));
		if($exCategories){
			$exCategories = explode(',', $exCategories);
			if($exCategories && count($exCategories)){
				$category = array_diff($category, $exCategories);
			}
		}
		return $category;
	}
}
}