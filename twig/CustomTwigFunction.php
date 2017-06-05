<?php

namespace Grav\Plugin;

use \Grav\Common\Grav;
use \Twig_Extension;
use \Twig_SimpleFunction;

/**
 * [Short description]
 *
 * [Long description]
 *
 * Class WebpackTwigFunction
 * @package Grav\Plugin
 * @return string|void Webpack assets
 * @license MIT License by YourNameHere
 */
class WebpackTwigFunction extends Twig_Extension
{

    /**
     * Environment-configuration
     * @var string
     */
    protected $mode;

    /**
     * Instantiate WebpackTwigFunction-class
     */
    public function __construct()
    {
        $this->mode = Grav::instance()['config']['plugins.webpackdevtools']['mode'];
    }

    /**
     * Declare extension name for backwards-compatibility
     * @return string Extension name
     */
    public function getName()
    {
        return 'WebpackTwigFunction';
    }

    /**
     * Returns webpack_asset-function
     * @return Twig_SimpleFunction
     */
    public function getFunctions()
    {
        return [
          new Twig_SimpleFunction('webpack_asset', [$this, 'webpackAssets'], ['is_safe' => ['html']])
        ];
    }
    /**
     * Add Webpack assets
     * @param string $file   [description]
     * @param boolean $inline [description]
     * @return string|void [description]
     */
    public function webpackAssets($file, $inline = false)
    {
        $locator = Grav::instance()['locator'];
        $pathParts = pathinfo($file);
        $filename = $pathParts['filename'];
        if (isset($pathParts['extension'])) {
            $fileExt = $pathParts['extension'];
        }

        /* Check environments */
        if ($this->mode == 'development') {
            $devPath = 'http://localhost:3000/assets/js/';
            $assetPath = $devPath . $filename . '.js';

            return '<script src="' . $assetPath . '" async></script>';
        } elseif ($this->mode == 'production') {
            $webpackAssets = $locator->findResource('theme://assets/webpack-assets.json', true);
            if (file_exists($webpackAssets)) {
                $assetsArray = json_decode(file_get_contents($webpackAssets), true);
                $assetPath = $assetsArray[$filename];
                $themePath = $locator->findResource('theme://', false);
                Grav::instance()['debugger']->addMessage($themePath);
                $css = $locator->findResource('theme://' . $assetPath['css'], true);
                $js = $locator->findResource('theme://' . $assetPath['js'], true);
                if ($inline) {
                    if (!$fileExt) {
                        return;
                    } elseif ($fileExt == 'css') {
                        return '<style>' . file_get_contents($css) . '</style>';
                    } elseif ($fileExt == 'js') {
                        return '<script>' . file_get_contents($js) . '</script>';
                    }
                } else {
                    if (!isset($fileExt)) {
                        // Grav::instance()['assets']->addCss('theme://' . $assetPath['css']);
                        // Grav::instance()['assets']->addJs('theme://' . $assetPath['js']);
                        $cssReference = '<link type="text/css" href="' . $themePath . $assetPath['css'] . '" />';
                        $jsReference = '<script type="text/javascript" src="' . $themePath . $assetPath['js'] . '"></script>';
                        return $cssReference . $jsReference;
                    }
                }
            }
        }
    }
}
