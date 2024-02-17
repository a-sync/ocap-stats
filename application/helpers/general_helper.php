<?php defined('BASEPATH') or exit('No direct script access allowed');

if (!function_exists('convert_filesize')) {
    function convert_filesize($bytes, $decimals = 2)
    {
        if (intval($bytes) === 0) {
            return '0 B';
        }
        $size = ['B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
        $factor = floor(log($bytes, 1024));

        return sprintf('%.' . $decimals . 'f', $bytes / pow(1024, $factor)) . ' ' . @$size[$factor];
    }
}

if (!function_exists('should_ignore')) {
    function should_ignore($operation)
    {
        if (function_exists('should_op_be_ignored')) {
            return should_op_be_ignored($operation);
        }

        return false;
    }
}

if (!function_exists('get_valid_event_types')) {
    function get_valid_event_types($operation)
    {
        $CI = &get_instance();

        if (isset($operation['tag'])) {
            $tag_event_types = array_change_key_case($CI->config->item('tag_event_types'), CASE_LOWER);

            if (count($tag_event_types) > 0) {
                $op_tag = strtolower($operation['tag']);

                if (isset($tag_event_types[$op_tag])) {
                    $valid_event_types = $tag_event_types[$op_tag];

                    if (!is_array($valid_event_types)) {
                        $valid_event_types = [$valid_event_types];
                    }

                    if (defined('STRICT_TAG_EVENT_TYPES')) {
                        return $valid_event_types;
                    } else {
                        return array_unique(array_merge($valid_event_types, array_keys($CI->config->item('event_types'))));
                    }
                }
            }

            if (defined('STRICT_TAG_EVENT_TYPES')) {
                return [];
            }
        }

        return array_keys($CI->config->item('event_types'));
    }
}

if (!function_exists('print_end_winners')) {
    function print_end_winners($end_winner, $end_message = null)
    {
        $CI = &get_instance();
        $sides = $CI->config->item('sides');

        $title = '';
        if (!is_null($end_message)) {
            $title = ' title="'.html_escape($end_message).'"';
        }

        $winner_sides = explode('/', $end_winner);
        $winners = [];
        foreach ($winner_sides as $s) {
            $winners[] = '<span class="side__' . html_escape(strtolower($s)) . '"' . $title . '>' . $sides[$s] . '</span>';
        }

        echo implode(' / ', $winners);
    }
}

if (!function_exists('get_timestamp')) {
    function get_timestamp($seconds)
    {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $seconds = $seconds % 60;

        if ($hours < 10) {
            $hours = str_pad($hours, 2, '0', STR_PAD_LEFT);
        }

        return sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds);
    }
}
