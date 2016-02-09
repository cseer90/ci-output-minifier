<?php
/**
 * Created by PhpStorm.
 * User: Sayed
 * Date: 29-11-2015
 * Time: 07:24
 */
if (!defined('BASEPATH')) exit('No direct script access allowed');
class MY_Form_validation extends CI_Form_validation
{

    function run($module = '', $group = '')
    {
        (is_object($module)) AND $this->CI = &$module;
        return parent::run($group);
    }
}