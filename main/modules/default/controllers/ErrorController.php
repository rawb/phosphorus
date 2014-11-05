<?php
class ErrorController extends Phosphorus_Core_Controller
{
    public function errorAction()
    {
      $meta = $this->getMeta();
      if(isset($meta["url_parameters"]["GET"]["error"])){
          $error = $meta["url_parameters"]["GET"]["error"];
	      echo "ERROR OH NOES!"."</br>";
	      echo '<img src="http://i.imgur.com/pGChc.gif" height="200" width="350">'."</br>";
	      echo "Message: ".$error->getMessage()."</br>";
	      echo "File: ".$error->getFile()."</br>";
	      echo "Line: ".$error->getLine()."</br>";
	      echo $this->stackTrace($error,1000);
      }else{
	      exit();
      }
    }
    
    /**
     * Retrieve the relevant portion of the PHP source file with syntax highlighting
     *
     * @param string    $fileName   The full path and filename to the source file
     * @param int       $lineNumber The line number which to highlight
     * @param int       $showLines  The number of surrounding lines to include as well
     */
    protected function _highlightSource($fileName, $lineNumber, $showLines)
    {
        $lines = file_get_contents($fileName);
        $lines = highlight_string($lines, true);
        $lines = explode("<br />", $lines);

        $offset = max(0, $lineNumber - ceil($showLines / 2));

        $lines = array_slice($lines, $offset, $showLines);

        $html = '';
        foreach ($lines as $line) {
            $offset++;
            $line = '<em class="lineno">' . sprintf('%4d', $offset) . ' </em>' . $line . '<br/>';
            if ($offset == $lineNumber) {
                $html .= '<div style="background: #ffc">' . $line . '</div>';
            } else {
                $html .= $line;
            }
        }

        return $html;
    }

    /**
     *
     * Print the stack Trace
     *
     * @param Exception $exception Any kind of exception
     * @param int       $showLines Number of surrounding lines to display (optional; defaults to 10)
     */
    public function stackTrace($exception, $showLines = 10)
    {
        $html  = '<style type="text/css">'
               . '.stacktrace p { margin: 0; padding: 0; }'
               . '.source { border: 1px solid #000; overflow: auto; background: #fff;'
               . ' font-family: monospace; font-size: 12px; margin: 0 0 25px 0 }'
               . '.lineno { color: #333; }'
               . '</style>'
               . '<div class="stacktrace">'
               . '<p>File: ' . $exception->getFile() . ' Line: ' . $exception->getLine() . '</p>'
               . '<div class="source">'
               . $this->_highlightSource($exception->getFile(), $exception->getLine(), $showLines)
               . '</div>';
        foreach ($exception->getTrace() as $trace) {
            $html .= '<p>File: ' . $trace['file'] . ' Line: ' . $trace['line'] . '</p>'
                   . '<div class="source">'
                   . $this->_highlightSource($trace['file'], $trace['line'], 5)
                   . '</div>';
        }
        $html .= '</div>';
        return $html;
    }
}