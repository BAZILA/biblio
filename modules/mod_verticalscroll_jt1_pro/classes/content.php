<?php
/**
 * @package    Vertical scroller JT1 Pro Module for Joomla! 4.x/5.x/6.x
 * @version    3.0
 * @author     muratyil
 * @copyright  (C) 2013- muratyil
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die;

use Joomla\CMS\Access\Access;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Helper\TagsHelper;
use Joomla\Component\Content\Site\Helper\RouteHelper as ContentRouteHelper;
use Joomla\Utilities\ArrayHelper;
use Joomla\String\StringHelper;

// Load base class
require_once __DIR__ . '/jtsource.php';

/**
 * Content Data Source Class
 */
if (!class_exists('BtContentDataSource')) {
    class BtContentDataSource extends JT3Source
    {
        /**
         * Get list of articles
         *
         * @return array
         */
        public function getList()
        {
            $params = &$this->_params;
            $app = Factory::getApplication();
            $db = Factory::getDbo();

            $formatter = $params->get('style_displaying', 'title');
            $titleMaxChars = $params->get('title_max_chars', '100');
            $limit_title_by = $params->get('limit_title_by', 'char');
            $descriptionMaxChars = $params->get('description_max_chars', 100);
            $limitDescriptionBy = $params->get('limit_description_by', 'char');
            $isThumb = $params->get('image_thumb', 1);
            $thumbWidth = (int) $params->get('thumbnail_width', 280);
            $thumbHeight = (int) $params->get('thumbnail_height', 150);
            $quality = (int) $params->get('image_quality', 80);
            $showimage = $params->get('show_image', 1);
            $replacer = $params->get('replacer');
            $isStrips = $params->get('auto_strip_tags', 1);
            $use_linka = $params->get('use_linka', 0);
            $use_introimg = $params->get('use_introimg', 1);
            $use_caption = $params->get('use_caption', 0);

            if (!$params->get('default_thumb', 1)) {
                $this->_defaultThumb = '';
            }

            $ordering = $params->get('ordering', 'created-asc');

            // Get an instance of the generic articles model
            try {
                $modelClass = 'Joomla\\Component\\Content\\Site\\Model\\ArticlesModel';
                if (class_exists($modelClass)) {
                    $model = new $modelClass(['ignore_request' => true]);
                } else {
                    return [];
                }
            } catch (Exception $e) {
                return [];
            }

            // Set application parameters in model
            $appParams = $app->getParams();
            $model->setState('params', $appParams);

            $model->setState('list.select', 'a.urls, a.images, a.fulltext, a.id, a.title, a.alias, a.introtext, a.state, a.catid, a.created, a.created_by, a.created_by_alias,' .
                ' a.modified, a.modified_by,a.publish_up, a.publish_down, a.attribs, a.metadata, a.metakey, a.metadesc, a.access,' .
                ' a.hits, a.featured,' .
                ' LENGTH(a.fulltext) AS readmore');

            // Set the filters based on the module params
            $model->setState('list.start', 0);
            $model->setState('list.limit', (int) $params->get('limit_items', 12));
            $model->setState('filter.published', 1);

            // Access filter
            $user = Factory::getUser();
            $userId = $user->get('id');
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
            } elseif (trim($ordering[0]) == 'featured') {
                $model->setState('list.ordering', 'fp.ordering');
                $model->setState('list.direction', 'asc');
            } else {
                $model->setState('list.ordering', 'a.' . $ordering[0]);
                $model->setState('list.direction', $ordering[1]);
            }

            // If category
            $items = [];
            if ($source == 'category') {
                $category_ids = $this->getCategoryIds();
                if (!empty($category_ids) && $params->get('limit_items_for_each')) {
                    $query = $db->getQuery(true)
                        ->select('id')
                        ->from($db->quoteName('#__categories'))
                        ->where($db->quoteName('id') . ' IN (' . implode(',', $category_ids) . ')')
                        ->order('lft');
                    $db->setQuery($query);
                    $category_ids = $db->loadColumn();
                    
                    foreach ($category_ids as $category_id) {
                        $model->setState('filter.category_id', [$category_id]);
                        $itemsPerCategory = $model->getItems();
                        $items = array_merge($items, $itemsPerCategory);
                    }
                } else {
                    // Category filter
                    $model->setState('filter.category_id', $category_ids);
                    $items = $model->getItems();
                }
            } elseif ($source == 'article_ids') {
                $ids = preg_split('/,/', $params->get('article_ids', ''));
                $tmp = [];
                foreach ($ids as $id) {
                    $tmp[] = (int) trim($id);
                }
                $model->setState('filter.article_id', $tmp);
                $items = $model->getItems();
            } else {
                // Joomla Tags
                $tagsHelper = new TagsHelper();
                $tagIds = $this->_params->get('joomla_tags');
                if ($tagIds) {
                    $query = $tagsHelper->getTagItemsQuery(implode(',', $tagIds));
                    $db->setQuery($query);
                    $rs = $db->loadObjectList();
                    
                    if (!$rs) {
                        $items = $model->getItems();
                    } else {
                        $articleIds = [];
                        foreach ($rs as $article) {
                            if ($article->type_alias != 'com_content.article') {
                                continue;
                            }
                            $articleIds[] = $article->content_item_id;
                        }
                        $model->setState('filter.article_id', $articleIds);
                        $items = $model->getItems();
                    }
                } else {
                    $items = $model->getItems();
                }
            }

            // Process items
            foreach ($items as &$item) {
                // Setting for route link
                $item->slug = $item->id . ':' . $item->alias;
                $item->catslug = $item->catid . ':' . $item->category_alias;

                // Item link
                $item->link = '';
                if ($access || in_array($item->access, $authorised)) {
                    // User has the privilege to view the article
                    if ($use_linka && $item->urls) {
                        $item->urls = json_decode($item->urls);
                        $item->link = $item->urls->urla ?? '';
                    }
                    if (!$item->link) {
                        $item->link = Route::_(ContentRouteHelper::getArticleRoute($item->slug, $item->catslug, $app->getLanguage()->getTag()));
                    }
                } else {
                    $item->link = Route::_('index.php?option=com_users&view=login');
                }

                // Format date
                $item->date = HTMLHelper::_('date', $item->created, $params->get('date_format'));

                // Thumbnail & caption
                $item->thumbnail = '';
                $item->mainImage = '';
                if (($use_introimg || $use_caption) && $item->images) {
                    $item->images = json_decode($item->images);
                }
                
                if ($showimage) {
                    if ($item->images && $use_introimg) {
                        $imgLink = $item->images->image_intro ?? '';
                        if (!$imgLink) {
                            $imgLink = $item->images->image_fulltext ?? '';
                        }
                        if ($imgLink) {
                            $item->mainImage = $imgLink;
                            if ($isThumb) {
                                $item->thumbnail = $this->renderThumb($imgLink, $thumbWidth, $thumbHeight, $isThumb, $quality);
                            } else {
                                $item->thumbnail = $imgLink;
                            }
                        }
                    }
                    if (!$item->thumbnail) {
                        $item = $this->generateImages($item, $isThumb, $quality);
                    }
                }
                
                // Change introtext with caption
                if ($use_caption && $item->images) {
                    $caption = $item->images->image_intro_caption ?? '';
                    if (!$caption) {
                        $caption = $item->images->image_fulltext_caption ?? '';
                    }
                    if ($caption) {
                        $item->introtext = $caption;
                    }
                }
                
                // Set category link
                $item->categoryLink = Route::_(ContentRouteHelper::getCategoryRoute($item->catid, $app->getLanguage()->getTag()));

                // Cut title
                if ($limit_title_by == 'word' && $titleMaxChars > 0) {
                    $item->title_cut = self::substrword($item->title, $titleMaxChars, $replacer, $isStrips);
                } elseif ($limit_title_by == 'char' && $titleMaxChars > 0) {
                    $item->title_cut = self::substring($item->title, $titleMaxChars, $replacer, $isStrips);
                }
                
                // Escape html characters
                $item->title = htmlspecialchars($item->title);

                // Import content prepare plugin
                if ($params->get('content_plugin')) {
                    $item->introtext = HTMLHelper::_('content.prepare', $item->introtext);
                }

                // Build allowed tags string
                $stringtags = '';
                if (!$isStrips) {
                    $allow_tags = $params->get('allow_tags', '');
                    if (!is_array($allow_tags)) {
                        $allow_tags = explode(',', $allow_tags);
                    }
                    foreach ($allow_tags as $tag) {
                        $stringtags .= '<' . $tag . '>';
                    }
                }

                // Cut description
                if ($limitDescriptionBy == 'word' && $descriptionMaxChars > 0) {
                    $item->description = self::substrword($item->introtext, $descriptionMaxChars, $replacer, $isStrips, $stringtags);
                } elseif ($limitDescriptionBy == 'char' && $descriptionMaxChars > 0) {
                    $item->description = self::substring($item->introtext, $descriptionMaxChars, $replacer, $isStrips, $stringtags);
                }

                // Set authorlink empty
                $item->authorLink = '#';
            }
            
            return $items;
        }

        /**
         * Get category IDs
         *
         * @return array
         */
        protected function getCategoryIds()
        {
            $db = Factory::getDbo();
            $app = Factory::getApplication();
            $input = $app->input;
            $category = [];
            
            if ($this->_params->get('auto_category') && $input->get('option') == 'com_content') {
                $catid = $input->getInt('catid');
                if (!$catid && $input->get('view') == 'category') {
                    $catid = $input->getInt('id');
                }
                if ($catid) {
                    $category = [$catid];
                } else {
                    if ($input->get('view') == 'article') {
                        $itemid = $input->getInt('id');
                        $query = $db->getQuery(true)
                            ->select('catid')
                            ->from($db->quoteName('#__content'))
                            ->where($db->quoteName('id') . ' = ' . $itemid);
                        $db->setQuery($query);
                        $catid = $db->loadResult();
                        if ($catid) {
                            $category = [$catid];
                        }
                    }
                }
            }
            
            if (empty($category)) {
                $category = $this->_params->get('category', []);
            }

            // Include sub-categories
            if ($this->_params->get('sub_categories', 0) && count($category)) {
                $parents = $category;
                foreach ($parents as $c) {
                    $instance = \Joomla\CMS\Categories\Categories::getInstance('Content');
                    $cat = $instance->get($c);
                    if ($cat) {
                        $children = $cat->getChildren();
                        if (count($children)) {
                            foreach ($children as $ch) {
                                if (!in_array($ch->id, $category)) {
                                    $category[] = $ch->id;
                                }
                            }
                        }
                    }
                }
            }

            // Exclude categories
            $exCategories = str_replace(' ', '', $this->_params->get('exclude_categories', ''));
            if ($exCategories) {
                $exCategories = explode(',', $exCategories);
                if ($exCategories && count($exCategories)) {
                    $category = array_diff($category, $exCategories);
                }
            }
            
            return $category;
        }
    }
}