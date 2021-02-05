<?php
use CRM_Yhvsignup_ExtensionUtil as E;

/**
 * Contact.Validatevolunteeremail API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 *
 * @see https://docs.civicrm.org/dev/en/latest/framework/api-architecture/
 */
function _civicrm_api3_contact_Validatevolunteeremail_spec(&$spec) {
  $spec['email']['api.required'] = 1;
  $spec['cid']['api.required'] = 1;
}

/**
 * Contact.Validatevolunteeremail API
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
function civicrm_api3_contact_Validatevolunteeremail($params) {
  // Get the WordPress ID for the user.
  $ufId = civicrm_api3('UFMatch', 'get', [
    'sequential' => 1,
    'return' => ["uf_id"],
    'contact_id' => $params['cid'],
  ]);
  if (!empty($ufId['values'][0]['uf_id'])) {
    $userfound = FALSE;
    // Retrieve WP user email.
    $user = get_user_by('id', $ufId['values'][0]['uf_id']);
    $existingEmail = $user->data->user_email;
    if ($existingEmail != $params['email']) {
      // Email is being updated, check to see if there are no conflicts with existing users.
      global $wpdb;
      $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}users WHERE user_email = '" . $params['email'] . "' AND id <> " . $ufId['values'][0]['uf_id'], OBJECT );
      if (!empty($results)) {
        $userfound = TRUE;
      }
    }
  }
  if (!empty($userfound)) {
    return civicrm_api3_create_success(TRUE, $params, 'Contact');
  }
  else {
    return civicrm_api3_create_success(FALSE, $params, 'Contact');
  }
}
