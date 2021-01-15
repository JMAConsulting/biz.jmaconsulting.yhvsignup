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
    'filterUrl' => $params['target'] . '/volunteer-filter',
    'actionUrl' => $params['target'] . '/volunteer-action',
    'validateUrl' => $params['target'] . '/volunteer-email',
  ];

  return civicrm_api3_create_success($returnValues, $params, 'Yhvsignup', 'Geturls');
}
