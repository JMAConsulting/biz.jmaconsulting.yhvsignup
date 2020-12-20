<?php
use CRM_Yhvsignup_ExtensionUtil as E;

/**
 * Contact.Validateemail API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 *
 * @see https://docs.civicrm.org/dev/en/latest/framework/api-architecture/
 */
function _civicrm_api3_contact_Validateemail_spec(&$spec) {
  $spec['email']['api.required'] = 1;
}

/**
 * Contact.Validateemail API
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
function civicrm_api3_contact_Validateemail($params) {
  $user = get_user_by('email', strtolower($params['email']));
  if (!empty($user)) {
    return civicrm_api3_create_success(TRUE, $params, 'Contact');
  }
  else {
    return civicrm_api3_create_success(FALSE, $params, 'Contact');
  }
}
