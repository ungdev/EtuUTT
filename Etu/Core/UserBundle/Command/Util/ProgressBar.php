<?php

namespace Etu\Core\UserBundle\Command\Util;

/**
 * Display a progress bar in the console.
 */
class ProgressBar
{
    /**
     * Skeleton for use with sprintf.
     */
    protected $_skeleton;
    /**
     * The bar gets filled with this.
     */
    protected $_bar;
    /**
     * The width of the bar.
     */
    protected $_blen;
    /**
     * The total width of the display.
     */
    protected $_tlen;
    /**
     * The position of the counter when the job is `done'.
     */
    protected $_target_num;
    /**
     * Options, like the precision used to display the numbers.
     */
    protected $_options = [];
    /**
     * Length to erase.
     */
    protected $_rlen = 0;
    /**
     * When the progress started.
     */
    protected $_start_time = null;
    protected $_rate_datapoints = [];
    /**
     * Time when the bar was last drawn.
     */
    protected $_last_update_time = 0.0;

    /**
     * Constructor, sets format and size
     * See the reset() method for documentation.
     *
     * @param string       The format string
     * @param string       The string filling the progress bar
     * @param string       The string filling empty space in the bar
     * @param int          The width of the display
     * @param float        The target number for the bar
     * @param array        Options for the progress bar
     * @param mixed $formatstring
     * @param mixed $bar
     * @param mixed $prefill
     * @param mixed $width
     * @param mixed $target_num
     * @param mixed $options
     *
     * @see reset
     */
    public function __construct($formatstring, $bar, $prefill, $width, $target_num, $options = [])
    {
        $this->reset($formatstring, $bar, $prefill, $width, $target_num, $options);
    }

    /**
     * Re-sets format and size.
     * <pre>
     * The reset method expects 5 to 6 arguments:
     * - The first argument is the format string used to display the progress
     *   bar. It may (and should) contain placeholders that the class will
     *   replace with information like the progress bar itself, the progress in
     *   percent, and so on. Current placeholders are:
     *     %bar%         The progress bar
     *     %current%     The current value
     *     %max%         The maximum malue (the "target" value)
     *     %fraction%    The same as %current%/%max%
     *     %percent%     The status in percent
     *     %elapsed%     The elapsed time
     *     %estimate%    An estimate of how long the progress will take
     *   More placeholders will follow. A format string like:
     *   "* stuff.tar %fraction% KB [%bar%] %percent%"
     *   will lead to a bar looking like this:
     *   "* stuff.tar 391/900 KB [=====>---------]  43.44%"
     * - The second argument is the string that is going to fill the progress
     *   bar. In the above example, the string "=>" was used. If the string you
     *   pass is too short (like "=>" in this example), the leftmost character
     *   is used to pad it to the needed size. If the string you pass is too long,
     *   excessive characters are stripped from the left.
     * - The third argument is the string that fills the "empty" space in the
     *   progress bar. In the above example, that would be "-". If the string
     *   you pass is too short (like "-" in this example), the rightmost
     *   character is used to pad it to the needed size. If the string you pass
     *   is too short, excessive characters are stripped from the right.
     * - The fourth argument specifies the width of the display. If the options
     *   are left untouched, it will tell how many characters the display should
     *   use in total. If the "absolute_width" option is set to false, it tells
     *   how many characters the actual bar (that replaces the %bar%
     *   placeholder) should use.
     * - The fifth argument is the target number of the progress bar. For
     *   example, if you wanted to display a progress bar for a download of a
     *   file that is 115 KB big, you would pass 115 here.
     * - The sixth argument optional. If passed, it should contain an array of
     *   options. For example, passing array('absolute_width' => false) would
     *   set the absolute_width option to false. Current options are:
     *     option             | def.  |  meaning
     *     --------------------------------------------------------------------
     *     percent_precision  | 2     |  Number of decimal places to show when
     *                        |       |  displaying the percentage.
     *     fraction_precision | 0     |  Number of decimal places to show when
     *                        |       |  displaying the current or target
     *                        |       |  number.
     *     percent_pad        | ' '   |  Character to use when padding the
     *                        |       |  percentage to a fixed size. Senseful
     *                        |       |  values are ' ' and '0', but any are
     *                        |       |  possible.
     *     fraction_pad       | ' '   |  Character to use when padding max and
     *                        |       |  current number to a fixed size.
     *                        |       |  Senseful values are ' ' and '0', but
     *                        |       |  any are possible.
     *     width_absolute     | true  |  If the width passed as an argument
     *                        |       |  should mean the total size (true) or
     *                        |       |  the width of the bar alone.
     *     ansi_terminal      | false |  If this option is true, a better
     *                        |       |  (faster) method for erasing the bar is
     *                        |       |  used. CAUTION - this is known to cause
     *                        |       |  problems with some terminal emulators,
     *                        |       |  for example Eterm.
     *     ansi_clear         | false |  If the bar should be cleared everytime
     *     num_datapoints     | 5     |  How many datapoints to use to create
     *                        |       |  the estimated remaining time
     *     min_draw_interval  | 0.0   |  If the last call to update() was less
     *                        |       |  than this amount of seconds ago,
     *                        |       |  don't update.
     * </pre>.
     *
     * @param string $formatString   The format string
     * @param string $bar            The string filling the progress bar
     * @param string $prefill        The string filling empty space in the bar
     * @param int    $width          The width of the display
     * @param float  $target_num     The target number for the bar
     * @param array  $optionsOptions for the progress bar
     * @param mixed  $formatstring
     * @param mixed  $options
     *
     * @return bool
     */
    public function reset($formatstring, $bar, $prefill, $width, $target_num, $options = [])
    {
        if (0 == $target_num) {
            throw new \ErrorException('ProgressBar: Using a target number equal to 0 is invalid, setting to 1 instead.');
            $this->_target_num = 1;
        } else {
            $this->_target_num = $target_num;
        }

        $default_options = [
            'percent_precision' => 2,
            'fraction_precision' => 0,
            'percent_pad' => ' ',
            'fraction_pad' => ' ',
            'width_absolute' => true,
            'ansi_terminal' => false,
            'ansi_clear' => false,
            'num_datapoints' => 5,
            'min_draw_interval' => 0.0,
        ];

        $intopts = [];

        foreach ($default_options as $key => $value) {
            if (!isset($options[$key])) {
                $intopts[$key] = $value;
            } else {
                settype($options[$key], gettype($value));
                $intopts[$key] = $options[$key];
            }
        }

        $this->_options = $options = $intopts;

        // placeholder
        $cur = '%2$\''.$options['fraction_pad'][0]
        .mb_strlen((int) $target_num).'.'.$options['fraction_precision'].'f';

        $max = $cur;
        $max[1] = 3;

        // pre php-4.3.7 %3.2f meant 3 characters before . and two after
        // php-4.3.7 and later it means 3 characters for the whole number
        if (version_compare(PHP_VERSION, '4.3.7', 'ge')) {
            $padding = 4 + $options['percent_precision'];
        } else {
            $padding = 3;
        }

        $perc = '%4$\''.$options['percent_pad'][0]
        .$padding.'.'.$options['percent_precision'].'f';

        $transitions = [
            '%%' => '%%',
            '%fraction%' => $cur.'/'.$max,
            '%current%' => $cur,
            '%max%' => $max,
            '%percent%' => $perc.'%%',
            '%bar%' => '%1$s',
            '%elapsed%' => '%5$s',
            '%estimate%' => '%6$s',
        ];

        $this->_skeleton = strtr($formatstring, $transitions);

        $slen = mb_strlen(sprintf($this->_skeleton, '', 0, 0, 0, '00:00:00', '00:00:00'));

        if ($options['width_absolute']) {
            $blen = $width - $slen;
            $tlen = $width;
        } else {
            $tlen = $width + $slen;
            $blen = $width;
        }

        $lbar = str_pad($bar, $blen, $bar[0], STR_PAD_LEFT);
        $rbar = str_pad($prefill, $blen, mb_substr($prefill, -1, 1));

        $this->_bar = mb_substr($lbar, -$blen).mb_substr($rbar, 0, $blen);
        $this->_blen = $blen;
        $this->_tlen = $tlen;
        $this->_first = true;

        return true;
    }

    /**
     * Updates the bar with new progress information.
     *
     * @param int current position of the progress counter
     * @param mixed $current
     *
     * @return bool
     */
    public function update($current)
    {
        $time = $this->_fetchTime();
        $this->_addDatapoint($current, $time);

        if ($this->_first) {
            if ($this->_options['ansi_terminal']) {
                echo "\x1b[s"; // save cursor position
            }

            $this->_first = false;
            $this->_start_time = $this->_fetchTime();
            $this->display($current);

            return;
        }

        if ($time - $this->_last_update_time < $this->_options['min_draw_interval'] and $current != $this->_target_num) {
            return;
        }

        $this->erase();
        $this->display($current);
        $this->_last_update_time = $time;
    }

    protected function _fetchTime()
    {
        if (!function_exists('microtime')) {
            return time();
        }

        if (version_compare(PHP_VERSION, '5.0.0', 'ge')) {
            return microtime(true);
        }

        return array_sum(explode(' ', microtime()));
    }

    protected function _addDatapoint($val, $time)
    {
        if (count($this->_rate_datapoints) == $this->_options['num_datapoints']) {
            array_shift($this->_rate_datapoints);
        }

        $this->_rate_datapoints[] = ['time' => $time, 'value' => $val];
    }

    /**
     * Prints the bar. Usually, you don't need this method, just use update()
     * which handles erasing the previously printed bar also. If you use a
     * custom function (for whatever reason) to erase the bar, use this method.
     *
     * @param int current position of the progress counter
     * @param mixed $current
     *
     * @return bool
     */
    public function display($current)
    {
        $percent = $current / $this->_target_num;
        $filled = round($percent * $this->_blen);
        $visbar = mb_substr($this->_bar, $this->_blen - $filled, $this->_blen);

        $elapsed = $this->_formatSeconds($this->_fetchTime() - $this->_start_time);

        $estimate = $this->_formatSeconds($this->_generateEstimate());

        $this->_rlen = printf(
            $this->_skeleton,
            $visbar,
            $current,
            $this->_target_num,
            $percent * 100,
            $elapsed,
            $estimate
        );

        // fix for php-versions where printf doesn't return anything
        if (null === $this->_rlen) {
            $this->_rlen = $this->_tlen;
        // fix for php versions between 4.3.7 and 5.x.y(?)
        } elseif ($this->_rlen < $this->_tlen) {
            echo str_repeat(' ', $this->_tlen - $this->_rlen);
            $this->_rlen = $this->_tlen;
        }

        return true;
    }

    /**
     * Returns a string containing the formatted number of seconds.
     *
     * @param float The number of seconds
     * @param mixed $seconds
     *
     * @return string
     */
    protected function _formatSeconds($seconds)
    {
        $hou = floor($seconds / 3600);
        $min = floor(($seconds - $hou * 3600) / 60);
        $sec = $seconds - $hou * 3600 - $min * 60;

        if (0 == $hou) {
            if (version_compare(PHP_VERSION, '4.3.7', 'ge')) {
                $format = '%2$02d:%3$05.2f';
            } else {
                $format = '%2$02d:%3$02.2f';
            }
        } elseif ($hou < 100) {
            $format = '%02d:%02d:%02d';
        } else {
            $format = '%05d:%02d';
        }

        return sprintf($format, $hou, $min, $sec);
    }

    protected function _generateEstimate()
    {
        if (count($this->_rate_datapoints) < 2) {
            return 0.0;
        }

        $first = $this->_rate_datapoints[0];
        $last = end($this->_rate_datapoints);

        return ($this->_target_num - $last['value']) / ($last['value'] - $first['value']) * ($last['time'] - $first['time']);
    }

    /**
     * Erases a previously printed bar.
     *
     * @param bool if the bar should be cleared in addition to resetting the
     *             cursor position
     * @param mixed $clear
     *
     * @return bool
     */
    public function erase($clear = false)
    {
        if ($this->_options['ansi_terminal'] and !$clear) {
            if ($this->_options['ansi_clear']) {
                echo "\x1b[2K\x1b[u";
            } // restore cursor position
            else {
                echo "\x1b[u";
            } // restore cursor position
        } elseif (!$clear) {
            echo str_repeat(chr(0x08), $this->_rlen);
        } else {
            echo str_repeat(chr(0x08), $this->_rlen), str_repeat(chr(0x20), $this->_rlen), str_repeat(
                chr(0x08),
                $this->_rlen
            );
        }
    }
}
