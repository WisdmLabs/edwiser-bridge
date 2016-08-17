<?php

namespace wisdmalbs\newtowrk\marketing;

if (!class_exists("wisdmalbs\newtowrk\marketing\ReadmeParse")) {
    class ReadmeParse
    {
        public function parseReadmeContents($fileContent)
        {
            $fileContent = str_replace(array("\r\n", "\r"), "\n", $fileContent);
            $fileContent = trim($fileContent);
            if (0 === strpos($fileContent, "\xEF\xBB\xBF")) {
                $fileContent = substr($fileContent, 3);
            }
            // Markdown transformations
            $fileContent = preg_replace("|^###([^#]+)#*?\s*?\n|im", '=$1='."\n", $fileContent);
            $fileContent = preg_replace("|^##([^#]+)#*?\s*?\n|im", '==$1=='."\n", $fileContent);
            $fileContent = preg_replace("|^#([^#]+)#*?\s*?\n|im", '===$1==='."\n", $fileContent);
            /*
             * Split the file content and create the array of the content.
             */
            $contToArray = preg_split('/^[\s]*==[\s]*(.+?)[\s]*==/m', $fileContent, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

            $readmeSec = array();

            /*
             * Prepare the array of the sections of the readme.
             */
            for ($index = 1; $index <= count($contToArray); $index += 2) {
                /*
                 * Change the =content= to <h4>content</h4>
                 */
                $contToArray[$index] = preg_replace('/^[\s]*=[\s]+(.+?)[\s]+=/m', '<h4>$1</h4>', $contToArray[$index]);

                /*
                 * Remove the whitespace and remove the escaping character from the html content/
                 */
                $secTitle = $this->remSpace($contToArray[$index - 1]);
                $readmeSec[str_replace(' ', '_', strtolower($secTitle))] = $contToArray[$index];
            }

            return $this->filterText($readmeSec['changelog'], true);
        }

        private function remSpace($text)
        {
            $text = strip_tags($text);
            $text = esc_html($text);
            $text = trim($text);

            return $text;
        }

        public function filterText($text, $markdown = false)
        {
            $text = trim($text);
            $text = call_user_func(array(__CLASS__, 'codeTrick'), $text, $markdown);
            // A better parser than Markdown's for: backticks -> CODE

            if ($markdown) { // Parse markdown.
                if (!function_exists('Markdown')) {
                    require_once 'MarkdownExtraParser.php';
                }
                $markdownParser = new MarkdownExtraParser();
                $text = $markdownParser->transform($text);
            }
            $allowed = array(
                'a' => array(
                    'href' => array(),
                    'title' => array(),
                    'rel' => array(),),
                'blockquote' => array('cite' => array()),
                'br' => array(),
                'p' => array(),
                'code' => array(),
                'pre' => array(),
                'em' => array(),
                'strong' => array(),
                'ul' => array(),
                'ol' => array(),
                'li' => array(),
                'h3' => array(),
                'h4' => array(),
            );

            $text = balanceTags($text);
            $text = wp_kses($text, $allowed);
            $text = trim($text);

            return $text;
        }

        public function codeTrick($text, $markdown)
        {
            if ($markdown) {
                $text = preg_replace_callback('!(<pre><code>|<code>)(.*?)(</code></pre>|</code>)!s', array(__CLASS__, 'decodeit'), $text);
            }

            $text = str_replace(array("\r\n", "\r"), "\n", $text);
            if (!$markdown) {
                // This gets the "inline" code blocks, but can't be used with Markdown.
                $text = preg_replace_callback('|(`)(.*?)`|', array(__CLASS__, 'encodeit'), $text);
                // This gets the "block level" code blocks and converts them to PRE CODE
                $text = preg_replace_callback("!(^|\n)`(.*?)`!s", array(__CLASS__, 'encodeit'), $text);
            } else {
                // Markdown can do inline code, we convert bbPress style block level code to Markdown style
                $text = preg_replace_callback("!(^|\n)([ \t]*?)`(.*?)`!s", array(__CLASS__, 'indent'), $text);
            }

            return $text;
        }

        public function encodeit($matches)
        {
            if (function_exists('encodeit')) {
                return encodeit($matches);
            }
            $text = trim($matches[2]);
            $text = htmlspecialchars($text, ENT_QUOTES);
            $text = str_replace(array("\r\n", "\r"), "\n", $text);
            $text = preg_replace("|\n\n\n+|", "\n\n", $text);
            $text = str_replace('&amp;lt;', '&lt;', $text);
            $text = str_replace('&amp;gt;', '&gt;', $text);
            $text = "<code>$text</code>";
            if ('`' != $matches[1]) {
                $text = "<pre>$text</pre>";
            }

            return $text;
        }
    }
}
