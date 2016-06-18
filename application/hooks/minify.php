<?php
/**
 * Created by PhpStorm.
 * User: Sayed
 * Date: 08-02-2016
 * Time: 08:20
 * Download YUI compressor from: https://github.com/yui/yuicompressor/releases
 */
class Minify
{
    static private $css;
    static private $js;
    var $auto_pick;
    public function init()
    {
        self::$css = [];
        self::$js = [];
        $ci = &get_instance();
        $config = $ci->config->item('minify_auto');
        $this->auto_pick = $config===null ?true :$config;
    }
    public static function storeInList($filename, $type)
    {
        $type = strtolower($type);
        $filename = trim($filename);
        if($type=='css')
        {
            if(!in_array($filename,self::$css))
            {
                array_push(self::$css,$filename);
                return base_url($filename);
            }
        }
        else if($type=='js')
        {
            if(!in_array($filename,self::$js))
            {
                array_push(self::$js,$filename);
                return base_url($filename);
            }
        }
    }

    public static function css($filename)
    {
        return self::storeInList($filename,'css');
    }
    public static function js($filename)
    {
        return self::storeInList($filename,'js');
    }

    public function renderHTML()
    {
        date_default_timezone_set('Asia/Calcutta');
        $ci = &get_instance();
        $output = $ci->output->get_output();
        $dom = new DOMDocument;
        @$dom->loadHTML($output);

        $xpath = new DOMXPath($dom);
        $scripts = $xpath->query('//script');

        $filename = '';
        if($scripts->length>0)
        {
            $last_mtime = 0;
            $parent = $scripts->item(0)->parentNode;
            foreach($scripts as $script)
            {
                $src = $script->getAttribute('src');
//                echo '<br>processing:'.$src;
                if(self::isLocalPath($src))
                {
                    $src = str_replace(base_url('/'),'',$src);
                    $file_mtime = $src ?filemtime(FCPATH.$src) :0;
                    $last_mtime = $file_mtime ?($file_mtime>$last_mtime ?$file_mtime :$last_mtime) :$last_mtime;
                    if($src && ($this->auto_pick || in_array($src,self::$js)))
                    {
                        $script->parentNode->removeChild($script);
                        $src = explode('.',basename($src));
                        array_pop($src);
                        $src = join('.',$src);
                        //$src = preg_replace('/^\d/','', $src);
                        $src = str_replace('.min','',$src);
                        $src = trim($src,'.');
                        //$filename .= '.'.$src;
                        $filename .= $src & 'random';//bitwise AND
                    }
                }
            }
            $filename = trim(str_replace('.min','',$filename),'.');
            $filename = preg_replace('/[^a-zA-Z0-9-]+/','',$filename);
            if($filename!='')
            {
                @mkdir(FCPATH."assets/cache/js/",0777, true);
                $version = '-v'.date('Ymd');
                $filename = FCPATH."assets/cache/js/$filename$version.min.js";
                $file_mtime = file_exists($filename) ?filemtime($filename) :0;
                if($file_mtime < $last_mtime)
                {
                    $content = '';
                    foreach(self::$js as $script)
                    {
                        $content .= trim(file_get_contents($script))."\n\r";
                    }
                    file_put_contents($filename,self::getCompressedJS($content));
                }
                $script = $dom->createElement('script');
                $script->setAttribute('type','text/javascript');
                $script->setAttribute('src',str_replace(FCPATH,base_url('/'),$filename));
                $parent->appendChild($script);
            }
        }

        $stylesheets = $xpath->query('//link');
        $filename = '';
        if($stylesheets->length>0)
        {
            $last_mtime = 0;
            foreach($stylesheets as $css)
            {
                $src = $css->getAttribute('href');
                $rel = strtolower($css->getAttribute('rel'));
                if(self::isLocalPath($src) && $rel=='stylesheet')
                {
                    $src = ltrim($src, base_url('/'));
                    $file_mtime = $src ?filemtime(FCPATH.$src) :0;
                    //echo "<br>Filename: $src<br>mTime: ".date('Y-m-d H:i:s',$file_mtime)."<br>Last:".date('Y-m-d H:i:s',$last_mtime);
                    $last_mtime = $file_mtime>$last_mtime ?$file_mtime :$last_mtime;
                    if($src && ($this->auto_pick  || in_array($src,self::$css)))
                    {
                        $css->parentNode->removeChild($css);
                        $src = explode('.',basename($src));
                        array_pop($src);
                        $src = join('.',$src);
                        //$src = preg_replace('/^\d/','', $src);
                        $src = str_replace('.min','',$src);
                        $src = trim($src,'.');
                        //$filename .= '.'.$src;
                        $filename .= $src & 'random';//bitwise AND
                    }
                }
            }
            //echo ('<br>Largest:'.$last_mtime);
            $filename = trim(str_replace('.min','',$filename),'.');
            $filename = preg_replace('/[^a-zA-Z0-9-]+/','',$filename);
            if($filename!='')
            {
                @mkdir(FCPATH."assets/cache/css/",0777, true);
                $version = '-v'.date('Ymd');
                $filename = FCPATH."assets/cache/css/$filename$version.min.css";
                $file_mtime = file_exists($filename) ?filemtime($filename) :0;
                //die('<br>mTime:'.$file_mtime.'  '.$filename.' '.$last_mtime);
                if($file_mtime < $last_mtime)
                {
                    $content = '';
                    foreach(self::$css as $script)
                    {
                        $script = FCPATH.$script;
                        $min_css = trim(file_get_contents($script));
                        $min_css = self::getCompressedCSS($min_css, $script);
                        $content .= $min_css."\n\r";
                    }
                    file_put_contents($filename,$content);
                }
                $path = str_replace(FCPATH,base_url('/'),$filename);
                $script = $dom->createElement('link');
                $script->setAttribute('rel','stylesheet');
                $script->setAttribute('type','text/css');
                $script->setAttribute('href',$path);
                $xpath->query('//head')->item(0)->appendChild($script);
            }
        }
        $html = $dom->saveHTML();
        echo $html;
//        echo self::getCompressedHTML($html);
    }

    /*Copied from:https://gist.github.com/tovic/d7b310dea3b33e4732c0
    */
    public function getCompressedCSS($input, $orig_file)
    {
        if(trim($input) === "") return $input;
        $files = [];
        preg_match_all('/(?:\.\.\/)+(.*?\))/', $input, $files);
        $files = count($files)>0 ?$files[0] :null;
        if($files)
        {
            $cache_path = FCPATH.'assets/cache/';
            if(!is_dir($cache_path))
                @mkdir($cache_path,0777,true);
            $orig_path = dirname($orig_file);

            chdir($orig_path);
            foreach($files as $file)
            {
                $file = str_replace(')','',$file);
                $file = explode('#',$file)[0];//remove version info from filename
                $file = explode('?',$file)[0];//remove version info from filename
                $file = trim($file,"'");
                $file = trim($file,'"');

                $source = realpath($file);
                $dest = $cache_path.'css/'.$file;
                $dir = dirname($dest);
                try
                {
                    if(!is_dir($dir))
                        mkdir($dir,0777,true);
                }
                catch(Exception $e)
                {
                    throw $e;
                }
                try
                {
                    if(!file_exists($dest) && !copy($source, $dest))
                        echo '<br>Error in copying file:'.error_get_last();
                }
                catch(Exception $e)
                {
                    throw $e;
                }
            }
            chdir(__dir__);
        }
        return preg_replace(
            array(
                // Remove comment(s)
                '#("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\')|\/\*(?!\!)(?>.*?\*\/)|^\s*|\s*$#s',
                // Remove unused white-space(s)
                '#("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\'|\/\*(?>.*?\*\/))|\s*+;\s*+(})\s*+|\s*+([*$~^|]?+=|[{};,>~+]|\s*+-(?![0-9\.])|!important\b)\s*+|([[(:])\s++|\s++([])])|\s++(:)\s*+(?!(?>[^{}"\']++|"(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\')*+{)|^\s++|\s++\z|(\s)\s+#si',
                // Replace `0(cm|em|ex|in|mm|pc|pt|px|vh|vw|%)` with `0`
                '#(?<=[\s:])(0)(cm|em|ex|in|mm|pc|pt|px|vh|vw|%)#si',
                // Replace `:0 0 0 0` with `:0`
                '#:(0\s+0|0\s+0\s+0\s+0)(?=[;\}]|\!important)#i',
                // Replace `background-position:0` with `background-position:0 0`
                '#(background-position):0(?=[;\}])#si',
                // Replace `0.6` with `.6`, but only when preceded by `:`, `,`, `-` or a white-space
                '#(?<=[\s:,\-])0+\.(\d+)#s',
                // Minify string value
                '#(\/\*(?>.*?\*\/))|(?<!content\:)([\'"])([a-z_][a-z0-9\-_]*?)\2(?=[\s\{\}\];,])#si',
                '#(\/\*(?>.*?\*\/))|(\burl\()([\'"])([^\s]+?)\3(\))#si',
                // Minify HEX color code
                '#(?<=[\s:,\-]\#)([a-f0-6]+)\1([a-f0-6]+)\2([a-f0-6]+)\3#i',
                // Replace `(border|outline):none` with `(border|outline):0`
                '#(?<=[\{;])(border|outline):none(?=[;\}\!])#',
                // Remove empty selector(s)
                '#(\/\*(?>.*?\*\/))|(^|[\{\}])(?:[^\s\{\}]+)\{\}#s'
            ),
            array(
                '$1',
                '$1$2$3$4$5$6$7',
                '$1',
                ':0',
                '$1:0 0',
                '.$1',
                '$1$3',
                '$1$2$4$5',
                '$1$2$3',
                '$1:0',
                '$1$2'
            ),
            $input);
    }

    /*Right now, just removes comments from JS*/
    function getCompressedJS($input)
    {
        return $input;
        if(trim($input) === "") return $input;
        return preg_replace(
            array(
                // Remove comment(s)
                '#\s*("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\')\s*|\s*\/\*(?!\!|@cc_on)(?>[\s\S]*?\*\/)\s*|\s*(?<![\:\=])\/\/.*(?=[\n\r]|$)|^\s*|\s*$#',
                // Remove white-space(s) outside the string and regex
                '#("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\'|\/\*(?>.*?\*\/)|\/(?!\/)[^\n\r]*?\/(?=[\s.,;]|[gimuy]|$))|\s*([!%&*\(\)\-=+\[\]\{\}|;:,.<>?\/])\s*#s',
                // Remove the last semicolon
                '#;+\}#',
                // Minify object attribute(s) except JSON attribute(s). From `{'foo':'bar'}` to `{foo:'bar'}`
                '#([\{,])([\'])(\d+|[a-z_][a-z0-9_]*)\2(?=\:)#i',
                // --ibid. From `foo['bar']` to `foo.bar`
                '#([a-z0-9_\)\]])\[([\'"])([a-z_][a-z0-9_]*)\2\]#i'
            ),
            array(
                '$1',
                '$1$2',
                '}',
                '$1$3',
                '$1.$3'
            ),
            $input);
    }

    public function getCompressedHTML($input)
    {
        if(trim($input) === "") return $input;
        // Remove extra white-space(s) between HTML attribute(s)
        $input = preg_replace_callback('#<([^\/\s<>!]+)(?:\s+([^<>]*?)\s*|\s*)(\/?)>#s', function($matches) {
            return '<' . $matches[1] . preg_replace('#([^\s=]+)(\=([\'"]?)(.*?)\3)?(\s+|$)#s', ' $1$2', $matches[2]) . $matches[3] . '>';
        }, str_replace("\r", "", $input));
        // Minify inline CSS declaration(s)
        if(strpos($input, ' style=') !== false) {
            $input = preg_replace_callback('#<([^<]+?)\s+style=([\'"])(.*?)\2(?=[\/\s>])#s', function($matches) {
                return '<' . $matches[1] . ' style=' . $matches[2] . self::getCompressedCSS($matches[3]) . $matches[2];
            }, $input);
        }
        return preg_replace(
            array(
                // t = text
                // o = tag open
                // c = tag close
                // Keep important white-space(s) after self-closing HTML tag(s)
                '#<(img|input)(>| .*?>)#s',
                // Remove a line break and two or more white-space(s) between tag(s)
                '#(<!--.*?-->)|(>)(?:\n*|\s{2,})(<)|^\s*|\s*$#s',
                '#(<!--.*?-->)|(?<!\>)\s+(<\/.*?>)|(<[^\/]*?>)\s+(?!\<)#s', // t+c || o+t
                '#(<!--.*?-->)|(<[^\/]*?>)\s+(<[^\/]*?>)|(<\/.*?>)\s+(<\/.*?>)#s', // o+o || c+c
                '#(<!--.*?-->)|(<\/.*?>)\s+(\s)(?!\<)|(?<!\>)\s+(\s)(<[^\/]*?\/?>)|(<[^\/]*?\/?>)\s+(\s)(?!\<)#s', // c+t || t+o || o+t -- separated by long white-space(s)
                '#(<!--.*?-->)|(<[^\/]*?>)\s+(<\/.*?>)#s', // empty tag
                '#<(img|input)(>| .*?>)<\/\1>#s', // reset previous fix
                '#(&nbsp;)&nbsp;(?![<\s])#', // clean up ...
                '#(?<=\>)(&nbsp;)(?=\<)#', // --ibid
                // Remove HTML comment(s) except IE comment(s)
                '#\s*<!--(?!\[if\s).*?-->\s*|(?<!\>)\n+(?=\<[^!])#s'
            ),
            array(
                '<$1$2</$1>',
                '$1$2$3',
                '$1$2$3',
                '$1$2$3$4$5',
                '$1$2$3$4$5$6$7',
                '$1$2$3',
                '<$1$2',
                '$1 ',
                '$1',
                ""
            ),
            $input);
    }

    public function isLocalPath($path)
    {
        $path = str_replace(base_url('/'),'',$path);
        $temp = parse_url($path);
        return empty($temp['host']);
    }
}

class Resource extends Minify{

}