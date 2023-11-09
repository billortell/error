<?php
namespace Helpers;

use Flight;

class Errors {

    static public function makePretty( $ex ){

        // take 2
        ob_start();
        $tags = [];
        $tag_no = 0;

        $errors = [];
        $errors[] = [
            'line' => $ex->getLine(),
            'file' => $ex->getFile(),
        ];

        foreach ( $ex->getTrace() as $error ) {
            $errors[] = $error;
        }

        foreach ($errors as $trace)
        {
            $line_info = sprintf("%s [%s] %s::%s", $trace['file'], $trace['line'], $trace['class'], $trace['function']);
            // store this for 'table of contents' at top
            $tag_name = 'bt-'.$tag_no;
            $href = "<a href='#$tag_name'>Show</a> $line_info";
            array_push($tags,$href);

            // start printing out error
            echo "<a name='$tag_name'></a>";
            echo "<div class='card mt-5 p-2'>";

            $line_info_pretty = sprintf("File: %s [%s] <br>Class: %s <br>Function: %s", $trace['file'], $trace['line'], $trace['class'], $trace['function']);
            echo $line_info_pretty;

            $file = file($trace['file']);
            // Read first line
            // Move back to beginning of file
            $start_line_no = $trace['line']-3;
            $end_line_no = $trace['line']+3;
            $start_line_actual = $start_line_no + 1;
            $end_line_actual = $end_line_no + 1;
            $lines = array_slice($file, $start_line_no, 7);
            $line_no = $start_line_no;
            echo "<div class='code'>";
            echo "<pre data-start='".($line_no+1)."' data-line='".$trace['line']."' class='line-numbers language-php'>";
            echo "<code class='small '>";
            foreach ($lines as $line){
                $line_no++;
                $line = str_replace(' ', '&nbsp;', $line);
                if ( $line_no == $trace['line'] )
//                    $line_formatted = sprintf("<span class='bg-goldenrod-200'><strong>%s</strong></span>", $line);
//                    $line_formatted = sprintf("<span class='bg-goldenrod-200'><strong>[%s]:  %s</strong></span>", $line_no, $line);
                    $line_formatted = sprintf("%s", $line);
                else
//                    $line_formatted = sprintf("[%s]:  %s", $line_no, $line);
                    $line_formatted = sprintf("%s", $line);

                echo self::SH($line_formatted)."<BR>";
            }

            echo "</code>";
            echo "</pre>";
            echo "</div>";
            echo "</div>";
        }
        $content_pre = ob_get_clean();

        ob_start();
        echo "<p>" . $ex->getMessage() . "</p>";
        echo "<p>";
        echo implode("<BR>",$tags);
        echo "</p>";
        echo $content_pre;
        $content = ob_get_clean();
        return $content;
    }

    private static function SH($s) {
        $mainTextColor = '#000000';
        $s = str_replace(['&quot;', '&apos;', '&#34;', '&#39;'], ['"', "'", '"', "'"], $s);
        return '<span style="color:'.$mainTextColor.'">' . preg_replace_callback('/' . implode('|', [
                    '<.*?>', // embedded HTML tags
                    '&lt;!\-\-[\s\S]*?\-\-&gt;', // HTML comments
                    '\/\/[^\n]+', // comments
                    '#\s+[^\n]+', // comments
                    '\/\*[\s\S]*?\*\/', // comments
                    '"(?:[^\n"\\\]|\\\.)*"', // strings
                    '\'(?:[^\n\'\\\]|\\\.)*\'', // strings
                    '`(?:[^`\\\]|\\\.)*`', // ES6 strings
                    '&lt;\/?[\w:!-]+.*?&gt;', // HTML tags
                    '&lt;\?\S*', '\?&gt;', // templates
                    '\/[^\n]+\/[gimuy]*', // regular expressions
                    '\$\w+', // PHP variables
                    '&amp;[^\s;]+;', // entities
                    '\b(?:true|false|null)\b', // null and booleans
                    '(?:\d*\\.)?\d+', // numbers
                    '\b(?:a(?:bstract|lias|nd|rguments|rray|s(?:m|sert)?|uto)|b(?:ase|egin|ool(?:ean)?|reak|yte)|c(?:ase|atch|har|hecked|lass|lone|ompl|onst|ontinue)|de(?:bugger|cimal|clare|f(?:ault|er)?|init|l(?:egate|ete)?)|do|double|e(?:cho|ls?if|lse(?:if)?|nd|nsure|num|vent|x(?:cept|ec|p(?:licit|ort)|te(?:nds|nsion|rn)))|f(?:allthrough|alse|inal(?:ly)?|ixed|loat|or(?:each)?|riend|rom|unc(?:tion)?)|global|goto|guard|i(?:f|mp(?:lements|licit|ort)|n(?:it|clude(?:_once)?|line|out|stanceof|t(?:erface|ernal)?)?|s)|l(?:ambda|et|ock|ong)|m(?:odule|utable)|NaN|n(?:amespace|ative|ext|ew|il|ot|ull)|o(?:bject|perator|r|ut|verride)|p(?:ackage|arams|rivate|rotected|rotocol|ublic)|r(?:aise|e(?:adonly|do|f|gister|peat|quire(?:_once)?|scue|strict|try|turn))|s(?:byte|ealed|elf|hort|igned|izeof|tatic|tring|truct|ubscript|uper|ynchronized|witch)|t(?:emplate|hen|his|hrows?|ransient|rue|ry|ype(?:alias|def|id|name|of))|u(?:n(?:checked|def(?:ined)?|ion|less|signed|til)|se|sing)|v(?:ar|irtual|oid|olatile)|w(?:char_t|hen|here|hile|ith)|xor|yield)\b' // keywords
                ]) . '/', function($a) {
                $a = $a[0];
                if (!empty($a)) {
                    if (($a[0] === '<' && substr($a, -1) === '>') || preg_match('/^\W$/', $a)) {
                        // skip punctuations and "real" tags ...
                    } else if (substr($a, 0, 5) === '&lt;?' || $a === '?&gt;' || substr($a, 0, 7) === '&lt;!--') {
                        $a = '<span style="color:#008000;font-style:italic;">' . $a . '</span>'; // HTML comments and templates
                    } else if (substr($a, 0, 5) === '&lt;!') {
                        $a = '<span style="color:#4682B4;font-style:italic;">' . $a . '</span>'; // document types
                    } else if (substr($a, 0, 4) === '&lt;' && substr($a, -4) === '&gt;') {
                        $a = '<span style="color:inherit;">' . self::SH_TAG($a) . '</span>'; // tags
                    } else if (strpos('/#', $a[0]) !== false && preg_match('/^(\/\/|#\s+|\/\*)/', $a)) {
                        $a = '<span style="color:#808080;font-style:italic;">' . $a . '</span>'; // comments
                    } else if (strpos('"\'`', $a[0]) !== false) {
                        $a = '<span style="color:#008000;">' . $a . '</span>'; // strings
                    } else if ($a[0] === '/') {
                        $a = '<span style="color:#4682B4;">' . $a . '</span>'; // regular expressions
                    } else if (is_numeric($a)) {
                        $a = '<span style="color:#0000FF;">' . $a . '</span>'; // numbers
                    } else if ($a === 'true' || $a === 'false' || $a === 'null') {
                        $a = '<span style="color:#A52A2A;font-weight:bold;">' . $a . '</span>'; // null and booleans
                    } else if ($a[0] === '$') {
                        $a = '<span style="font-weight:bold;">' . $a . '</span>'; // PHP variables
                    } else if (substr($a, 0, 5) === '&amp;' && substr($a, -1) === ';') {
                        $a = '<span style="color:#FF4500;">' . $a . '</span>'; // entities
                    } else {
                        $a = '<span style="color:#FF0000;">' . $a . '</span>'; // keywords
                    }
                }
                return $a;
            }, $s) . '</span>';
    }

    private static function SH_TAG($s) {
        return preg_replace_callback('/&lt;(\/?)(\S+)(\s.*?)?&gt;/', function($m) {
            $m[2] = '<span style="color:#800080;font-weight:bold;">' . $m[2] . '</span>';
            if (!empty($m[3])) {
                $m[3] = preg_replace_callback('/(\s+)([^\s=]+)(?:=("(?:[^\n"\\\]|\\\.)*"|\'(?:[^\n\'\\\]|\\\.)*\'|[^\s"\']+))?/', function($m) {
                    $o = $m[1] . '<span style="font-weight:bold;">' . $m[2] . '</span>';
                    if (!empty($m[3])) {
                        $o .= '=<span style="color:#0000FF;">' . $m[3] . '</span>';
                    }
                    return $o;
                }, $m[3]);
            } else {
                $m[3] = "";
            }
            return '&lt;' . $m[1] . $m[2] . $m[3] . '&gt;';
        }, $s);
    }

}


