<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| Hooks
| -------------------------------------------------------------------------
| This file lets you define "hooks" to extend CI without hacking the core
| files.  Please see the user guide for info:
|
|	http://codeigniter.com/user_guide/general/hooks.html
|
*/
$hook['pre_controller'][] = array(
    'class'     => 'Minify',
    'function'  => 'setDefaults',
    'filename'  => 'minify.php',
    'filepath'  => 'hooks'
);
$hook['pre_controller'][] = array(
    'class'     => 'Minify',
    'function'  => 'init',
    'filename'  => 'minify.php',
    'filepath'  => 'hooks'
);
$hook['display_override'][] = array(
    'class'     => 'Minify',
    'function'  => 'renderHTML',
    'filename'  => 'minify.php',
    'filepath'  => 'hooks'
);