<?php

namespace Nextend\SmartSlider3Pro\Generator\Joomla\Easydiscuss\Sources;

use Joomla\CMS\Factory;
use Nextend\Framework\Database\Database;
use Nextend\Framework\Form\Container\ContainerTable;
use Nextend\Framework\Form\Element\MixedField\GeneratorOrder;
use Nextend\Framework\Form\Element\OnOff;
use Nextend\Framework\Form\Element\Select\Filter;
use Nextend\Framework\Form\Element\Text;
use Nextend\Framework\Parser\Common;
use Nextend\SmartSlider3\Generator\AbstractGenerator;
use Nextend\SmartSlider3\Platform\Joomla\ImageFallback;
use Nextend\SmartSlider3Pro\Generator\Joomla\Easydiscuss\Elements\EasydiscussCategories;
use Nextend\SmartSlider3Pro\Generator\Joomla\Easydiscuss\Elements\EasydiscussTags;


class EasydiscussDiscussions extends AbstractGenerator {

    protected $layout = 'article';

    public function getDescription() {
        return sprintf(n2_('Creates slides from %1$s content.'), 'EasyDiscuss');
    }

    public function renderFields($container) {
        parent::renderFields($container);

        $filterGroup = new ContainerTable($container, 'filter', n2_('Filter'));

        $source = $filterGroup->createRow('source-row');
        new EasydiscussCategories($source, 'easydiscusscategories', n2_('Category'), 0);
        new EasydiscussTags($source, 'easydiscusstags', n2_('Tags'), 0);

        $limit = $filterGroup->createRow('limit-row');
        new Text($limit, 'easydiscussuserid', n2_('User ID'), '');
        new Filter($limit, 'easydiscussfeatured', n2_('Featured'), 0);
        new Filter($limit, 'easydiscussresolved', n2_('Resolved'), 0);
        new OnOff($limit, 'easydiscussmain', n2_('Only main discussions'), 1);

        $orderGroup = new ContainerTable($container, 'order-group', n2_('Order'));
        $order      = $orderGroup->createRow('order-row');
        new GeneratorOrder($order, 'easydiscussorder', 'created|*|desc', array(
            'options' => array(
                ''         => n2_('None'),
                'title'    => n2_('Title'),
                'cattitle' => n2_('Category title'),
                'ordering' => n2_('Ordering'),
                'created'  => n2_('Creation time'),
                'modified' => n2_('Modification time')
            )
        ));
    }

    protected function _getData($count, $startIndex) {

        $category = array_map('intval', explode('||', $this->data->get('easydiscusscategories', '')));

        $where = array("published = '1'");

        if (!in_array('0', $category)) {
            $where[] = 'category_id IN (' . implode(',', $category) . ') ';
        }

        $tags = array_map('intval', explode('||', $this->data->get('easydiscusstags', '0')));

        if (!in_array(0, $tags)) {
            $where[] = 'id IN (SELECT post_id FROM #__discuss_posts_tags WHERE tag_id IN(' . implode(',', $tags) . ')) ';
        }

        switch ($this->data->get('easydiscussfeatured', 0)) {
            case 1:
                $where[] = "featured = 1 ";
                break;
            case -1:
                $where[] = "featured = 0 ";
                break;
        }

        switch ($this->data->get('easydiscussresolved', 0)) {
            case 1:
                $where[] = "isresolve = 1 ";
                break;
            case -1:
                $where[] = "isresolve = 0 ";
                break;
        }

        $sourceUserId = intval($this->data->get('easydiscussuserid', ''));
        if (!empty($sourceUserId)) {
            $where[] = 'user_id = ' . $sourceUserId . ' ';
        }

        $sourceDiscussionMain = intval($this->data->get('easydiscussmain', ''));
        if (!empty($sourceDiscussionMain)) {
            $where[] = "parent_id = '0' ";
        }

        $query = 'SELECT * FROM #__discuss_posts WHERE ' . implode(' AND ', $where) . ' ';

        $order = Common::parse($this->data->get('easydiscussorder', 'created|*|desc'));
        if ($order[0]) {
            $query .= 'ORDER BY ' . $order[0] . ' ' . $order[1] . ' ';
        }

        $query .= 'LIMIT ' . $startIndex . ', ' . $count . ' ';

        $result = Database::queryAll($query);

        $data = array();
        for ($i = 0; $i < count($result); $i++) {
            $user = Factory::getUser($result[$i]['user_id']);
            $r    = array(
                'title'           => $result[$i]['title'],
                'description'     => $result[$i]['content'],
                'url'             => 'index.php?option=com_easydiscuss&view=post&id=' . $result[$i]['id'],
                'url_label'       => n2_('View discussion'),
                'category_url'    => 'index.php?option=com_easydiscuss&view=categories&layout=listings&category_id=' . $result[$i]['category_id'],
                'user_name'       => $user->username,
                'user_real_name'  => $user->name,
                'vote'            => $result[$i]['vote'],
                'hits'            => $result[$i]['hits'],
                'number_of_likes' => $result[$i]['num_likes'],
                'number_of_votes' => $result[$i]['sum_totalvote'],
                'created'         => $result[$i]['created'],
                'modified'        => $result[$i]['modified'],
                'user_id'         => $result[$i]['user_id'],
                'latitude'        => $result[$i]['latitude'],
                'longitude'       => $result[$i]['longitude'],
                'parent_id'       => $result[$i]['parent_id'],
                'category_id'     => $result[$i]['category_id'],
                'id'              => $result[$i]['id']
            );

            $r['image'] = $r['thumbnail'] = ImageFallback::fallback(array(), array($result[$i]['content']));

            $data[] = $r;
        }

        return $data;
    }
}
