<?php

class MainTemplater {

    private $template;

    function __construct($template = null) {

        foreach ($GLOBALS as $key => $values) {
            $this->$key = $values;
        }
        if (isset($template)) {
            $this->load($template);
        }
    }

    public function load($template) {
        /*
         * This function loads the template file
         */

        if (!is_file($template)) {
            echo "file not found";
            // throw new FileNotFoundException("File not found: $template");
        } elseif (!is_readable($template)) {
            throw new IOException("Could not access file: $template");
        } else {
            $this->template = $template;
        }
    }

    public function put($var, $content) {
        $this->$var = $content;
    }

    //public function get($var, $key) {
      //  $var = $this->$key;
    //}

    public function publish($output = true) {
        /*
         * Prints out the theme to the page
         * However, before we do that, we need to remove every var witin {} that are not set
         * @params
         *  $output - whether to output the template to the screen or to just return the template
         */
        ob_start();
        require $this->template;
        $content = ob_get_clean();
        print $content;
    }

    public function compile() {
        /*
         * Function that just returns the template file so it can be reused
         */
       ob_start();
       require $this->template;
        $content = ob_get_clean();
        for($i=0;$i<5;$i++){
          $content = preg_replace_callback('/\{([A-Z_]+)\}/',
            function ($matches) {
                return (defined($matches[1]) ? constant($matches[1]) : $matches[0]);
            },
            $content
          );
          if (strpos($content,"{")<0){
            break;
          }
        }

        return $content;
    }

    /*public function warning($fileName, $variableName) {
        echo '
        <br><div class="alert alert-danger">
          <strong>Warning! </strong>
          <b>' . $variableName . '</b> not fount in <b>' . $fileName . '</b> pattren for variable is {{' . $variableName . '}}
        </div>
        ';
    }*/

}

?>