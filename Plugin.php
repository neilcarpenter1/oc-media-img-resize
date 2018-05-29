<?php namespace Neilcarpenter1\Ocmediaimgresize;

use Backend;
use System\Classes\PluginBase;
use Config;

/**
 * Mediathumb Plugin Information File
 */
class Plugin extends PluginBase
{
    /**
     * Returns information about this plugin.
     *
     * @return array
     */
    public function pluginDetails()
    {
        return [
            'name'        => 'ocmediaimageresize',
            'description' => 'Resize and crop media Library image files',
            'author'      => 'Neil Carpenter',
            'icon'        => 'icon-compress',
            'homepage'    => 'https://github.com/neilcarpenter1/october-mediathumb'
        ];
    }
    
    public function registerMarkupTags()
    {
        return [
            'filters' => [
                'media_resize' => ['Neilcarpenter1\Ocmediaimgresize\Classes\Resizer', 'resizeimage']
            ]
        ];
    }
}