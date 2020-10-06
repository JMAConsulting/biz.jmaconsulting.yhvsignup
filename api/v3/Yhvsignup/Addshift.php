<?php
use CRM_Yhvsignup_ExtensionUtil as E;

/**
 * Yhvsignup.Addshift API
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
function civicrm_api3_yhvsignup_Addshift($params) {
  if (array_key_exists('magicword', $params) && $params['magicword'] == 'sesame') {
    $returnValues = array(
      // OK, return several data rows
      12 => ['id' => 12, 'name' => 'Twelve'],
      34 => ['id' => 34, 'name' => 'Thirty four'],
      56 => ['id' => 56, 'name' => 'Fifty six'],
    );
    // ALTERNATIVE: $returnValues = []; // OK, success
    // ALTERNATIVE: $returnValues = ["Some value"]; // OK, return a single value

    // Spec: civicrm_api3_create_success($values = 1, $params = [], $entity = NULL, $action = NULL)
    return civicrm_api3_create_success($returnValues, $params, 'Yhvsignup', 'Addshift');
  }
  else {
    throw new API_Exception(/*error_message*/ 'Everyone knows that the magicword is "sesame"', /*error_code*/ 'magicword_incorrect');
  }
}
