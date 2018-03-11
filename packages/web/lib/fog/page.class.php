<?php
/**
 * The page display/modifier
 *
 * PHP version 5
 *
 * @category Page
 * @package  FOGProject
 * @author   Tom Elliott <tommygunsster@gmail.com>
 * @license  http://opensource.org/licenses/gpl-3.0 GPLv3
 * @link     https://fogproject.org
 */
/**
 * The page display/modifier
 *
 * @category Page
 * @package  FOGProject
 * @author   Tom Elliott <tommygunsster@gmail.com>
 * @license  http://opensource.org/licenses/gpl-3.0 GPLv3
 * @link     https://fogproject.org
 */
class Page extends FOGBase
{
    /**
     * The title element.
     *
     * @var string
     */
    protected $title;
    /**
     * The body container
     *
     * @var string
     */
    protected $body;
    /**
     * The menu container
     *
     * @var mixed
     */
    protected $menu;
    /**
     * The media container
     *
     * @var mixed
     */
    protected $media;
    /**
     * The theme container
     *
     * @var mixed
     */
    protected $theme;
    /**
     * If this is homepage
     *
     * @var bool
     */
    protected $isHomepage;
    /**
     * The page title container
     *
     * @var string
     */
    protected $pageTitle;
    /**
     * The section title container
     *
     * @var string
     */
    protected $sectionTitle;
    /**
     * The stylesheets to add
     *
     * @var array
     */
    protected $stylesheets = array();
    /**
     * The javascripts to add
     *
     * @var array
     */
    protected $javascripts = array();
    /**
     * Initializes the page element
     *
     * @return void
     */
    public function __construct()
    {
        global $node;
        global $sub;
        parent::__construct();
        if (!$this->theme) {
            $this->theme = self::getSetting('FOG_THEME');
            if (!$this->theme) {
                $this->theme = 'default/fog.css';
            } elseif (!file_exists("../management/css/$this->theme")) {
                $this->theme = 'default/fog.css';
            }
            $dispTheme = "css/$this->theme";
            $this->imagelink = 'css/'
                . dirname($this->theme)
                . '/images/';
            if (!file_exists("../management/$dispTheme")) {
                $dispTheme = 'css/default/fog.css';
            }
        }
        $this
            ->addCSS('css/bootstrap.min.css')
            ->addCSS('css/font-awesome.min.css')
            ->addCSS('css/select2.min.css')
            ->addCSS('css/ionicons.min.css')
            ->addCSS('css/datatables.min.css')
            ->addCSS('css/slider.css')
            ->addCSS('css/pnotify.min.css')
            ->addCSS('css/icheck-square-blue.css')
            ->addCSS('css/animate.css')
            ->addCSS('css/pace.min.css')
            ->addCSS('css/AdminLTE.min.css')
            ->addCSS('css/adminlte-skins.min.css')
            ->addCSS('css/font.css');
        if (!isset($node)
            || !$node
        ) {
            $node = 'home';
        }
        $homepages = array(
            'home',
            'dashboard',
            'schema',
            'client',
            'ipxe',
            'login',
            'logout'
        );
        $this->isHomepage = in_array($node, $homepages)
            || !self::$FOGUser->isValid();
        FOGPage::buildMainMenuItems($this->menu);
        $files = array(
            'js/jquery.min.js',
            'js/lodash.min.js',
            'js/bootstrap.min.js',
            'js/bootstrap-slider.min.js',
            'js/fastclick.js',
            'js/Flot/jquery.flot.js',
            'js/Flot/jquery.flot.resize.js',
            'js/Flot/jquery.flot.pie.js',
            'js/Flot/jquery.flot.time.js',
            'js/select2.full.min.js',
            'js/jquery.slimscroll.min.js',
            'js/adminlte.min.js',
            'js/datatables.min.js',
            'js/icheck.min.js',
            'js/bootbox.min.js',
            'js/pnotify.min.js',
            'js/pace.min.js',
            'js/input-mask/jquery.inputmask.js',
            'js/input-mask/jquery.inputmask.extensions.js',
            'js/input-mask/jquery.inputmask.regex.extensions.js',
            'js/input-mask/jquery.inputmask.numeric.extensions.js',
            'js/input-mask/jquery.inputmask.date.extensions.js',
            'js/bootstrap-slider/bootstrap-slider.js',
            'js/fog/fog.common.js',
        );
        if (!self::$FOGUser->isValid()) {
            $files[] = 'js/fog/fog.login.js';
        } else {
            $subset = $sub;
            $node = preg_replace(
                '#_#',
                '-',
                $node
            );
            $subset = preg_replace(
                '#_#',
                '-',
                $subset
            );
            $filepaths = [];
            if (empty($subset)) {
                $filepaths = ["js/fog/{$node}/fog.{$node}.js"];
            } else {
                $filepaths = ["js/fog/{$node}/fog.{$node}.{$subset}.js"];
            }
        }
        array_map(
            function (&$jsFilepath) use (&$files) {
                if (file_exists($jsFilepath)) {
                    array_push($files, $jsFilepath);
                }
                unset($jsFilepath);
            },
            (array)$filepaths
        );
        if ($this->isHomepage
            && self::$FOGUser->isValid()
            && ($node == 'home'
            || !$node)
        ) {
            array_push($files, 'js/fog/dashboard/fog.dashboard.js');
            $test = preg_match(
                '#MSIE [6|7|8|9|10|11]#',
                self::$useragent
            );
            if ($test) {
                array_push(
                    $files,
                    'js/flot/excanvas.js'
                );
            }
        }
        if ($node === 'schema') {
            array_push($files, 'js/fog/schema/fog.schema.js');
        }
        self::$HookManager->processEvent(
            'PAGE_JS_FILES',
            ['files' => &$files]
        );
        $files = array_unique((array)$files);
        array_map(
            function (&$path) {
                if (file_exists($path)) {
                    $this->addJavascript($path);
                }
                unset($path);
            },
            (array)$files
        );
    }
    /**
     * Sets the title
     *
     * @param string $title the title to set
     *
     * @return object
     */
    public function setTitle($title)
    {
        $this->pageTitle = $title;
        return $this;
    }
    /**
     * Sets the section title
     *
     * @param string $title the title to set
     *
     * @return object
     */
    public function setSecTitle($title)
    {
        $this->sectionTitle = $title;
        return $this;
    }
    /**
     * Adds a css path
     *
     * @param string $path the path to add
     *
     * @return object
     */
    public function addCSS($path)
    {
        $this->stylesheets[] = "../management/$path";
        return $this;
    }
    /**
     * Adds a javascript path
     *
     * @param string $path the path to add
     *
     * @return object
     */
    public function addJavascript($path)
    {
        $this->javascripts[] = $path;
        return $this;
    }
    /**
     * Starts the body
     *
     * @return object
     */
    public function startBody()
    {
        ob_start();
        return $this;
    }
    /**
     * Ends the body
     *
     * @return object
     */
    public function endBody()
    {
        $this->body = ob_get_clean();
        return $this;
    }
    /**
     * Renders the index page
     *
     * @return object
     */
    public function render()
    {
        if (true === self::$showhtml) {
            include '../management/other/index.php';
        } else {
            echo $this->body;
            exit;
        }
        foreach (array_keys(get_defined_vars()) as $var) {
            unset($$var);
        }
        return $this;
    }
}
