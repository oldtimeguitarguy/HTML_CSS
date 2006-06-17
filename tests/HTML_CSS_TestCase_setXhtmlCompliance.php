<?php
/**
 * API setXhtmlCompliance Unit tests for HTML_CSS class.
 *
 * @version    $Id$
 * @author     Laurent Laville <pear@laurent-laville.org>
 * @package    HTML_CSS
 * @ignore
 */

require_once 'PEAR.php';

/**
 * @ignore
 */
class HTML_CSS_TestCase_setXhtmlCompliance extends PHPUnit_TestCase
{
    /**
     * A CSS object
     * @var        object
     */
    var $stylesheet;

    function HTML_CSS_TestCase_setXhtmlCompliance($name)
    {
        $this->PHPUnit_TestCase($name);
    }

    function setUp()
    {
        error_reporting(E_ALL & ~E_NOTICE);

        $attrs = array();
        $prefs= array('push_callback' => array(&$this, '_handleError'));
        $this->stylesheet = new HTML_CSS($attrs, $prefs);
    }

    function tearDown()
    {
        unset($this->stylesheet);
    }

    function _methodExists($name)
    {
        if (substr(PHP_VERSION,0,1) < '5') {
            $n = strtolower($name);
        } else {
            $n = $name;
        }
        if (in_array($n, get_class_methods($this->stylesheet))) {
            return true;
        }
        $this->assertTrue(false, 'method '. $name . ' not implemented in ' . get_class($this->stylesheet));
        return false;
    }

    function _handleError($code, $level)
    {
        // don't die if the error is an exception (as default callback)
        return PEAR_ERROR_RETURN;
    }

    function _getResult($res)
    {
        if (PEAR::isError($res)) {
            $msg = $res->getMessage() . '&nbsp;&gt;&gt;';
            $this->assertTrue(false, $msg);
        } else {
            $this->assertTrue(true);
        }
    }

    /**
     * Tests a setXhtmlCompliance method
     */
    function test_setXhtmlCompliance_fail_no_boolean()
    {
        $c = $this->stylesheet->setXhtmlCompliance('true');
        $this->_getResult($c);
    }

    function test_setXhtmlCompliance()
    {
        $c = $this->stylesheet->setXhtmlCompliance(true);
        $this->_getResult($c);
    }
}
?>