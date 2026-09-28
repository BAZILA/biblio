<?php
/**
 * @package     mod_jttabs
 * @copyright   Copyright (C) 2007 - 2023 http://www.joomlatema.net, Inc. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @author      JoomlaTema.Net
 * @link        http://www.joomlatema.net
 **/

// Prevent direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Helper\ModuleHelper;

// Suppress specific PHP error types
error_reporting(E_ALL ^ E_NOTICE ^ E_DEPRECATED ^ E_WARNING);

$tab_items = $params->get('tab_items');
$TabTypeMod = $params->get('TabType') == 'modules';
?>

<style>
.fade {
    -webkit-transition: opacity .15s linear;
    -o-transition: opacity .15s linear;
    transition: opacity .15s linear;
}
</style>

<?php if ($tab_items) : ?>
<div class="jttabs-wrapper layout-default" id="jttabs_<?php echo $module->id; ?>">

    <?php if ($params->get('showpretext') == 1): ?>
        <div class="jt-pretext">
            <span class="pretext_title"> <?php echo $params->get('pretexttitle'); ?> </span>
            <p class="pretext"> <?php echo $params->get('pretext'); ?> </p>
        </div>
    <?php endif; ?>

    <!-- Navigation Tabs -->
    <ul class="nav nav-tabs" role="tablist">
        <?php $counter = 1; ?>
        <?php foreach ($tab_items as $item) : ?>
            <li class="nav-item" role="presentation">
                <button class="nav-link <?php echo ($counter == 1 ? 'active' : ''); ?>" role="tab" type="button"
                        data-bs-toggle="tab" data-bs-target="#jttab<?php echo $counter . $module->id; ?>"
                        aria-controls="jttab<?php echo $counter . $module->id; ?>">
                    <?php echo (!empty($item->tabtitle)) ? $item->tabtitle : ('Tab ' . $counter); ?>
                </button>
            </li>
            <?php $counter++; ?>
        <?php endforeach; ?>
    </ul>

    <!-- Tab Content -->
    <div class="tab-content">
        <?php $counter = 1; ?>
        <?php foreach ($tab_items as $item) : ?>
            <div id="jttab<?php echo $counter . $module->id; ?>" class="tab-pane fade <?php echo ($counter == 1 ? 'active show' : ''); ?>" role="tabpanel">
                <?php if ($item->TabType == 'modules') : ?>
                    <?php
                    jimport('joomla.application.module.helper');
                    if (!empty($item->id)) {
                        foreach ($item->id as $moduleId) {
                            $modules = ModuleHelper::getModuleById($moduleId);
                            echo ModuleHelper::renderModule($modules);
                        }
                    }
                    ?>
                <?php elseif ($item->TabType == 'articles') : ?>
                    <div style="width:<?php echo $item->tabpanelwidth; ?>; height:<?php echo $item->tabpanelheight; ?>">
                        <?php echo (new modJtTabsHelper)->getArticle($item->article, $params); ?>
                    </div>
                <?php endif; ?>
            </div>
            <?php $counter++; ?>
        <?php endforeach; ?>
    </div>
</div>
<?php else: ?>
    <span class="text-danger">No Tabs to Display!</span>
<?php endif; ?>

<script>
    jQuery("#contentpanel").on("load", function() {
        let head = jQuery("#contentpanel").contents().find("head");
        let css = '<style> div.phpdebugbar,.pagenavigation{ display:none;}</style>';
        jQuery(head).append(css);
    });
</script>
