<?php
/**
 * @package   T3 Blank
 * @copyright Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;

// get params
$sitename  = $this->params->get('sitename');
$slogan    = $this->params->get('slogan', '');
$logotype  = $this->params->get('logotype', 'text');
$logoimage = $logotype == 'image' ? $this->params->get('logoimage', T3Path::getUrl('images/logo.png', '', true)) : '';
$logoimgsm = ($logotype == 'image' && $this->params->get('enable_logoimage_sm', 0)) ? $this->params->get('logoimage_sm', T3Path::getUrl('images/logo-sm.png', '', true)) : false;

if (!$sitename) {$sitename = Factory::getConfig()->get('sitename');}

$logosize =  $this->params->get('logosize','4');
$headercalc=12-$logosize;
$headrightsize='col-sm-'.$headercalc. ' col-md-'.$headercalc;

?>
<!-- HEADER -->
<header id="t3-header">
<div class="container t3-header">
	<div class="row">
		<!-- LOGO -->
		<div class="col-xs-12 col-sm-<?php echo $logosize ?> col-md-<?php echo $logosize ?> logo">
			<div class="logo-<?php echo $logotype, ($logoimgsm ? ' logo-control' : '') ?>">
				<a href="<?php echo Uri::base() ?>" title="<?php echo strip_tags($sitename) ?>">
					<?php if($logotype == 'image'): ?>
						<img class="logo-img" src="<?php echo Uri::base(true) . '/' . $logoimage ?>" alt="<?php echo strip_tags($sitename) ?>" />
					<?php endif ?>
					<?php if($logoimgsm) : ?>
						<img class="logo-img-sm" src="<?php echo Uri::base(true) . '/' . $logoimgsm ?>" alt="<?php echo strip_tags($sitename) ?>" />
					<?php endif ?>
					<span><?php echo $sitename ?></span>
				</a>
				<small class="site-slogan"><?php echo $slogan ?></small>
			</div>
		</div>
		<!-- //LOGO -->
			<div class="col-xs-12 <?php echo $headrightsize ?> header-right <?php $this->_c('header-right') ?>">
				<div class="mainnav-inner pull-right">
				<?php $this->loadBlock('mainnav') ?>
				</div>
			</div>
	</div>
	</div>
</header>
<!-- //HEADER -->
