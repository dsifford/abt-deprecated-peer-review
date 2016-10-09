<?php
/*
 *	Plugin Name: Academic Blogger's Toolkit - Deprecated Peer Review Extension
 *	Plugin URI: https://wordpress.org/plugins/academic-bloggers-toolkit/
 *	Description: Extension containing deprecated Peer Review Boxes for Academic Blogger's Toolkit
 *	Version: 1.0.0
 *	Author: Derek P Sifford
 *	Author URI: https://github.com/dsifford
 *	License: GPL3 or later
 */

function enqueue_abt_peer_review_admin_scripts() {
    global $pagenow;

    if ($pagenow != 'post.php') return;

    wp_enqueue_media();
    wp_enqueue_style('abt-deprecated-peer-review-style', plugins_url('abt-peer-review/styles.css'));
    wp_enqueue_script('abt-PR-metabox', plugins_url('abt-peer-review/peer-review-metabox.js'), [], false, true);
}
add_action('admin_enqueue_scripts', 'enqueue_abt_peer_review_admin_scripts');

function enqueue_abt_peer_review_frontend_js() {
    wp_enqueue_style('dashicons');
	wp_enqueue_style('abt-deprecated-peer-review-style', plugins_url('abt-peer-review/styles.css'), ['dashicons']);

    if (is_singular()) {
        wp_enqueue_script('abt-deprecated-peer-review-js', plugins_url('abt-peer-review/frontend.js'));
    }
}
add_action('wp_enqueue_scripts', 'enqueue_abt_peer_review_frontend_js');


function add_abt_peer_review_metabox() {
    add_meta_box(
        'abt_peer_review',
        __('Add Peer Review(s)', 'academic-bloggers-toolkit'),
        'render_abt_peer_review_metabox',
        ['post', 'page'],
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'add_abt_peer_review_metabox');

function render_abt_peer_review_metabox($post) {
    wp_nonce_field(basename(__file__), 'abt_PR_nonce');
    $meta_fields = unserialize(base64_decode(get_post_meta($post->ID, '_abt-meta', true)));
    $meta_fields = stripslashes_deep($meta_fields);

    if (!empty($meta_fields['peer_review'])) {
        for ($i = 1; $i < 4; $i++) {
            if (!empty($meta_fields['peer_review'][$i]['response']['content'])) {
                $replaced = substr($meta_fields['peer_review'][$i]['response']['content'], 3);
                $meta_fields['peer_review'][$i]['response']['content'] = preg_replace('/(<br>)|(<br \/>)|(<p>)|(<\/p>)/', "\r", $replaced);
            }
            if (!empty($meta_fields['peer_review'][$i]['review']['content'])) {
                $replaced = substr($meta_fields['peer_review'][$i]['review']['content'], 3);
                $meta_fields['peer_review'][$i]['review']['content'] = preg_replace('/(<br>)|(<br \/>)|(<p>)|(<\/p>)/', "\r", $replaced);
            }
        }
        wp_localize_script('abt-PR-metabox', 'ABT_PR_Metabox_Data', $meta_fields['peer_review']);
    }
    else {
        wp_localize_script('abt-PR-metabox', 'ABT_PR_Metabox_Data', [
            '1' => [
                'heading' => '',
                'response' => [],
                'review' => [],
            ],
            '2' => [
                'heading' => '',
                'response' => [],
                'review' => [],
            ],
            '3' => [
                'heading' => '',
                'response' => [],
                'review' => [],
            ],
            'selection' => '',
        ]);
    }

    echo "<div id='abt-peer-review-metabox'></div>";
}


function save_abt_peer_review_meta($post_id) {
    $is_autosave = wp_is_post_autosave($post_id);
    $is_revision = wp_is_post_revision($post_id);
    $is_valid_nonce = (isset($_POST[ 'abt_PR_nonce' ]) && wp_verify_nonce($_POST[ 'abt_PR_nonce' ], basename(__FILE__))) ? true : false;

    if ($is_autosave || $is_revision || !$is_valid_nonce) return;

    // Set variable for allowed html tags in 'Background' Section
    $abtAllowedTags = [
        'a' => [
            'href' => [],
            'title' => [],
            'target' => [],
        ],
        'br' => [],
        'em' => [],
    ];

    $new_PR_meta = unserialize(base64_decode(get_post_meta($post_id, '_abt-meta', true)));

    if (empty($new_PR_meta['peer_review'])) {
        $new_PR_meta['peer_review'] = [
            '1' => [
                'heading' => '',
                'response' => [],
                'review' => [],
            ],
            '2' => [
                'heading' => '',
                'response' => [],
                'review' => [],
            ],
            '3' => [
                'heading' => '',
                'response' => [],
                'review' => [],
            ],
            'selection' => '',
        ];
    }

    // Begin Saving Meta Variables
    $new_PR_meta['peer_review']['selection'] = esc_attr($_POST[ 'reviewer_selector' ]);

    for ($i = 1; $i < 4; ++$i) {
        $new_PR_meta['peer_review'][$i]['heading'] = isset($_POST[ 'peer_review_box_heading_'.$i ])
            ? sanitize_text_field($_POST[ 'peer_review_box_heading_'.$i ])
            : '';

        $new_PR_meta['peer_review'][$i]['review']['name'] = isset($_POST[ 'reviewer_name_'.$i ])
            ? sanitize_text_field($_POST[ 'reviewer_name_'.$i ])
            : '';

        $new_PR_meta['peer_review'][$i]['review']['twitter'] = isset($_POST[ 'reviewer_twitter_'.$i ])
            ? sanitize_text_field($_POST[ 'reviewer_twitter_'.$i ])
            : '';

        $new_PR_meta['peer_review'][$i]['review']['background'] = isset($_POST[ 'reviewer_background_'.$i ])
            ? wp_kses($_POST[ 'reviewer_background_'.$i ], $abtAllowedTags)
            : '';

        $new_PR_meta['peer_review'][$i]['review']['content'] = isset($_POST[ 'peer_review_content_'.$i ])
            ? wp_kses_post(wpautop($_POST[ 'peer_review_content_'.$i ]))
            : '';

        $new_PR_meta['peer_review'][$i]['review']['image'] = isset($_POST[ 'reviewer_headshot_image_'.$i ])
            ? $_POST[ 'reviewer_headshot_image_'.$i ]
            : '';

        $new_PR_meta['peer_review'][$i]['response']['name'] = isset($_POST[ 'author_name_'.$i ])
            ? sanitize_text_field($_POST[ 'author_name_'.$i ])
            : '';

        $new_PR_meta['peer_review'][$i]['response']['twitter'] = isset($_POST[ 'author_twitter_'.$i ])
            ? sanitize_text_field($_POST[ 'author_twitter_'.$i ])
            : '';

        $new_PR_meta['peer_review'][$i]['response']['background'] = isset($_POST[ 'author_background_'.$i ])
            ? wp_kses($_POST[ 'author_background_'.$i ], $abtAllowedTags)
            : '';

        $new_PR_meta['peer_review'][$i]['response']['content'] = isset($_POST[ 'author_content_'.$i ])
            ? wp_kses_post(wpautop($_POST[ 'author_content_'.$i ]))
            : '';

        $new_PR_meta['peer_review'][$i]['response']['image'] = isset($_POST[ 'author_headshot_image_'.$i ])
            ? $_POST[ 'author_headshot_image_'.$i ]
            : '';
    }
    update_post_meta($post_id, '_abt-meta', base64_encode(serialize($new_PR_meta)));
}
add_action('save_post', 'save_abt_peer_review_meta');


function abt_append_peer_reviews($content) {
    if (is_single() || is_page()) {
        global $post;

        $meta = unserialize(base64_decode(get_post_meta($post->ID, '_abt-meta', true)));

        if (!isset($meta['peer_review']) || empty($meta['peer_review'])) {
            return $content;
        }

        if ($post->post_type == 'post' || $post->post_type == 'page') {
            for ($i = 1; $i < 4; ++$i) {
                $heading = $meta['peer_review'][$i]['heading'];
                $review_name = $meta['peer_review'][$i]['review']['name'];

                if (empty($review_name)) {
                    continue;
                }

                $review_background = $meta['peer_review'][$i]['review']['background'];
                $review_content = $meta['peer_review'][$i]['review']['content'];
                $review_image = $meta['peer_review'][$i]['review']['image'];
                $review_image = !empty($review_image)
                ? "<img src='${review_image}' width='100px'>"
                : "<i class='dashicons dashicons-admin-users abt_PR_headshot' style='font-size: 100px;'></i>";

                $review_twitter = $meta['peer_review'][$i]['review']['twitter'];
                $review_twitter = !empty($review_twitter)
                ? '<img style="vertical-align: middle;"'.
                'src="https://g.twimg.com/Twitter_logo_blue.png" width="10px" height="10px">'.
                '<a href="http://www.twitter.com/'.
                ($review_twitter[0] == '@' ? substr($review_twitter, 1) : $review_twitter).
                '" target="_blank">@'.
                ($review_twitter[0] == '@' ? substr($review_twitter, 1) : $review_twitter).
                '</a>'
                : '';

                $response_name = $meta['peer_review'][$i]['response']['name'];
                $response_block = '';

                if (!empty($response_name)) {
                    $response_twitter = $meta['peer_review'][$i]['response']['twitter'];
                    $response_twitter = !empty($response_twitter)
                    ? '<img style="vertical-align: middle;"'.
                    'src="https://g.twimg.com/Twitter_logo_blue.png" width="10px" height="10px">'.
                    '<a href="http://www.twitter.com/'.
                    ($response_twitter[0] == '@' ? substr($response_twitter, 1) : $response_twitter).
                    '" target="_blank">@'.
                    ($response_twitter[0] == '@' ? substr($response_twitter, 1) : $response_twitter).
                    '</a>'
                    : '';

                    $response_image = $meta['peer_review'][$i]['response']['image'];
                    $response_image = !empty($response_image)
                    ? "<img src='${response_image}' width='100px'>"
                    : "<i class='dashicons dashicons-admin-users abt_PR_headshot' style='font-size: 100px;'></i>";

                    $response_background = $meta['peer_review'][$i]['response']['background'];
                    $response_content = $meta['peer_review'][$i]['response']['content'];

                    $response_block =
                    "<div class='abt_chat_bubble'>$response_content</div>".
                    "<div class='abt_PR_info'>".
                        "<div class='abt_PR_headshot'>".
                            "$response_image".
                        '</div>'.
                        '<div>'.
                            "<strong>$response_name</strong>".
                        '</div>'.
                        '<div>'.
                            "$response_background".
                        '</div>'.
                        '<div>'.
                            "$response_twitter".
                        '</div>'.
                    '</div>';
                }

                ${'reviewer_block_'.$i} =
                "<h3 class='abt_PR_heading noselect'>$heading</h3>".
                '<div>'.
                    "<div class='abt_chat_bubble'>$review_content</div>".
                    "<div class='abt_PR_info'>".
                        "<div class='abt_PR_headshot'>".
                            "$review_image".
                        '</div>'.
                        '<div>'.
                            "<strong>$review_name</strong>".
                        '</div>'.
                        '<div>'.
                            "$review_background".
                        '</div>'.
                        '<div>'.
                            "$review_twitter".
                        '</div>'.
                    '</div>'.
                    "$response_block".
                '</div>';
            }

            if (!empty($reviewer_block_1)) {
                $content .=
                '<div id="abt_PR_boxes">'.
                    $reviewer_block_1.
                    ((!empty($reviewer_block_2)) ? $reviewer_block_2 : '').
                    ((!empty($reviewer_block_3)) ? $reviewer_block_3 : '').
                    '</div>';
            }
        }
    }

    return $content;
}
add_filter('the_content', 'abt_append_peer_reviews');
