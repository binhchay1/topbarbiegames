<?php
class Admin_Form
{
    const ID = 'config-seo';

    public function init()
    {
        add_action('admin_menu', array($this, 'add_menu_pages'), 1);
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
        add_action('wp_ajax_save_category', array($this, 'save_category'));
        add_action('wp_ajax_save_general', array($this, 'save_general'));
    }

    public function get_id()
    {
        return self::ID;
    }

    public function admin_enqueue_scripts($hook_suffix)
    {
        if (strpos($hook_suffix, $this->get_id()) === false) {
            return;
        }

        wp_enqueue_media();

        wp_enqueue_style('config-admin-form-bs', 'https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css', BINHCHAY_ADMIN_VERSION);
        wp_enqueue_script(
            'config-admin-form-bs',
            'https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js',
            array('jquery'),
            BINHCHAY_ADMIN_VERSION,
            true
        );

        wp_enqueue_script(
            'config-admin-form-bs',
            'https://code.jquery.com/jquery-3.7.1.slim.js'
        );

        echo '
        <style>
            .button-submit {
                border: 1px solid black !important;
            }

            .post-title {
                font-weight: bold !important;
                font-size: 19px !important;
            }

            #alert-post {
                display: none;
            }
        </style>';
    }

    function add_menu_pages()
    {
        add_menu_page('For SEO', 'For SEO', 10, $this->get_id() . '_general', array(&$this, 'load_view_general'), plugins_url('binhchay/images/icon.png'));
        add_submenu_page($this->get_id() . '_general', 'General', 'General', 10,  $this->get_id() . '_general', array(&$this, 'load_view_general'));
        add_submenu_page($this->get_id() . '_general', 'Set Category', 'Set Category', 10,  $this->get_id() . '_set_category', array(&$this, 'load_view_set_category'));
    }

    public function load_view_general()
    {
        $h1Homepage = get_option('h1_homepage');
        $shortDescriptionHomepage = get_option('short_description_homepage');
        $nonce = wp_create_nonce("get_game_nonce");
        $link = admin_url('admin-ajax.php');

        echo '<script>
            let dataGeneral = {
                h1: "",
                description: "",
            };';
        if ($shortDescriptionHomepage) {
            echo 'dataGeneral.description = `' . $shortDescriptionHomepage . '`;';
        }
        echo '</script>';
        echo '<script src="https://cdn.ckeditor.com/4.22.1/standard/ckeditor.js"></script>';
        echo '<div class="container mt-5">';
        echo "<div class='alert' role='alert' id='alert-post'></div>";
        echo '<h3>Setup H1 in homepage</h3>';
        if ($h1Homepage) {
            echo '<input class="form-control" value="' . $h1Homepage . '" id="h1-home-page">';
        } else {
            echo '<input class="form-control" id="h1-home-page">';
        }
        echo '<h3 class="mt-4">Setup Short description in homepage</h3>';
        echo '<span style="margin-top: 10px;">
        <input class="form-control" type="text" id="short_description_homepage" value="' . $shortDescriptionHomepage . '"></input>
        </span>';
        echo '<script>
        let short_description_homepage = jQuery("#short_description_homepage");
        short_description_homepage.on("change", function(evt) {
            dataGeneral.description = short_description_homepage.val();
        });
        </script>';
        echo '<button class="btn btn-primary mt-4" type="button" id="save-general">Save</button>';
        echo '</div>';

        echo '<script>
        jQuery(document).ready( function() {
            jQuery("#save-general").on("click", function(e) {
                e.preventDefault();
                dataGeneral.h1 = jQuery("#h1-home-page").val();
        
                jQuery.post("' . $link . '", 
                    {
                        "action": "save_general",
                        "data": dataGeneral,
                        "nonce": "' . $nonce . '"
                    }, 
                    function(response) {
                        if(response == "failed") {
                            let alert = document.getElementById("alert-post");
                            if(alert.classList.contains("alert-success")) {
                                alert.classList.remove("alert-success");
                            }
                            alert.classList.add("alert-danger");
                            alert.style.display = "block";
                            alert.innerHTML = "Save failed! H1 or Short description is empty.";
                        } else {
                            let alert = document.getElementById("alert-post");
                            if(alert.classList.contains("alert-danger")) {
                                alert.classList.remove("alert-danger");
                            }
                            alert.classList.add("alert-success");
                            alert.style.display = "block";
                            alert.innerHTML = "Save successfully!";
                        }
                    }
                );
            });
        });
        </script>';
    }

    public function load_view_set_category()
    {
        $categories = get_categories();
        $custom_categories = $this->getCustomCategory();
        $nonce = wp_create_nonce("get_game_nonce");
        $link = admin_url('admin-ajax.php');

        echo '<script src="https://cdn.ckeditor.com/4.22.1/standard/ckeditor.js"></script>';
        echo '<script>
            let listCategory = ' . json_encode($categories) . ';
            let array = Object.keys(listCategory);
            let dataCategory = {};
        </script>';
        echo '<div class="container mt-5">';
        echo "<div class='alert' role='alert' id='alert-post'></div>";
        echo '<ul class="list-group ul-post">';

        foreach ($categories as $category) {
            foreach ($custom_categories as $cate) {
                if ($category->term_id == $cate->category_id) {
                    $category->title = $cate->title;
                    $category->content = $cate->content;
                }
            }

            echo '<li class="list-group-item item-post">
                <div>
                <span class="post-title">' . $category->name . '</span>
                </div>
                <span>
                <p class="mt-2 mb-2">Title</p>
                <input class="form-control" type="text" value="' . $category->title . '" id="title-' . $category->slug . '">
                </span>
                <span>
                <p class="mt-2 mb-2">Description</p>
                <textarea name="' . $category->slug . '">' . $category->content . '</textarea>
                </span>
                <script>
                let area_' . str_replace('-', '_', $category->slug) . ' = CKEDITOR.replace("' . $category->slug . '");
                dataCategory.' . str_replace('-', '_', $category->slug) . ' = {
                    content: "",
                    title: "",
                };
                area_' . str_replace('-', '_', $category->slug) . '.on("change", function(evt) {
                    dataCategory.' . str_replace('-', '_', $category->slug) . '.content = String(evt.editor.getData());
                });
                
                jQuery("#title-' . $category->slug . '").on("change", function() {
                    dataCategory.' . str_replace('-', '_', $category->slug) . '.title = jQuery("#title-' . $category->slug . '").val();
                });';

            if (isset($category->content)) {
                echo 'dataCategory.' . str_replace('-', '_', $category->slug) . '.content = `' . html_entity_decode($category->content) . '`;';
            }

            echo '</script></li>';
        }

        echo '</ul>';
        echo '<button class="btn btn-success mt-4" type="button" id="save-category">Save</button>';
        echo '</div>';

        echo '<script>
        jQuery(document).ready( function() {
            jQuery("#save-category").on("click", function(e) {
                e.preventDefault();
        
                jQuery.post("' . $link . '", 
                    {
                        "action": "save_category",
                        "data": dataCategory,
                        "nonce": "' . $nonce . '"
                    }, 
                    function(response) {
                        document.documentElement.scrollTop = 0;
                        setInterval(function () {
                            let alert = document.getElementById("alert-post");
                            alert.classList.add("alert-success");
                            alert.style.display = "block";
                            alert.innerHTML = "Save successfully!";
                        }, 3000);
                    }
                );
            });
        });
        </script>';
    }

    public function getCustomCategory()
    {
        global $wpdb;
        $result = $wpdb->get_results("SELECT * FROM wp_category_custom");

        return $result;
    }

    public function save_category()
    {
        if (!wp_verify_nonce($_REQUEST['nonce'], "get_game_nonce")) {
            exit("Please don't fucking hack this API");
        }

        global $wpdb;
        $data = $_REQUEST['data'];

        foreach ($data as $key => $val) {
            $slug = str_replace("_", "-", $key);
            $getCategory = get_category_by_slug($slug);
            $queryGet = "SELECT * FROM " . $wpdb->prefix . 'category_custom WHERE category_id = "' . $getCategory->term_id . '"';
            $result = $wpdb->query($queryGet);
            if ($result == 0) {
                $query = 'INSERT INTO ' . $wpdb->prefix . 'category_custom (`category_id`, `content`, `title`) VALUES ';
                $query .= ' ("' . $getCategory->term_id . '", "' . htmlentities($val['content']) . '", "' . $val['title'] . '")';
            } else {
                $query = 'UPDATE ' . $wpdb->prefix . 'category_custom';
                $query .= ' SET `content` = "' . htmlentities($val['content']) . '", `title` = "' . $val['title'] . '" WHERE category_id = "' . $getCategory->term_id . '"';
            }

            $wpdb->query($query);
        }

        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            $result = json_encode($result);
            echo $result;
        } else {
            header("Location: " . $_SERVER["HTTP_REFERER"]);
        }
    }

    public function save_general()
    {
        if (!wp_verify_nonce($_REQUEST['nonce'], "get_game_nonce")) {
            exit("Please don't fucking hack this API");
        }

        $data = $_REQUEST['data'];
        if (empty($data['h1']) || empty($data['description'])) {
            echo 'failed';
            die;
        }

        $h1Homepage = get_option('h1_homepage');
        $shortDescriptionHomepage = get_option('short_description_homepage');

        if ($h1Homepage == false) {
            add_option('h1_homepage', $data['h1']);
        } else {
            update_option('h1_homepage', $data['h1']);
        }

        if ($shortDescriptionHomepage == false) {
            add_option('short_description_homepage', $data['description']);
        } else {
            update_option('short_description_homepage', $data['description']);
        }

        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            echo 'success';
        } else {
            header("Location: " . $_SERVER["HTTP_REFERER"]);
        }
    }

    public function slugify($text, string $divider = '-')
    {
        $text = preg_replace('~[^\pL\d]+~u', $divider, $text);
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        $text = preg_replace('~[^-\w]+~', '', $text);
        $text = trim($text, $divider);
        $text = preg_replace('~-+~', $divider, $text);
        $text = strtolower($text);

        if (empty($text)) {
            return 'n-a';
        }

        return $text;
    }
}
