<?php
/**
 * @package   Charionsafe
 * @copyright Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>

<!-- FOOTER -->
<footer id="t3-footer" class="wrap t3-footer">
	<?php if ($this->checkSpotlight('footnav', 'footer-1, footer-2, footer-3, footer-4, footer-5, footer-6')) : ?>
		<!-- FOOT NAVIGATION -->
		<div class="t3-footer-overlay"></div>
			<?php $this->spotlight('footnav', 'footer-1, footer-2, footer-3, footer-4, footer-5, footer-6') ?>
		<!-- //FOOT NAVIGATION -->
	<?php endif ?>
	<section class="t3-copyright">
				<div class="col-xs-12 col-sm-6 <?php echo $this->countModules('footerbase-2') ? 'col-md-6' : 'col-md-12' ?> copyright <?php $this->_c('footerbase-1') ?>">
					<jdoc:include type="modules" name="<?php $this->_p('footerbase-1') ?>" />
				</div>
				<?php if ($this->countModules('footerbase-2')) : ?>
					<div class="col-xs-12 col-sm-6 col-md-6 footerbase-2 <?php $this->_c('footerbase-2') ?>">
<jdoc:include type="modules" name="<?php $this->_p('footerbase-2') ?>" />
					</div>
				<?php endif; ?>
	</section>
</footer>
 <?php if ($this->countModules('sitebase')) : ?>
<div style="clear:both"></div>
<div class="sitebase <?php $this->_c('sitebase') ?>"><jdoc:include type="modules" name="<?php $this->_p('sitebase') ?>" />
</div>
<?php endif; ?>
    <!-- BACK TOP TOP BUTTON -->
     
    <div id="back-to-top" data-spy="affix" data-offset-top="300" class="back-to-top hidden-xs hidden-sm affix-top">
     
      <button class="btn btn-primary" title="Back to Top"><i class="fa fa-angle-up"></i></button>
     
    </div>
     
     
     
    <script type="text/javascript">
     
    (function($) {
     
    	// Back to top
     
    	$('#back-to-top').on('click', function(){
     
    		$("html, body").animate({scrollTop: 0}, 500);
     
    		return false;
     
    	});
     
    })(jQuery);
     
    </script>
     
    <!-- BACK TO TOP BUTTON -->
<!-- //FOOTER -->