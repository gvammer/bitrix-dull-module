<?php

use Bitrix\Main\Application;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;

defined('ADMIN_MODULE_NAME') or define('ADMIN_MODULE_NAME', 'bex.d7dull');

if (!$USER->isAdmin()) {
    $APPLICATION->authForm('Nope');
}

$app = Application::getInstance();
$context = $app->getContext();
$request = $context->getRequest();

Loc::loadMessages($context->getServer()->getDocumentRoot() . "/bitrix/modules/main/options.php");
Loc::loadMessages(__FILE__);

$arAllOptions = [
    [
        'max_image_size',
        GetMessage('REFERENCES_MAX_IMAGE_SIZE'),
        '40',
        [
            'text',
            100
        ]
    ],
];

$aTabs = [
    [
        'DIV' => 'edit1',
        'TAB' => Loc::getMessage("MAIN_TAB_SET"),
        'TITLE' => Loc::getMessage("MAIN_TAB_TITLE_SET")
    ],
];

$tabControl = new CAdminTabControl("tabControl", $aTabs, true, true);


if ((!empty($save) || !empty($restore)) && $request->isPost() && check_bitrix_sessid()) {
    if (!empty($restore)) {
        Option::delete(ADMIN_MODULE_NAME);
        CAdminMessage::showMessage(array(
            "MESSAGE" => Loc::getMessage("REFERENCES_OPTIONS_RESTORED"),
            "TYPE" => "OK",
        ));
    } else {

        $bSaved = false;
        foreach ($arAllOptions as $arOption) {

            $optionName = $arOption[0];
            $optionValue = $request->getPost($optionName);

            if ($optionValue !== null) {
                $fieldType = $arOption[3][0];

                if ($fieldType == 'checkbox' && $optionValue != 'Y') $optionValue = 'N';

                $bSaved = true;
                Option::set(ADMIN_MODULE_NAME, $optionName, $optionValue);
            }
        }

        if (!$bSaved) {
            CAdminMessage::showMessage(Loc::getMessage("REFERENCES_INVALID_VALUE"));
        } else {
            CAdminMessage::showMessage(array(
                "MESSAGE" => Loc::getMessage("REFERENCES_OPTIONS_SAVED"),
                "TYPE" => "OK",
            ));
        }

    }
}

$tabControl->begin();
?>

<form method="post"
      action="<?= sprintf('%s?mid=%s&lang=%s', $request->getRequestedPage(), urlencode($mid), LANGUAGE_ID) ?>">

    <?
    echo bitrix_sessid_post();

    $tabControl->BeginNextTab();

    foreach ($arAllOptions as $arOption) {

        $optionName = $arOption[0];
        $optionTitle = $arOption[1];
        $optionDefaultValue = $arOption[2];
        $optionValue = Option::get(ADMIN_MODULE_NAME, $optionName, $optionDefaultValue);
        $fieldType = $arOption[3][0];
        $fieldSizeAttr1 = $arOption[3][1];
        $fieldSizeAttr2 = $arOption[3][2];
        ?>
      <tr>
        <td width="40%" nowrap<? if ($fieldType == 'textarea') echo ' class="adm-detail-valign-top"'; ?>>
          <span id="hint_<?= $optionName ?>"></span>
          <label for="<?= $optionName ?>"><?= $optionTitle ?>:</label>
        </td>
        <td width="60%">
            <?
            switch ($fieldType) {
                case 'checkbox':
                    ?>
                  <input type="hidden" name="<?= $optionName ?>" value="N">
                  <input type="checkbox" id="<?= $optionName ?>" name="<?= $optionName ?>"
                         value="Y"<? if ($optionValue == 'Y') echo ' checked'; ?>>
                    <? break;
                case 'text':
                    ?>
                  <input type="text" size="<?= $fieldSizeAttr1 ?>" maxlength="255" value="<?= $optionValue ?>"
                         name="<?= $optionName ?>">
                    <? break;
                case 'textarea':
                    ?>
                  <textarea rows="<?= $fieldSizeAttr1 ?>" cols="<?= $fieldSizeAttr2 ?>"
                            name="<?= $optionName ?>"><?= $optionValue ?></textarea>
                    <? break;
            } ?>
        </td>
      </tr>
    <? }

    $tabControl->buttons();
    ?>
  <input type="submit"
         name="save"
         value="<?= Loc::getMessage("MAIN_SAVE") ?>"
         title="<?= Loc::getMessage("MAIN_OPT_SAVE_TITLE") ?>"
         class="adm-btn-save"
  />
  <input type="submit"
         name="restore"
         title="<?= Loc::getMessage("MAIN_HINT_RESTORE_DEFAULTS") ?>"
         onclick="return confirm('<?= AddSlashes(GetMessage("MAIN_HINT_RESTORE_DEFAULTS_WARNING")) ?>')"
         value="<?= Loc::getMessage("MAIN_RESTORE_DEFAULTS") ?>"
  />
    <?php
    $tabControl->end();
    ?>
</form>
