<?php
use CRM_Yhvsignup_ExtensionUtil as E;

/**
 * Yhvsignup.Geturls API
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
function civicrm_api3_yhvsignup_Geturls($params) {
  $returnValues = [
    'getDept' => CRM_Utils_System::url('civicrm/getdept', NULL, TRUE, NULL, TRUE, TRUE),
    'getProg' => CRM_Utils_System::url('civicrm/getpro', NULL, TRUE, NULL, TRUE, TRUE),
    'insertSignup' => CRM_Utils_System::url('civicrm/insertsignup', NULL, TRUE, NULL, TRUE, TRUE),
    'signup' => CRM_Utils_System::url('civicrm/signup', NULL, TRUE, NULL, TRUE, TRUE),
    'searchSignup' => CRM_Utils_System::url('civicrm/searchsignup', NULL, TRUE, NULL, TRUE, TRUE),
  ];

  return civicrm_api3_create_success($returnValues, $params, 'Yhvsignup', 'Geturls');
}
