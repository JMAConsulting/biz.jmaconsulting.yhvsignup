<?php
use CRM_Yhvsignup_ExtensionUtil as E;

/**
 * Contact.Validateusername API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 *
 * @see https://docs.civicrm.org/dev/en/latest/framework/api-architecture/
 */
function _civicrm_api3_contact_Validateusername_spec(&$spec) {
  $spec['username']['api.required'] = 1;
}

/**
 * Contact.Validateusername API
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
function civicrm_api3_contact_Validateusername($params) {
  $user = get_user_by('login', $params['username']) ?: get_user_by('email', $params['username']);
  if (!empty($user)) {
    if (in_array('inactive', $user->roles)) {
      return civicrm_api3_create_success(['error' => 'You have not been approved yet. Please contact your system administrator'], $params, 'Contact');
    }
  }
  $response = $user ? TRUE : FALSE;
  return civicrm_api3_create_success($response, $params, 'Contact');
}
