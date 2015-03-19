<?php namespace Laraext\Log\Formatter;

use Monolog\Formatter\LineFormatter as MonologLineFormatter;
use Exception;

class LineFormatter extends MonologLineFormatter
{

    public function __construct($format = null, $dateFormat = null, $allowInlineLineBreaks = false, $ignoreEmptyContextAndExtra = false)
    {
        parent :: __construct($format, $dateFormat, $allowInlineLineBreaks, $ignoreEmptyContextAndExtra);
        if (empty($format))
        {
            if (php_sapi_name() == "cli")
            {
                $this->format = "[%datetime%] (cli) %level_name%: %message% %extra%\n";
            }
            else
            {
                $this->format = "[%datetime%] (%extra.ip%:%user%) %extra.http_method% %extra.url%[%extra.referrer%]\n%message%\n%context%\n\n";
            }
        }
    }


    public function format(array $record)
    {
        $vars = $this->normalize($record);

        $output = $this->format;

        if (isset($record['context']['exception']) && in_array(class_basename($record['context']['exception']), \Config :: get('laraext.log.skip_trace')))
        {
            $vars['message'] = get_class($record['context']['exception']) . ":" . $record['context']['exception']->getMessage();
        }

        if (\Auth :: check())
        {
            $vars['user'] = \Auth :: user()->id;
        }
        else
        {
            $vars['user'] = "0";
        }

        foreach ($vars['extra'] as $var => $val) {
            if (false !== strpos($output, '%extra.'.$var.'%')) {
                $output = str_replace('%extra.'.$var.'%', $this->stringify($val), $output);
                unset($vars['extra'][$var]);
            }
        }

        if ($this->ignoreEmptyContextAndExtra) {
            if (empty($vars['context'])) {
                unset($vars['context']);
                $output = str_replace('%context%', '', $output);
            }

            if (empty($vars['extra'])) {
                unset($vars['extra']);
                $output = str_replace('%extra%', '', $output);
            }
        }

        foreach ($vars as $var => $val) {
            if (false !== strpos($output, '%'.$var.'%')) {
                $output = str_replace('%'.$var.'%', $this->stringify($val), $output);
            }
        }

        return $output;
    }

}
