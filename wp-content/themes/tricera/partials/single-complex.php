<?php
if (have_posts()) {
  while (have_posts()) {
    the_post();
?>
    <div class="gamepagebox">
      <div class="gameshow">
        <h1>
          <a href="<?php the_permalink() ?>" rel="bookmark" title="Permanent Link to <?php the_title(); ?>">
            <?php the_title(); ?>
          </a>
        </h1>

        <div class="options_panel">
          <div class="leader_gamepage">
            <?php if (tricera_get_option('game_page_top_banner')) : ?>
              <div class="leader_adspace_gamepage_top_ad">
                <?php echo tricera_get_option('game_page_top_banner'); ?>
              </div>
            <?php endif; ?>

            <?php if (tricera_display_buttons()) : ?>
              <div id="game_buttons">

                <?php if (tricera_get_option('fullscreen_button') == '1') : ?>
                  <?php // Display Fullscreen button 
                  ?>

                  <a href="<?php echo get_permalink() . 'fullscreen'; ?>" class="fullscreen tooltip" title="<span><?php _e('Play Fullscreen', 'tricera'); ?></span>">
                    <div class="command"></div>
                  </a>
                <?php endif; ?>

                <?php if (tricera_get_option('lights_button') == '1') : ?>
                  <?php // Display Lights-Switch 
                  ?>
                  <div class="command interruptor">
                    <a href="#" title="<span><?php _e('Turn lights off and on', 'tricera'); ?></span>" class="interruptor tooltip"></a>
                  </div>
                <?php endif; ?>

                <?php if (tricera_get_option('favorite_button') == '1') : ?>
                  <div class="command">
                    <?php if (is_user_logged_in()) {
                      tricera_favorite();
                    } else {
                      echo '<img class="favlogin" src="' . get_template_directory_uri() . '/images/favlogin.png" alt="Login to favorite" />';
                    }
                    ?>
                  </div>
                <?php endif; ?>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <div class="<?php if ((tricera_get_option('game_page_side_banner'))) {    ?>gameplayshow<?php } else { ?>gameplayshow_adoff<?php } ?>">
        <div class="gamebox">
          <?php get_template_part('partials/games', 'play'); ?>
        </div>
      </div>

      <?php if (tricera_get_option('game_page_side_banner')) : ?>
        <div class="vertical_adbox">
          <?php echo tricera_get_option('game_page_side_banner'); ?>
        </div>
      <?php endif; ?>
    </div>

    <div id="turnoff"></div>

    <div class="normal_box">
      <div class="norm_left" id="tabs">
        <div class="tabcontent" id="tab-1">
          <div class="gamedesc_info">
            <h2 style="font-size: 20px;">Detailed description of the <?php the_title() ?></h2>
            <p style="font-size: 14px; margin-left: 10.5%;"><?php
                                                            // Display Description of game
                                                            $tricera_desc = get_post_meta($post->ID, 'mabp_description', true);
                                                            if ($tricera_desc) {
                                                              echo $tricera_desc;
                                                            }
                                                            ?>
            </p>
          </div>

          <div class="gameinfo_functions">
            <?php if (function_exists('the_views')) { ?>
              <div class="gamemiscstats">
                <span style="color: #ffffff; font-weight:bold; font: 12px arial;"><?php the_views(); ?></span>
              </div>
            <?php } ?>

            <div class="gpcats">
              <span style="color: #ffffff; font-weight:bold; font: 14px arial;"><?php _e("Game Categories", "tricera"); ?></span><br />
              <span class="catbtn cat-orange"><?php the_category('</span><span class="catbtn cat-orange"> '); ?></span>
            </div>

            <div class="gptags">
              <span style="color: #ffffff; font-weight:bold; font: 14px arial;"><?php _e('Game tags', 'tricera'); ?></span><br />
              <?php the_tags('<span class="tagbtn tag-blue">', '</span><span class="tagbtn tag-blue">', '</span>'); ?>
            </div>
          </div>

          <?php if ((tricera_get_option('display_screenshots') == '1') && myarcade_count_screenshots()) {
          ?>
            <h3 class="screenshot_title"><?php the_title(''); ?> <?php _e('Screen Shots', 'tricera'); ?></h3>
            <div class="screenshot_box">
              <?php myarcade_all_screenshots(130, 130, 'screen_thumb'); ?>
            </div>
          <?php
          }
          ?>

          <div class="admin_actions">
            <?php tricera_admin_links(); ?>
          </div>

          <br />

          <?php
          // Show the game share box
          if ((tricera_get_option('game_embed_box') == '1')) {
          ?>
          <?php
          }
          ?>

          <?php if (tricera_get_option('game_page_bottom_banner')) : ?>
            <div class="leader_adspace_bottom">
              <?php echo tricera_get_option('game_page_bottom_banner'); ?>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <div class="nextprevgames">
        <section>
          <h3 style="font-size: 20px; font-weight: bold; margin-bottom: 10px;">Related category</h3>
          <div class="top-cate-homepage">
            <div class="list-top-cate">
              <?php
              $cat_id = get_the_category()[0]->term_id;
              $exclude = array($cat_id);
              $cat_list = array();

              foreach (get_categories() as $cat) {
                if (!in_array($cat->term_id, $exclude) and $cat->name != 'TOP DOWNLOAD') {
                  $cat_list[] = '<a href="' . esc_url(get_category_link($cat->term_id)) .
                    '"><div class="in-list-top-cate">' . $cat->name . '</div></a>';
                }
              }

              echo implode(' ', $cat_list);
              ?>
            </div>
          </div>
        </section>
        <div style="display: flex;">
          <?php get_template_part('partials/single', 'prevnext-game'); ?>
          <div class="random_gameinc" style="float:right; max-width:660px;">
            <?php if (function_exists('related_entries')) {
              related_entries();
            } else {
              $tags = wp_get_post_tags($post->ID);

              if ($tags) {
                $tag_ids = array();

                foreach ($tags as $individual_tag)
                  $tag_ids[] = $individual_tag->term_id;

                $args = array(
                  'tag__in' => $tag_ids,
                  'post__not_in' => array($post->ID),
                  'showposts' => 12,  // Number of related posts that will be shown.
                  'ignore_sticky_posts' => true
                );

                $my_query = new wp_query($args);

                if ($my_query->have_posts()) {
                  echo '<ul class="games-related custom-related" style="margin-left: 10px;">';

                  while ($my_query->have_posts()) {
                    $my_query->the_post();
            ?>

                    <li>
                      <a href="<?php the_permalink() ?>" class="tooltip" title='<span>Play <?php the_title_attribute(); ?></span><br />'>
                        <span class="framespan"></span><?php myarcade_thumbnail(100, 100, 'ft_gameimg'); ?></a>
                    </li>
            <?php
                  }

                  wp_reset_query();

                  echo '</ul>';
                }
              }
            }
            ?>
          </div>
        </div>
      </div>
    </div>



    <?php if (comments_open()) { ?>
      <div class="normal_box">
        <div class="norm_left" id="tabs">
          <div class="tabcontent" id="tab-1">

            <div class="allcomments">
              <?php comments_template(); ?>
            </div>
          </div>
        </div>
      </div>
    <?php } ?>
  <?php
  } // end while
} else {
  ?>
  <div class="ft_gameshowcase">
    <div id="content_tricera">
      <p><?php _e("I'm Sorry, you are looking for something that is not here. Try a different search.", "tricera"); ?></p>
    </div>
  </div>
<?php } ?>