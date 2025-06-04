<?php
class Layout {
    public static function header($title) {
        require_once 'layout_header.php';
    }

    public static function footer() {
        require_once 'layout_footer.php';
    }

    public static function render($title, $content) {
        self::header($title);
        if (is_callable($content)) {
            call_user_func($content);
        } else {
            echo $content;
        }
        self::footer();
    }
} 