<?php namespace Maherelgamil\Asset\Models;



class Compressor
{


    /**
     * compress css or js
     *
     * @param $buffer
     * @param string $type
     * @return mixed
     */
    public function compress($buffer , $type = 'css')
    {
        if($type == 'css')
        {
            $buffer = $this->compressCss($buffer);
        }
        elseif($type == 'js')
        {
            $buffer = $this->compressJs($buffer);
        }

        return $buffer;
    }


    /**
     * compress Css
     * @param $buffer
     * @return mixed
     */
    protected function compressCss($buffer)
    {
        // Remove comments:
        $buffer = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $buffer);
        // Remove tabs, excessive spaces and newlines
        $buffer = str_replace(["\r\n", "\r", "\n", "\t", '  ', '   '], '', $buffer);

        return $buffer ;
    }


    /**
     * compress Js
     *
     * @param $buffer
     * @return mixed
     */
    protected function compressJs($buffer)
    {
        $replace = [
            "/\/\*[\s\S]*?\*\//" => '',//remove nultile line comment
            "/\/\/.*$/" => '',//remove single line comment
            '#[\r\n]+#'   => "\n",// remove blank lines and \r's
            '#\n([ \t]*//.*?\n)*#s'   => "\n",// strip line comments (whole line only)
            '#([^\\])//([^\'"\n]*)\n#s' => "\\1\n",
            '#\n\s+#' => "\n",// strip excess whitespace
            '#\s+\n#' => "\n",// strip excess whitespace
            '#(//[^\n]*\n)#s' => "\\1\n", // extra line feed after any comments left
        ];

        $search = array_keys( $replace );
        $script = preg_replace( $search, $replace, $buffer );
        $replace = [
            "&&\n" => '&&',
            '|| ' => '||',
            "(\n"  => '(',
            ")\n"  => ')',
            "[\n"  => '[',
            "]\n"  => ']',
            "+\n"  => '+',
            ",\n"  => ',',
            "?\n"  => '?',
            ":\n"  => ':',
            ";\n"  => ';',
            "{\n"  => '{',
            "\n]"  => ']',
            "\n)"  => ')',
            "\n}"  => '}',
            ' ='  => '=',
            '= '  => '=',
            "\n\n" => ' ',
            'if (' => 'if(',
            ' || ' => '||'
        ];
        $search = array_keys($replace);
        $script = str_replace( $search, $replace, $script );
        $script = str_replace(';}', '}',$script);
        $buffer = preg_replace( "/\r|\n/", "", $script);


        return $buffer ;
    }
}