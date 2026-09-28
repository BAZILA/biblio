<?php
/**
* @autor        JoomlaTema
* @title		Player Squad JT Module
* @website		https://www.joomlatema.net
* @copyright	Copyright (C) 2008 -2021 JoomlaTema.net. All rights reserved.
* @license		http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
**/

// no direct access
defined('_JEXEC') or die;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
$document = Factory::getDocument();


$set =	$params->get('set');
$radius =	$params->get('radius');
$see_profile_text=$params->get('see_profile_text');

$group1_names = array(
	$params->get('group1-name-1'),
	$params->get('group1-name-2'),
	$params->get('group1-name-3'),
	$params->get('group1-name-4'),
	$params->get('group1-name-5'),
	$params->get('group1-name-6'),
	$params->get('group1-name-7'),
	$params->get('group1-name-8'),
	$params->get('group1-name-9'),
	$params->get('group1-name-10')
);
$group1_positions = array(
	$params->get('group1-pos-1'),
	$params->get('group1-pos-2'),
	$params->get('group1-pos-3'),
	$params->get('group1-pos-4'),
	$params->get('group1-pos-5'),
	$params->get('group1-pos-6'),
	$params->get('group1-pos-7'),
	$params->get('group1-pos-8'),
	$params->get('group1-pos-9'),
	$params->get('group1-pos-10')
);
$group1_nationality = array(
	$params->get('group1-nat-1'),
	$params->get('group1-nat-2'),
	$params->get('group1-nat-3'),
	$params->get('group1-nat-4'),
	$params->get('group1-nat-5'),
	$params->get('group1-nat-6'),
	$params->get('group1-nat-7'),
	$params->get('group1-nat-8'),
	$params->get('group1-nat-9'),
	$params->get('group1-nat-10')
);
$group1_txts = array(
	$params->get('group1-desc-1'),
	$params->get('group1-desc-2'),
	$params->get('group1-desc-3'),
	$params->get('group1-desc-4'),
	$params->get('group1-desc-5'),
	$params->get('group1-desc-6'),
	$params->get('group1-desc-7'),
	$params->get('group1-desc-8'),
	$params->get('group1-desc-9'),
	$params->get('group1-desc-10')
);
$group1_imgs = array(
	$params->get('group1-image-1'),
	$params->get('group1-image-2'),
	$params->get('group1-image-3'),
	$params->get('group1-image-4'),
	$params->get('group1-image-5'),
	$params->get('group1-image-6'),
	$params->get('group1-image-7'),
	$params->get('group1-image-8'),
	$params->get('group1-image-9'),
	$params->get('group1-image-10')
);
$group1_socs1 = array(
	$params->get('group1-sicon1-1'),
	$params->get('group1-sicon1-2'),
	$params->get('group1-sicon1-3'),
	$params->get('group1-sicon1-4'),
	$params->get('group1-sicon1-5'),
	$params->get('group1-sicon1-6'),
	$params->get('group1-sicon1-7'),
	$params->get('group1-sicon1-8'),
	$params->get('group1-sicon1-9'),
	$params->get('group1-sicon1-10')
);
$group1_socs2 = array(
	$params->get('group1-sicon2-1'),
	$params->get('group1-sicon2-2'),
	$params->get('group1-sicon2-3'),
	$params->get('group1-sicon2-4'),
	$params->get('group1-sicon2-5'),
	$params->get('group1-sicon2-6'),
	$params->get('group1-sicon2-7'),
	$params->get('group1-sicon2-8'),
	$params->get('group1-sicon2-9'),
	$params->get('group1-sicon2-10')
);
$group1_socs3 = array(
	$params->get('group1-sicon3-1'),
	$params->get('group1-sicon3-2'),
	$params->get('group1-sicon3-3'),
	$params->get('group1-sicon3-4'),
	$params->get('group1-sicon3-5'),
	$params->get('group1-sicon3-6'),
	$params->get('group1-sicon3-7'),
	$params->get('group1-sicon3-8'),
	$params->get('group1-sicon3-9'),
	$params->get('group1-sicon3-10')
);
$group1_socs4 = array(
	$params->get('group1-sicon4-1'),
	$params->get('group1-sicon4-2'),
	$params->get('group1-sicon4-3'),
	$params->get('group1-sicon4-4'),
	$params->get('group1-sicon4-5'),
	$params->get('group1-sicon4-6'),
	$params->get('group1-sicon4-7'),
	$params->get('group1-sicon4-8'),
	$params->get('group1-sicon4-9'),
	$params->get('group1-sicon4-10')
);
$group1_link1 = array(
	$params->get('group1-link1-1'),
	$params->get('group1-link1-2'),
	$params->get('group1-link1-3'),
	$params->get('group1-link1-4'),
	$params->get('group1-link1-5'),
	$params->get('group1-link1-6'),
	$params->get('group1-link1-7'),
	$params->get('group1-link1-8'),
	$params->get('group1-link1-9'),
	$params->get('group1-link1-10')
);
$group1_link2 = array(
	$params->get('group1-link2-1'),
	$params->get('group1-link2-2'),
	$params->get('group1-link2-3'),
	$params->get('group1-link2-4'),
	$params->get('group1-link2-5'),
	$params->get('group1-link2-6'),
	$params->get('group1-link2-7'),
	$params->get('group1-link2-8'),
	$params->get('group1-link2-9'),
	$params->get('group1-link2-10')
);
$group1_link3 = array(
	$params->get('group1-link3-1'),
	$params->get('group1-link3-2'),
	$params->get('group1-link3-3'),
	$params->get('group1-link3-4'),
	$params->get('group1-link3-5'),
	$params->get('group1-link3-6'),
	$params->get('group1-link3-7'),
	$params->get('group1-link3-8'),
	$params->get('group1-link3-9'),
	$params->get('group1-link3-10')
);
$group1_link4 = array(
	$params->get('group1-link4-1'),
	$params->get('group1-link4-2'),
	$params->get('group1-link4-3'),
	$params->get('group1-link4-4'),
	$params->get('group1-link4-5'),
	$params->get('group1-link4-6'),
	$params->get('group1-link4-7'),
	$params->get('group1-link4-8'),
	$params->get('group1-link4-9'),
	$params->get('group1-link4-10')
);

$group1_profile_link = array(
	$params->get('group1-profilelink-1'),
	$params->get('group1-profilelink-2'),
	$params->get('group1-profilelink-3'),
	$params->get('group1-profilelink-4'),
	$params->get('group1-profilelink-5'),
	$params->get('group1-profilelink-6'),
	$params->get('group1-profilelink-7'),
	$params->get('group1-profilelink-8'),
	$params->get('group1-profilelink-9'),
	$params->get('group1-profilelink-10')
);
$group2_profile_link = array(
	$params->get('group2-profilelink-1'),
	$params->get('group2-profilelink-2'),
	$params->get('group2-profilelink-3'),
	$params->get('group2-profilelink-4'),
	$params->get('group2-profilelink-5'),
	$params->get('group2-profilelink-6'),
	$params->get('group2-profilelink-7'),
	$params->get('group2-profilelink-8'),
	$params->get('group2-profilelink-9'),
	$params->get('group2-profilelink-10')
);
$group3_profile_link = array(
	$params->get('group3-profilelink-1'),
	$params->get('group3-profilelink-2'),
	$params->get('group3-profilelink-3'),
	$params->get('group3-profilelink-4'),
	$params->get('group3-profilelink-5'),
	$params->get('group3-profilelink-6'),
	$params->get('group3-profilelink-7'),
	$params->get('group3-profilelink-8'),
	$params->get('group3-profilelink-9'),
	$params->get('group3-profilelink-10')
);
$group4_profile_link = array(
	$params->get('group4-profilelink-1'),
	$params->get('group4-profilelink-2'),
	$params->get('group4-profilelink-3'),
	$params->get('group4-profilelink-4'),
	$params->get('group4-profilelink-5'),
	$params->get('group4-profilelink-6'),
	$params->get('group4-profilelink-7'),
	$params->get('group4-profilelink-8'),
	$params->get('group4-profilelink-9'),
	$params->get('group4-profilelink-10')
);
$group5_profile_link = array(
	$params->get('group5-profilelink-1'),
	$params->get('group5-profilelink-2'),
	$params->get('group5-profilelink-3'),
	$params->get('group5-profilelink-4'),
	$params->get('group5-profilelink-5'),
	$params->get('group5-profilelink-6'),
	$params->get('group5-profilelink-7'),
	$params->get('group5-profilelink-8'),
	$params->get('group5-profilelink-9'),
	$params->get('group5-profilelink-10')
);
//output
if( $group1_names[0] ){
    $group1_title = $params->get('group1_title');
} else {
    $group1_title = '';
}

echo '<div class="playersquad-entry">
    <div class="player box'. $set .' rad'. $radius .'">
        <h3 class="group-title">'.$group1_title.'</h3>';

for( $wb = 0; $wb < 10; $wb++ ){
    if( $group1_names[$wb] ){
        echo '<div class="member">
            <div class="member-inner">
                <div class="playersquad-avatar">';

                if( $group1_imgs[$wb] ){
                    echo '<img src="' . URI::base() . $group1_imgs[$wb] . '" alt="' . $group1_names[$wb] . '" border="0" />';
                } else {
                    echo '<img src="' . URI::base() . 'modules/mod_playersquad_jt/images/avatar.png" alt="' . $group1_names[$wb] . '" />';
                }

                if( ($group1_socs1[$wb] && $group1_link1[$wb]) || ($group1_socs2[$wb] && $group1_link2[$wb]) || ($group1_socs3[$wb] && $group1_link3[$wb]) || ($group1_socs4[$wb] && $group1_link4[$wb]) ){
                    echo '<figcaption>
                        <ul>';
                    if ( $group1_socs1[$wb] ) { echo '<li><a class="' . $group1_socs1[$wb] . '" target="_blank" rel="nofollow" href="'.$group1_link1[$wb].'"></a></li>'; }
                    if ( $group1_socs2[$wb] ) { echo '<li><a class="' . $group1_socs2[$wb] . '" target="_blank" rel="nofollow" href="'.$group1_link2[$wb].'"></a></li>'; }
                    if ( $group1_socs3[$wb] ) { echo '<li><a class="' . $group1_socs3[$wb] . '" target="_blank" rel="nofollow" href="'.$group1_link3[$wb].'"></a></li>'; }
                    if ( $group1_socs4[$wb] ) { echo '<li><a class="' . $group1_socs4[$wb] . '" target="_blank" rel="nofollow" href="'.$group1_link4[$wb].'"></a></li>'; }
                    echo '</ul>
                    </figcaption>';
                }

                echo '</div>'; // End of playersquad-avatar

                echo '<h2>' . $group1_names[$wb] . '</h2>';

                if( $group1_positions[$wb] ){
                    echo '<span class="position">' . $group1_positions[$wb] . '</span>';
                }

                if( $group1_nationality[$wb] ){
                    echo '<span class="nationality">' . $group1_nationality[$wb] . '</span>';
                }

                if( $group1_txts[$wb] ){
                    echo '<p class="intro">' . $group1_txts[$wb] . '</p>';
                }

                if( !empty($group1_profile_link[$wb]) ) {
                    echo '<a class="profile-link" href="' . $group1_profile_link[$wb] . '">'
                        .$see_profile_text.
                    '</a>';
                }

                echo '</div>
            </div>'; // End of member-inner and member
    }
}

echo '</div><div style="clear:both"></div></div>'; // End of playersquad-entry and clear both



$group2_names = array(
	$params->get('group2-name-1'),
	$params->get('group2-name-2'),
	$params->get('group2-name-3'),
	$params->get('group2-name-4'),
	$params->get('group2-name-5'),
	$params->get('group2-name-6'),
	$params->get('group2-name-7'),
	$params->get('group2-name-8'),
	$params->get('group2-name-9'),
	$params->get('group2-name-10')
);
$group2_positions = array(
	$params->get('group2-pos-1'),
	$params->get('group2-pos-2'),
	$params->get('group2-pos-3'),
	$params->get('group2-pos-4'),
	$params->get('group2-pos-5'),
	$params->get('group2-pos-6'),
	$params->get('group2-pos-7'),
	$params->get('group2-pos-8'),
	$params->get('group2-pos-9'),
	$params->get('group2-pos-10')
);
$group2_nationality = array(
	$params->get('group2-nat-1'),
	$params->get('group2-nat-2'),
	$params->get('group2-nat-3'),
	$params->get('group2-nat-4'),
	$params->get('group2-nat-5'),
	$params->get('group2-nat-6'),
	$params->get('group2-nat-7'),
	$params->get('group2-nat-8'),
	$params->get('group2-nat-9'),
	$params->get('group2-nat-10')
);
$group2_txts = array(
	$params->get('group2-desc-1'),
	$params->get('group2-desc-2'),
	$params->get('group2-desc-3'),
	$params->get('group2-desc-4'),
	$params->get('group2-desc-5'),
	$params->get('group2-desc-6'),
	$params->get('group2-desc-7'),
	$params->get('group2-desc-8'),
	$params->get('group2-desc-9'),
	$params->get('group2-desc-10')
);
$group2_imgs = array(
	$params->get('group2-image-1'),
	$params->get('group2-image-2'),
	$params->get('group2-image-3'),
	$params->get('group2-image-4'),
	$params->get('group2-image-5'),
	$params->get('group2-image-6'),
	$params->get('group2-image-7'),
	$params->get('group2-image-8'),
	$params->get('group2-image-9'),
	$params->get('group2-image-10')
);
$group2_socs1 = array(
	$params->get('group2-sicon1-1'),
	$params->get('group2-sicon1-2'),
	$params->get('group2-sicon1-3'),
	$params->get('group2-sicon1-4'),
	$params->get('group2-sicon1-5'),
	$params->get('group2-sicon1-6'),
	$params->get('group2-sicon1-7'),
	$params->get('group2-sicon1-8'),
	$params->get('group2-sicon1-9'),
	$params->get('group2-sicon1-10')
);
$group2_socs2 = array(
	$params->get('group2-sicon2-1'),
	$params->get('group2-sicon2-2'),
	$params->get('group2-sicon2-3'),
	$params->get('group2-sicon2-4'),
	$params->get('group2-sicon2-5'),
	$params->get('group2-sicon2-6'),
	$params->get('group2-sicon2-7'),
	$params->get('group2-sicon2-8'),
	$params->get('group2-sicon2-9'),
	$params->get('group2-sicon2-10')
);
$group2_socs3 = array(
	$params->get('group2-sicon3-1'),
	$params->get('group2-sicon3-2'),
	$params->get('group2-sicon3-3'),
	$params->get('group2-sicon3-4'),
	$params->get('group2-sicon3-5'),
	$params->get('group2-sicon3-6'),
	$params->get('group2-sicon3-7'),
	$params->get('group2-sicon3-8'),
	$params->get('group2-sicon3-9'),
	$params->get('group2-sicon3-10')
);
$group2_socs4 = array(
	$params->get('group2-sicon4-1'),
	$params->get('group2-sicon4-2'),
	$params->get('group2-sicon4-3'),
	$params->get('group2-sicon4-4'),
	$params->get('group2-sicon4-5'),
	$params->get('group2-sicon4-6'),
	$params->get('group2-sicon4-7'),
	$params->get('group2-sicon4-8'),
	$params->get('group2-sicon4-9'),
	$params->get('group2-sicon4-10')
);
$group2_link1 = array(
	$params->get('group2-link1-1'),
	$params->get('group2-link1-2'),
	$params->get('group2-link1-3'),
	$params->get('group2-link1-4'),
	$params->get('group2-link1-5'),
	$params->get('group2-link1-6'),
	$params->get('group2-link1-7'),
	$params->get('group2-link1-8'),
	$params->get('group2-link1-9'),
	$params->get('group2-link1-10')
);
$group2_link2 = array(
	$params->get('group2-link2-1'),
	$params->get('group2-link2-2'),
	$params->get('group2-link2-3'),
	$params->get('group2-link2-4'),
	$params->get('group2-link2-5'),
	$params->get('group2-link2-6'),
	$params->get('group2-link2-7'),
	$params->get('group2-link2-8'),
	$params->get('group2-link2-9'),
	$params->get('group2-link2-10')
);
$group2_link3 = array(
	$params->get('group2-link3-1'),
	$params->get('group2-link3-2'),
	$params->get('group2-link3-3'),
	$params->get('group2-link3-4'),
	$params->get('group2-link3-5'),
	$params->get('group2-link3-6'),
	$params->get('group2-link3-7'),
	$params->get('group2-link3-8'),
	$params->get('group2-link3-9'),
	$params->get('group2-link3-10')
);
$group2_link4 = array(
	$params->get('group2-link4-1'),
	$params->get('group2-link4-2'),
	$params->get('group2-link4-3'),
	$params->get('group2-link4-4'),
	$params->get('group2-link4-5'),
	$params->get('group2-link4-6'),
	$params->get('group2-link4-7'),
	$params->get('group2-link4-8'),
	$params->get('group2-link4-9'),
	$params->get('group2-link4-10')
);

if( $group2_names[0] ){
    $group2_title = $params->get('group2_title');
} else {
    $group2_title = '';
}

//output
echo '<div class="playersquad-entry">
    <div class="player box'. $set .' rad'. $radius .'">
        <h3 class="group-title">'.$group2_title.'</h3>';

for( $wb = 0; $wb < 10; $wb++ ){
    if( $group2_names[$wb] ){
        echo '<div class="member">
            <div class="member-inner">
                <div class="playersquad-avatar">';
                
                if( $group2_imgs[$wb] ){
                    echo '<img src="' . URI::base() . $group2_imgs[$wb] . '" alt="' . $group2_names[$wb] . '" border="0" />';
                } else {
                    echo '<img src="' . URI::base() . 'modules/mod_playersquad_jt/images/avatar.png" alt="' . $group2_names[$wb] . '" />';
                }
                
                if( ($group2_socs1[$wb] && $group2_link1[$wb]) || ($group2_socs2[$wb] && $group2_link2[$wb]) || ($group2_socs3[$wb] && $group2_link3[$wb]) || ($group2_socs4[$wb] && $group2_link4[$wb]) ){
                    echo '<figcaption>
                        <ul>';
                    if ( $group2_socs1[$wb] ) { echo '<li><a class="' . $group2_socs1[$wb] . '" target="_blank" rel="nofollow" href="'.$group2_link1[$wb].'"></a></li>'; }
                    if ( $group2_socs2[$wb] ) { echo '<li><a class="' . $group2_socs2[$wb] . '" target="_blank" rel="nofollow" href="'.$group2_link2[$wb].'"></a></li>'; }
                    if ( $group2_socs3[$wb] ) { echo '<li><a class="' . $group2_socs3[$wb] . '" target="_blank" rel="nofollow" href="'.$group2_link3[$wb].'"></a></li>'; }
                    if ( $group2_socs4[$wb] ) { echo '<li><a class="' . $group2_socs4[$wb] . '" target="_blank" rel="nofollow" href="'.$group2_link4[$wb].'"></a></li>'; }
                    echo '</ul>
                    </figcaption>';
                }
                
                echo '</div>'; // End of playersquad-avatar
                
                echo '<h2>' . $group2_names[$wb] . '</h2>';
                
                if( $group2_positions[$wb] ){
                    echo '<span class="position">' . $group2_positions[$wb] . '</span>';
                }
                if( $group2_nationality[$wb] ){
                    echo '<span class="nationality">' . $group2_nationality[$wb] . '</span>';
                }
                
                if( $group2_txts[$wb] ){
                    echo '<p class="intro">' . $group2_txts[$wb] . '</p>';
                }
                
                if( !empty($group2_profile_link[$wb]) ) {
                    echo '<a class="profile-link" href="' . $group2_profile_link[$wb] . '">'
                        .$see_profile_text.
                    '</a>';
                }
                
            echo '</div>
        </div>'; // End of member-inner and member
    }
}

echo '</div><div style="clear:both"></div></div>'; // End of playersquad-entry and clear both


$group3_names = array(
	$params->get('group3-name-1'),
	$params->get('group3-name-2'),
	$params->get('group3-name-3'),
	$params->get('group3-name-4'),
	$params->get('group3-name-5'),
	$params->get('group3-name-6'),
	$params->get('group3-name-7'),
	$params->get('group3-name-8'),
	$params->get('group3-name-9'),
	$params->get('group3-name-10')
);
$group3_positions = array(
	$params->get('group3-pos-1'),
	$params->get('group3-pos-2'),
	$params->get('group3-pos-3'),
	$params->get('group3-pos-4'),
	$params->get('group3-pos-5'),
	$params->get('group3-pos-6'),
	$params->get('group3-pos-7'),
	$params->get('group3-pos-8'),
	$params->get('group3-pos-9'),
	$params->get('group3-pos-10')
);
$group3_nationality = array(
	$params->get('group3-nat-1'),
	$params->get('group3-nat-2'),
	$params->get('group3-nat-3'),
	$params->get('group3-nat-4'),
	$params->get('group3-nat-5'),
	$params->get('group3-nat-6'),
	$params->get('group3-nat-7'),
	$params->get('group3-nat-8'),
	$params->get('group3-nat-9'),
	$params->get('group3-nat-10')
);

$group3_txts = array(
	$params->get('group3-desc-1'),
	$params->get('group3-desc-2'),
	$params->get('group3-desc-3'),
	$params->get('group3-desc-4'),
	$params->get('group3-desc-5'),
	$params->get('group3-desc-6'),
	$params->get('group3-desc-7'),
	$params->get('group3-desc-8'),
	$params->get('group3-desc-9'),
	$params->get('group3-desc-10')
);
$group3_imgs = array(
	$params->get('group3-image-1'),
	$params->get('group3-image-2'),
	$params->get('group3-image-3'),
	$params->get('group3-image-4'),
	$params->get('group3-image-5'),
	$params->get('group3-image-6'),
	$params->get('group3-image-7'),
	$params->get('group3-image-8'),
	$params->get('group3-image-9'),
	$params->get('group3-image-10')
);
$group3_socs1 = array(
	$params->get('group3-sicon1-1'),
	$params->get('group3-sicon1-2'),
	$params->get('group3-sicon1-3'),
	$params->get('group3-sicon1-4'),
	$params->get('group3-sicon1-5'),
	$params->get('group3-sicon1-6'),
	$params->get('group3-sicon1-7'),
	$params->get('group3-sicon1-8'),
	$params->get('group3-sicon1-9'),
	$params->get('group3-sicon1-10')
);
$group3_socs2 = array(
	$params->get('group3-sicon2-1'),
	$params->get('group3-sicon2-2'),
	$params->get('group3-sicon2-3'),
	$params->get('group3-sicon2-4'),
	$params->get('group3-sicon2-5'),
	$params->get('group3-sicon2-6'),
	$params->get('group3-sicon2-7'),
	$params->get('group3-sicon2-8'),
	$params->get('group3-sicon2-9'),
	$params->get('group3-sicon2-10')
);
$group3_socs3 = array(
	$params->get('group3-sicon3-1'),
	$params->get('group3-sicon3-2'),
	$params->get('group3-sicon3-3'),
	$params->get('group3-sicon3-4'),
	$params->get('group3-sicon3-5'),
	$params->get('group3-sicon3-6'),
	$params->get('group3-sicon3-7'),
	$params->get('group3-sicon3-8'),
	$params->get('group3-sicon3-9'),
	$params->get('group3-sicon3-10')
);
$group3_socs4 = array(
	$params->get('group3-sicon4-1'),
	$params->get('group3-sicon4-2'),
	$params->get('group3-sicon4-3'),
	$params->get('group3-sicon4-4'),
	$params->get('group3-sicon4-5'),
	$params->get('group3-sicon4-6'),
	$params->get('group3-sicon4-7'),
	$params->get('group3-sicon4-8'),
	$params->get('group3-sicon4-9'),
	$params->get('group3-sicon4-10')
);
$group3_link1 = array(
	$params->get('group3-link1-1'),
	$params->get('group3-link1-2'),
	$params->get('group3-link1-3'),
	$params->get('group3-link1-4'),
	$params->get('group3-link1-5'),
	$params->get('group3-link1-6'),
	$params->get('group3-link1-7'),
	$params->get('group3-link1-8'),
	$params->get('group3-link1-9'),
	$params->get('group3-link1-10')
);
$group3_link2 = array(
	$params->get('group3-link2-1'),
	$params->get('group3-link2-2'),
	$params->get('group3-link2-3'),
	$params->get('group3-link2-4'),
	$params->get('group3-link2-5'),
	$params->get('group3-link2-6'),
	$params->get('group3-link2-7'),
	$params->get('group3-link2-8'),
	$params->get('group3-link2-9'),
	$params->get('group3-link2-10')
);
$group3_link3 = array(
	$params->get('group3-link3-1'),
	$params->get('group3-link3-2'),
	$params->get('group3-link3-3'),
	$params->get('group3-link3-4'),
	$params->get('group3-link3-5'),
	$params->get('group3-link3-6'),
	$params->get('group3-link3-7'),
	$params->get('group3-link3-8'),
	$params->get('group3-link3-9'),
	$params->get('group3-link3-10')
);
$group3_link4 = array(
	$params->get('group3-link4-1'),
	$params->get('group3-link4-2'),
	$params->get('group3-link4-3'),
	$params->get('group3-link4-4'),
	$params->get('group3-link4-5'),
	$params->get('group3-link4-6'),
	$params->get('group3-link4-7'),
	$params->get('group3-link4-8'),
	$params->get('group3-link4-9'),
	$params->get('group3-link4-10')
);
if( $group3_names[0] ){
    $group3_title = $params->get('group3_title');
} else {
    $group3_title = '';
}

//output
echo '<div class="playersquad-entry">
    <div class="player box'. $set .' rad'. $radius .'">
        <h3 class="group-title">'.$group3_title.'</h3>';

for( $wb = 0; $wb < 10; $wb++ ){
    if( $group3_names[$wb] ){
        echo '<div class="member">
            <div class="member-inner">
                <div class="playersquad-avatar">';
                
                if( $group3_imgs[$wb] ){
                    echo '<img src="' . URI::base() . $group3_imgs[$wb] . '" alt="' . $group3_names[$wb] . '" border="0" />';
                } else {
                    echo '<img src="' . URI::base() . 'modules/mod_playersquad_jt/images/avatar.png" alt="' . $group3_names[$wb] . '" />';
                }
                
                if( ($group3_socs1[$wb] && $group3_link1[$wb]) || ($group3_socs2[$wb] && $group3_link2[$wb]) || ($group3_socs3[$wb] && $group3_link3[$wb]) || ($group3_socs4[$wb] && $group3_link4[$wb]) ){
                    echo '<figcaption>
                        <ul>';
                    if ( $group3_socs1[$wb] ) { echo '<li><a class="' . $group3_socs1[$wb] . '" target="_blank" rel="nofollow" href="'.$group3_link1[$wb].'"></a></li>'; }
                    if ( $group3_socs2[$wb] ) { echo '<li><a class="' . $group3_socs2[$wb] . '" target="_blank" rel="nofollow" href="'.$group3_link2[$wb].'"></a></li>'; }
                    if ( $group3_socs3[$wb] ) { echo '<li><a class="' . $group3_socs3[$wb] . '" target="_blank" rel="nofollow" href="'.$group3_link3[$wb].'"></a></li>'; }
                    if ( $group3_socs4[$wb] ) { echo '<li><a class="' . $group3_socs4[$wb] . '" target="_blank" rel="nofollow" href="'.$group3_link4[$wb].'"></a></li>'; }
                    echo '</ul>
                    </figcaption>';
                }
                
                echo '</div>'; // End of playersquad-avatar
                
                echo '<h2>' . $group3_names[$wb] . '</h2>';
                
                if( $group3_positions[$wb] ){
                    echo '<span class="position">' . $group3_positions[$wb] . '</span>';
                }
                
                if( $group3_nationality[$wb] ){
                    echo '<span class="nationality">' . $group3_nationality[$wb] . '</span>';
                }
                
                if( $group3_txts[$wb] ){
                    echo '<p class="intro">' . $group3_txts[$wb] . '</p>';
                }
                
                if( !empty($group3_profile_link[$wb]) ) {
                    echo '<a class="profile-link" href="' . $group3_profile_link[$wb] . '">'
                        .$see_profile_text.
                    '</a>';
                }
                
            echo '</div>
        </div>'; // End of member-inner and member
    }
}

echo '</div><div style="clear:both"></div></div>'; // End of playersquad-entry and clear both

$group4_names = array(
	$params->get('group4-name-1'),
	$params->get('group4-name-2'),
	$params->get('group4-name-3'),
	$params->get('group4-name-4'),
	$params->get('group4-name-5'),
	$params->get('group4-name-6'),
	$params->get('group4-name-7'),
	$params->get('group4-name-8'),
	$params->get('group4-name-9'),
	$params->get('group4-name-10')
);
$group4_positions = array(
	$params->get('group4-pos-1'),
	$params->get('group4-pos-2'),
	$params->get('group4-pos-3'),
	$params->get('group4-pos-4'),
	$params->get('group4-pos-5'),
	$params->get('group4-pos-6'),
	$params->get('group4-pos-7'),
	$params->get('group4-pos-8'),
	$params->get('group4-pos-9'),
	$params->get('group4-pos-10')
);
$group4_nationality = array(
	$params->get('group4-nat-1'),
	$params->get('group4-nat-2'),
	$params->get('group4-nat-3'),
	$params->get('group4-nat-4'),
	$params->get('group4-nat-5'),
	$params->get('group4-nat-6'),
	$params->get('group4-nat-7'),
	$params->get('group4-nat-8'),
	$params->get('group4-nat-9'),
	$params->get('group4-nat-10')
);
$group4_txts = array(
	$params->get('group4-desc-1'),
	$params->get('group4-desc-2'),
	$params->get('group4-desc-3'),
	$params->get('group4-desc-4'),
	$params->get('group4-desc-5'),
	$params->get('group4-desc-6'),
	$params->get('group4-desc-7'),
	$params->get('group4-desc-8'),
	$params->get('group4-desc-9'),
	$params->get('group4-desc-10')
);
$group4_imgs = array(
	$params->get('group4-image-1'),
	$params->get('group4-image-2'),
	$params->get('group4-image-3'),
	$params->get('group4-image-4'),
	$params->get('group4-image-5'),
	$params->get('group4-image-6'),
	$params->get('group4-image-7'),
	$params->get('group4-image-8'),
	$params->get('group4-image-9'),
	$params->get('group4-image-10')
);
$group4_socs1 = array(
	$params->get('group4-sicon1-1'),
	$params->get('group4-sicon1-2'),
	$params->get('group4-sicon1-3'),
	$params->get('group4-sicon1-4'),
	$params->get('group4-sicon1-5'),
	$params->get('group4-sicon1-6'),
	$params->get('group4-sicon1-7'),
	$params->get('group4-sicon1-8'),
	$params->get('group4-sicon1-9'),
	$params->get('group4-sicon1-10')
);
$group4_socs2 = array(
	$params->get('group4-sicon2-1'),
	$params->get('group4-sicon2-2'),
	$params->get('group4-sicon2-3'),
	$params->get('group4-sicon2-4'),
	$params->get('group4-sicon2-5'),
	$params->get('group4-sicon2-6'),
	$params->get('group4-sicon2-7'),
	$params->get('group4-sicon2-8'),
	$params->get('group4-sicon2-9'),
	$params->get('group4-sicon2-10')
);
$group4_socs3 = array(
	$params->get('group4-sicon3-1'),
	$params->get('group4-sicon3-2'),
	$params->get('group4-sicon3-3'),
	$params->get('group4-sicon3-4'),
	$params->get('group4-sicon3-5'),
	$params->get('group4-sicon3-6'),
	$params->get('group4-sicon3-7'),
	$params->get('group4-sicon3-8'),
	$params->get('group4-sicon3-9'),
	$params->get('group4-sicon3-10')
);
$group4_socs4 = array(
	$params->get('group4-sicon4-1'),
	$params->get('group4-sicon4-2'),
	$params->get('group4-sicon4-3'),
	$params->get('group4-sicon4-4'),
	$params->get('group4-sicon4-5'),
	$params->get('group4-sicon4-6'),
	$params->get('group4-sicon4-7'),
	$params->get('group4-sicon4-8'),
	$params->get('group4-sicon4-9'),
	$params->get('group4-sicon4-10')
);
$group4_link1 = array(
	$params->get('group4-link1-1'),
	$params->get('group4-link1-2'),
	$params->get('group4-link1-3'),
	$params->get('group4-link1-4'),
	$params->get('group4-link1-5'),
	$params->get('group4-link1-6'),
	$params->get('group4-link1-7'),
	$params->get('group4-link1-8'),
	$params->get('group4-link1-9'),
	$params->get('group4-link1-10')
);
$group4_link2 = array(
	$params->get('group4-link2-1'),
	$params->get('group4-link2-2'),
	$params->get('group4-link2-3'),
	$params->get('group4-link2-4'),
	$params->get('group4-link2-5'),
	$params->get('group4-link2-6'),
	$params->get('group4-link2-7'),
	$params->get('group4-link2-8'),
	$params->get('group4-link2-9'),
	$params->get('group4-link2-10')
);
$group4_link3 = array(
	$params->get('group4-link3-1'),
	$params->get('group4-link3-2'),
	$params->get('group4-link3-3'),
	$params->get('group4-link3-4'),
	$params->get('group4-link3-5'),
	$params->get('group4-link3-6'),
	$params->get('group4-link3-7'),
	$params->get('group4-link3-8'),
	$params->get('group4-link3-9'),
	$params->get('group4-link3-10')
);
$group4_link4 = array(
	$params->get('group4-link4-1'),
	$params->get('group4-link4-2'),
	$params->get('group4-link4-3'),
	$params->get('group4-link4-4'),
	$params->get('group4-link4-5'),
	$params->get('group4-link4-6'),
	$params->get('group4-link4-7'),
	$params->get('group4-link4-8'),
	$params->get('group4-link4-9'),
	$params->get('group4-link4-10')
);

if( $group4_names[0] ){
    $group4_title = $params->get('group4_title');
} else {
    $group4_title = '';
}

//output
echo '<div class="playersquad-entry">
    <div class="player box'. $set .' rad'. $radius .'">
        <h3 class="group-title">'.$group4_title.'</h3>';

for( $wb = 0; $wb < 10; $wb++ ){
    if( $group4_names[$wb] ){
        echo '<div class="member">
            <div class="member-inner">
                <div class="playersquad-avatar">';
                
                if( $group4_imgs[$wb] ){
                    echo '<img src="' . URI::base() . $group4_imgs[$wb] . '" alt="' . $group4_names[$wb] . '" border="0" />';
                } else {
                    echo '<img src="' . URI::base() . 'modules/mod_playersquad_jt/images/avatar.png" alt="' . $group4_names[$wb] . '" />';
                }
                
                if( ($group4_socs1[$wb] && $group4_link1[$wb]) || ($group4_socs2[$wb] && $group4_link2[$wb]) || ($group4_socs3[$wb] && $group4_link3[$wb]) || ($group4_socs4[$wb] && $group4_link4[$wb]) ){
                    echo '<figcaption>
                        <ul>';
                    if ( $group4_socs1[$wb] ) { echo '<li><a class="' . $group4_socs1[$wb] . '" target="_blank" rel="nofollow" href="'.$group4_link1[$wb].'"></a></li>'; }
                    if ( $group4_socs2[$wb] ) { echo '<li><a class="' . $group4_socs2[$wb] . '" target="_blank" rel="nofollow" href="'.$group4_link2[$wb].'"></a></li>'; }
                    if ( $group4_socs3[$wb] ) { echo '<li><a class="' . $group4_socs3[$wb] . '" target="_blank" rel="nofollow" href="'.$group4_link3[$wb].'"></a></li>'; }
                    if ( $group4_socs4[$wb] ) { echo '<li><a class="' . $group4_socs4[$wb] . '" target="_blank" rel="nofollow" href="'.$group4_link4[$wb].'"></a></li>'; }
                    echo '</ul>
                    </figcaption>';
                }
                
                echo '</div>'; // End of playersquad-avatar
                
                echo '<h2>' . $group4_names[$wb] . '</h2>';
                
                if( $group4_positions[$wb] ){
                    echo '<span class="position">' . $group4_positions[$wb] . '</span>';
                }
                if( $group4_nationality[$wb] ){
                    echo '<span class="nationality">' . $group4_nationality[$wb] . '</span>';
                }
                
                if( $group4_txts[$wb] ){
                    echo '<p class="intro">' . $group4_txts[$wb] . '</p>';
                }
                if( !empty($group4_profile_link[$wb]) ) {
                    echo '<a class="profile-link" href="' . $group4_profile_link[$wb] . '">'
                        .$see_profile_text.
                    '</a>';
                }
                
            echo '</div>
        </div>'; // End of member-inner and member
    }
}

echo '</div><div style="clear:both"></div></div>'; // End of playersquad-entry and clear both


$group5_names = array(
	$params->get('group5-name-1'),
	$params->get('group5-name-2'),
	$params->get('group5-name-3'),
	$params->get('group5-name-4'),
	$params->get('group5-name-5'),
	$params->get('group5-name-6'),
	$params->get('group5-name-7'),
	$params->get('group5-name-8'),
	$params->get('group5-name-9'),
	$params->get('group5-name-10')
);
$group5_positions = array(
	$params->get('group5-pos-1'),
	$params->get('group5-pos-2'),
	$params->get('group5-pos-3'),
	$params->get('group5-pos-4'),
	$params->get('group5-pos-5'),
	$params->get('group5-pos-6'),
	$params->get('group5-pos-7'),
	$params->get('group5-pos-8'),
	$params->get('group5-pos-9'),
	$params->get('group5-pos-10')
);
$group5_nationality = array(
	$params->get('group5-nat-1'),
	$params->get('group5-nat-2'),
	$params->get('group5-nat-3'),
	$params->get('group5-nat-4'),
	$params->get('group5-nat-5'),
	$params->get('group5-nat-6'),
	$params->get('group5-nat-7'),
	$params->get('group5-nat-8'),
	$params->get('group5-nat-9'),
	$params->get('group5-nat-10')
);
$group5_txts = array(
	$params->get('group5-desc-1'),
	$params->get('group5-desc-2'),
	$params->get('group5-desc-3'),
	$params->get('group5-desc-4'),
	$params->get('group5-desc-5'),
	$params->get('group5-desc-6'),
	$params->get('group5-desc-7'),
	$params->get('group5-desc-8'),
	$params->get('group5-desc-9'),
	$params->get('group5-desc-10')
);
$group5_imgs = array(
	$params->get('group5-image-1'),
	$params->get('group5-image-2'),
	$params->get('group5-image-3'),
	$params->get('group5-image-4'),
	$params->get('group5-image-5'),
	$params->get('group5-image-6'),
	$params->get('group5-image-7'),
	$params->get('group5-image-8'),
	$params->get('group5-image-9'),
	$params->get('group5-image-10')
);
$group5_socs1 = array(
	$params->get('group5-sicon1-1'),
	$params->get('group5-sicon1-2'),
	$params->get('group5-sicon1-3'),
	$params->get('group5-sicon1-4'),
	$params->get('group5-sicon1-5'),
	$params->get('group5-sicon1-6'),
	$params->get('group5-sicon1-7'),
	$params->get('group5-sicon1-8'),
	$params->get('group5-sicon1-9'),
	$params->get('group5-sicon1-10')
);
$group5_socs2 = array(
	$params->get('group5-sicon2-1'),
	$params->get('group5-sicon2-2'),
	$params->get('group5-sicon2-3'),
	$params->get('group5-sicon2-4'),
	$params->get('group5-sicon2-5'),
	$params->get('group5-sicon2-6'),
	$params->get('group5-sicon2-7'),
	$params->get('group5-sicon2-8'),
	$params->get('group5-sicon2-9'),
	$params->get('group5-sicon2-10')
);
$group5_socs3 = array(
	$params->get('group5-sicon3-1'),
	$params->get('group5-sicon3-2'),
	$params->get('group5-sicon3-3'),
	$params->get('group5-sicon3-4'),
	$params->get('group5-sicon3-5'),
	$params->get('group5-sicon3-6'),
	$params->get('group5-sicon3-7'),
	$params->get('group5-sicon3-8'),
	$params->get('group5-sicon3-9'),
	$params->get('group5-sicon3-10')
);
$group5_socs4 = array(
	$params->get('group5-sicon4-1'),
	$params->get('group5-sicon4-2'),
	$params->get('group5-sicon4-3'),
	$params->get('group5-sicon4-4'),
	$params->get('group5-sicon4-5'),
	$params->get('group5-sicon4-6'),
	$params->get('group5-sicon4-7'),
	$params->get('group5-sicon4-8'),
	$params->get('group5-sicon4-9'),
	$params->get('group5-sicon4-10')
);
$group5_link1 = array(
	$params->get('group5-link1-1'),
	$params->get('group5-link1-2'),
	$params->get('group5-link1-3'),
	$params->get('group5-link1-4'),
	$params->get('group5-link1-5'),
	$params->get('group5-link1-6'),
	$params->get('group5-link1-7'),
	$params->get('group5-link1-8'),
	$params->get('group5-link1-9'),
	$params->get('group5-link1-10')
);
$group5_link2 = array(
	$params->get('group5-link2-1'),
	$params->get('group5-link2-2'),
	$params->get('group5-link2-3'),
	$params->get('group5-link2-4'),
	$params->get('group5-link2-5'),
	$params->get('group5-link2-6'),
	$params->get('group5-link2-7'),
	$params->get('group5-link2-8'),
	$params->get('group5-link2-9'),
	$params->get('group5-link2-10')
);
$group5_link3 = array(
	$params->get('group5-link3-1'),
	$params->get('group5-link3-2'),
	$params->get('group5-link3-3'),
	$params->get('group5-link3-4'),
	$params->get('group5-link3-5'),
	$params->get('group5-link3-6'),
	$params->get('group5-link3-7'),
	$params->get('group5-link3-8'),
	$params->get('group5-link3-9'),
	$params->get('group5-link3-10')
);
$group5_link4 = array(
	$params->get('group5-link4-1'),
	$params->get('group5-link4-2'),
	$params->get('group5-link4-3'),
	$params->get('group5-link4-4'),
	$params->get('group5-link4-5'),
	$params->get('group5-link4-6'),
	$params->get('group5-link4-7'),
	$params->get('group5-link4-8'),
	$params->get('group5-link4-9'),
	$params->get('group5-link4-10')
);

if( $group5_names[0] ){
    $group5_title = $params->get('group5_title');
} else {
    $group5_title = '';
}

// Output
echo '<div class="playersquad-entry">
    <div class="player box' . $set . ' rad' . $radius . '">
        <h3 class="group-title">' . $group5_title . '</h3>';

for( $wb = 0; $wb < 10; $wb++ ){
    if( $group5_names[$wb] ){
        echo '<div class="member">
            <div class="member-inner">
                <div class="playersquad-avatar">';

        if( $group5_imgs[$wb] ){
            echo '<img src="' . URI::base() . $group5_imgs[$wb] . '" alt="' . $group5_names[$wb] . '" border="0" />';
        } else {
            echo '<img src="' . URI::base() . 'modules/mod_playersquad_jt/images/avatar.png" alt="' . $group5_names[$wb] . '" />';
        }

        if( ($group5_socs1[$wb] && $group5_link1[$wb]) || ($group5_socs2[$wb] && $group5_link2[$wb]) || ($group5_socs3[$wb] && $group5_link3[$wb]) || ($group5_socs4[$wb] && $group5_link4[$wb]) ){
            echo '<figcaption><ul>';
            if( $group5_socs1[$wb] ) { echo '<li><a class="' . $group5_socs1[$wb] . '" target="_blank" rel="nofollow" href="' . $group5_link1[$wb] . '"></a></li>'; }
            if( $group5_socs2[$wb] ) { echo '<li><a class="' . $group5_socs2[$wb] . '" target="_blank" rel="nofollow" href="' . $group5_link2[$wb] . '"></a></li>'; }
            if( $group5_socs3[$wb] ) { echo '<li><a class="' . $group5_socs3[$wb] . '" target="_blank" rel="nofollow" href="' . $group5_link3[$wb] . '"></a></li>'; }
            if( $group5_socs4[$wb] ) { echo '<li><a class="' . $group5_socs4[$wb] . '" target="_blank" rel="nofollow" href="' . $group5_link4[$wb] . '"></a></li>'; } 
            echo '</ul></figcaption>';
        }

        echo '</div>'; // Closing .playersquad-avatar

        echo '<h2>' . $group5_names[$wb] . '</h2>';

        if( $group5_positions[$wb] ){
            echo '<span class="position">' . $group5_positions[$wb] . '</span>';
        }
        if( $group5_nationality[$wb] ){
            echo '<span class="nationality">' . $group5_nationality[$wb] . '</span>';
        }

        if( $group5_txts[$wb] ){
            echo '<p class="intro">' . $group5_txts[$wb] . '</p>';
        }
        if( !empty($group5_profile_link[$wb]) ) {
            echo '<a class="profile-link" href="' . $group5_profile_link[$wb] . '">' . $see_profile_text . '</a>';
        }

        echo '</div></div>'; // Closing .member-inner and .member
    }
}

echo '</div><div style="clear:both"></div></div>'; // Closing .player and .playersquad-entry


?>
