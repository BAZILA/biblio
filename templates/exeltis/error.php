<?php
/**
 * @package     Joomla.Site
 * @subpackage  Template.system
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

/** @var JDocumentError $this */

if (!isset($this->error))
{
	$this->error = JError::raiseWarning(404, Text::_('JERROR_ALERTNOAUTHOR'));
	$this->debug = false;
}

$app = Factory::getApplication();
?>
<!DOCTYPE html>
<html lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>">
<head>
	<meta charset="utf-8" />
	<title><?php echo $this->error->getCode(); ?> - <?php echo htmlspecialchars($this->error->getMessage(), ENT_QUOTES, 'UTF-8'); ?></title>
	<link href="<?php echo $this->baseurl; ?>/templates/<?php echo $this->template; ?>/css/error.css" rel="stylesheet" />
	<?php if ($this->direction === 'rtl') : ?>
		<link href="<?php echo $this->baseurl; ?>/templates/<?php echo $this->template; ?>/css/error_rtl.css" rel="stylesheet" />
	<?php endif; ?>
	<?php if ($app->get('debug_lang', '0') == '1' || $app->get('debug', '0') == '1') : ?>
		<link href="<?php echo Uri::root(true); ?>/media/cms/css/debug.css" rel="stylesheet" />
	<?php endif; ?>
	<!--[if lt IE 9]><script src="<?php echo Uri::root(true); ?>/media/jui/js/html5.js"></script><![endif]-->
</head>
<body class="errorpage">
	<div class="error corrus">
	<h1>Error Page</h1>
		<div id="outline">
		<div id="errorboxoutline corrus">
			<div id="errorboxheader"><h2><?php echo $this->error->getCode(); ?></h2></div>
			<div id="errorboxbody">
			<p class="sub-errortext">
<span class="sub-errorinfo">Sorry! </span>
The page you requested was not found!....
</p>
			<div class="error-explain">
			<div class="error-explain-inner">
			<p><strong><?php echo Text::_('JERROR_LAYOUT_NOT_ABLE_TO_VISIT'); ?></strong></p>
			<ol>
				<li><?php echo Text::_('JERROR_LAYOUT_AN_OUT_OF_DATE_BOOKMARK_FAVOURITE'); ?></li>
				<li><?php echo Text::_('JERROR_LAYOUT_SEARCH_ENGINE_OUT_OF_DATE_LISTING'); ?></li>
				<li><?php echo Text::_('JERROR_LAYOUT_MIS_TYPED_ADDRESS'); ?></li>
				<li><?php echo Text::_('JERROR_LAYOUT_YOU_HAVE_NO_ACCESS_TO_THIS_PAGE'); ?></li>
				<li><?php echo Text::_('JERROR_LAYOUT_REQUESTED_RESOURCE_WAS_NOT_FOUND'); ?></li>
				<li><?php echo Text::_('JERROR_LAYOUT_ERROR_HAS_OCCURRED_WHILE_PROCESSING_YOUR_REQUEST'); ?></li>
			</ol>
			<p><strong><?php echo Text::_('JERROR_LAYOUT_PLEASE_TRY_ONE_OF_THE_FOLLOWING_PAGES'); ?></strong></p>
			<ul class="gotohome">
				<li><a class="homebutton" href="<?php echo Uri::root(true); ?>/index.php" title="<?php echo Text::_('JERROR_LAYOUT_GO_TO_THE_HOME_PAGE'); ?>"><?php echo Text::_('JERROR_LAYOUT_HOME_PAGE'); ?></a>
				</li>
				<li><a class="homebutton" href="<?php echo Uri::root(true); ?>/index.php?option=com_contact&view=contact&id=1" title="Contact us">Contact Us</a>
				</li>
			</ul>
			<p><?php echo Text::_('JERROR_LAYOUT_PLEASE_CONTACT_THE_SYSTEM_ADMINISTRATOR'); ?></p>
			<div>
			<p>
				
				<?php if ($this->debug) : ?>
					<br/><?php echo htmlspecialchars($this->error->getFile(), ENT_QUOTES, 'UTF-8');?>:<?php echo $this->error->getLine(); ?>
				<?php endif; ?>
			</p>
			</div>
			<?php if ($this->debug) : ?>
				<div>
					<?php echo $this->renderBacktrace(); ?>
					<?php // Check if there are more Exceptions and render their data as well ?>
					<?php if ($this->error->getPrevious()) : ?>
						<?php $loop = true; ?>
						<?php // Reference $this->_error here and in the loop as setError() assigns errors to this property and we need this for the backtrace to work correctly ?>
						<?php // Make the first assignment to setError() outside the loop so the loop does not skip Exceptions ?>
						<?php $this->setError($this->_error->getPrevious()); ?>
						<?php while ($loop === true) : ?>
							<p><strong><?php echo Text::_('JERROR_LAYOUT_PREVIOUS_ERROR'); ?></strong></p>
							<p>
								<?php echo htmlspecialchars($this->_error->getMessage(), ENT_QUOTES, 'UTF-8'); ?>
								<br/><?php echo htmlspecialchars($this->_error->getFile(), ENT_QUOTES, 'UTF-8');?>:<?php echo $this->_error->getLine(); ?>
							</p>
							<?php echo $this->renderBacktrace(); ?>
							<?php $loop = $this->setError($this->_error->getPrevious()); ?>
						<?php endwhile; ?>
						<?php // Reset the main error object to the base error ?>
						<?php $this->setError($this->error); ?>
					<?php endif; ?>
				</div>
			<?php endif; ?>
			<p><strong>Error Code:</strong>
				<?php echo htmlspecialchars($this->error->getMessage(), ENT_QUOTES, 'UTF-8'); ?></p>
			</div></div>
			</div>
		</div>
		</div>
	</div>
</body>
</html>
