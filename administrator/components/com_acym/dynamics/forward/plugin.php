<?php
defined('_JEXEC') || die('Restricted Access');

use AcyMailing\Core\AcymPlugin;

require_once __DIR__.DIRECTORY_SEPARATOR.'ForwardMailboxAction.php';

class plgAcymForward extends AcymPlugin
{
    use ForwardMailboxAction;
}
