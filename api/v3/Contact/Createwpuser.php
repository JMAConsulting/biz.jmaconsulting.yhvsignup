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
  // Return the user since we already have one with an existing email.
  $user = get_user_by('login', $params['email']);
  if (!empty($user)) {
    return civicrm_api3_create_success($user->ID, $params, 'Contact');
  }
  $uid = NULL;
  if (!$user) {
    // We didn't find a user, so generate a username.
    $username = generateUserName($params);

    $user_data = [
      'ID' => '',
      'user_pass' => randomPassword(),
      'user_login' => $username,
      'user_email' => strtolower($params['email']),
      'first_name' => $params['first_name'],
      'last_name' => $params['last_name'],
      'nickname' => $username,
      'role' => 'inactive',
    ];
    $uid = wp_insert_user($user_data);

    $ufMatch = [
      'uf_id' => $uid,
      'contact_id' => $params['cid'],
      'uf_name' => $params['email'],
    ];
    $ufDupeName = new CRM_Core_DAO_UFMatch();
    $ufDupeName->uf_name = $params['email'];
    if ($ufDupeName->find(TRUE)) {
      if ($ufDupeName->contact_id != $params['cid']) {
        $ufDupeName->contact_id = $params['cid'];
        $ufDupeName->save();
      }
    }
    else {
      CRM_Core_BAO_UFMatch::create($ufMatch);
    }
  }

  return civicrm_api3_create_success($uid, $params, 'Contact');
}

/**
 * Generate a safe WordPress user name for use
 * @param array $params
 */
function generateUserName($params) {
  // Check to see if a the user name exists.
  $username = strtolower(trim(sanitize_user(implode('.', [$params['first_name'], $params['last_name']]))));
  $existingUsers = get_users( array( 'search' => $username ) );
  if (!empty($existingUsers)) {
    $userCount = count($existingUsers) + 1;
    return $username . $userCount;
  }
  return $username;
}

/**
 * Generate a random strong password
 */
function randomPassword() {
  $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_-=+;:,.?";
  $length = rand(10, 16);
  $password = substr( str_shuffle(sha1(rand() . time()) . $chars ), 0, $length );
  return $password;
}
