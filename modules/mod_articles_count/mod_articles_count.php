<?php
/*
copyright 2009 Fiona Coulter http://www.iswebdesign.co.uk

This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.

*/
// no direct access

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Helper\ModuleHelper;

require_once (dirname(__FILE__).'/helper.php');


$lists = modArticlesCountHelper::getList($params);
if(count(array($lists))>0)
{
    require(ModuleHelper::getLayoutPath('mod_articles_count',$lists->layout));
}