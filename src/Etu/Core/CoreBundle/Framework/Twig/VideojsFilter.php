<?php

namespace Etu\Core\CoreBundle\Framework\Twig;

use Decoda\Decoda;
use Decoda\Filter\AbstractFilter;

/**
 * Provides the tag for video.js, for self hosted videos
 */
class VideojsFilter extends AbstractFilter
{

    /**
     * Regex pattern.
     */
    const SIZE_PATTERN = '/^(?:small|medium|large)$/i';
    const VIDEO_PATTERN = '/^([\/\w \.-:]*)+\.mp4$/is';

    /**
     * Supported tags.
     *
     * @type array
     */
    protected $_tags = array(
        'videojs' => array(
            'template' => 'videojs',
            'displayType' => Decoda::TYPE_BLOCK,
            'allowedTypes' => Decoda::TYPE_NONE,
            'contentPattern' => self::VIDEO_PATTERN,
            'attributes' => array(
                'size' => self::SIZE_PATTERN
            )
        )
    );

    /**
     * Video formats.
     *
     * @type array
     */
    protected $_formats = array(
        'videojs' => array(
            'small' => array(400, 225),
            'medium' => array(520, 292),
            'large' => array(725, 405),
        ),
    );

    /**
     * Custom build the HTML for videos.
     *
     * @param array $tag
     * @param string $content
     * @return string
     */
    public function parse(array $tag, $content)
    {
        $size = mb_strtolower(isset($tag['attributes']['size']) ? $tag['attributes']['size'] : 'medium');
        $video = $this->_formats['videojs'];
        $size = isset($video[$size]) ? $video[$size] : $video['medium'];

        $tag['attributes']['width'] = $size[0];
        $tag['attributes']['height'] = $size[1];
        $tag['attributes']['url']=$content;
        $tag['attributes']['type']='video/mp4';
        return parent::parse($tag, $content);
    }
}
