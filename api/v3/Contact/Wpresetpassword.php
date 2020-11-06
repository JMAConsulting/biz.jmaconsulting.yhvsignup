<?php
use CRM_Yhvsignup_ExtensionUtil as E;

require_once __DIR__ . '/../../../yhvsignup.variables.php';

/**
 * Contact.Wpresetpassword API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 *
 * @see https://docs.civicrm.org/dev/en/latest/framework/api-architecture/
 */
function _civicrm_api3_contact_Wpresetpassword_spec(&$spec) {
  $spec['username']['api.required'] = 1;
  $spec['password']['api.required'] = 1;
  $spec['key']['api.required'] = 1;
}

/**
 * Contact.Wpresetpassword API
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
function civicrm_api3_contact_Wpresetpassword($params) {
  $user = get_user_by('login', $params['username']) ?: get_user_by('email', $params['username']);
  if (in_array('inactive', $user->roles)) {
    return civicrm_api3_create_success(['error' => 'You have not been approved yet. Please contact your system administrator'], $params, 'Contact');
  }
  if (!empty($user->data->ID)) {
    $contactID = CRM_Core_BAO_UFMatch::getContactId($user->data->ID);
    if ($contactID && !CRM_Contact_BAO_Contact_Utils::validChecksum($contactID, $params['key'])) {
      return civicrm_api3_create_success(FALSE, $params, 'Contact');
    }
  }
  reset_password($user, $params['password']);
  if (!empty($user->data->ID)) {
    $contactID = CRM_Core_BAO_UFMatch::getContactId($user->data->ID);
    $contact = civicrm_api3('Contact', 'getsingle', ['id' => $contactID]);
    $contact['email'] = $contact['email'] ?: CRM_Core_DAO::singleValueQuery("SELECT email FROM civicrm_email WHERE is_primary = 1 AND contact_id = " . $contactID . " LIMIT 1");
    $messageTemplates = new CRM_Core_DAO_MessageTemplate();
    $messageTemplates->id = CONFIRM_RESETLINK_MSG_TEMPLATE;
    $messageTemplates->find(TRUE);
    $body_subject = CRM_Core_Smarty::singleton()->fetch("string:$messageTemplates->msg_subject");
    $body_text    = $messageTemplates->msg_text;
    $body_html    = $messageTemplates->msg_html;
    $body_html = CRM_Core_Smarty::singleton()->fetch("string:{$body_html}");
    $body_text = CRM_Core_Smarty::singleton()->fetch("string:{$body_text}");
    $mailParams = array(
      'groupName' => 'Volunteer Password Reset Confirmation',
      'from' => FROM_EMAIL,
      'toName' =>  $contact['display_name'],
      'toEmail' => $contact['email'],
      'subject' => $body_subject,
      'messageTemplateID' => $messageTemplates->id,
      'html' => $body_html,
      'text' => $body_text,
    );
    CRM_Utils_Mail::send($mailParams);
  }
  return civicrm_api3_create_success($user, $params, 'Contact');
}
