<?php

if (!defined('_PS_VERSION_')) {
  exit;
}

class My_module extends Module
{
  protected $config_form = false;

  public function __construct()
  {
    $this->name = 'My_module';
    $this->tab = 'front_office_features';
    $this->version = '1.0.0';
    $this->author = 'exi66';
    $this->need_instance = 1;

    /**
     * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
     */
    $this->bootstrap = true;

    parent::__construct();

    $this->displayName = $this->l('Тестовое задание alto');
    $this->description = $this->l('Выводит кол-во товаров в заданном диапазоне');

    $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');

    $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
  }

  /**
   * Don't forget to create update methods if needed:
   * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
   */
  public function install()
  {
    Configuration::updateValue('MY_MODULE_MIN', null);
    Configuration::updateValue('MY_MODULE_MAX', null);

    return parent::install()
      && $this->registerHook('displayFooter');
  }

  public function uninstall()
  {
    Configuration::deleteByName('MY_MODULE_MIN');
    Configuration::deleteByName('MY_MODULE_MAX');

    return parent::uninstall();
  }

  /**
   * Load the configuration form
   */
  public function getContent()
  {
    /**
     * If values have been submitted in the form, process.
     */
    if (((bool) Tools::isSubmit('submitMy_moduleModule')) == true) {
      $this->postProcess();
    }

    $this->context->smarty->assign('module_dir', $this->_path);

    $output = $this->context->smarty->fetch($this->local_path . 'views/templates/admin/configure.tpl');

    return $output . $this->renderForm();
  }

  /**
   * Create the form that will be displayed in the configuration of your module.
   */
  protected function renderForm()
  {
    $helper = new HelperForm();

    $helper->show_toolbar = false;
    $helper->table = $this->table;
    $helper->module = $this;
    $helper->default_form_language = $this->context->language->id;
    $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

    $helper->identifier = $this->identifier;
    $helper->submit_action = 'submitMy_moduleModule';
    $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
      . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
    $helper->token = Tools::getAdminTokenLite('AdminModules');

    $helper->tpl_vars = array(
      'fields_value' => $this->getConfigFormValues(),
      /* Add values for your inputs */
      'languages' => $this->context->controller->getLanguages(),
      'id_language' => $this->context->language->id,
    );

    return $helper->generateForm(array($this->getConfigForm()));
  }

  /**
   * Create the structure of your form.
   */
  protected function getConfigForm()
  {
    return array(
      'form' => array(
        'legend' => array(
          'title' => $this->l('Settings'),
          'icon' => 'icon-cogs',
        ),
        'input' => array(
          array(
            'col' => 3,
            'type' => 'text',
            'prefix' => '',
            'desc' => $this->l('For example: 1'),
            'name' => 'MY_MODULE_MIN',
            'label' => $this->l('Цена ОТ'),
          ),
          array(
            'col' => 3,
            'type' => 'text',
            'prefix' => '',
            'desc' => $this->l('For example: 20'),
            'name' => 'MY_MODULE_MAX',
            'label' => $this->l('Цена ДО'),
          ),
        ),
        'submit' => array(
          'title' => $this->l('Save'),
        ),
      ),
    );
  }

  /**
   * Set values for the inputs.
   */
  protected function getConfigFormValues()
  {
    return array(
      'MY_MODULE_MIN' => Configuration::get('MY_MODULE_MIN', null),
      'MY_MODULE_MAX' => Configuration::get('MY_MODULE_MAX', null),
    );
  }

  /**
   * Save form data.
   */
  protected function postProcess()
  {
    $form_values = $this->getConfigFormValues();

    foreach (array_keys($form_values) as $key) {
      Configuration::updateValue($key, Tools::getValue($key));
    }
  }

  public function hookDisplayFooter()
  {
    $min = Configuration::get('MY_MODULE_MIN');
    $max = Configuration::get('MY_MODULE_MAX');
    $query = 'SELECT COUNT(*) FROM `'._DB_PREFIX_.'product` WHERE `price`>'.$min.' AND `price`<'.$max;
    $count = Db::getInstance()->getValue($query);
    $this->context->smarty->assign(
      [
        'MY_MODULE_COUNT' => $count,
      ]
    );

    return $this->display(__FILE__, 'my_module.tpl');
  }
}