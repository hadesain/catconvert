<?php


include_once('Catconvert_LifeCycle.php');

class Catconvert_Plugin extends Catconvert_LifeCycle {

    /**
     * See: http://plugin.michael-simpson.com/?page_id=31
     * @return array of option meta data.
     */
    public function getOptionMetaData() {
        //  http://plugin.michael-simpson.com/?page_id=31
        return array(
            //'_version' => array('Installed Version'), // Leave this one commented-out. Uncomment to test upgrades.
            'LinkText' => array(__('Enter the text the link should display, default is \'Download as mp3\'', 'catconvert')),
            'LinkPosition' => array(__('Select the position where the link should be displayed under the video', 'catconvert'), 'Left', 'Right'),
            'LinkCssClass' => array(__('Enter the css class to decorate the a-href html tag', 'catconvert')),
            'ContainerCssClass' => array(__('Enter the css class to decorate the div containing a-href html tag', 'catconvert')),
            'Categories' => array(__('Enter the blog-post categorie(s) separated by a \',\' on which the plugin will run', 'catconvert'))
        );
    }

//    protected function getOptionValueI18nString($optionValue) {
//        $i18nValue = parent::getOptionValueI18nString($optionValue);
//        return $i18nValue;
//    }

    protected function initOptions() {
        $options = $this->getOptionMetaData();
        if (!empty($options)) {
            foreach ($options as $key => $arr) {
                if (is_array($arr) && count($arr > 1)) {
                    $this->addOption($key, $arr[1]);
                }
            }
        }
    }

    public function getPluginDisplayName() {
        return 'Catconvert';
    }

    protected function getMainPluginFileName() {
        return 'catconvert.php';
    }

    /**
     * See: http://plugin.michael-simpson.com/?page_id=101
     * Called by install() to create any database tables if needed.
     * Best Practice:
     * (1) Prefix all table names with $wpdb->prefix
     * (2) make table names lower case only
     * @return void
     */
    protected function installDatabaseTables() {
        //        global $wpdb;
        //        $tableName = $this->prefixTableName('mytable');
        //        $wpdb->query("CREATE TABLE IF NOT EXISTS `$tableName` (
        //            `id` INTEGER NOT NULL");
    }

    /**
     * See: http://plugin.michael-simpson.com/?page_id=101
     * Drop plugin-created tables on uninstall.
     * @return void
     */
    protected function unInstallDatabaseTables() {
        //        global $wpdb;
        //        $tableName = $this->prefixTableName('mytable');
        //        $wpdb->query("DROP TABLE IF EXISTS `$tableName`");
    }


    /**
     * Perform actions when upgrading from version X to version Y
     * See: http://plugin.michael-simpson.com/?page_id=35
     * @return void
     */
    public function upgrade() {
    }

    public function addActionsAndFilters() {

        // Add options administration page
        // http://plugin.michael-simpson.com/?page_id=47
        add_action('admin_menu', array(&$this, 'addSettingsSubMenuPage'));

        // Example adding a script & style just for the options administration page
        // http://plugin.michael-simpson.com/?page_id=47
        //        if (strpos($_SERVER['REQUEST_URI'], $this->getSettingsSlug()) !== false) {
        //            wp_enqueue_script('my-script', plugins_url('/js/my-script.js', __FILE__));
        //            wp_enqueue_style('my-style', plugins_url('/css/my-style.css', __FILE__));
        //        }


        // Add Actions & Filters
        // http://plugin.michael-simpson.com/?page_id=37
        //add_filter( 'embed_oembed_html', array(&$this, 'add_catconvert_buttons'), 99999 , 4 );
        add_filter( 'the_content', array(&$this, 'add_catconvert_buttons'), 99999999);


        // Adding scripts & styles to all pages
        // Examples:
        //        wp_enqueue_script('jquery');
        //        wp_enqueue_style('my-style', plugins_url('/css/catconvert-style.css', __FILE__));
        //        wp_enqueue_script('my-script', plugins_url('/js/my-script.js', __FILE__));

        wp_enqueue_style('catconvert-style', plugins_url('/css/catconvert-style.css', __FILE__));

        // Register short codes
        // http://plugin.michael-simpson.com/?page_id=39


        // Register AJAX hooks
        // http://plugin.michael-simpson.com/?page_id=41

    }


    function add_catconvert_buttons( $html ){
        // check if html is empty.
        if(empty($html)){
            return $html;
        }

        $categories_enabled = $this->getOption('Categories');

        if(isset($categories_enabled) && $categories_enabled != ''){
            // check if category is supported
            $categories_enabled = explode(",", $categories_enabled);
            $categories = get_the_category();
            $separator = ' ';
            $output = '';
            $is_enabled = false;
            if($categories){
                foreach($categories as $category) {
                    if(in_array($category->name, $categories_enabled)){
                        $is_enabled = true;
                    }
                }

                if(!$is_enabled){
                    return $html;
                }
            }
        }

        $linkCssClass = $this->getOption('LinkCssClass');
        $linkPosition = $this->getOption('LinkPosition');
        $linkText = $this->getOption('LinkText');
        $containerCssClass = $this->getOption('ContainerCssClass');

        $linkPosition = isset($linkPosition) && $linkPosition != '' ? $linkPosition : 'Left';
        $linkText = isset($linkText) && $linkText != '' ? $linkText : 'Download as mp3';
        $linkCssClass = isset($linkCssClass) && $linkCssClass != '' ? $linkCssClass : 'catconvert-default-btn';
        $containerCssClass = isset($containerCssClass) && $containerCssClass != '' ? $containerCssClass : 'catconvert-default-container';
        $containerPosistionCssClass = $linkPosition == 'Left' ? 'catconvert-default-container-position-left' : 'catconvert-default-container-position-right';

        $containerCssClass = $containerCssClass . ' ' . $containerPosistionCssClass;

        $dom = new DomDocument();
        libxml_use_internal_errors(true);

        $html = mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8');

        $dom->loadHtml($html);
        libxml_clear_errors();

        $xpath = new DomXpath($dom);
        $iframes = $xpath->query("//iframe[contains(@src,'youtube')]");

        // support for
        // - Default Wordpress behavior without any wordpress plugin,
        // - Smart Youtube PRO
        // - YouTube
        // - Advanced YouTube Embed by Embed Plus
        foreach ($iframes as $iframe) {
            $url = $iframe->getAttribute('src');
            $videoId = $this->get_youtube_id_from_url($url);

            $containerElement = $dom->createElement('div');
            $containerClassAttribute = $this->createAttribute($dom, 'class', $containerCssClass);
            $containerElement->appendChild($containerClassAttribute);

            $linkElement = $dom->createElement('a', $linkText);
            $linkClassAttribute = $this->createAttribute($dom, 'class', $linkCssClass);
            $linkElement->appendChild($linkClassAttribute);

            $linkNoFollowAttribute = $this->createAttribute($dom, 'rel', 'nofollow');
            $linkElement->appendChild($linkNoFollowAttribute);

            $linkTargetAttribute = $this->createAttribute($dom, 'target', '_blank');
            $linkElement->appendChild($linkTargetAttribute);

            $catconvertUrl = "http://www.catconvert.com/en/?url=http://www.youtube.com/watch?v=".$videoId;
            $linkHrefAttribute = $this->createAttribute($dom, 'href', $catconvertUrl);
            $linkElement->appendChild($linkHrefAttribute);

            $containerElement->appendChild($linkElement);

            if($iframe->parentNode->nodeName != 'object'){
                $iframe->parentNode->appendChild($containerElement);
            }else{
                $iframe->parentNode->parentNode->appendChild($containerElement);
            }
        }

        // support for
        // - viper plugin
        $iframes = $xpath->query("//span[contains(@class,'vvqbox')]/span/a  ");
        foreach ($iframes as $iframe) {
            $url = $iframe->getAttribute('href');
            $videoId = $this->get_youtube_id_from_url($url);

            $containerElement = $dom->createElement('div');
            $containerClassAttribute = $this->createAttribute($dom, 'class', $containerCssClass);
            $containerStyleAttribute = $this->createAttribute($dom, 'style', 'margin-top: -7px;');
            $containerElement->appendChild($containerStyleAttribute);
            $containerElement->appendChild($containerClassAttribute);

            $linkElement = $dom->createElement('a', $linkText);
            $linkClassAttribute = $this->createAttribute($dom, 'class', $linkCssClass);
            $linkElement->appendChild($linkClassAttribute);

            $linkNoFollowAttribute = $this->createAttribute($dom, 'rel', 'nofollow');
            $linkElement->appendChild($linkNoFollowAttribute);

            $linkTargetAttribute = $this->createAttribute($dom, 'target', '_blank');
            $linkElement->appendChild($linkTargetAttribute);

            $catconvertUrl = "http://www.catconvert.com/en/?url=http://www.youtube.com/watch?v=".$videoId;
            $linkHrefAttribute = $this->createAttribute($dom, 'href', $catconvertUrl);
            $linkElement->appendChild($linkHrefAttribute);

            $containerElement->appendChild($linkElement);

            $iframe->parentNode->parentNode->appendChild($containerElement);
        }

        return utf8_decode($dom->saveHTML());
    }

    function createAttribute($dom, $name, $value){
        $attribute = $dom->createAttribute($name);
        $attribute->value = $value;
        return $attribute;
    }

    function get_youtube_id_from_url($url)
    {
        if (preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $url, $match)) {
            return $match[1];
        }
    }
}
