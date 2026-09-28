<?php 

/*
 * @version		$Id: default.php 1.2.8 07-03-2019 $
 * @package		Yendif Video Share
 * @copyright   Copyright (C) 2014-2019 Yendif Technologies (P) Ltd
 * @license     GNU/GPL http://www.gnu.org/licenses/gpl-2.0.html
*/

defined('_JEXEC') or die('Restricted access');

$span = 'col-xs-12 col-sm-'.floor(12 / $cols). ' col-md-'.floor(12 / $cols);?>

<div class="yendif-video-share categories exeltis <?php echo $moduleclass_sfx; ?>">
  	<div class="row-fluid">
  		<ul class="thumbnails">
    	<?php 
  	  		foreach( $items as $item ) {			
  				if( $column >= $cols ) {
					echo '</ul><ul class="thumbnails">';
					$column = 0;
				}
		
				$target_url = YendifVideoShareUtils::buildRoute( $item, 'category', $itemId );
				$image = YendifVideoShareUtils::getImage( $item->image, '_thumb', $config->default_image );	
		
				$count = '';
				if( $show_videos_count ) {
					$count = YendifVideoShareUtils::getMediaCount( $item->id );
					$count = '&nbsp;(' .$count. ')';
				}
    			?>
                <li class="<?php echo $span; ?>">
                	<div class="thumbnail">
    					<a href="<?php echo $target_url; ?>" class="yendif-thumbnail">
                            <div class="yendif-image" style="background-image: url(<?php echo $image; ?>);">&nbsp;</div>
                        </a>
                        <div class="caption">
        					<h4><a href="<?php echo $target_url; ?>"><?php echo $item->name.$count; ?></a></h4>
                            
                            <?php if( $show_excerpt && ! empty( $item->description ) ) : ?>
                            	<p><?php echo YendifVideoShareUtils::Truncate( $item->description, $excerpt_length ); ?></p>
                            <?php endif; ?>
                        </div>
  	  				</div> 
    			</li>
    			<?php
					if( $column >= $cols ) echo '</ul>';
		  			$column++;
      		}
		?>    
    	</ul>
  	</div>  	
</div>