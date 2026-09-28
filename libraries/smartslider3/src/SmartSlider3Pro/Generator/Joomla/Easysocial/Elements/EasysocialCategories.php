<?php

namespace Nextend\SmartSlider3Pro\Generator\Joomla\Easysocial\Elements;

use Nextend\Framework\Database\Database;
use Nextend\Framework\Form\Element\Select;


class EasysocialCategories extends Select {

    protected $table = '';
    protected $typeDb = '';
    protected $clusterType = '';
    protected $orderBy = 'ordering, id';
    protected $ini = false;

    public function __construct($insertAt, $name = '', $label = '', $default = '', $parameters = array()) {
        parent::__construct($insertAt, $name, $label, $default, $parameters);

        if (!empty($this->typeDb)) {
            $typeDb = "AND type='" . $this->typeDb . "'";
        } else {
            $typeDb = '';
        }

        if (!empty($this->clusterType)) {
            $cluserType = "AND cluster_type='" . $this->clusterType . "'";
        } else {
            $cluserType = '';
        }

        $categories = Database::queryAll("SELECT * FROM #__" . $this->table . " WHERE state = 1 " . $typeDb . $cluserType . "  ORDER BY " . $this->orderBy, false, "object");

        $this->options[0] = n2_('All');

        if (count($categories)) {
            foreach ($categories as $category) {
                $this->options[$category->id] = $this->runIni($category->title);
            }
        }
    }

    public function setTable($table) {
        $this->table = $table;
    }

    public function setTypeDb($typeDb) {
        $this->typeDb = $typeDb;
    }

    public function setClusterType($clusterType) {
        $this->clusterType = $clusterType;
    }

    public function setOrderBy($orderBy) {
        $this->orderBy = $orderBy;
    }

    public function setIni($ini) {
        $this->ini = true;
    }

    private function runIni($title) {
        if ($this->ini && function_exists('parse_ini_file')) {
            $language = parse_ini_file(JPATH_ROOT . '/language/en-GB/en-GB.com_easysocial.ini');
            if (isset($language[$title])) {
                return $language[$title];
            } else {
                return $title;
            }
        } else {
            return $title;
        }
    }
}