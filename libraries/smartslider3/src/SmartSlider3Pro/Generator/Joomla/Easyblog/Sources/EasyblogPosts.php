<?php

namespace Nextend\SmartSlider3Pro\Generator\Joomla\Easyblog\Sources;

use EB;
use EBMM;
use EBR;
use Foundry;
use Joomla\CMS\Factory;
use Nextend\Framework\Database\Database;
use Nextend\Framework\Form\Container\ContainerTable;
use Nextend\Framework\Form\Element\MixedField\GeneratorOrder;
use Nextend\Framework\Form\Element\OnOff;
use Nextend\Framework\Form\Element\Select\Filter;
use Nextend\Framework\Form\Element\Text;
use Nextend\Framework\Parser\Common;
use Nextend\Framework\Url\Url;
use Nextend\SmartSlider3\Generator\AbstractGenerator;
use Nextend\SmartSlider3\Platform\Joomla\ImageFallback;
use Nextend\SmartSlider3Pro\Generator\Joomla\Easyblog\Elements\EasyblogCategories;
use Nextend\SmartSlider3Pro\Generator\Joomla\Easyblog\Elements\EasyblogTags;

class EasyblogPosts extends AbstractGenerator {

    protected $layout = 'article';

    public function getDescription() {
        return sprintf(n2_('Creates slides from %1$s content.'), 'EasyBlog');
    }

    public function renderFields($container) {
        parent::renderFields($container);

        $filterGroup = new ContainerTable($container, 'filter', n2_('Filter'));

        $source = $filterGroup->createRow('source-row');
        new EasyblogCategories($source, 'easyblogcategories', n2_('Categories'), 0);
        new EasyblogTags($source, 'easyblogtags', n2_('Tags'), 0);
        new OnOff($source, 'easyblogsubcategories', n2_('Include subcategories'), 0);

        $limit = $filterGroup->createRow('limit-row');
        new Text($limit, 'easybloguserid', n2_('User ID'), '');
        new Filter($limit, 'easyblogfrontpage', n2_('Frontpage'), 0);
        new Filter($limit, 'easyblogfeatured', n2_('Featured'), 0);
        new Text($limit, 'easyblogexclude', n2_('Exclude ID'), '');

        $orderGroup = new ContainerTable($container, 'order-group', n2_('Order'));
        $order      = $orderGroup->createRow('order-row');
        new GeneratorOrder($order, 'easyblogorder', 'con.created|*|desc', array(
            'options' => array(
                ''             => n2_('None'),
                'con.title'    => n2_('Title'),
                'cattitle'     => n2_('Category title'),
                'blogger'      => n2_('Username'),
                'con.ordering' => n2_('Ordering'),
                'con.created'  => n2_('Creation time'),
                'con.modified' => n2_('Modification time')
            )
        ));
    }

    private function findImage($path, $url) {
        $locations = array(
            'easyblog_images',
            'easyblog_articles',
            'easyblog_shared',
            'easyblog_cavatar',
            'easyblog_tavatar'
        );

        $pathlocation = '';

        foreach ($locations as $l) {
            if (strpos($path, $l)) {
                $pathlocation = $l;
                break;
            }
        }

        if ($pathlocation != '') {
            foreach ($locations as $l) {
                if ($pathlocation != $l) {
                    if (file_exists(str_replace($pathlocation, $l, $path))) {
                        return str_replace($pathlocation, $l, $url);
                        break;
                    }
                }
            }
        }
    }

    protected function _getData($count, $startIndex) {
        require_once(JPATH_ADMINISTRATOR . "/components/com_easyblog/includes/easyblog.php");
        EB::mediamanager();

        $category = array_map('intval', explode('||', $this->data->get('easyblogcategories', '')));
        if (!in_array('0', $category) && $this->data->get('easyblogsubcategories', 0)) {
            $checkCategory = $category;
            do {
                $catQuery  = 'SELECT id FROM #__easyblog_category WHERE parent_id IN (' . implode(',', $checkCategory) . ')';
                $catResult = Database::queryAll($catQuery);
                if (!empty($catResult)) {
                    $checkCategory = array();
                    foreach ($catResult as $subCategory) {
                        $checkCategory[] = $category[] = $subCategory['id'];
                    }
                }
            } while (!empty($catResult));
        }

        $query = 'SELECT con.*, con.intro as "main_content_of_post", con.content as "rest_of_the_post", usr.id AS "user_id", usr.nickname as "blogger", usr.avatar as "blogger_avatar_picture", cat.title as cat_title ';

        /* id 	created_by 	title 	description 	alias 	avatar 	parent_id 	private 	created 	status 	published 	ordering 	level 	lft 	rgt 	default */

        $query .= 'FROM #__easyblog_post con ';

        $query .= 'LEFT JOIN #__easyblog_users usr ON usr.id = con.created_by ';

        $query .= 'LEFT JOIN #__easyblog_category cat ON cat.id = con.category_id ';

        $jnow  = Factory::getDate();
        $now   = $jnow->toSql();
        $where = array("con.published = 1 AND (con.publish_up = '0000-00-00 00:00:00' OR con.publish_up IS NULL OR con.publish_up < '" . $now . "') AND (con.publish_down = '0000-00-00 00:00:00' OR con.publish_down IS NULL OR con.publish_down > '" . $now . "') ");

        $exclude = $this->data->get('easyblogexclude', '');
        if (!empty($exclude)) {
            $where[] = ' con.id NOT IN (' . $exclude . ') ';
        }

        if (!in_array('0', $category)) {
            $where[] = 'con.id IN (SELECT post_id FROM #__easyblog_post_category WHERE category_id in (' . implode(',', $category) . ')) ';
        }

        $tags = array_map('intval', explode('||', $this->data->get('easyblogtags', '0')));

        if (!in_array(0, $tags)) {
            $where[] = 'con.id IN (SELECT post_id FROM #__easyblog_post_tag WHERE tag_id IN(' . implode(',', $tags) . '))';
        }

        switch ($this->data->get('easyblogfrontpage', 0)) {
            case 1:
                $where[] = "con.frontpage = 1 ";
                break;
            case -1:
                $where[] = "con.frontpage = 0 ";
                break;
        }

        switch ($this->data->get('easyblogfeatured', 0)) {
            case 1:
                $where[] = "con.id IN (SELECT content_id FROM #__easyblog_featured WHERE type = 'post')";
                break;
            case -1:
                $where[] = "con.id NOT IN (SELECT content_id FROM #__easyblog_featured WHERE type = 'post')";
                break;
        }

        $sourceUserId = intval($this->data->get('easybloguserid', ''));
        if (!empty($sourceUserId)) {
            $where[] = 'con.created_by = ' . $sourceUserId . ' ';
        }

        $where[] = " con.state = 0 ";

        if (count($where) > 0) {
            $query .= 'WHERE ' . implode(' AND ', $where) . ' ';
        }

        $order = Common::parse($this->data->get('easyblogorder', 'con.title|*|asc'));
        if ($order[0]) {
            $query .= 'ORDER BY ' . $order[0] . ' ' . $order[1] . ' ';
        }

        $query .= 'LIMIT ' . $startIndex . ', ' . $count . ' ';

        $result = Database::queryAll($query);

        $data = array();
        $root = Url::getBaseUri();
        for ($i = 0; $i < count($result); $i++) {
            $description = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', "", $result[$i]['main_content_of_post']);

            $url = 'index.php?option=com_easyblog&view=entry&id=' . $result[$i]['id'];
            if (class_exists('EBR', false)) {
                $url = EBR::_($url, true, null, false, false, false);
            }

            $r = array(
                'title'       => $result[$i]['title'],
                'description' => $description,
                'url'         => $url,
            );

            if (!empty($result[$i]['image'])) {
                $imageUrl = EBMM::getUrl($result[$i]['image']);
                $filename = EBMM::getTitle($result[$i]['image']);
                $filepath = EBMM::getPath($result[$i]['image']);
                if (file_exists($filepath)) {
                    $fullRoot = '';
                    $image    = $imageUrl;
                } else {
                    $newImageUrl = $this->findImage($filepath, $imageUrl);
                    if (!empty($newImageUrl)) {
                        $fullRoot = str_replace($filename, '', $newImageUrl);
                        $image    = $filename;
                    } else {
                        $fullRoot = $root;
                        $image    = '';
                    }
                }
            } else {
                $fullRoot = $root;
                $image    = '';
            }

            $r['image'] = $r['thumbnail'] = ImageFallback::fallback(array($image), array($result[$i]['content']), $fullRoot);
            $content    = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', "", $result[$i]['content']);

            $category_url = 'index.php?option=com_easyblog&view=categories&id=' . $result[$i]['category_id'];
            if (class_exists('EBR', false)) {
                $category_url = EBR::_($category_url);
            }

            if (class_exists('EB', false)) {
                $category = EB::table('Category');
                $category->load($result[$i]['category_id']);
                $r['category_post_cover'] = $category->getDefaultPostCover();
            }

            $r += array(
                'url_label'         => n2_('View post'),
                'category_url'      => $category_url,
                'category_title'    => $result[$i]['cat_title'],
                'blogger'           => $result[$i]['blogger'],
                'created_by_id'     => $result[$i]['created_by'],
                'creation_time'     => $result[$i]['created'],
                'modification_time' => $result[$i]['modified'],
                'content'           => $content,
                'latitude'          => $result[$i]['latitude'],
                'longitude'         => $result[$i]['longitude'],
                'address'           => $result[$i]['address'],
                'hits'              => $result[$i]['hits'],
                'category_id'       => $result[$i]['category_id'],
                'id'                => $result[$i]['id'],
            );

            if (class_exists('Foundry')) {
                $user = Foundry::user($result[$i]['user_id']);
                $r    += array(
                    'blogger_avatar_picture'        => $user->getAvatar("medium"),
                    'blogger_avatar_picture_small'  => $user->getAvatar("small"),
                    'blogger_avatar_picture_square' => $user->getAvatar("square"),
                    'blogger_avatar_picture_large'  => $user->getAvatar("large"),
                );
            }
            $data[] = $r;
        }

        return $data;
    }
}
