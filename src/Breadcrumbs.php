<?php

namespace Crumby\Breadcrumbs;

class Breadcrumbs
{
    const SEO_ITEMPROP = 'itemprop';
    const SEO_JSON_LD = 'json-ld';
    
    /**
     * The array which will store all of our breadcrumbs.
     *
     * @var array
     */
    protected $breadcrumbs = array();

    /**
     * Classes applied to the main `<ul>` container element.
     *
     * @var array
     */
    protected $breadcrumbsCssClasses = array();

    /**
     * The divider symbol between the breadcrumbs. Uses a slash as a default,
     * since that's the Twitter Bootstrap style.
     *
     * @var string
     */
    protected $divider = '/';

    /**
     * The DOM-element that wraps the breadcrumbs. Set to ul by default.
     */
    protected $listElement = 'ul';

    /**
     * Divider configuration 
     */
    protected $useDivider = true;
    
    /**
     * SEO configuration 
     * false, 'itemprop', 'json-ld'
     */
    protected $useSeo = false;
    protected $allowSeo = [false, self::SEO_ITEMPROP, self::SEO_JSON_LD];
    
    /**
     * Localization configuration
     * true|false
     */
    protected $translate = false;
    
     /**
     * Routes metadat configuration
     * array
     */
    protected $routes = [];
    
    
    /**
     * The class constructor. Accepts an optional array of breadcrumbs, and an
     * optional array of CSS classes to be applied to the container element.
     *
     * @param array $breadcrumbs
     * @param array $cssClasses
     */
    public function __construct($breadcrumbs = array(), $cssClasses = array())
    {
        $this->setBreadcrumbs($breadcrumbs);

        if (empty($cssClasses)) {
            $this->setCssClasses('breadcrumb');
        } else {
            $this->setCssClasses($cssClasses);
        }
    }

    /**
     * Sets all the breadcrumbs. Useful for quickly configuring the instance.
     *
     * @param array $breadcrumbs
     * @return $this
     */
    public function setBreadcrumbs($breadcrumbs)
    {
        if (!is_array($breadcrumbs)) {
            throw new \InvalidArgumentException(
                'Breadcrumbs::setBreadcrumbs() only accepts arrays, but '
                . (is_object($breadcrumbs) ? get_class($breadcrumbs) : gettype($breadcrumbs))
                . ' given: ' . print_r($breadcrumbs, true)
            );
        }

        foreach ($breadcrumbs as $key => $breadcrumb) {
            if (!static::isValidCrumb($breadcrumb)) {
                throw new \InvalidArgumentException(
                    'Breadcrumbs::setBreadcrumbs() only accepts correctly formatted arrays, but at least one of the '
                    . 'values was misformed: $breadcrumbs[' . $key . '] = ' . print_r($breadcrumb, true)
                );
            } else {
                $this->addCrumb(
                    $breadcrumb['name'] ?: '',
                    $breadcrumb['href'] ?: '',
                    isset($breadcrumb['hrefIsFullUrl']) ? (bool) $breadcrumb['hrefIsFullUrl'] : false
                );
            }
        }

        return $this;
    }

    /**
     * Adds a crumb to the internal array.
     *
     * @param string  $name          The name of this breadcrumb, which will be
     *                               seen by the users.
     * @param string  $href          If this parameter begins with a forward
     *                               slash, it will be treated as a full URL,
     *                               and the `$hrefIsFullUrl` parameter will be
     *                               forced to `true`, regardless of its value.
     * @param boolean $hrefIsFullUrl Whether the `$href` argument is a full URL
     *                               or just a segment. The difference is that
     *                               segments will be built upon previous
     *                               breadcrumbs, while full URLs will be
     *                               returned as they are inputted. This can be
     *                               automatically forced to `true`, depending
     *                               on the `$href` argument - read its
     *                               description for details.
     * @return $this
     */
    public function addCrumb($name = '', $href = '', $hrefIsFullUrl = false)
    {
        if (mb_substr($href, 0, 1) === '/') {
            $length = mb_strlen($href);
            $href = mb_substr($href, 1, $length - 1);
            $this->addCrumb($name, $href, true);
        } elseif ((mb_substr($href, 0, 7) === 'http://') && !$hrefIsFullUrl) {
            $this->addCrumb($name, $href, true);
        } elseif ((mb_substr($href, 0, 8) === 'https://') && !$hrefIsFullUrl) {
            $this->addCrumb($name, $href, true);
        } else {
            $crumb = array(
                'name' => $name,
                'href' => $href,
                'hrefIsFullUrl' => $hrefIsFullUrl,
            );

            $this->breadcrumbs[] = $crumb;
        }

        return $this;
    }


    /**
     * Adds a crumb to the internal array.
     *
     * Alias for `Breadcrumbs::addCrumb` method.
     *
     * @param string  $name          The name of this breadcrumb, which will be
     *                               seen by the users.
     * @param string  $href          If this parameter begins with a forward
     *                               slash, it will be treated as a full URL,
     *                               and the `$hrefIsFullUrl` parameter will be
     *                               forced to `true`, regardless of its value.
     * @param boolean $hrefIsFullUrl Whether the `$href` argument is a full URL
     *                               or just a segment. The difference is that
     *                               segments will be built upon previous
     *                               breadcrumbs, while full URLs will be
     *                               returned as they are inputted. This can be
     *                               automatically forced to `true`, depending
     *                               on the `$href` argument - read its
     *                               description for details.
     * @return $this
     */
    public function add($name = '', $href = '', $hrefIsFullUrl = false)
    {
        return $this->addCrumb($name, $href, $hrefIsFullUrl);
    }


    /**
     * Checks whether a crumb is valid, so that it can safely be added to the
     * internal breadcrumbs array.
     *
     * @param array $crumb
     * @return boolean
     */
    public static function isValidCrumb($crumb)
    {
        if (!is_array($crumb)) {
            return false;
        }

        if (!isset($crumb['name'], $crumb['href'])) {
            return false;
        }

        if (!is_string($crumb['name']) || !is_string($crumb['href'])) {
            return false;
        }

        if (empty($crumb['name']) || empty($crumb['href'])) {
            return false;
        }

        return true;
    }

    /**
     * Sets the CSS classes to be applied to the containing `<ul>` element. Can
     * be passed a string or an array. If passed a string, separate CSS classes
     * should be separated with spaces.
     *
     * @param string|array $cssClasses
     * @return $this
     */
    public function setCssClasses($cssClasses)
    {
        if (is_string($cssClasses)) {
            $cssClasses = explode(' ', $cssClasses);
        }

        if (!is_array($cssClasses)) {
            throw new \InvalidArgumentException(
                'Breadcrumbs::setCssClasses() only accepts strings or arrays, but '
                . (is_object($cssClasses) ? get_class($cssClasses) : gettype($cssClasses))
                . ' given: ' . print_r($cssClasses, true)
            );
        }

        foreach ($cssClasses as $key => $cssClass) {
            if (!is_string($cssClass)) {
                throw new \InvalidArgumentException(
                    'Breadcrumbs::setCssClasses() was passed an array, but at least one of the values was not a '
                    . 'string: $cssClasses[' . $key . '] = ' . print_r($cssClass, true)
                );
            }
        }

        $this->breadcrumbsCssClasses = array_unique($cssClasses);

        return $this;
    }

    /**
     * Adds more CSS classes which will be applied to the containing `<ul>`
     * element. Can be passed a string or an array. If passed a string, separate
     * CSS classes should be separated with spaces.
     *
     * @param string|array $breadcrumbsCssClasses
     * @return $this
     */
    public function addCssClasses($cssClasses)
    {
        if (is_string($cssClasses)) {
            $cssClasses = explode(' ', $cssClasses);
        }

        if (!is_array($cssClasses)) {
            throw new \InvalidArgumentException(
                'Breadcrumbs::addCssClasses() only accepts strings or arrays, but '
                . (is_object($cssClasses) ? get_class($cssClasses) : gettype($cssClasses))
                . ' given: ' . print_r($cssClasses, true)
            );
        }

        foreach ($cssClasses as $key => $cssClass) {
            if (!is_string($cssClass)) {
                throw new \InvalidArgumentException(
                    'Breadcrumbs::addCssClasses() was passed an array, but at least one of the values was not a '
                    . 'string: $cssClasses[' . $key . '] = ' . print_r($cssClass, true)
                );
            }
        }

        $cssClasses = array_merge(
            $this->breadcrumbsCssClasses,
            $cssClasses
        );

        $this->breadcrumbsCssClasses = array_unique($cssClasses);

        return $this;
    }

    /**
     * Removes one or more CSS classes that have been set by other methods. This
     * method won't fail if the passed class has not been set already.
     *
     * @param string|array $cssClasses
     * @return $this
     */
    public function removeCssClasses($cssClasses)
    {
        if (is_string($cssClasses)) {
            $cssClasses = explode(' ', $cssClasses);
        }

        if (!is_array($cssClasses)) {
            throw new \InvalidArgumentException(
                'Breadcrumbs::removeCssClasses() only accepts strings or arrays, but '
                . (is_object($cssClasses) ? get_class($cssClasses) : gettype($cssClasses))
                . ' given: ' . print_r($cssClasses, true)
            );
        }

        foreach ($cssClasses as $key => $cssClass) {
            if (!is_string($cssClass)) {
                throw new \InvalidArgumentException(
                    'Breadcrumbs::removeCssClasses() was passed an array, but at least one of the values was not a '
                    . 'string: $cssClasses[' . $key . '] = ' . print_r($cssClass, true)
                );
            }
        }

        $cssClasses = array_diff(
            $this->breadcrumbsCssClasses,
            $cssClasses
        );

        $this->breadcrumbsCssClasses = array_unique($cssClasses);

        return $this;
    }

    /**
     * Gets the currently configured breadcrumbs CSS classes.
     *
     * @return array
     */
    public function getBreadcrumbsCssClasses()
    {
        return $this->breadcrumbsCssClasses;
    }

    /**
     * Sets the divider which will be printed between the breadcrumbs.
     *
     * If set to `null`, the divider won't be printed at all.
     *
     * @param string $divider
     * @return $this
     */
    public function setDivider($divider)
    {
        if (!is_string($divider) && !is_null($divider)) {
            throw new \InvalidArgumentException(
                'Breadcrumbs::setDivider() only accepts strings or NULL, but '
                . (is_object($divider) ? get_class($divider) : gettype($divider))
                . ' given: ' . print_r($divider, true)
            );
        }

        $this->divider = $divider;

        return $this;
    }

    /**
     * Gets the divider currently in use.
     *
     * @return string
     */
    public function getDivider()
    {
        return $this->divider;
    }

    /**
     * Set the containing list DOM element
     *
     * @param string $element
     * @return $this
     */
    public function setListElement($element)
    {
        if (!is_string($element)) {
            throw new \InvalidArgumentException(
                'Breadcrumbs::setListElement() only accepts strings, but '
                . (is_object($element) ? get_class($element) : gettype($element))
                . ' given: ' . print_r($element, true)
            );
        }

        $this->listElement = $element;

        return $this;
    }

    /**
     * Gets the currently configured breadcrumbs.
     *
     * @return array
     */
    public function getBreadcrumbs()
    {
        return $this->breadcrumbs;
    }

    /**
     * Gets the current amount of breadcrumbs
     *
     * @return int
     */
    public function count()
    {
        return count($this->breadcrumbs);
    }

    /**
     * Checks whether there are any breadcrumbs added yet.
     *
     * @return boolean
     */
    public function isEmpty()
    {
        return $this->count() === 0;
    }

    /**
     * Removes all breadcrumbs.
     *
     * @return $this
     */
    public function removeAll()
    {
        $this->breadcrumbs = array();

        return $this;
    }

    /**
     * Renders a single breadcrumb, Twitter Bootstrap-style.
     *
     * @param string $name
     * @param string $href
     * @param boolean $isLast
     * @param number $position
     * @return string
     */
    protected function renderCrumb($name, $href, $isLast = false, $position = null)
    {
        if ($this->divider && $this->isUseDivider()) {
            $divider = " <span class=\"divider\">{$this->divider}</span>";
        } else {
            $divider = '';
        }

        if ($position != null && ($this->getUseSeo() === self::SEO_ITEMPROP)) {
            $positionMeta = "<meta itemprop=\"position\" content=\"{$position}\" />";
        } else {
            $positionMeta = "";
        }

        if ($this->getUseSeo() === self::SEO_ITEMPROP) {
            if (!$isLast) {
            return '<li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem" >'
                . "<a itemprop=\"item\" href=\"{$href}\"><span itemprop=\"name\">{$name}</span></a>"
                . "{$positionMeta}{$divider}</li>";
            } else {
                return '<li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem" '
                    . "class=\"active\"><span itemprop=\"name\">{$name}</span>"
                    . "{$positionMeta}</li>";
            }
        }
        else {
            if (!$isLast) {
                return "<li><a href=\"{$href}\">{$name}</a>{$divider}</li>";
            } else {
                return "<li class=\"active\">{$name}</li>";
            }
        }
    }

    /**
     * Renders the crumbs one by one, and returns them concatenated.
     *
     * @return string
     */
    protected function renderCrumbs()
    {
        end($this->breadcrumbs);
        $lastKey = key($this->breadcrumbs);

        $output = '';

        $hrefSegments = array();

        $position = 1;

        foreach ($this->breadcrumbs as $key => $crumb) {
            $isLast = ($lastKey === $key);

            if ($crumb['hrefIsFullUrl']) {
                $hrefSegments = array();
            }

            if ($crumb['href']) {
                $hrefSegments[] = $crumb['href'];
            }

            $href = implode('/', $hrefSegments);

            if (!preg_match('#^https?://.*#', $href)) {
                $href = "/{$href}";
            }

            $output .= $this->renderCrumb($crumb['name'], $href, $isLast, $position);
            $position++;
        }

        return $output;
    }

    /**
     * Renders the complete breadcrumbs into Twitter Bootstrap-compatible HTML.
     *
     * @return string
     */
    public function render()
    {
        if (empty($this->breadcrumbs)) {
            return '';
        }

        $cssClasses = implode(' ', $this->breadcrumbsCssClasses);
        
        if ($this->getUseSeo() === self::SEO_ITEMPROP) {
            return '<'. $this->listElement . ' itemscope itemtype="http://schema.org/BreadcrumbList"'
                    .' class="' . $cssClasses .'">'
                    . $this->renderCrumbs()
                    . '</'. $this->listElement .'>';
        }
        else {
            return "<{$this->listElement} class=\"{$cssClasses}\">"
                    . $this->renderCrumbs()
                    . "</{$this->listElement}>";
        }
    }

    /**
     * `__toString` magic method.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->render();
    }
    
    /**
     * Set is use divider.
     * 
     * @param type $useDivider
     * @return \Crumby\Breadcrumbs\Breadcrumbs
     */
    public function setUseDivider($useDivider=true) {
        $this->useDivider = $useDivider;
        return $this;
    }
    
    /**
     * Set is use divider.
     * 
     * @return boolean 
     */
    public function isUseDivider() {
        return $this->useDivider;
    }
    
    /**
     * Set is use Seo during output, if use - in what format.
     * 
     * @param type $useSeo
     * @return \Crumby\Breadcrumbs\Breadcrumbs
     */
    public function setUseSeo($useSeo=false) {
        if (!in_array($useSeo, $this->allowSeo)) {
            throw new \InvalidArgumentException("Use SEO argument {$useSeo} is invalid");
        }
        $this->useSeo = $useSeo;
        return $this;
    }
    
    /**
     * Return use Seo.
     * 
     * @return false, 'itemprop', 'json-ld'
     */
    public function getUseSeo() {
        return $this->useSeo;
    }
    
    /**
     * Set required or not Label translation
     * @param type $translate is translation requred
     * @return \Crumby\Breadcrumbs\Breadcrumbs
     */
    public function setTranslate($translate=false) {
        $this->translate = $translate;
        return $this;
    }
    
    /**
     * Return if translation.
     * 
     * @return boolean
     */
    public function isTranslate() {
        return $this->translate;
    }
    
    /**
     * Sets routes data from config file
     * @param array $routes
     */
    public function setRoutes($routes) {
        $this->routes = $routes;
    }
    
    /**
     * Gets routes data from config file
     * @param string $name Route name set 'as' in \Route 
     */
    public function getRouteByName($name) {
        if (isset($this->routes[$name])) {
            return $this->routes[$name];
        }
        return false;
    }
    
    /**
     * Gets routes data from config file
     * @param string $routeName Route name set 'as' in \Route 
     * @param string $propName The route breadcrumbs property  set in config/crumby-crumbs/route-breadcrumbs.php
     * @return mixed|null  Null - if the proprty is not set
     */
    public function getRouteProperty($routeName, $propName) {
        if (isset($this->routes[$routeName][$attrName])) {
            return $this->routes[$routeName][$attrName];
        }
        return null;
    }
    
    /**
     * Gets routes binded model property name, to resolve breadcrumbs Label
     * @param string $name Route name set 'as' in \Route 
     * @return string|false
     */
    public function getRouteModelProp($name) {
        if (isset($this->routes[$name]['bind']['modelProp'])) {
            return $this->routes[$name]['bind']['modelProp'];
        }
        return false;
    }
    
    /**
     * Gets the route expected dynamic param name form config, binded to model
     * @param string $name Route name set 'as' in \Route 
     * @return string|false
     */
    public function getRouteParam($name) {
        if (isset($this->routes[$name]['bind']['param'])) {
            return $this->routes[$name]['bind']['param'];
        }
        return false;
    }
    
    /**
     * Gets routes 'childOf' ( parent ) from config file.
     * @param string $name Route name set 'as' in \Route 
     * @return string|false
     */
    public function getRouteParentName($name) {
        if (isset($this->routes[$name]['childOf']['route'])) {
            return $this->routes[$name]['childOf']['route'];
        }
        return false;
    }
    
  
    public function addCrumbRecurcive($routeName = null) {  
        if (is_null($routeName)) {
            $routeName = \Route::current()->getName();
        }
        if ($parentRouteName = $this->getRouteParentName($routeName)) {
            $this->addCrumbRecurcive($parentRouteName); 
        } 
        
        $route = \Route::getRoutes()->getByName($routeName)->uri();
       
        if ($routeResolved = \RouteResolver::resolveRouteItem($route, null, true)) {
            $route = $routeResolved['url'];
        }
 
        $this->addCrumb($this->getLabel($routeName, $this->isTranslate()), \URL::to($route));
    }
    
    /**
     * Returns current route Display name: 
     * - it is translated by route 'as' key value.
     * - OR default route 'label' key value
     * - for translation value of config/breadcrumbs.php 'translate' nkey set to true
     * @return string The route label, set in web.php/api.php as route 'label'
     */
    protected function getLabel($routeName, $translate = false) {
        /*
         * Check if the route haves dynamic parameter.
         * Expected to be translated already.
         */
        if ($route = \Route::getRoutes()->getByName($routeName)->uri()) {
            if ($routeResolved = \RouteResolver::resolveRouteItem($route)) {
                return $routeResolved['label'];
            }
        }
        
        /**
         * Regular static route. Will use translation form file.
         */
        if ($route = \Route::getRoutes()->getByName($routeName)) {
            $action = $route->getAction();
   
            $fallBackLabel = '';
            if (isset($action['label'])) {
                // fallback Label
                $fallBackLabel = $action['label'];  
            }
   
            // if no translation requred return Label, set in route by ket 'label'
            if ($translate === false) {  
                return $fallBackLabel;
            }

            if($translatedLabel = trans('breadcrumbs.' . $routeName)) {
                return $translatedLabel;
            }
            else {
                return $fallBackLabel;
            }
        }
        
        return $routeName;
    }
   
    /**
     * Returns array breadcrumbs presentation.
     * 
     * @return [] | false
     */
    public function toArray() {
        if ($breadcrumbs = $this->getBreadcrumbs()) {
            $schema = [
                '@type' => 'BreadcrumbList',
                'itemListElement' => [],
            ];
            $position = 1;
            foreach ($breadcrumbs as $breadcrumb) {
                $schema['itemListElement'][] = [
                    '@type' => 'ListItem',
                    'position' => $position++,
                    'item' => [
                        '@id' => \URL::to($breadcrumb['href']),
                        'name' => $breadcrumb['name'],
                    ],
                ];
            }
            return $schema;
        }
        
        return false;   
    }
  
    /**
     * Return JSON-LD Seo presentation.
     * 
     * @return json-ld structure | empty string
     */
    public function getJsonLdSeo() {
        if ($this->useSeo === self::SEO_JSON_LD) {
            if($breadcrumbs = $this->toArray())
            {
                return json_encode($breadcrumbs);
            }
        }
        
        return false;
        
    }
    
    /**
     * Load Breadcrumbs configuration
     */
    public function loadConfig() {
        $useDiv = config('crumby-crumbs.breadcrumbs.useDivider');
        $cssClass = config('crumby-crumbs.breadcrumbs.cssClass');
        $useSeo = config('crumby-crumbs.breadcrumbs.useSeo');
        $translate = config('crumby-crumbs.breadcrumbs.translate');
        $routes = config('crumby-crumbs.route-breadcrumbs.routes');
        $this->setUseDivider($useDiv);
        $this->setUseSeo($useSeo);
        $this->setTranslate($translate);
        $this->addCssClasses($cssClass);
        $this->setRoutes($routes);
    }
}
