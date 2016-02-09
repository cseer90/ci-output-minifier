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
    'class'     => 'Router',
    'function'  => 'setDefaults',
    'filename'  => 'router.php',
    'filepath'  => 'hooks'
);
$hook['pre_controller'][] = array(
    'class'     => 'Resource',
    'function'  => 'init',
    'filename'  => 'resource.php',
    'filepath'  => 'hooks'
);
$hook['display_override'][] = array(
    'class'     => 'Resource',
    'function'  => 'renderHTML',
    'filename'  => 'resource.php',
    'filepath'  => 'hooks'
);