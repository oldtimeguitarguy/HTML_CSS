<?php
/**
 * Customize error renderer with PEAR_ErrorStack and PEAR::Log
 *
 * PHP versions 4 and 5
 *
 * @category   HTML
 * @package    HTML_CSS
 * @subpackage Examples
 * @author     Klaus Guenther <klaus@capitalfocus.org>
 * @author     Laurent Laville <pear@laurent-laville.org>
 * @copyright  2005-2007 Klaus Guenther, Laurent Laville
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD
 * @version    CVS: $Id$
 * @link       http://pear.php.net/package/HTML_CSS
 * @since      File available since Release 1.0.0RC1
 * @ignore
 */

require_once 'HTML/CSS.php';
require_once 'HTML/CSS/Error.php';
require_once 'PEAR/ErrorStack.php';
require_once 'Log.php';

/**
 * This class creates a css error stack object with help of PEAR_ErrorStack
 *
 * @ignore
 */
class HTML_CSS_ErrorStack
{
    function HTML_CSS_ErrorStack()
    {
        $s = &PEAR_ErrorStack::singleton('HTML_CSS');
        $t = HTML_CSS_Error::_getErrorMessage();
        $s->setErrorMessageTemplate($t);
        $s->setMessageCallback(array(&$this,'getMessage'));
        $s->setContextCallback(array(&$this,'getBacktrace'));

        $ident = $_SERVER['REMOTE_ADDR'];
        $conf  = array('lineFormat' => '%1$s - %2$s [%3$s] %4$s');
        $file  = &Log::singleton('file', 'html_css_err.log', $ident, $conf);

        $conf    = array('error_prepend' => '<font color="#ff0000"><tt>',
                         'error_append'  => '</tt></font>');
        $display = &Log::singleton('display', '', '', $conf);

        $composite = &Log::singleton('composite');
        $composite->addChild($display);
        $composite->addChild($file);

        $s->setLogger($composite);
        $s->pushCallback(array(&$this,'errorHandler'));
    }

    function push($code, $level, $params)
    {
        $s = &PEAR_ErrorStack::singleton('HTML_CSS');
        return $s->push($code, $level, $params);
    }

    /**
     *  default ErrorStack message callback is
     *  PEAR_ErrorStack::getErrorMessage()
     */
    function getMessage(&$stack, $err, $template = false)
    {
        global $prefs;

        $message = $stack->getErrorMessage($stack, $err, $template);

        $lineFormat    = '%1$s %2$s [%3$s]';
        $contextFormat = 'in %1$s on line %2$s';

        if (isset($prefs['handler']['display']['lineFormat'])) {
            $lineFormat = $prefs['handler']['display']['lineFormat'];
            $lineFormat = strip_tags($lineFormat);
        }
        if (isset($prefs['handler']['display']['contextFormat'])) {
            $contextFormat = $prefs['handler']['display']['contextFormat'];
            $contextFormat = strip_tags($contextFormat);
        }

        $context = $err['context'];

        if ($context) {
            $file = $context['file'];
            $line = $context['line'];

            $contextExec = sprintf($contextFormat, $file, $line);
        } else {
            $contextExec = '';
        }

        $msg = sprintf($lineFormat, '', $message, $contextExec);
        return trim($msg);
    }

    function getBacktrace()
    {
        if (function_exists('debug_backtrace')) {
            $backtrace = debug_backtrace();
            $backtrace = $backtrace[count($backtrace)-1];
        } else {
            $backtrace = false;
        }
        return $backtrace;
    }

    function errorHandler($err)
    {
        global $halt_onException;

        if ($halt_onException) {
            if ($err['level'] == 'exception') {
                return PEAR_ERRORSTACK_DIE;
            }
        }
    }
}

// set it to on if you want to halt script on any exception
$halt_onException = false;


// Example A. ---------------------------------------------

$stack =& new HTML_CSS_ErrorStack();

$attribs = array();
$prefs   = array('error_handler' => array(&$stack, 'push'));

// A1. Error
$css1 = new HTML_CSS($attribs, $prefs);

$group1 = $css1->createGroup('body, html', 'grp1');
$group2 = $css1->createGroup('p, html', 'grp1');


// Example B. ---------------------------------------------

$displayConfig = array(
    'lineFormat' => '<b>%1$s</b>: %2$s<br/>%3$s<hr/>',
    'contextFormat' =>   '<b>File:</b> %1$s <br/>'
                       . '<b>Line:</b> %2$s '
);
$attribs       = array();
$prefs         = array(
    'error_handler' => array(&$stack, 'push'),
    'handler' => array('display' => $displayConfig)
);

$css2 = new HTML_CSS($attribs, $prefs);

// B1. Error
$css2->getStyle('h1', 'class');

// B2. Exception
$css2->setXhtmlCompliance('true');

print 'still alive !';
?>