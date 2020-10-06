<?php
use CRM_Yhvsignup_ExtensionUtil as E;

/**
 * Yhvsignup.Getselectvals API
 *
 * @param array $params
 *
 * @return array
 *   API result descriptor
 *
 * @see civicrm_api3_create_success
 *
 * @throws API_Exception
 */
function civicrm_api3_yhvsignup_Getselectvals($params) {
  foreach ($params as $val) {
    $$val = getCustomFieldOptions($val);
    $returnValues[$val] = json_encode($$val);
  }

  return civicrm_api3_create_success($returnValues, $params, 'Yhvsignup', 'Getselectvals');
}

function getCustomFieldOptions($name) {
  $optionGroupName = CRM_Core_DAO::singleValueQuery("SELECT g.name
        FROM civicrm_custom_field c
        INNER JOIN civicrm_option_group g ON g.id = c.option_group_id
        WHERE c.name = %1 AND c.custom_group_id = %2", [1 => [$name, 'String'], 2 => [VOLUNTEERING_CUSTOM, 'Integer']]);
  if (empty($optionGroupName)) {
    return [];
  }
  $values = CRM_Core_OptionGroup::values($optionGroupName);

  $returnVals = [];
  $returnVals[] = [
    "Name" => "",
    "Id" => "",
  ];
  foreach ($values as $key => $value) {
    $returnVals[] = [
      "Name" => $value,
      "Id" => $key,
    ];
  }

  return $returnVals;
}
