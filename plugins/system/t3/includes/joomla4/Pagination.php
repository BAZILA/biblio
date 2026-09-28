<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Pagination;

defined('JPATH_PLATFORM') or die;

/*
 * T3/T4 Framework compatibility.
 *
 * The framework creates an alias for Joomla's original Pagination class
 * so this class can extend it while providing backwards-compatible methods.
 */
\T3::makeAlias(
    JPATH_LIBRARIES . '/src/Pagination/Pagination.php',
    'Pagination',
    '_Pagination'
);

/**
 * Pagination Class.
 *
 * Provides a common interface for content pagination.
 *
 * @since 1.5
 */
class Pagination extends _Pagination
{
    /**
     * Create and return the pagination page list.
     *
     * @return string
     *
     * @since 1.5
     */
    public function getPagesLinks()
    {
        // Build the page navigation list.
        $data = $this->_buildDataObject();

        $list = array();
        $list['prefix'] = $this->prefix;

        $itemOverride = false;
        $listOverride = false;

        /*
         * T3/T4 Framework pagination override.
         *
         * Keep support for legacy pagination functions if the template
         * provides them, but do not use the removed Joomla JLog class.
         */
        $chromePath = \T3Path::getPath('html/pagination.php');

        if (file_exists($chromePath)) {
            include_once $chromePath;

            /*
             * Legacy pagination item functions.
             *
             * These functions are deprecated in Joomla, but may still
             * exist in older T3/T4 template overrides.
             */
            if (
                function_exists('pagination_item_active')
                && function_exists('pagination_item_inactive')
            ) {
                $itemOverride = true;
            }

            /*
             * Legacy pagination list rendering function.
             */
            if (function_exists('pagination_list_render')) {
                $listOverride = true;
            }
        }

        // All pages.
        if ($data->all->base !== null) {
            $list['all']['active'] = true;
            $list['all']['data'] = $itemOverride
                ? pagination_item_active($data->all)
                : $this->_item_active($data->all);
        } else {
            $list['all']['active'] = false;
            $list['all']['data'] = $itemOverride
                ? pagination_item_inactive($data->all)
                : $this->_item_inactive($data->all);
        }

        // Start page.
        if ($data->start->base !== null) {
            $list['start']['active'] = true;
            $list['start']['data'] = $itemOverride
                ? pagination_item_active($data->start)
                : $this->_item_active($data->start);
        } else {
            $list['start']['active'] = false;
            $list['start']['data'] = $itemOverride
                ? pagination_item_inactive($data->start)
                : $this->_item_inactive($data->start);
        }

        // Previous page.
        if ($data->previous->base !== null) {
            $list['previous']['active'] = true;
            $list['previous']['data'] = $itemOverride
                ? pagination_item_active($data->previous)
                : $this->_item_active($data->previous);
        } else {
            $list['previous']['active'] = false;
            $list['previous']['data'] = $itemOverride
                ? pagination_item_inactive($data->previous)
                : $this->_item_inactive($data->previous);
        }

        // Make sure pages array exists.
        $list['pages'] = array();

        // Page numbers.
        foreach ($data->pages as $i => $page) {
            if ($page->base !== null) {
                $list['pages'][$i]['active'] = true;
                $list['pages'][$i]['data'] = $itemOverride
                    ? pagination_item_active($page)
                    : $this->_item_active($page);
            } else {
                $list['pages'][$i]['active'] = false;
                $list['pages'][$i]['data'] = $itemOverride
                    ? pagination_item_inactive($page)
                    : $this->_item_inactive($page);
            }
        }

        // Next page.
        if ($data->next->base !== null) {
            $list['next']['active'] = true;
            $list['next']['data'] = $itemOverride
                ? pagination_item_active($data->next)
                : $this->_item_active($data->next);
        } else {
            $list['next']['active'] = false;
            $list['next']['data'] = $itemOverride
                ? pagination_item_inactive($data->next)
                : $this->_item_inactive($data->next);
        }

        // End page.
        if ($data->end->base !== null) {
            $list['end']['active'] = true;
            $list['end']['data'] = $itemOverride
                ? pagination_item_active($data->end)
                : $this->_item_active($data->end);
        } else {
            $list['end']['active'] = false;
            $list['end']['data'] = $itemOverride
                ? pagination_item_inactive($data->end)
                : $this->_item_inactive($data->end);
        }

        // Render pagination only when there is more than one page.
        if ($this->total > $this->limit) {
            return $listOverride
                ? pagination_list_render($list)
                : $this->_list_render($list);
        }

        return '';
    }

    /**
     * Return the pagination footer.
     *
     * @return string
     *
     * @since 1.5
     */
    public function getListFooter()
    {
        /*
         * T3/T4 Framework pagination override.
         */
        $chromePath = \T3Path::getPath('html/pagination.php');

        if (file_exists($chromePath)) {
            include_once $chromePath;

            /*
             * Keep backwards compatibility with legacy
             * pagination_list_footer() overrides.
             */
            if (function_exists('pagination_list_footer')) {
                $list = array(
                    'prefix'       => $this->prefix,
                    'limit'        => $this->limit,
                    'limitstart'   => $this->limitstart,
                    'total'        => $this->total,
                    'limitfield'   => $this->getLimitBox(),
                    'pagescounter' => $this->getPagesCounter(),
                    'pageslinks'   => $this->getPagesLinks(),
                );

                return pagination_list_footer($list);
            }
        }

        return $this->getPaginationLinks();
    }

    /**
     * Compatible with Joomla 3 style property access.
     *
     * This method is retained because some older T3/T4
     * template overrides may still call Pagination::get().
     *
     * @param string $property Property name.
     * @param mixed  $default  Default value.
     *
     * @return mixed
     */
    public function get($property, $default = null)
    {
        /*
         * Support Joomla 3 style properties such as:
         *
         * pages.total
         * pages.current
         *
         * Joomla's newer Pagination class exposes these
         * properties differently.
         */
        if (strpos($property, '.') !== false) {
            $prop = explode('.', $property, 2);

            if (isset($prop[1])) {
                $prop[1] = ucfirst($prop[1]);
                $property = implode('', $prop);
            }
        }

        if (isset($this->$property)) {
            return $this->$property;
        }

        return $default;
    }
}