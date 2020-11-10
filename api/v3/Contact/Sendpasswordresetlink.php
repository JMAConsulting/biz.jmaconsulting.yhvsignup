<?php
use CRM_Yhvsignup_ExtensionUtil as E;

require_once __DIR__ . '/../../../yhvsignup.variables.php';

/**
 * Contact.Sendpasswordresetlink API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 *
 * @see https://docs.civicrm.org/dev/en/latest/framework/api-architecture/
 */
function _civicrm_api3_contact_Sendpasswordresetlink_spec(&$spec) {
  $spec['username']['api.required'] = 1;
}

/**
 * Contact.Sendpasswordresetlink API
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
function civicrm_api3_contact_Sendpasswordresetlink($params) {
  $userdata = get_user_by('login', $params['username']) ?: get_user_by('email', strtolower($params['username']));
  if (!$userdata->data->ID) {
    return civicrm_api3_create_success(FALSE, $params, 'Contact');
  }
  if (in_array('inactive', $userdata->roles)) {
    return civicrm_api3_create_success(['error' => 'You have not been approved yet. Please contact your system administrator'], $params, 'Contact');
  }
  $contactID = CRM_Core_BAO_UFMatch::getContactId($userdata->data->ID);

  $cs = CRM_Contact_BAO_Contact_Utils::generateChecksum($contactID, NULL, 'inf');
  $contact = civicrm_api3('Contact', 'getsingle', ['id' => $contactID]);
  $contact['email'] = $contact['email'] ?: CRM_Core_DAO::singleValueQuery("SELECT email FROM civicrm_email WHERE is_primary = 1 AND contact_id = " . $contactID . " LIMIT 1");
  $messageTemplates = new CRM_Core_DAO_MessageTemplate();
  $messageTemplates->id = SEND_RESETLINK_MSG_TEMPLATE;
  $messageTemplates->find(TRUE);
  $url = YHV_FRONT_SITE . '?action=resetpassword&cs=' . $cs . '&uid=' . $userdata->data->ID;
  $body_subject = CRM_Core_Smarty::singleton()->fetch("string:$messageTemplates->msg_subject");
  $body_text    = str_replace('{username}', $params['username'], str_replace('{url}', $url, $messageTemplates->msg_text));
  $body_html    = str_replace('{username}', $params['username'], str_replace('{url}', $url, $messageTemplates->msg_html));
  $body_html = CRM_Core_Smarty::singleton()->fetch("string:{$body_html}");
  $body_text = CRM_Core_Smarty::singleton()->fetch("string:{$body_text}");
  $mailParams = array(
    'groupName' => 'Reset Password',
    'from' => FROM_EMAIL,
    'toName' =>  $contact['display_name'],
    'toEmail' => $contact['email'],
    'subject' => $body_subject,
    'messageTemplateID' => $messageTemplates->id,
    'html' => $body_html,
    'text' => $body_text,
  );
  CRM_Utils_Mail::send($mailParams);
  return civicrm_api3_create_success(TRUE, $params, 'Contact');
}
