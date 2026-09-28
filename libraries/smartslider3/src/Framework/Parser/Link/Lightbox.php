<?php
/**
 * @required N2SSPRO
 */

namespace Nextend\Framework\Parser\Link;

use Nextend\Framework\Asset\Predefined;
use Nextend\Framework\Platform\Platform;
use Nextend\Framework\ResourceTranslator\ResourceTranslator;
use Nextend\SmartSlider3\Settings;

class Lightbox implements ParserInterface {

    public function parse($argument, &$attributes) {
        if (!empty($argument)) {

            $attributes['data-n2-lightbox'] = '';

            if (!isset($attributes['class'])) $attributes['class'] = '';
            $attributes['class'] .= " n2-lightbox-trigger nolightbox no-lightbox";

            Predefined::loadLiteBox();

            $realUrls = array();
            $alts     = array();
            $titles   = array();

            //JSON V2 storage
            if ($argument[0] == '{') {
                $data = json_decode($argument, true);

                if (!empty($data['urls'])) {

                    $urls = array_values(array_filter($data['urls'], function ($url) {
                        return trim($url) !== '';
                    }));

                    if (!empty($urls)) {

                        $firstUrl                           = array_shift($urls);
                        $firstUrlParts                      = explode('|||', $firstUrl);
                        $attributes['data-n2-lightbox']     = ResourceTranslator::toUrl($firstUrlParts[0]);
                        $attributes['data-n2-lightbox-alt'] = !empty($firstUrlParts[1]) ? $firstUrlParts[1] : '';

                        if (isset($data['titles'][0])) {
                            $title = array_shift($data['titles']);
                            if (!empty($title)) {
                                $attributes['data-title'] = $title;
                            }
                        }

                        if ($data['autoplay'] > 0) {
                            $attributes['data-autoplay'] = intval($data['autoplay']);
                        }

                        for ($i = 0; $i < count($urls); $i++) {
                            if (!empty($urls[$i])) {
                                $parts      = explode('|||', $urls[$i]);
                                $realUrls[] = ResourceTranslator::toUrl($parts[0]);
                                $alts[]     = !empty($parts[1]) ? $parts[1] : '';
                                $titles[]   = !empty($data['titles'][$i]) ? $data['titles'][$i] : '';
                            }
                        }
                        $attributes['data-n2-lightbox-urls']   = implode(',', $realUrls);
                        $attributes['data-n2-lightbox-alts']   = implode('|||', $alts);
                        $attributes['data-n2-lightbox-titles'] = implode('|||', $titles);
                        if (count($realUrls)) {
                            $attributes['data-litebox-group'] = md5(uniqid(mt_rand(), true));
                        }
                    }
                }
            } else {

                $urls                           = explode(',', $argument);
                $parts                          = explode(';', array_shift($urls), 2);
                $attributes['data-n2-lightbox'] = ResourceTranslator::toUrl($parts[0]);
                if (!empty($parts[1])) {
                    $attributes['data-title'] = $parts[1];
                }

                if (count($urls)) {
                    if (intval($urls[count($urls) - 1]) > 0) {
                        $attributes['data-autoplay'] = intval(array_pop($urls));
                    }
                    for ($i = 0; $i < count($urls); $i++) {
                        if (!empty($urls[$i])) {
                            $parts      = explode(';', $urls[$i], 2);
                            $realUrls[] = ResourceTranslator::toUrl($parts[0]);
                            $titles[]   = !empty($parts[1]) ? $parts[1] : '';
                        }
                    }
                    $attributes['data-n2-lightbox-urls']   = implode(',', $realUrls);
                    $attributes['data-n2-lightbox-titles'] = implode('|||', $titles);
                    if (count($realUrls)) {
                        $attributes['data-litebox-group'] = md5(uniqid(mt_rand(), true));
                    }
                }
            }
        }
        $attributes['role'] = 'button';

        $attributes['data-privacy'] = intval(Settings::get('youtube-privacy-enhanced', 0));

        return '#';
    }
}