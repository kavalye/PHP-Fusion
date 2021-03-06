<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: home.php
| Author: Chubatyj Vitalij (Rizado)
| Co-Author: Takács Ákos (Rimelek)
| Co-Author: Frederick MC Chan (Chan)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
require_once INCLUDES."infusions_include.php";

class HomePanel {

    protected static $locale = array();
    protected static $configs = array();
    protected static $contents = array();
    protected static $latest_content = array();
    protected static $popular_content = array();
    protected static $featured_content = array();
    private static $instance = NULL;

    /**
     * @return object
     */
    public static function getInstance() {
        if (self::$instance === NULL) {
            self::$instance = new static();
            self::$instance->setInfo();
        }

        return (object)self::$instance;
    }

    /**
     * Sets the necessary variables
     */
    public function setInfo() {
        self::$locale = fusion_get_locale('', INFUSIONS."home_panel/locale/".LANGUAGE.".php");
        self::setConfig();
        self::setContent();
    }

    /**
     * Execute Configs
     */
    private static function setConfig() {

        self::$configs[DB_NEWS] = array(
            'select' => "SELECT
                        ns.news_id as id, ns.news_subject as title, ns.news_news as content,
                        ns.news_datestamp as datestamp, us.user_id, us.user_name,
                        ns.news_sticky as sticky,
                        us.user_status, nc.news_cat_id as cat_id, nc.news_cat_name as cat_name,
                        ni.news_image as image,
                        nc.news_cat_image as cat_image,
                        count(c1.comment_id) as comment_count,
                        count(r1.rating_id) as rating_count
                        FROM ".DB_NEWS." as ns
                        LEFT JOIN ".DB_NEWS_IMAGES." ni ON ni.news_id=ns.news_id AND ns.news_image_front_default=ni.news_image_id
                        LEFT JOIN ".DB_NEWS_CATS." as nc ON nc.news_cat_id = ns.news_cat
                        LEFT JOIN ".DB_COMMENTS." as c1 ON (c1.comment_item_id = ns.news_id and c1.comment_type = 'N')
                        LEFT JOIN ".DB_RATINGS." as r1 ON (r1.rating_item_id = ns.news_id AND r1.rating_type = 'N')
                        INNER JOIN ".DB_USERS." as us ON ns.news_name = us.user_id
                        WHERE (".time()." > ns.news_start OR ns.news_start = 0)
                        AND (".time()." < ns.news_end OR ns.news_end = 0)
                        AND ".groupaccess('ns.news_visibility')." ".(multilang_table("NS") ? "AND news_language='".LANGUAGE."'" : "")."
                        group by ns.news_id
                        ORDER BY ns.news_sticky DESC, ns.news_datestamp DESC, comment_count DESC LIMIT 15",
            'locale' => array(
                'norecord' => self::$locale['home_0050'],
                'blockTitle' => self::$locale['home_0000'],
            ),
            'infSettings' => get_settings("news"),
            'categoryLinkPattern' => INFUSIONS."news/news.php?cat_id={cat_id}",
            'contentLinkPattern' => INFUSIONS."news/news.php?readmore={id}",
        );

        self::$configs[DB_ARTICLES] = array(
            'select' => "SELECT
                        ar.article_id as id, ar.article_subject as title, ar.article_snippet as content,
                        ar.article_datestamp as datestamp, ac.article_cat_id as cat_id, ac.article_cat_name as cat_name,
                        us.user_id, us.user_name, us.user_status
                        FROM ".DB_ARTICLES." as ar
                        INNER JOIN ".DB_ARTICLE_CATS." as ac ON ac.article_cat_id = ar.article_cat
                        INNER JOIN ".DB_USERS." as us ON us.user_id = ar.article_name
                        WHERE 
							ar.article_draft='0' AND ac.article_cat_status='1' AND ".groupaccess('ar.article_visibility')." AND ".groupaccess('ac.article_cat_visibility')."
							".(multilang_table("AR") ? "AND ac.article_cat_language='".LANGUAGE."' AND ar.article_language='".LANGUAGE."'" : "")."
                        ORDER BY ar.article_datestamp DESC LIMIT 10",
            'locale' => array(
                'norecord' => self::$locale['home_0051'],
                'blockTitle' => self::$locale['home_0001'],
            ),
            'infSettings' => get_settings("article"),
            'categoryLinkPattern' => INFUSIONS."articles/articles.php?cat_id={cat_id}",
            'contentLinkPattern' => INFUSIONS."articles/articles.php?article_id={id}",
        );

        self::$configs[DB_BLOG] = array(
            'select' => "SELECT
                        bl.blog_id as id, bl.blog_subject as title, bl.blog_blog as content,
                        bl.blog_datestamp as datestamp, us.user_id, us.user_name,
                        bl.blog_sticky as sticky,
                        us.user_status, bc.blog_cat_id as cat_id, bc.blog_cat_name as cat_name,
                        bl.blog_image as image,
                        bc.blog_cat_image as cat_image,
                        count(c1.comment_id) as comment_count,
                        count(r1.rating_id) as rating_count
                        FROM ".DB_BLOG." as bl
                        LEFT JOIN ".DB_BLOG_CATS." as bc ON bc.blog_cat_id = bl.blog_cat
                        LEFT JOIN ".DB_COMMENTS." as c1 on (c1.comment_item_id = bl.blog_id and c1.comment_type = 'BL')
                        LEFT JOIN ".DB_RATINGS." as r1 on (r1.rating_item_id = bl.blog_id AND r1.rating_type = 'BL')
                        INNER JOIN ".DB_USERS." as us ON bl.blog_name = us.user_id
                        WHERE (".time()." > bl.blog_start OR bl.blog_start = 0)
                        AND (".time()." < bl.blog_end OR bl.blog_end = 0)
                        AND ".groupaccess('bl.blog_visibility')." ".(multilang_table("BL") ? "AND blog_language='".LANGUAGE."'" : "")."
                        group by bl.blog_id
                        ORDER BY bl.blog_sticky DESC, bl.blog_datestamp DESC LIMIT 10",
            'locale' => array(
                'norecord' => self::$locale['home_0052'],
                'blockTitle' => self::$locale['home_0002']
            ),
            'infSettings' => get_settings("blog"),
            'categoryLinkPattern' => INFUSIONS."blog/blog.php?cat_id={cat_id}",
            'contentLinkPattern' => INFUSIONS."blog/blog.php?readmore={id}",
        );

        self::$configs[DB_DOWNLOADS] = array(
            'select' => "SELECT
                        dl.download_id as id, dl.download_title as title, dl.download_description_short as content,
                        dl.download_datestamp as datestamp, dc.download_cat_id as cat_id, dc.download_cat_name as cat_name,
                        us.user_id, us.user_name, us.user_status,
                        dl.download_image as image,
                        count(c1.comment_id) as comment_count,
                        count(r1.rating_id) as rating_count
                        FROM ".DB_DOWNLOADS." dl
                        INNER JOIN ".DB_DOWNLOAD_CATS." dc ON dc.download_cat_id = dl.download_cat
                        INNER JOIN ".DB_USERS." us ON us.user_id = dl.download_user
                        LEFT JOIN ".DB_COMMENTS." as c1 on (c1.comment_item_id = dl.download_id and c1.comment_type = 'D')
                        LEFT JOIN ".DB_RATINGS." as r1 on (r1.rating_item_id = dl.download_id AND r1.rating_type = 'D')
                        WHERE ".groupaccess('dl.download_visibility')." ".(multilang_table("DL") ? "AND dc.download_cat_language='".LANGUAGE."'" : "")."
                        group by dl.download_id
                        ORDER BY dl.download_datestamp DESC LIMIT 10",
            'locale' => array(
                'norecord' => self::$locale['home_0053'],
                'blockTitle' => self::$locale['home_0003']
            ),
            'infSettings' => get_settings("downloads"),
            'categoryLinkPattern' => DOWNLOADS."downloads.php?cat_id={cat_id}",
            'contentLinkPattern' => DOWNLOADS."downloads.php?download_id={id}",
        );

    }
    /**
     * Execute Parsing of Configs
     */
    private static function setContent() {

        // Default format for Latest, Popular and Featured
        $default_item = array(
            'title' => '',
            'url' => '',
            'image' => '',
            'content' => self::$locale['home_0107'],
            'meta' => '',
            'datestamp' => 0,
            'comment_count' => 0,
        );

        foreach (self::$configs as $table => $config) {

            if (!db_exists($table)) {
                continue;
            }

            self::$contents[$table] = array(
                'data' => array(),
                'colwidth' => 0,
                'norecord' => $config['locale']['norecord'],
                'blockTitle' => $config['locale']['blockTitle'],
                'infSettings' => $config['infSettings']
            );

            $result = dbquery($config['select']);

            $items_count = dbrows($result);

            if (!$items_count) {
                continue;
            }

            self::$contents[$table]['colwidth'] = floor(12 / $items_count);

            $data = array();

            $count = 1;

            while ($row = dbarray($result)) {
                $keys = array_keys($row);
                foreach ($keys as $i => $key) {
                    $keys[$i] = '{'.$key.'}';
                }
                $row['content'] = str_replace("../../images", IMAGES, $row['content']);
                $pairs = array_combine($keys, array_values($row));
                $cat = $row['cat_id'] ? "<a href='".strtr($config['categoryLinkPattern'],
                                                          $pairs)."'>".$row['cat_name']."</a>" : self::$locale['home_0102'];
                $data[$count] = array(
                    'cat' => $cat,
                    'url' => strtr($config['contentLinkPattern'], $pairs),
                    'title' => $row['title'],
                    'image' => isset($row['image']) ? $row['image'] : '',
                    'meta' => self::$locale['home_0105'].profile_link($row['user_id'], $row['user_name'],
                                                                      $row['user_status'])." ".showdate('newsdate',
                                                                                                        $row['datestamp']).self::$locale['home_0106'].$cat,
                    'content' => parse_textarea($row['content']),
                    'datestamp' => $row['datestamp'],
                    'cat_name' => $row['cat_name'],
                );

                /* Infusion Settings Readings */
                switch ($table) {
                    case DB_NEWS:
                        if ($config['infSettings']['news_image_frontpage']) { // if it's 0 use uploaded photo, 1 always use category image
                            // go for cat image always
                            if ($row['cat_image']) {
                                $data[$count]['image'] = INFUSIONS."news/news_cats/".$row['cat_image'];
                            }
                        } else {
                            // go for image if available
                            if ($row['image'] || $row['cat_image']) {
                                if ($row['cat_image']) {
                                    $data[$count]['image'] = INFUSIONS."news/news_cats/".$row['cat_image'];
                                }
                                if ($row['image']) {
                                    $data[$count]['image'] = INFUSIONS."news/images/".$row['image'];
                                }
                            }
                        }
                        break;
                    case DB_BLOG:
                        if ($row['image'] || $row['cat_image']) {
                            if ($row['cat_image']) {
                                $data[$count]['image'] = INFUSIONS."blog/blog_cats/".$row['cat_image'];
                            }
                            if ($row['image']) {
                                $data[$count]['image'] = INFUSIONS."blog/images/".$row['image'];
                            }
                        }
                        break;
                    case DB_DOWNLOADS:
                        if ($row['image']) {
                            $data[$count]['image'] = INFUSIONS."downloads/images/".$row['image'];
                        }
                    // end switch
                }

                // Aggregate Latest
                if (isset($row['datestamp'])) {
                    $data[$count]['datestamp'] = $row['datestamp'];
                    $current_latest = $data[$count];
                    $current_latest += $default_item;
                    self::$latest_content[] = $current_latest;
                }

                // Aggregate Most Popular
                if (isset($row['comment_count'])) {
                    $data[$count]['comment_count'] = $row['comment_count'];
                    $current_popular = $data[$count];
                    $current_popular += $default_item;
                    self::$popular_content[] = $current_popular;
                }

                // Aggregate Featured
                if (isset($row['sticky']) && $row['sticky'] == 1) {
                    $data[$count]['datestamp'] = $row['datestamp'];
                    $current_featured = $data[$count];
                    $current_featured += $default_item;
                    self::$featured_content[] = $current_featured;
                }
                $count++;
            }

            self::$contents[$table]['data'] = $data;
        }

        if (!empty(self::$latest_content)) {
            $items = sorter(self::$latest_content, 'datestamp', 'DESC');
            $items = array_values($items);
            self::$latest_content = $items;
        } else {
            self::$latest_content[0] = $default_item;
        }

        if (!empty(self::$popular_content)) {
            // popular content
            $items = sorter(self::$popular_content, 'comment_count', 'DESC');
            $items = array_values($items);
            self::$popular_content = $items;
        } else {
            self::$popular_content[0] = $default_item;
        }

        if (!empty(self::$featured_content)) {
            $items = sorter(self::$featured_content, 'datestamp', 'DESC');
            $items = array_values($items);
            self::$popular_content = $items;
        } else {
            self::$featured_content[0] = $default_item;
        }

    }

    /**
     * Get the latest content by datestamp
     * @return array
     */
    public static function get_latestContent() {
        return (array)self::$latest_content;

    }

    /**
     * Get the most viewed content
     * @return array
     */
    public static function get_popularContent() {
        return (array)self::$popular_content;
    }

    /**
     * Get the latest sticky content
     * @return array
     */
    public static function get_featuredContent() {
        return (array)self::$featured_content;
    }

    /**
     * Displays parsed information
     */
    public function display_panel() {
        require_once INFUSIONS."home_panel/templates/home_panel.php";
        display_home(self::$contents);
    }

}