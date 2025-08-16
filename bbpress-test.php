<?php
/*
 * Plugin Name: bbPress Template Override
 * Description: bbPressのテンプレートをカスタマイズするプラグイン
 * Version: 1.0
 * Author: T.I.
*/

add_action('wp_enqueue_scripts', 'bbpress_override_enqueue_tailwind');
function bbpress_override_enqueue_tailwind()
{
    // ビルド済みCSSファイルのパス
    $css_file = plugin_dir_path(__FILE__) . 'assets/style.css';
    $css_url = plugin_dir_url(__FILE__) . 'assets/style.css';

    // ファイルが存在する場合のみ読み込み
    if (file_exists($css_file)) {
        wp_enqueue_style(
            'bbpress-tailwind',
            $css_url,
            array(),
            filemtime($css_file) // ファイル更新時刻をバージョンに使用
        );
    }

    // Font Awesomeを読み込み
    wp_enqueue_style(
        'font-awesome',
        'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css',
        array(),
        '6.4.0'
    );
}

/**
 * bbPressテンプレートスタック機能について
 * 
 * bbPressは独自のテンプレート階層システムを持っており、以下の順序でテンプレートを検索します：
 * 1. 子テーマ
 * 2. 親テーマ  
 * 3. bbPressプラグイン内のデフォルトテンプレート
 * 
 * bbp_template_stackフィルターを使用することで、この検索パスに独自の場所を追加できます。
 */

// bbPressがテンプレートを検索する度に実行される
add_filter('bbp_template_stack', 'add_plugin_template_location');

/**
 * プラグインのテンプレートディレクトリをbbPressのテンプレート検索パスに追加
 * この関数は bbp_get_template_stack() 内で呼び出される
 * https://github.com/bbpress/bbPress/blob/4d538c1e8146a72ffdf947ff82d81219f709f2a1/src/includes/core/template-functions.php
 * 
 * @param array $stack 既存のテンプレートスタック（通常は空配列で開始）
 * @return string 追加するテンプレートディレクトリのパス
 */
function add_plugin_template_location($stack = array())
{
    // プラグインディレクトリ内の templates フォルダを指定
    $plugin_template_dir = plugin_dir_path(__FILE__) . 'templates';

    // デバッグ表示
    // echo '<div style="background: #e8f5e8; font-size: 0.5rem;">テンプレートスタックに追加: ' . $plugin_template_dir . '</div>';

    return $plugin_template_dir;
}

/**
 * デバッグ用フィルター：テンプレート検索過程を可視化
 * 
 * bbp_locate_template は実際のテンプレート選択には影響しない「通知用」のアクション
 * bbPressのソースファイル: includes/core/template-functions.php の99行目参照
 * このフィルターは以下の情報を提供：
 * - どのテンプレートファイルが検索されているか
 * - 最終的にどのテンプレートが選択されたか
 * 
 * フックタイミング: bbp_locate_template()関数内で、テンプレートが見つかった後に実行
 */
add_filter('bbp_locate_template', 'debug_bbpress_templates', 10, 4);

/**
 * bbPressテンプレート検索プロセスをデバッグ表示
 * 
 * @param string $template 最終的に選択されたテンプレートファイルのフルパス
 * @param array $template_names 検索対象のテンプレートファイル名配列
 * @param bool $load テンプレートを読み込むかどうか
 * @param bool $require_once require_onceを使用するかどうか
 * @return string 変更せずにそのまま返す（このフィルターは情報取得のみ）
 */
function debug_bbpress_templates($template, $template_names, $load, $require_once)
{
    // 検索されたテンプレートファイル名を表示
    // if (!is_admin()) {
    //     foreach ((array)$template_names as $template_name) {
    //         echo '<div style="background: #F8FAFD; font-size: 0.5rem;">テンプレート検索: ' . $template_name . '</div>';
    //     }
    // }

    // 最終的に選択されたテンプレートを表示
    $display_template = $template ? $template : '見つかりませんでした';

    // デバッグ出力を条件付きで表示（ヘッダー送信エラーを防ぐ）
    if (is_user_logged_in() && !is_admin()) {
        // echo '<div style="background: #E9EEF6; font-size: 0.5rem;">選択されたテンプレート: ' . $display_template . '</div>';
    }

    // 重要: テンプレートパスを変更せずにそのまま返す
    // このフィルターは監視用であり、実際のテンプレート選択は bbp_template_stack で行う
    return $template;
}

/**
 * テンプレートファイル内のハードコードされた文字列も変更
 */
function custom_bbp_text_strings($translated_text, $text, $domain)
{
    if ($domain === 'bbpress') {
        switch ($text) {
            case 'Forum':
                return '質問掲示板';
            case 'Forums':
                return '質問掲示板';
            case 'Forum:':
                return '質問掲示板:';
            case 'Create New Forum':
                return '新しい質問掲示板を作成';
            case 'Create New Forum in "%s"':
                return '「%s」に新しい質問掲示板を作成';
            case 'Now Editing "%s"':
                return '「%s」を編集中';
            case 'Forum Name (Maximum Length: %d):':
                return '質問掲示板名（最大文字数: %d）:';
            case 'Forum Type:':
                return '質問掲示板タイプ:';
            case 'Forum Moderators:':
                return '質問掲示板モデレーター:';
            case 'This group does not currently have a forum.':
                return 'このグループには現在質問掲示板がありません。';
        }
    }
    return $translated_text;
}
// add_filter('gettext', 'custom_bbp_text_strings', 20, 3);

/**
 * 購読リンクの文字列を変更
 */
add_filter('gettext', 'custom_bbpress_subscription_text', 20, 3);
function custom_bbpress_subscription_text($translated_text, $text, $domain)
{
    if ($domain === 'bbpress') {
        switch ($text) {
            case 'Subscribe':
                return 'メール通知を受け取る';
            case 'Unsubscribe':
                return 'メール通知解除';
        }
    }
    return $translated_text;
}


/**
 * bbPressフォーラムのページタイトルを「質問掲示板」に変更
 */
function custom_bbp_forum_labels($labels)
{
    $labels['name'] = '質問掲示板';
    $labels['singular_name'] = '質問掲示板';
    $labels['menu_name'] = '質問掲示板';
    $labels['all_items'] = 'すべての質問掲示板';
    $labels['add_new_item'] = '新しい質問掲示板を作成';
    $labels['edit_item'] = '質問掲示板を編集';
    $labels['new_item'] = '新しい質問掲示板';
    $labels['view_item'] = '質問掲示板を表示';
    $labels['view_items'] = '質問掲示板を表示';
    $labels['search_items'] = '質問掲示板を検索';
    $labels['not_found'] = '質問掲示板が見つかりません';
    $labels['not_found_in_trash'] = 'ゴミ箱に質問掲示板が見つかりません';
    $labels['archives'] = '質問掲示板';

    return $labels;
}
add_filter('bbp_get_forum_post_type_labels', 'custom_bbp_forum_labels');

/**
 * 返信の管理リンクから不要なものを削除
 */
add_filter('bbp_get_reply_admin_links', 'custom_reply_admin_links', 5, 2);
function custom_reply_admin_links($links, $reply_id)
{
    // 文字列の場合（実際のケース）
    if (is_string($links)) {
        $new_links = array();

        // 編集リンクを抽出
        if (preg_match('/<a[^>]*class="[^"]*bbp-reply-edit-link[^"]*"[^>]*>.*?<\/a>/i', $links, $matches)) {
            $new_links[] = $matches[0];
        }

        // ゴミ箱リンクを抽出
        if (preg_match('/<a[^>]*class="[^"]*bbp-reply-trash-link[^"]*"[^>]*>.*?<\/a>/i', $links, $matches)) {
            $new_links[] = $matches[0];
        }

        // 返信リンクを抽出
        if (preg_match('/<a[^>]*class="[^"]*bbp-reply-to-link[^"]*"[^>]*>.*?<\/a>/i', $links, $matches)) {
            $new_links[] = $matches[0];
        }

        // 新しいHTMLを生成
        if (!empty($new_links)) {
            return '<span class="bbp-admin-links">' . implode(' | ', $new_links) . '</span>';
        } else {
            return '';
        }
    }

    return $links;
}

/**
 * トピックの管理リンク：編集、クローズ、ゴミ箱、返信のみ表示（文字列対応版）
 */
add_filter('bbp_get_topic_admin_links', 'custom_topic_admin_links', 5, 2);
function custom_topic_admin_links($links, $topic_id)
{
    // 文字列の場合（実際のケース）
    if (is_string($links)) {
        $new_links = array();

        // 編集リンクを抽出
        if (preg_match('/<a[^>]*class="[^"]*bbp-topic-edit-link[^"]*"[^>]*>.*?<\/a>/i', $links, $matches)) {
            $new_links[] = $matches[0];
        }

        // クローズリンクを抽出
        if (preg_match('/<a[^>]*class="[^"]*bbp-topic-close-link[^"]*"[^>]*>.*?<\/a>/i', $links, $matches)) {
            $new_links[] = $matches[0];
        }

        // ゴミ箱リンクを抽出
        if (preg_match('/<a[^>]*class="[^"]*bbp-topic-trash-link[^"]*"[^>]*>.*?<\/a>/i', $links, $matches)) {
            $new_links[] = $matches[0];
        }

        // 返信リンクを抽出
        if (preg_match('/<a[^>]*class="[^"]*bbp-reply-to-link[^"]*"[^>]*>.*?<\/a>/i', $links, $matches)) {
            $new_links[] = $matches[0];
        }

        // 新しいHTMLを生成
        if (!empty($new_links)) {
            return '<span class="bbp-admin-links">' . implode(' | ', $new_links) . '</span>';
        } else {
            return '';
        }
    }

    return $links;
}

/**
 * bbPressのDATETIMEエラーを修正
 */
add_filter('bbp_has_topics_query', function ($query_args) {
    // トピックタグページの場合はタグベースのクエリに変更
    if (bbp_is_topic_tag()) {
        $tag_id = bbp_get_topic_tag_id();
        if ($tag_id) {
            $query_args['post_type'] = 'topic';
            $query_args['tax_query'] = array(
                array(
                    'taxonomy' => 'topic-tag',
                    'field'    => 'term_id',
                    'terms'    => $tag_id,
                )
            );
            // トピックタグページではpost_parentを削除
            unset($query_args['post_parent']);
        }
    } else {
        // 通常のフォーラムページではpost_parentを数値に変換
        if (isset($query_args['post_parent'])) {
            $query_args['post_parent'] = intval($query_args['post_parent']);
        }
    }

    return $query_args;
});

/**
 * メール送信テスト
 * メール送信テスト用のURL: https://domain.com/?mailtest=1
 */
add_action('init', function () {
    if (isset($_GET['mailtest'])) {
        $ok = wp_mail(get_option('admin_email'), 'wp_mail test', 'OK');
        header('Content-Type: text/plain; charset=utf-8');
        echo $ok ? 'OK' : 'NG';
        exit;
    }
});

/**
 * フォーラムとカテゴリーにサムネイル機能を追加
 */

// 管理画面でサムネイルフィールドを追加
add_action('add_meta_boxes', 'add_forum_thumbnail_meta_box');
function add_forum_thumbnail_meta_box()
{
    add_meta_box(
        'forum_thumbnail',
        'サムネイル画像',
        'forum_thumbnail_meta_box_callback',
        'forum',
        'side',
        'high'
    );
}

// サムネイルフィールドのHTML
function forum_thumbnail_meta_box_callback($post)
{
    wp_nonce_field('forum_thumbnail_nonce', 'forum_thumbnail_nonce');

    $thumbnail_id = get_post_meta($post->ID, '_forum_thumbnail_id', true);
    $thumbnail_url = wp_get_attachment_image_url($thumbnail_id, 'large');

    echo '<div id="forum_thumbnail_container">';
    if ($thumbnail_url) {
        echo '<img src="' . esc_url($thumbnail_url) . '" style="max-width: 100%; height: auto; margin-bottom: 10px;" />';
    }
    echo '<input type="hidden" id="forum_thumbnail_id" name="forum_thumbnail_id" value="' . esc_attr($thumbnail_id) . '" />';
    echo '<button type="button" id="upload_thumbnail_button" class="button">画像を選択</button>';
    if ($thumbnail_id) {
        echo ' <button type="button" id="remove_thumbnail_button" class="button">削除</button>';
    }
    echo '</div>';

    echo '<script>
    jQuery(document).ready(function($) {
        $("#upload_thumbnail_button").click(function(e) {
            e.preventDefault();
            var image = wp.media({
                title: "サムネイル画像を選択",
                multiple: false
            }).open().on("select", function() {
                var uploaded_image = image.state().get("selection").first();
                var image_url = uploaded_image.toJSON().sizes.thumbnail ? uploaded_image.toJSON().sizes.thumbnail.url : uploaded_image.toJSON().url;
                $("#forum_thumbnail_container").html(
                    \'<img src="\' + image_url + \'" style="max-width: 100%; height: auto; margin-bottom: 10px;" />\' +
                    \'<input type="hidden" id="forum_thumbnail_id" name="forum_thumbnail_id" value="\' + uploaded_image.id + \'" />\' +
                    \'<button type="button" id="upload_thumbnail_button" class="button">画像を選択</button>\' +
                    \' <button type="button" id="remove_thumbnail_button" class="button">削除</button>\'
                );
            });
        });
        
        $(document).on("click", "#remove_thumbnail_button", function() {
            $("#forum_thumbnail_container").html(
                \'<input type="hidden" id="forum_thumbnail_id" name="forum_thumbnail_id" value="" />\' +
                \'<button type="button" id="upload_thumbnail_button" class="button">画像を選択</button>\'
            );
        });
    });
    </script>';
}

// サムネイルデータを保存
add_action('save_post', 'save_forum_thumbnail');
function save_forum_thumbnail($post_id)
{
    if (!isset($_POST['forum_thumbnail_nonce']) || !wp_verify_nonce($_POST['forum_thumbnail_nonce'], 'forum_thumbnail_nonce')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    if (isset($_POST['forum_thumbnail_id'])) {
        update_post_meta($post_id, '_forum_thumbnail_id', sanitize_text_field($_POST['forum_thumbnail_id']));
    }
}

// サムネイル画像を取得する関数
function get_forum_thumbnail($forum_id = null, $size = 'large')
{
    if (!$forum_id) {
        $forum_id = get_the_ID();
    }

    $thumbnail_id = get_post_meta($forum_id, '_forum_thumbnail_id', true);

    if ($thumbnail_id) {
        return wp_get_attachment_image($thumbnail_id, $size, false, array('class' => 'forum-thumbnail'));
    }

    return false;
}

// サムネイルURLを取得する関数
function get_forum_thumbnail_url($forum_id = null, $size = 'large')
{
    if (!$forum_id) {
        $forum_id = get_the_ID();
    }

    $thumbnail_id = get_post_meta($forum_id, '_forum_thumbnail_id', true);

    if ($thumbnail_id) {
        return wp_get_attachment_image_url($thumbnail_id, $size);
    }

    return false;
}

/**
 * いいね機能
 */

// 返信のいいね数を更新（メタ情報が存在しない場合は自動的に追加）
function my_add_like_to_reply($reply_id)
{
    $likes = (int) get_post_meta($reply_id, 'reply_likes', true);
    update_post_meta($reply_id, 'reply_likes', $likes + 1);
}

// いいねリンクのURLを作成
function get_like_url($reply_id)
{
    return add_query_arg(array(
        'action' => 'add_like',
        'reply_id' => $reply_id,
        'nonce' => wp_create_nonce('add_like_nonce')
    ), home_url());
}

// いいね処理のエンドポイント
add_action('init', 'handle_like_request');
function handle_like_request()
{
    if (isset($_GET['action']) && $_GET['action'] === 'add_like') {
        if (!wp_verify_nonce($_GET['nonce'], 'add_like_nonce')) {
            wp_die('セキュリティチェックに失敗しました');
        }

        $reply_id = (int) $_GET['reply_id'];

        // リプライが存在するかチェック
        if (get_post($reply_id)) {
            my_add_like_to_reply($reply_id);

            // 元のページにリダイレクト（リファラーを使用）
            $redirect_url = wp_get_referer();
            if (!$redirect_url) {
                $redirect_url = home_url();
            }
            wp_redirect($redirect_url);
            exit;
        }
    }
}

/**
 * ユーザーの総いいね数を取得
 */
function get_user_total_likes($user_id)
{
    global $wpdb;

    $reply_post_type = function_exists('bbp_get_reply_post_type') ? bbp_get_reply_post_type() : 'reply';

    $total_likes = $wpdb->get_var($wpdb->prepare(
        "SELECT COALESCE(SUM(CAST(pm.meta_value AS UNSIGNED)), 0) as total_likes
         FROM {$wpdb->posts} p
         LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = 'reply_likes'
         WHERE p.post_author = %d 
         AND p.post_type = %s 
         AND p.post_status = 'publish'",
        $user_id,
        $reply_post_type
    ));

    return (int) $total_likes;
}
