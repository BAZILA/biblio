<?php

// Prepare list display
use AcyMailing\Helpers\CaptchaHelper;
use AcyMailing\Helpers\SecurityHelper;

if (!function_exists('acymailing_displayModuleLists')) {
    function acymailing_displayModuleLists($allLists, $visibleLists, $checkedLists, $formName)
    {
        if (empty($visibleLists)) {
            return;
        }
        ?>

		<div class="acym_lists">
            <?php foreach ($visibleLists as $myListId) { ?>
				<div class="onelist">
					<input type="checkbox"
					       class="acym_checkbox"
					       name="subscription[]"
					       id="acylist_<?php echo acym_escape($myListId.'_'.$formName); ?>"
                        <?php acym_checked(in_array($myListId, $checkedLists)); ?>
						   value="<?php echo acym_escape($myListId); ?>" />
					<label for="acylist_<?php echo acym_escape($myListId.'_'.$formName); ?>">
                        <?php echo acym_escapeHtml(!empty($allLists[$myListId]->display_name) ? $allLists[$myListId]->display_name : $allLists[$myListId]->name); ?>
					</label>
				</div>
            <?php } ?>
		</div>
        <?php
    }
}

if ($listPosition === 'before') {
    acymailing_displayModuleLists($allLists, $visibleLists, $checkedLists, $formName);
}
?>

<div class="acym_form">
    <?php
    foreach ($fields as $field) {
        $field->option = empty($field->option) ? new stdClass() : json_decode($field->option);
        $fieldDB = empty($field->option->fieldDB) ? '' : json_decode($field->option->fieldDB);
        $field->value = empty($field->value) ? '' : json_decode($field->value);
        $valuesArray = [];
        if (!empty($field->value)) {
            foreach ($field->value as $value) {
                $valueTmp = new stdClass();
                $valueTmp->text = $value->title;
                $valueTmp->value = $value->value;
                if ($value->disabled == 'y') $valueTmp->disable = true;
                $valuesArray[$value->value] = $valueTmp;
            }
        }

        if (!empty($fieldDB) && !empty($fieldDB->value)) {
            $fromDB = $fieldClass->getValueFromDB($fieldDB);
            foreach ($fromDB as $value) {
                $valuesArray[$value->value] = $value->title;
            }
        }

        echo '<div class="onefield fieldacy'.acym_escape($field->id.' acyfield_'.$field->type).'" id="field_'.acym_escape($field->id).'">';
        $fieldClass->displayField($field, $field->default_value, $valuesArray, $displayOutside, true, $identifiedUser);
        echo '</div>';

        if ($field->id == 2 && $config->get('email_confirmation')) {
            $fieldClass->setEmailConfirmationField($displayOutside, $field);
        }
    }

    if ($listPosition != 'before') {
        acymailing_displayModuleLists($allLists, $visibleLists, $checkedLists, $formName);
    }

    if (empty($identifiedUser->id) && $config->get('captcha', 'none') !== 'none' && acym_level(ACYM_ESSENTIAL)) {
        echo '<div class="onefield fieldacycaptcha" id="field_captcha_'.$formName.'">';
        $captcha = new CaptchaHelper();
        $captcha->display($formName, $params->get('includejs') === 'module');
        echo '</div>';
    }

    if (!empty($termslink)) {
        echo '<div class="onefield fieldacyterms" id="field_terms_'.acym_escape($formName).'">';
        echo '<label for="mailingdata_terms_'.acym_escape($formName).'">';
        echo '<input id="mailingdata_terms_'.acym_escape($formName).'" class="checkbox" type="checkbox" name="terms" title="'.acym_escape(
                acym_translation('ACYM_TERMS_CONDITIONS')
            ).'"/> ';
        echo acym_escapeHtmlWithAllowedTags(
            $termslink,
            SecurityHelper::ALLOWED_HTML_TERMS
        );
        echo '</label>';
        echo '</div>';
    }

    if (!empty($showTrackingConsent)) {
        $trackingChecked = (int)($identifiedUser->tracking ?? 0) === 1;
        echo '<div class="onefield fieldacytracking" id="field_tracking_'.acym_escape($formName).'">';
        echo '<label for="mailingdata_tracking_'.acym_escape($formName).'">';
        echo '<input type="hidden" name="user[tracking]" value="0"/>';
        echo '<input id="mailingdata_tracking_'.acym_escape($formName).'" class="acym_checkbox" type="checkbox" name="user[tracking]" value="1" '.acym_checked($trackingChecked, true, false).'/> '.acym_escapeHtml(
                acym_translation('ACYM_TRACKING_CONSENT')
            );
        echo '</label>';
        echo '</div>';
    }
    ?>
</div>

<p class="acysubbuttons">
	<noscript>
        <?php echo acym_escapeHtml(acym_translation('ACYM_NO_JAVASCRIPT')); ?>
	</noscript>
	<button type="submit"
	        class="btn btn-primary button subbutton"
	        onclick="try{ return submitAcymForm('subscribe','<?php echo acym_escape(
                $formName
            ); ?>', 'acymSubmitSubForm'); }catch(err){alert('The form could not be submitted '+err);return false;}">
        <?php echo acym_escapeHtml(acym_translation($subscribeText)); ?>
	</button>
    <?php if ($unsubButton === '2' || ($unsubButton === '1' && !empty($countUnsub))) { ?>
		<button type="submit"
		        class="btn button unsubbutton"
		        onclick="try{ return submitAcymForm('unsubscribe','<?php echo acym_escape(
                    $formName
                ); ?>', 'acymSubmitSubForm'); }catch(err){alert('The form could not be submitted '+err);return false;}">
            <?php echo acym_escapeHtml(acym_translation($unsubscribeText)); ?>
		</button>
    <?php } ?>
</p>
