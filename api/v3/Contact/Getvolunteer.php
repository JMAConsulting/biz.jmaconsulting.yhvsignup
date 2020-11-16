<?php
use CRM_Yhvsignup_ExtensionUtil as E;

/**
 * Contact.Getvolunteer API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 *
 * @see https://docs.civicrm.org/dev/en/latest/framework/api-architecture/
 */
function _civicrm_api3_contact_Getvolunteer_spec(&$spec) {
  $spec['cid']['api.required'] = 1;
}

/**
 * Contact.Getvolunteer API
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
function civicrm_api3_contact_Getvolunteer($params) {
  if (array_key_exists('cid', $params)) {
    $customFields = [
      'Age_18',
      'Chinese_Name',
      'Other_profession',
      'Profession_checkbox',
      'Area_of_Education_',
      'Other_Areas_of_Education',
      'Car_',
      'How_many_years_of_driving_experience_do_you_have_in_Ontario_',
    ];
    foreach ($customFields as $field) {
      $customIds[$field] = 'custom_' . CRM_Core_DAO::singleValueQuery('SELECT id FROM civicrm_custom_field WHERE name = %1 AND custom_group_id = 9', [1 => [$field, 'String']]);
    }

    $returnFields = [
      'first_name',
      'last_name',
      'gender',
      'email',
      'state_province',
    ];
    $returnFields = array_merge($returnFields, $customIds);
    $contact = civicrm_api3('Contact', 'getsingle', ['id' => $params['cid'], 'return' => $returnFields]);
    foreach ($customIds as $id => $customField) {
      $contact[$id] = $contact[$customField];		    
    }
    // Phones.
    $mobile = civicrm_api3('Phone', 'get', ['return' => 'phone_numeric', 'sequential' => 1, 'contact_id' => $params['cid'], 'location_type_id' => 'Home', 'phone_type_id' => 'Mobile']);
    $residence = civicrm_api3('Phone', 'get', []);
    CRM_Core_Error::debug('ag', $phones);
    //foreach () {
    //}
    CRM_Core_Error::debug('efa', $contact);exit;
    return civicrm_api3_create_success($returnValues, $params, 'Contact', 'Getvolunteer');
  }
  else {
    throw new API_Exception(/*error_message*/ 'Contact ID is required', /*error_code*/ 'cid_incorrect');
  }
}
