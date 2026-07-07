<?php

parse_str('settings[get_name]=0&settings[get_name]=1&settings[get_mobile_number]=0&settings[get_mobile_number]=1', $out);
var_export($out);
echo PHP_EOL;

foreach (['0', '1', ['0', '1']] as $v) {
    echo 'filter_var: '.var_export(filter_var($v, FILTER_VALIDATE_BOOLEAN), true).PHP_EOL;
}
