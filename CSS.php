<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | PHP Version 4                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997 - 2003 The PHP Group                              |
// +----------------------------------------------------------------------+
// | This source file is subject to version 3.0 of the PHP license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available at through the world-wide-web at                           |
// | http://www.php.net/license/3_0.txt.                                  |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Author:  Klaus Guenther <klaus@capitalfocus.org>                     |
// +----------------------------------------------------------------------+
//
// $Id$

require_once "PEAR.php";
require_once "HTML/Common.php";

/**
 * Base class for CSS definitions
 *
 * This class handles the details for creating properly constructed CSS declarations.
 *
 * Example for direct output of stylesheet:
 * <code>
 * require_once 'HTML/CSS.php';
 * 
 * $css = new HTML_CSS();
 * 
 * // define styles
 * $css->setStyle('body', 'background-color', '#0c0c0c');
 * $css->setStyle('body', 'color', '#ffffff');
 * $css->setStyle('h1', 'text-align', 'center');
 * $css->setStyle('h1', 'font', '16pt helvetica, arial, sans-serif');
 * $css->setStyle('p', 'font', '12pt helvetica, arial, sans-serif');
 *
 * // output the stylesheet directly to browser
 * $css->display();
 * </code>
 *
 * Example in combination with HTML_Page:
 * <code>
 * require_once 'HTML/Page.php';
 * require_once 'HTML/CSS.php';
 * 
 * $css = new HTML_CSS();
 * $css->setStyle('body', 'background-color', '#0c0c0c');
 * $css->setStyle('body', 'color', '#ffffff');
 * $css->setStyle('h1', 'text-align', 'center');
 * $css->setStyle('h1', 'font', '16pt helvetica, arial, sans-serif');
 * $css->setStyle('p', 'font', '12pt helvetica, arial, sans-serif');
 *
 * $p = new HTML_Page();
 *
 * $p->setTitle("My page");
 * // it can be added as an object
 * $p->addStyleDeclaration($css, 'text/css');
 * $p->setMetaData("author", "My Name");
 * $p->addBodyContent("<h1>headline</h1>");
 * $p->addBodyContent("<p>some text</p>");
 * $p->addBodyContent("<p>some more text</p>");
 * $p->addBodyContent("<p>yet even more text</p>");
 * $p->display();
 * </code>
 * 
 * Example for generating inline code:
 * <code>
 * require_once 'HTML/CSS.php';
 * 
 * $css = new HTML_CSS();
 * 
 * $css->setStyle('body', 'background-color', '#0c0c0c');
 * $css->setStyle('body', 'color', '#ffffff');
 * $css->setStyle('h1', 'text-align', 'center');
 * $css->setStyle('h1', 'font', '16pt helvetica, arial, sans-serif');
 * $css->setStyle('p', 'font', '12pt helvetica, arial, sans-serif');
 * $css->setSameStyle('body', 'p');
 * 
 * echo '<body style="' . $css->toInline('body') . '">';
 * // will output:
 * // <body style="font:12pt helvetica, arial, sans-serif;background-color:#0c0c0c;color:#ffffff;">
 * </code>
 *
 * @author     Klaus Guenther <klaus@capitalfocus.org>
 * @package    HTML_CSS
 * @version    0.3.0
 * @access     public
 * @license    http://www.php.net/license/3_0.txt  PHP License 3.0
 */
class HTML_CSS extends HTML_Common {
    
    /**
     * Contains the CSS definitions.
     *
     * @var     array
     * @access  private
     */
    var $_css = array();
    
    /**
     * Contains "alibis" (other elements that share a definition) of an element defined in CSS
     *
     * @var     array
     * @access  private
     */
    var $_alibis = array();
    
    /**
     * Controls caching of the page
     *
     * @var     bool
     * @access  private
     */
    var $_cache = true;
    
    /**
     * Contains the character encoding string
     *
     * @var     string
     * @access  private
     */
    var $_charset = 'iso-8859-1';
    
    /**
     * Class constructor
     *
     * @access  public
     */
    function HTML_CSS()
    {
        $commonVersion = 1.7;
        if (HTML_Common::apiVersion() < $commonVersion) {
            return PEAR::raiseError("HTML_CSS version " . $this->apiVersion() . " requires " .
            "HTML_Common version 1.2 or greater.", 0, PEAR_ERROR_TRIGGER);
        }
    }
    
    /**
     * Returns the current API version
     *
     * @access   public
     * @returns  double
     */
    function apiVersion()
    {
        return 0.3;
    } // end func apiVersion
    
    /**
     * Sets or adds a CSS definition
     *
     * @param    string  $element   Element (or class) to be defined
     * @param    string  $property  Property defined
     * @param    string  $value     Value assigned
     * @access   public
     */
    function setStyle ($element, $property, $value)
    {
        $this->_css[$element][$property]= $value;
    } // end func setStyle
    
    /**
     * Retrieves the value of a CSS property
     *
     * @param    string  $element   Element (or class) to be defined
     * @param    string  $property  Property defined
     * @access   public
     */
    function getStyle($element, $property)
    {
        return $this->_css[$element][$property];
    } // end func getStyle
    
    /**
     * Sets or adds a CSS definition
     *
     * @param    string  $element   Element (or class) to be defined
     * @param    string  $others    Other elements that share the definitions, separated by commas
     * @access   public
     */
    function setSameStyle ($others, $element)
    {
        $others =  explode(',', $others);
        foreach ($others as $other) {
            $other = trim($other);
            $this->_css[$element]['other-elements'][] = $other;
            $this->_alibis[$other][] = $element;
        }
    } // end func setSameStyle
    
    /**
     * Defines if the document should be cached by the browser. Defaults to false.
     *
     * @param string $cache Options are currently 'true' or 'false'. Defaults to 'true'.
     * @access public
     */
    function setCache($cache = 'true')
    {
        if ($cache == 'true'){
            $this->_cache = true;
        } else {
            $this->_cache = false;
        }
    } // end func setCache
    
    /**
     * Defines the charset for the file. defaults to ISO-8859-1 because of CSS1
     * compatability issue for older browsers.
     *
     * @param string $type Charset encoding; defaults to ISO-8859-1.
     * @access public
     */
    function setCharset($type = 'iso-8859-1')
    {
        $this->_charset = $type;
    } // end func setCharset
    
    /**
     * Returns the charset encoding string
     *
     * @access public
     */
    function getCharset()
    {
        return $this->_charset;
    } // end func getCharset
    
    /**
     * Parse a textstring that contains css information
     *
     * @param    string  $str    text string to parse
     * @since    0.3.0
     * @access   public
     * @return   void
     */
    function parseString($str) 
    {
        // Remove comments
        $str = preg_replace("/\/\*(.*)?\*\//Usi", "", $str);
        
        // Parse each element of csscode
        $parts = explode("}",$str);
        foreach($parts as $part) {
            $part = trim($part);
            if (strlen($part) > 0) {
                
                // Parse each group of element in csscode
                list($keystr,$codestr) = explode("{",$part);
                $keys = explode(",",trim($keystr));
                foreach($keys as $key) {
                    $key = trim($key);
                    if (strlen($key) > 0) {
                        
                        // Parse each property of an element
                        $codes = explode(";",trim($codestr));
                        foreach ($codes as $code) {
                            if (strlen($code) > 0) {
                                list($property,$value) = explode(":",trim($code));
                                $this->setStyle($key, $property, $value);
                            }
                        }
                    }
                }
            }
        }
    } // end func parseString
    
    /**
     * Parse a file that contains CSS information
     *
     * @param    string  $filename    file to parse
     * @since    0.3.0
     * @return   void
     * @access   public
     */
    function parseFile($filename) 
    { 
        if (file_exists($filename)) {
            if (function_exists('file_get_contents')){
                $this->parseString(file_get_contents($filename));
            } else {
                $file = fopen("$filename", "rb");
                $this->parseString(fread($file, filesize($filename)));
                fclose($file);
            }
            
        } else {
            return PEAR::raiseError("HTML_CSS::parseFile() error: $filename does not exist.",
                                        0, PEAR_ERROR_TRIGGER);
        }
    } // func parseFile
    
    /**
     * Generates and returns the array of CSS properties
     *
     * @return  array
     * @access  public
     */
    function toArray()
    {
        return $this->_css;
    } // end func toArray
    
    /**
     * Generates and returns the CSS properties of an element or class as a string for inline use.
     *
     * @param   string  $element    Element or class for which inline CSS should be generated
     * @return  string
     * @access  public
     */
    function toInline($element)
    {
        $strCss = '';
        $newCssArray = '';
        
        // Iterate through the array of properties for the supplied element
        // This allows for grouped elements definitions to work
        if ($this->_alibis[$element]) {
            $alibis = $this->_alibis[$element];
            foreach ($alibis as $int => $newElement) {
                foreach ($this->_css[$newElement] as $key => $value) {
                    if ($key != 'other-elements') {
                        $newCssArray[$key] = $value;
                    }
                }
            }
        }
        
        // The reason this comes second is because if something is defined twice,
        // the value specifically assigned to this element should override
        // values inherited from other element definitions
        if ($this->_css[$element]) {
            foreach ($this->_css[$element] as $key => $value) {
                if ($key != 'other-elements') {
                    $newCssArray[$key] = $value;
                }
            }
        }
        
        foreach ($newCssArray as $key => $value) {
            $strCss .= $key . ':' . $value . ";";
        }
        
        // Let's roll!
        return $strCss;
    } // end func toInline
    
    /**
     * Generates CSS and stores it in a file.
     *
     * @return  void
     * @since   0.3.0
     * @access  public
     */
    function toFile($filename)
    {
        if (function_exists('file_put_content')){
            file_put_content($filename, $this->toString());
        } else {
            $file = fopen($filename,'wb');
            fwrite($file, $this->toString());
            fclose($file);
        }
        if (!file_exists($filename)){
            return PEAR::raiseError("HTML_CSS::toFile() error: Failed to write to $filename",
                                        0, PEAR_ERROR_TRIGGER);
        }
        
    } // end func toFile
    
    /**
     * Generates and returns the complete CSS as a string.
     *
     * @return string
     * @access public
     */
    function toString()
    {
        // get line endings
        $lnEnd = $this->_getLineEnd();
        $tabs = $this->_getTabs();
        $tab = $this->_getTab();
        
        $strCss = '';
        
        // Allow a CSS comment
        if ($this->_comment) {
            $strCss = $tabs . '/* ' . $this->getComment() . ' */' . $lnEnd;
        }
        
        // Iterate through the array and process each element
        foreach ($this->_css as $element => $property) {
            $strCss .= $lnEnd;
            $alibis = '';
            if (isset($property['other-elements']) && is_array($property['other-elements'])){
                foreach ($property['other-elements'] as $int => $other) {
                    $alibis .= "$other, ";
                }
            }
            //start CSS element definition
            $strCss .= $tabs . $alibis . $element . ' {' . $lnEnd;
            
            foreach ($property as $key => $value) {
                if ($key != 'other-elements') {
                    $strCss .= $tabs . $tab . $key . ': ' . $value . ';' . $lnEnd;
                }
            }
            
            // end CSS element definition
            $strCss .= $tabs . '}' . $lnEnd;
        }
        
        // Let's roll!
        return $strCss;
    } // end func toString
    
    /**
     * Outputs the stylesheet to the browser.
     *
     * @access    public
     */
    function display()
    {
        $lnEnd = $this->_getLineEnd();
        
        if(! $this->_cache) {
            header("Expires: Tue, 1 Jan 1980 12:00:00 GMT");
            header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
            header("Cache-Control: no-cache");
            header("Pragma: no-cache");
        }
        
        // set character encoding
        header("Content-Type: text/css; charset=" . $this->_charset);
        
        $strCss = $this->toString();
        print $strCss;
    } // end func display
}
?>
