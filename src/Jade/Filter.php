<?php

namespace Jade;

class Filter {
    protected static function getTextOfNodes($data) {
        if (is_object($data)) {
            $new_str = '';
            foreach ($data->nodes as $n) {
                $new_str .= $n->value . "\n";
            }
            $data = $new_str;
        }
        return self::parse_data($data);
    }

    public static function cdata($data) {
        if (is_object($data)) {
            $new_data = '';
            foreach ($data->nodes as $n) {
                //TODO: original doing interpolation here
                $new_data .= $n->value . "\n";
            }
            $data = $new_data;
        }
        $data = self::parse_data($data);

        return "<!CDATA[\n" . $data . "\n]]>";
    }

    public static function css($data) {
        return '<style type="text/css">' . self::getTextOfNodes($data) . '</style>';
    }

    public static function javascript($data) {
        return '<script type="text/javascript">' . self::getTextOfNodes($data) . '</script>';
    }

    public static function php($data) {
        if (is_object($data)) {
            $new_data = '';
            foreach ($data->nodes as $n) {
                if (preg_match('/^[[:space:]]*\|(.*)/', $n->value, $m)) {
                    $new_data = $m[1];
                } else {
                    $new_data .= $n->value . "\n";
                }
            }
            $data = $new_data;
        }
        return '<?php ' . $data . ' ?>';
    }

    /**
     * @param $data
     * @return mixed
     */
    private static function parse_data($data) {
        preg_match_all('/#{(.+)}/imU', $data, $result, PREG_SET_ORDER);
        foreach ($result as $match) {
            list($full, $cmd) = $match;
            $cmd = str_replace('.', '->', $cmd);
            preg_match_all('/\[(.+)\]/imU', $cmd, $result2, PREG_SET_ORDER);
            foreach ($result2 as $match2) {
                list($full2, $cmd2) = $match2;
                $cmd = str_replace($cmd2, '\'' . trim($cmd2, "'") . '\'', $cmd);
            }
            $data = str_replace($full, '<?php echo htmlspecialchars($' . $cmd . ') ?>', $data);
        }
        return $data;
    }
}
