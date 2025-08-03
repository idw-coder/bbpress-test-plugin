<?php
/*
 * Plugin Name: bbPress Template Override
 * Description: bbPressのテンプレートをカスタマイズするプラグイン
 * Version: 1.0
 * Author: T.I.
*/

/**
 * TailwindCSS（ビルド済み）を読み込み
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
    foreach ((array)$template_names as $template_name) {
        echo '<div style="background: #F8FAFD; font-size: 0.5rem;">テンプレート検索: ' . $template_name . '</div>';
    }

    // 最終的に選択されたテンプレートを表示
    $display_template = $template ? $template : '見つかりませんでした';
    echo '<div style="background: #E9EEF6; font-size: 0.5rem;">選択されたテンプレート: ' . $display_template . '</div>';

    // 重要: テンプレートパスを変更せずにそのまま返す
    // このフィルターは監視用であり、実際のテンプレート選択は bbp_template_stack で行う
    return $template;
}
