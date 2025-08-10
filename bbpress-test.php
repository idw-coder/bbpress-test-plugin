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
    // if (is_user_logged_in() && !is_admin()) {
    //     echo '<div style="background: #E9EEF6; font-size: 0.5rem;">選択されたテンプレート: ' . $display_template . '</div>';
    // }

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
    // フォーラムIDを確実に数値として扱う
    if (isset($query_args['post_parent'])) {
        $query_args['post_parent'] = intval($query_args['post_parent']);
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
