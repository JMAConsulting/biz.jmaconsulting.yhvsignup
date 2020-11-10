<?php
define('CMRF_PROFILE_ID', 1);

require_once 'yhvsignup.civix.php';
require_once 'yhvsignup.variables.php';
// phpcs:disable
use CRM_Yhvsignup_ExtensionUtil as E;
use Symfony\Component\DependencyInjection\ContainerBuilder;
// phpcs:enable

/**
 * Implements hook_civicrm_config().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_config/
 */
function yhvsignup_civicrm_config(&$config) {
  _yhvsignup_civix_civicrm_config($config);
}
/**
 * Implements hook_civicrm_container().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_container
 */
function yhvsignup_civicrm_container(ContainerBuilder $container) {
  $container->addCompilerPass(new Civi\Volunteersignup\CompilerPass());
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_xmlMenu
 */
function yhvsignup_civicrm_xmlMenu(&$files) {
  _yhvsignup_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_install
 */
function yhvsignup_civicrm_install() {
  _yhvsignup_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_postInstall().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_postInstall
 */
function yhvsignup_civicrm_postInstall() {
  _yhvsignup_civix_civicrm_postInstall();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_uninstall
 */
function yhvsignup_civicrm_uninstall() {
  _yhvsignup_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_enable
 */
function yhvsignup_civicrm_enable() {
  _yhvsignup_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_disable
 */
function yhvsignup_civicrm_disable() {
  _yhvsignup_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_upgrade
 */
function yhvsignup_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _yhvsignup_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_managed
 */
function yhvsignup_civicrm_managed(&$entities) {
  _yhvsignup_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types.
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_caseTypes
 */
function yhvsignup_civicrm_caseTypes(&$caseTypes) {
  _yhvsignup_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
 *
 * Note: This hook only runs in CiviCRM 4.5+. It may
 * use features only available in v4.6+.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_angularModules
 */
function yhvsignup_civicrm_angularModules(&$angularModules) {
  _yhvsignup_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_alterSettingsFolders
 */
function yhvsignup_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _yhvsignup_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Implements hook_civicrm_entityTypes().
 *
 * Declare entity types provided by this module.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_entityTypes
 */
function yhvsignup_civicrm_entityTypes(&$entityTypes) {
  _yhvsignup_civix_civicrm_entityTypes($entityTypes);
}

/**
 * Implements hook_civicrm_thems().
 */
function yhvsignup_civicrm_themes(&$themes) {
  _yhvsignup_civix_civicrm_themes($themes);
}

/**
 * Implements hook_civicrm_pre().
 */
function yhvsignup_civicrm_pre($op, $objectName, $id, &$params) {
  // Save funder automatically for volunteer/request activities.
  if (in_array($op, ['create', 'edit']) && $objectName == 'Activity') {
    $activityTypes = CRM_Activity_BAO_Activity::buildOptions('activity_type_id');
    if (!in_array($activityTypes[$params['activity_type_id']], ['Volunteer', 'Volunteer Request'])) {
      return;
    }
    $clauses = [];
    foreach (['Location','Division','Program','Funder'] as $field) {
      $$field = CRM_Yhvrequestform_Utils::getCustomFieldID($field);
      foreach ($params as $key => $value) {
        if (substr($key, 0, strlen($$field)) === $$field) {
          if ($field != 'Funder' && !empty($value)) {
            $clauses[] = $field . ' = "' . $value . '"';
          }
          if ($field == 'Funder') {
            $funderKey = $key;
          }
        }
      }
    }
    if (empty($clauses)) {
      return;
    }
    $sql = "SELECT Funder FROM civicrm_volunteer_lookup WHERE " . implode(' AND ', $clauses);
    $funder = CRM_Core_DAO::singleValueQuery($sql);
    if (!empty($funder) && empty($params[$funderKey])) {
      $params[$funderKey] = $funder;
    }
  }
}

function yhvsignup_civicrm_tokens(&$tokens) {
  $tokens['contact'] = [
    'contact.resetlink' => 'Reset Password Link',
    'contact.username' => 'Username',
  ];
}

function yhvsignup_civicrm_tokenValues(&$values, $cids, $job = null, $tokens = [], $context = null) {
  if (isset($tokens['contact'])) {
    foreach ($cids as $cid) {
      $uf = civicrm_api3('UFMatch', 'get', [
        'sequential' => 1,
        'contact_id' => $cid,
      ]);
      $cs = CRM_Contact_BAO_Contact_Utils::generateChecksum($cid, NULL, 'inf');
      if (!empty($uf['values'][0]['uf_id'])) {
        $values[$cid]['contact.resetlink'] = YHV_FRONT_SITE . '?action=resetpassword&cs=' . $cs . '&uid=' . $uf['values'][0]['uf_id'];
        $user = get_user_by('id', $uf['values'][0]['uf_id']);
        $values[$cid]['contact.userid'] = $user->data->user_login;
      }
    }
  }
}


// --- Functions below this ship commented out. Uncomment as required. ---

/**
 * Implements hook_civicrm_preProcess().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_preProcess
 */
//function yhvsignup_civicrm_preProcess($formName, &$form) {
//
//}

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_navigationMenu
 */
//function yhvsignup_civicrm_navigationMenu(&$menu) {
//  _yhvsignup_civix_insert_navigation_menu($menu, 'Mailings', array(
//    'label' => E::ts('New subliminal message'),
//    'name' => 'mailing_subliminal_message',
//    'url' => 'civicrm/mailing/subliminal',
//    'permission' => 'access CiviMail',
//    'operator' => 'OR',
//    'separator' => 0,
//  ));
//  _yhvsignup_civix_navigationMenu($menu);
//}
