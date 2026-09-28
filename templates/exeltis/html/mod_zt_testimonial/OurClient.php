<?php
defined( '_JEXEC' ) or die( 'Access Deny' );
use Joomla\CMS\Uri\Uri;

$document = Factory::getDocument();
$uri = URI::getInstance();

$arr=   $this->parsedData['arrayTestimonial'];
$str ='';
$str .='<div class="zt-module-testimonial-description exeltis client">'.$this->parsedData['descriptionAboutUs'].'</div>';
$str .='<div id="module_'.$module->id.'" class="owl-carousel zt-module-testimonial exeltis client">';
foreach ($arr as $key=>$test) {
    $str .= '<div class="zt-testimonial exeltis client">';    
	$str .= '<div class="col-md-12 col-sm-12 item-client">';

    $str .= '<p class="content-testimonial">';
    $str .= '<i class="fa fa-quote-left pull-left"></i>';

    $str .= $test->testimonial;
    $str .= '</p>';

    $str .= '<div class="row info">';
    $str .= '<div class="col-xs-6 col-sm-12 col-md-12 col-lg-3 avatarblock">';
    if($this->parsedData['avatar']){
        $linkAvatar = str_replace(' ', '', $test->linkAvatar);
        if($linkAvatar !=='') {
            $str .= '<a href="'. $linkAvatar.'">';
        }
        $str .= '<img class="img-circle img-responsive avatar" src="'.$uri->root().$test->urlAvatar.'">';
        $linkAvatar = str_replace(' ', '', $test->linkAvatar);
        if($linkAvatar !=='') {
            $str .= '</a>';
        }
    }
    $str .= '</div>';//end div image

    $str .= '<div class="col-xs-12 col-sm-12 col-md-12 col-lg-9 infoblock">';
    if($this->parsedData['name']){
        $linkName = str_replace(' ', '', $test->linkName);
        if($linkName !=='') {
            $str .= '<a href="'. $linkName.'">';
        }
        $str .= '<div class="name"><h4>'.$test->name.'</h4></div>';
        $linkName = str_replace(' ', '', $test->linkName);
        if($linkName !=='') {
            $str .= '</a>';
        }
    }
    if($this->parsedData['email']){
        $str .= '<div class="email">'.$test->email.'</div>';
    }
    if($this->parsedData['position']){
        $str .= '<div class="position">'.$test->position.'</div>';
    }

    //var_dump($test->twitter);
    $web = $this->checkWebsite($test->website);
    $fb = $this->checkFacebook($test->facebook);
    $tw = $this->checkTwitter($test->twitter);
    if($this->parsedData['website']){
        $str .= '<div class="website">';
        $str .= '<span>Website: </span><a href="'.$web.'" target="_blank">'.$web.'</a>';
        $str .='</div>';
    }

    $str .= '<div class="tw-fb">';
    if($this->parsedData['facebook']){
        $str .='<a href="'.$fb.'" target="_blank"><i class="fa fa-facebook"></i></a>';
    }
    if($this->parsedData['twitter']){
        $str .='<a href="'.$tw.'" target="_blank"><i class="fa fa-twitter"></i></a>';
    }
    //them
    if($this->parsedData['google']){
        $str .='<a href="'.$test->google.'" target="_blank"><i class="fa fa-google-plus"></i></a>';
    }
    if($this->parsedData['linkedin']){
        $str .='<a href="'.$test->linkedin.'" target="_blank"><i class="fa fa-linkedin"></i></a>';
    }
    if($this->parsedData['printerest']){
        $str .='<a href="'.$test->printerest.'" target="_blank"><i class="fa fa-pinterest"></i></a>';
    }
    $str .='</div>';



    $str .= '</div>';//end information

    $str .= '</div>';//end col 4(2 2) 8
    $str .= '</div>';//end col 12
    $str .= '</div>';// end zt-testimonial
}

$str .= '</div>';
echo $str;

//add stylesheet to document header
$jversion = new JVersion;
$current_version = $jversion->getShortVersion();
if (version_compare($current_version, '3.0.0') <= 0){
    $document->addStyleSheet($uri->root().'modules/mod_zt_testimonial/asset/vendor/bootstrap/css/testimonial.css');
    $document->addScript($uri->root().'modules/mod_zt_testimonial/asset/vendor/jquery/jquery-1.11.1.js');
    $document->addScript($uri->root().'modules/mod_zt_testimonial/asset/vendor/jquery/jquery.noConflict.js');
    $document->addScript($uri->root().'modules/mod_zt_testimonial/asset/vendor/bootstrap/js/bootstrap.js');
    $document->addScript($uri->root().'modules/mod_zt_testimonial/asset/vendor/jquery/fixConflict.js');
} else {
    $document->addStyleSheet($uri->root().'modules/mod_zt_testimonial/asset/vendor/bootstrap/css/testimonial.css');
}

//$document->addStyleSheet('//netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.css');
$document->addStyleSheet($uri->root().'modules/mod_zt_testimonial/asset/css/style3.css');

$document->addStyleSheet($uri->root().'modules/mod_zt_testimonial/asset/vendor/owl-carousel/owl.carousel.css');
$document->addStyleSheet($uri->root().'modules/mod_zt_testimonial/asset/vendor/owl-carousel/owl.theme.css');

?>

<script src="<?php echo $uri->root().'modules/mod_zt_testimonial/asset/vendor/owl-carousel/owl.carousel.js'; ?>"></script>
<script src="<?php echo $uri->root().'modules/mod_zt_testimonial/asset/js/script.js'; ?>"></script>

<script type="text/javascript">

    jQuery(document).ready(function(){
        jQuery("#module_<?php echo $module->id; ?>").owlCarousel({
            items: <?php echo isset($this->parsedData['item'])? $this->parsedData['item'] : '3'; ?>,
            autoPlay: <?php echo isset($this->parsedData['pause'])? $this->parsedData['pause'] : 'true' ?>,
            stopOnHover :  <?php echo isset($this->parsedData['autoHover'])? $this->parsedData['autoHover'] : 'true' ?>,
			navigation : true,
        navigationText : ["<i class='fa fa-arrow-left' aria-hidden='true'></i>", "<i class='fa fa-arrow-right' aria-hidden='true'></i>"],
        rewindNav : true
        });
    });
</script>