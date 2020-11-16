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
    // Gender.
    if (!empty($contact['gender_id'])) {
      $gender = CRM_Contact_BAO_Contact::buildOptions('gender_id');
      $contact['gender'] = $gender[$contact['gender_id']];
    }
    // Address.
    $address = civicrm_api3('Address', 'get', [
      'contact_id' => $params['cid'],
      'is_primary' => 1,
      'sequential' => 1,
    ]);
    if (!empty($address['values'])) {
      $contact['address'] = $address['values'][0];
      $contact['street_address'] = $address['values'][0]['street_address'];
      $contact['city'] = $address['values'][0]['city'];
      $contact['postal_code'] = $address['values'][0]['postal_code'];
    }
    // Phones.
    $mobile = civicrm_api3('Phone', 'get', [
      'return' => 'phone',
      'sequential' => 1,
      'contact_id' => $params['cid'],
      'location_type_id' => 'Home',
      'phone_type_id' => 'Mobile',
    ]);
    $residence = civicrm_api3('Phone', 'get', [
      'return' => 'phone',
      'sequential' => 1,
      'contact_id' => $params['cid'],
      'location_type_id' => 'Home',
      'phone_type_id' => 'Phone',
    ]);
    $office = civicrm_api3('Phone', 'get', [
      'return' => 'phone',
      'sequential' => 1,
      'contact_id' => $params['cid'],
      'location_type_id' => 'Work',
      'phone_type_id' => 'Phone',
    ]);
    if (!empty($mobile['values'][0]['phone_numeric'])) {
      $contact['mobile'] = $mobile['values'][0]['phone'];
    }
    if (!empty($residence['values'][0]['phone_numeric'])) {
      $contact['residence'] = $residence['values'][0]['phone'];
    }
    if (!empty($office['values'][0]['phone_numeric'])) {
      $contact['office'] = $office['values'][0]['phone'];
    }
    // Relationship.
    $relationships = civicrm_api3('Relationship', 'get', [
      'contact_id_a' => $params['cid'],
      'sequential' => 1,
      'options' => ['limit' => 1],
    ]);
    if (!empty($relationships['values'])) {
      $contact['relationships'] = $relationships['values'];
      $relationshipTypes = [
        12 => 'Child 子／女',
        13 => 'Friend 朋友',
        14 => 'Grandchild 孫子／孫女',
        22 => 'Grandparent 祖父／祖母',
        15 => 'Guardian 監護人',
        16 => 'Parent 父／母',
        17 => 'Partner 伴侶',
        18 => 'Relative 親戚',
        19 => 'Sibling 兄弟姊妹',
        20 => 'Spouse 配偶',
      ];
      $emergencyContact = civicrm_api3('Contact', 'getsingle', [
        'contact_id' => $relationships['values'][0]['contact_id_b'],
        'return' => ['first_name', 'last_name', 'phone'],
      ]);
      $contact['emergency_first_name'] = $emergencyContact['first_name'];
      $contact['emergency_last_name'] = $emergencyContact['last_name'];
      $contact['emergency_phone'] = $emergencyContact['phone'];
      $contact['emergency_relationship'] = $relationshipTypes[$emergencyContact['relationship_type_id']];
    }
    // Timetable.
    $timeTableParams = ['entity_id' => $params['cid'], 'entity_table' => 'civicrm_contact'];
    $timeTable = CRM_Yhvrequestform_BAO_VolunteerTimetable::getTimeTable($timeTableParams);
    if (!empty($timeTable)) {
      foreach ($timeTable as $entries) {
        $contact[$entries['day'] . '_' . $entries['time']] = $entries['number_of_volunteers'];
      }
    }
    return civicrm_api3_create_success($contact, $params, 'Contact', 'Getvolunteer');
  }
  else {
    throw new API_Exception(/*error_message*/ 'Contact ID is required', /*error_code*/ 'cid_incorrect');
  }
}
