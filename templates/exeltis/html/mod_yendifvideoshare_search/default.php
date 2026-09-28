<?php 

/*
 * @version		$Id: default.php 1.2.8 07-03-2019 $
 * @package		Yendif Video Share
 * @copyright   Copyright (C) 2014-2019 Yendif Technologies (P) Ltd
 * @license     GNU/GPL http://www.gnu.org/licenses/gpl-2.0.html
*/

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Router\Route; ?>

<div class="yendif-video-share search exeltis <?php echo $moduleclass_sfx; ?>">
	<form action="<?php echo Route::_( 'index.php?option=com_yendifvideoshare&view=search&Itemid='.$itemId ); ?>" method="post" class="form-validate" >
    	<input type="hidden" name="option" value="com_yendifvideoshare"/>
    	<input type="hidden" name="view" value="search"/>
    	<input type="hidden" name="Itemid" value="<?php echo $itemId; ?>"/>
        <div class="input-append">
   			<input type="text" name="search" class="required" value="<?php echo htmlspecialchars( $search_key ); ?>"/>
   			<button type="submit" class="btn btn-primary"><?php echo JText::_('YENDIF_VIDEO_SHARE_GO'); ?></button>
        </div>
  	</form>
</div>