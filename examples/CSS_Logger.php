<?php
/**
 * ErrorHandler with logger example
 *
 * This example show how to use a class with all necessary methods
 * as an error handler for HTML_CSS.
 * It used the new plug-in system introduces in version 1.0.0RC1
 *
 * PHP versions 4 and 5
 *
 * LICENSE: This source file is subject to version 3.0 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_0.txt.  If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @category   HTML
 * @package    HTML_CSS
 * @subpackage Examples
 * @author     Klaus Guenther <klaus@capitalfocus.org>
 * @author     Laurent Laville <pear@laurent-laville.org>
 * @copyright  1997-2005 The PHP Group
 * @license    http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version    CVS: $Id$
 * @link       http://pear.php.net/package/HTML_CSS
 * @since      File available since Release 1.0.0RC1
 */

require_once 'HTML/CSS.php';
require_once 'PEAR.php';

class myErrorHandler
{
    var $_display;

    function myErrorHandler($display = null)
    {
        $default = array('lineFormat' => '<b>%1$s</b>: %2$s %3$s',
                         'contextFormat' => ' in <b>%3$s</b> (file <b>%1$s</b> at line <b>%2$s</b>)',
                         'eol' => "\n"
                         );

        if (is_array($display)) {
            $this->_display = array_merge($default, $display);
        } else {
            $this->_display = $default;
        }
    }

    function _handleError($code, $level)
    {
        return null;
    }

    function errorCallback($err)
    {
        $display_errors = ini_get('display_errors');
        $log_errors = ini_get('log_errors');

        $info = $err->getUserInfo();
        $level = isset($info['errorLevel']) ? $info['errorLevel'] : 'notice';
        $message = $err->getMessage();
        $backtrace = $err->getBacktrace();

        if ($display_errors) {
            $this->display($message, $level, $backtrace);
        }
        if ($log_errors) {
            $this->log($message, $level);
        }
    }

    function log($message, $level)
    {
        $log = array('eol' => "\n",
                     'lineFormat' => '%1$s %2$s [%3$s] %4$s',
                     'timeFormat' => '%b %d %H:%M:%S'
                     );

        $msg = sprintf($log['lineFormat'] . $log['eol'],
                       strftime($log['timeFormat'], time()),
                       $_SERVER['REMOTE_ADDR'],
                       $level,
                       $message
                       );

        error_log($msg, 3, 'htmlcss.log');
    }

    function display($message, $level, $backtrace)
    {
        $backtrace = array_pop($backtrace);

        if ($backtrace) {
            $file = $backtrace['file'];
            $line = $backtrace['line'];

            if (isset($backtrace['class'])) {
                $func  = $backtrace['class'];
                $func .= $backtrace['type'];
                $func .= $backtrace['function'];
            } else {
                $func = $backtrace['function'];
            }
        }

        $lineFormat = $this->_display['lineFormat'] . $this->_display['eol'];
        $contextFormat = $this->_display['contextFormat'];
        $contextExec = sprintf($contextFormat, $file, $line, $func);

        printf($lineFormat, ucfirst($level), $message, $contextExec);
    }
}

$myErrorHandler = new myErrorHandler();
PEAR::setErrorHandling(PEAR_ERROR_CALLBACK, array(&$myErrorHandler, 'errorCallback'));

ini_set('display_errors', 1);
ini_set('log_errors', 1);

$includes = get_included_files();
$display_errors = ini_get('display_errors');
$log_errors = ini_get('log_errors');

echo '<pre>';
print_r($includes);
printf("display_errors = %s\n", $display_errors);
printf("log_errors = %s\n", $log_errors);
echo '<hr />';
echo '</pre>';

/**
 * avoid script to die on HTML_CSS API exception; line 155 will be reached
 * @see HTML_CSS::setXhtmlCompliance()
 */
$prefs = array('push_callback' => array(&$myErrorHandler, '_handleError'));

$css = new HTML_CSS(null, $prefs);

$group1 = $css->createGroup('body, html', 'grp1');
$group2 = $css->createGroup('p, html', 'grp1');

echo '<hr />';

$options = array('lineFormat' => '<b>%1$s :</b> %2$s <br />%3$s',
                 'contextFormat' => '<b>Function :</b> %3$s <br/><b>File :</b> %1$s <br /><b>Line :</b> %2$s <br/>'
                 );

$myErrorHandler = new myErrorHandler($options);
PEAR::setErrorHandling(PEAR_ERROR_CALLBACK, array(&$myErrorHandler, 'errorCallback'));

$css->setXhtmlCompliance('true');  // generate an API exception

print '<hr />';
print "still alive";

?>