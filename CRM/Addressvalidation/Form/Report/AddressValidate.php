<?php

class CRM_Addressvalidation_Form_Report_AddressValidate extends CRM_Reportbase_Form_Report_Reportbase {
  protected $_summary = NULL;

  protected $_customGroupExtends = array('Address');
  protected $_customGroupGroupBy = FALSE;
  protected $_baseTable = 'civicrm_address';

  function __construct() {
    $this->_columns = $this->getAddressColumns()
    + $this->getContactColumns();
    $this->_columns['civicrm_address']['fields']['id']['required'] = TRUE;
    $this->_columns['civicrm_contact']['fields']['id']['required'] = TRUE;
    $this->_groupFilter = TRUE;
    $this->_tagFilter = TRUE;
    parent::__construct();
  }

  function preProcess() {
    $this->assign('reportTitle', ts('Address Validation'));
    parent::preProcess();
  }

  function alterDisplay(&$rows){
    //re-order rows
    $moveEarlier = array(
      'googleAddress' => array('title' => 'Google Address'),
      'civicrm_value_status_39_custom_28' => $this->_columnHeaders['civicrm_value_status_39_custom_28'],
      'civicrm_contact_external_identifier' => $this->_columnHeaders['civicrm_contact_external_identifier'],
    );
    $this->_columnHeaders = array_merge($moveEarlier, $this->_columnHeaders);

    // google to civicrm field name map
    $fieldMap = array(
        'street_number' => 'street_number',
        'route' => 'street_name',
        'locality' => 'city',
        'sublocality' => 'supplemental_address_1',
        'postal_code' => 'postal_code',
        //   'country' => 'country_id',
    );
    foreach ($rows as &$row){
      $contactLink = CRM_Utils_System::href('CiviCRM', 'civicrm/contact/view', "reset=1&cid={$row['civicrm_contact_id']}");

      $row['googleAddress'] = "<table></tr><th></th><th>Google</th><th>{$contactLink}</th></tr>";
      $googleAddress = json_decode($row['civicrm_value_status_39_custom_29']);

      foreach ($googleAddress as $field => $value){
        if($field =='country'){
          continue;
        }
        $row['googleAddress'] .= "<tr><td> $field </td><td>$value</td><td"
        . (($value == $row['civicrm_address_' . $fieldMap[$field]]) ? '' :  " class='primary'" )
        . ">"
        . $row['civicrm_address_' . $fieldMap[$field]]
        . "</td></tr>";
      }
      $row['googleAddress'] .= "</table>";
    }
    parent::alterDisplay($rows);
    //<div id=address-79614 class='crm-entity'>
    //<span class='crm-editable crmf-custom_28 editable_select crm-editable-enabled' data-action='create' data-type='select' data-options='{"Needs Review":"Needs Review","selected":"Needs Review"}'>Needs Review</span></div>
  }

  function fromClauses() {
    return array(
      'contact_from_address',
    );
  }

}
