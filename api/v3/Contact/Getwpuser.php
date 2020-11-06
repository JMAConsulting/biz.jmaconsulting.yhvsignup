<?php
use CRM_Yhvsignup_ExtensionUtil as E;

/**
 * Contact.Getwpuser API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 *
 * @see https://docs.civicrm.org/dev/en/latest/framework/api-architecture/
 */
function _civicrm_api3_contact_Getwpuser_spec(&$spec) {
  $spec['username']['api.required'] = 1;
  $spec['password']['api.required'] = 1;
}

/**
 * Contact.Getwpuser API
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
 function civicrm_api3_contact_Getwpuser($params) {
   $response = wp_authenticate($params['username'], $params['password']);
   if (is_wp_error($response)) {
     $error_code = $response->get_error_code();
     return civicrm_api3_create_success(['error' => strip_tags($response->get_error_message( $error_code ))], $params, 'Contact');
   }
   else {
     // Check to see if user has an inactive role.
     if (in_array('inactive', $response->roles)) {
       return civicrm_api3_create_success(['error' => 'You have not been approved yet. Please contact your system administrator', $params, 'Contact');
     }
     if (!empty($response->data->ID)) {
       $response->data->cid = CRM_Core_BAO_UFMatch::getContactId($response->data->ID);
     }
     return civicrm_api3_create_success($response, $params, 'Contact');
   }
 }
