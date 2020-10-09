<?php
use CRM_Yhvsignup_ExtensionUtil as E;

/**
 * Yhvsignup.Getchainedselect API
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
function civicrm_api3_yhvsignup_Getchainedselect($params) {

  if (!empty($params['_value'])) {
    $returnVals[] = [
      "Name" => "",
      "Id" => "",
    ];
    if ($params['_return'] == 'division') {
      $divs = CRM_Yhvrequestform_Utils::getChainedSelectValues('Division', $params['_value']);

      if (!empty($divs)) {
        foreach ($divs as $div) {
          $returnVals[] = [
            "Name" => $div['value'],
            "Id" => $div['key'],
          ];
        }
      }
    }
    else {
      $progs = CRM_Yhvrequestform_Utils::getChainedSelectValues('Program', $params['_value'], $params['_loc']);
      if (!empty($progs)) {
        foreach ($progs as $prog) {
          $returnVals[] = [
            "Name" => $prog['value'],
            "Id" => $prog['key'],
          ];
        }
      }
    }
  }
  return civicrm_api3_create_success($returnVals, $params, 'Yhvsignup', 'Getchainedselect');
}
