<?php
use CRM_Yhvsignup_ExtensionUtil as E;

/**
 * Contact.Createwpuser API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 *
 * @see https://docs.civicrm.org/dev/en/latest/framework/api-architecture/
 */
function _civicrm_api3_contact_Createwpuser_spec(&$spec) {
  $spec['cid']['api.required'] = 1;
  $spec['cid']['type'] = CRM_Utils_Type::T_INT;
  $spec['email']['api.required'] = 1;
  $spec['first_name']['api.required'] = 1;
  $spec['last_name']['api.required'] = 1;
}

/**
 * Contact.Createwpuser API
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
function civicrm_api3_contact_Createwpuser($params) {
  $username = strtolower(trim(sanitize_user(implode('.', [$params['first_name'], $params['last_name']]))));
  $user = get_user_by('login', $params['email']) ?: get_user_by('login', $username);
  $uid = NULL;
  if (!$user) {
    $user_data = [
      'ID' => '',
      'user_pass' => 'changeme',
      'user_login' => $username,
      'user_email' => $params['mail'],
      'nickname' => $username,
      'role' => get_option('default_role'),
    ];
    $uid = wp_insert_user($user_data);
    $ufMatch = [
      'uf_id' => $uid,
      'contact_id' => $params['cid'],
      'uf_name' => $params['email'],
    ];
    CRM_Core_BAO_UFMatch::create($ufmatch);
  }
  else {
    $uid = $user->ID;
  }

  return civicrm_api3_create_success($uid, $params, 'Contact');
}
