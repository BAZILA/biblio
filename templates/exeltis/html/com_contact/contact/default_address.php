<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_contact
 *
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\String\PunycodeHelper;
?>
<dl class="com-contact__address contact-address biblio-contact-details" itemprop="address" itemscope itemtype="https://schema.org/PostalAddress">
    <?php if (($this->params->get('address_check') > 0) && ($this->item->address || $this->item->suburb || $this->item->state || $this->item->country || $this->item->postcode)) : ?>
        <?php if ($this->item->address && $this->params->get('show_street_address')) : ?>
            <dt><?php echo Text::_('COM_CONTACT_ADDRESS'); ?></dt>
            <dd>
                <span class="contact-street" itemprop="streetAddress"><?php echo nl2br($this->item->address, false); ?></span>
            </dd>
        <?php endif; ?>
        <?php if ($this->item->suburb && $this->params->get('show_suburb')) : ?>
            <dt><?php echo Text::_('COM_CONTACT_SUBURB'); ?></dt>
            <dd>
                <span class="contact-suburb" itemprop="addressLocality"><?php echo $this->item->suburb; ?></span>
            </dd>
        <?php endif; ?>
        <?php if ($this->item->state && $this->params->get('show_state')) : ?>
            <dt><?php echo Text::_('COM_CONTACT_STATE'); ?></dt>
            <dd>
                <span class="contact-state" itemprop="addressRegion"><?php echo $this->item->state; ?></span>
            </dd>
        <?php endif; ?>
        <?php if ($this->item->postcode && $this->params->get('show_postcode')) : ?>
            <dt><?php echo Text::_('COM_CONTACT_POSTCODE'); ?></dt>
            <dd>
                <span class="contact-postcode" itemprop="postalCode"><?php echo $this->item->postcode; ?></span>
            </dd>
        <?php endif; ?>
        <?php if ($this->item->country && $this->params->get('show_country')) : ?>
            <dt><?php echo Text::_('COM_CONTACT_COUNTRY'); ?></dt>
            <dd>
                <span class="contact-country" itemprop="addressCountry"><?php echo $this->item->country; ?></span>
            </dd>
        <?php endif; ?>
    <?php endif; ?>

    <?php if ($this->item->email_to && $this->params->get('show_email')) : ?>
        <dt><?php echo Text::_('COM_CONTACT_EMAIL_LABEL'); ?></dt>
        <dd>
            <span class="contact-emailto"><?php echo $this->item->email_to; ?></span>
        </dd>
    <?php endif; ?>

    <?php if ($this->item->telephone && $this->params->get('show_telephone')) : ?>
        <dt><?php echo Text::_('COM_CONTACT_TELEPHONE'); ?></dt>
        <dd>
            <span class="contact-telephone" itemprop="telephone"><?php echo $this->item->telephone; ?></span>
        </dd>
    <?php endif; ?>

    <?php if ($this->item->fax && $this->params->get('show_fax')) : ?>
        <dt><?php echo Text::_('COM_CONTACT_FAX'); ?></dt>
        <dd>
            <span class="contact-fax" itemprop="faxNumber"><?php echo $this->item->fax; ?></span>
        </dd>
    <?php endif; ?>

    <?php if ($this->item->mobile && $this->params->get('show_mobile')) : ?>
        <dt><?php echo Text::_('COM_CONTACT_MOBILE'); ?></dt>
        <dd>
            <span class="contact-mobile" itemprop="telephone"><?php echo $this->item->mobile; ?></span>
        </dd>
    <?php endif; ?>

    <?php if ($this->item->webpage && $this->params->get('show_webpage')) : ?>
        <dt><?php echo Text::_('COM_CONTACT_WEBPAGE'); ?></dt>
        <dd>
            <span class="contact-webpage">
                <a href="<?php echo $this->item->webpage; ?>" target="_blank" rel="noopener noreferrer" itemprop="url">
                    <?php echo $this->escape(PunycodeHelper::urlToUTF8($this->item->webpage)); ?>
                </a>
            </span>
        </dd>
    <?php endif; ?>
</dl>
