<?php

namespace Grav\Plugin;

use \Grav\Common\Grav;
use \Twig_Extension;
use \Twig_SimpleFunction;

/**
 * ARRAY OF CUSTOM TWIG FUNCTIONS
 */

class WebpackTwigFunction extends Twig_Extension
{

    protected $mode;

    public function __construct()
    {
        $this->mode = Grav::instance()['config']['plugins.webpackdevtools']['mode'];
    }

    /**
     * RETURNS EXTENSION NAME
     */

    public function getName()
    {
        return 'WebpackTwigFunction';
    }

    /**
     * REGISTER TWIG FUNCTIONS
     */

    public function getFunctions()
    {
        return [
          new Twig_SimpleFunction('webpack_asset', [$this, 'webpackAssets'], ['is_safe' => ['html']])
        ];
    }

    /**
    * WEBPACK ASSETS FUNCTION
    *
    * @param $file [string] the asset file name
    *
    * @param $inline [boolean] 'true' if inline (default is 'false')
    */

    public function webpackAssets($file, $inline = false)
    {
        $stringParts = explode('.', $file);
        $file_name = $stringParts[0];

        if (preg_match('/(\.css|\.js)$/i', $file)) {
            $file_ext = $stringParts[1];
        } else {
            $file_ext = null;
        }

        /**
         * If environment is development
         */

        if ($this->mode == 'development') {
            $dev_path = 'http://localhost:3000/assets/js/';
            $asset_path = $dev_path . $file_name . '.js';

            return '<script src="' . $asset_path . '" async></script>';
        }

        /**
         * If environment is production
         */

        if ($this->mode == 'production') {
            $webpack_assets =  'theme://assets/webpack-assets.json';
            if (file_exists($webpack_assets)) {
                $assets_array = json_decode(file_get_contents($webpack_assets), true);
                $asset_path = $assets_array[$file_name];

                // If inline is true
                if ($inline) {
                    // If file extension is null
                    if (!$file_ext) {
                        return;
                    // If file extension is css
                    } elseif ($file_ext == 'css') {
                        return '<style>' . file_get_contents('theme://' . $asset_path['css']) . '</style>';
                    // If file extension is js
                    } elseif ($file_ext == 'js') {
                        return '<script>' . file_get_contents('theme://' . $asset_path['js']) . '</script>';
                    }
                // If inline is false
                } else {
                    // If file extension is null
                    if (!$file_ext) {
                        return '<link rel="stylesheet" href="theme://' . $asset_path['css'] . '"><script src="theme://' . $asset_path['js'] . '" async></script>';
                    // If file extension is css
                    } elseif ($file_ext == 'css') {
                        return '<link rel="stylesheet" href="theme://' . $asset_path['css'] . '">';
                    // If file extension is js
                    } elseif ($file_ext == 'js') {
                        return '<script src="theme://' . $asset_path['js'] . '" async></script>';
                    }
                }
            }
        }
    }
}
