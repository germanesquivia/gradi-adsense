<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

class Gradiadsense extends Module {
    protected $config_form = false;

    public function __construct() {
        $this->name = 'gradiadsense';
        $this->tab = 'administration';
        $this->version = '1.0.0';
        $this->author = 'www.gradi.co';
        $this->need_instance = 1;
        $this->bootstrap = true;
        parent::__construct();
        $this->displayName = $this->l('Gradi Adsense');
        $this->description = $this->l('Banner para Publicidad');
        $this->confirmUninstall = $this->l('Desea Desinstalar este Modulo');
        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
    }

    public function install() {
        Configuration::updateValue('GRADIADSENSE_LIVE_MODE', false);

        include(dirname(__FILE__).'/sql/install.php');

        return parent::install() &&
            $this->registerHook('header') &&
            $this->registerHook('backOfficeHeader') &&
            $this->registerHook('displayHome');
    }

    public function uninstall() {
        Configuration::deleteByName('GRADIADSENSE_LIVE_MODE');
        include(dirname(__FILE__).'/sql/uninstall.php');
        return parent::uninstall();
    }

    public function getContent() {
        if (((bool)Tools::isSubmit('submitGradiadsenseModule')) == true) {
            $this->postProcess();
        }

        $this->context->smarty->assign('module_dir', $this->_path);
        $output = $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');
        return $output.$this->renderForm();
    }

    protected function renderForm() {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitGradiadsenseModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(), 
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigForm()));
    }

    protected function getConfigForm() {
        return array(
            'form' => array(
                'legend' => array(
                'title' => $this->l('Settings'),
                'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Activo'),
                        'name' => 'GRADIADSENSE_LIVE_MODE',
                        'is_bool' => true,
                        'desc' => $this->l('Habilita este Modulo'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'type' => 'file',                        
                        'desc' => $this->l('Ruta de la Imagen a Subir'),
                        'name' => 'GRADIADSENSE_UPLOADED_FILE',
                        'label' => $this->l('Imagen del Banner:'),
                        'id' => 'uploadedfile',
                        'display_image' => false,
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'desc' => $this->l('Titulo de La Publicidad'),
                        'name' => 'GRADIADSENSE_TITLE_PUBLIC',
                        'label' => $this->l('Titulo'),
                    ),
                    array(
                        'col' => 4,
                        'type' => 'text',
                        'desc' => $this->l('Descripcion Corta de La Publicidad'),
                        'name' => 'GRADIADSENSE_DESC_PUBLIC',
                        'label' => $this->l('Descripcion Corta'),
                    ),
                    array(
                        'col' => 6,
                        'type' => 'text',
                        'desc' => $this->l('Label de un CTA'),
                        'name' => 'GRADIADSENSE_LABEL_CTA',
                        'label' => $this->l('Label de un CTA'),
                    ),
                    array(
                        'col' => 7,
                        'type' => 'text',
                        'desc' => $this->l('Url del CTA'),
                        'name' => 'GRADIADSENSE_URL_CTA',
                        'label' => $this->l('URL de CTA'),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    protected function getConfigFormValues() {
        return array(
            'GRADIADSENSE_LIVE_MODE' => Configuration::get('GRADIADSENSE_LIVE_MODE', true),
            'GRADIADSENSE_TITLE_PUBLIC' => Configuration::get('GRADIADSENSE_TITLE_PUBLIC', null),
            'GRADIADSENSE_DESC_PUBLIC' => Configuration::get('GRADIADSENSE_DESC_PUBLIC', null),
            'GRADIADSENSE_LABEL_CTA' => Configuration::get('GRADIADSENSE_LABEL_CTA', null),
            'GRADIADSENSE_URL_CTA' => Configuration::get('GRADIADSENSE_URL_CTA', null),
            'GRADIADSENSE_UPLOADED_FILE' => Configuration::get('GRADIADSENSE_UPLOADED_FILE', null),            
        );
    }

    protected function postProcess() {
        $form_values = $this->getConfigFormValues();

        foreach (array_keys($form_values) as $key) {
            Configuration::updateValue($key, Tools::getValue($key));
        }
    }

    public function hookBackOfficeHeader() {
        if (Tools::getValue('module_name') == $this->name) {
            $this->context->controller->addJS($this->_path.'views/js/back.js');
            $this->context->controller->addCSS($this->_path.'views/css/back.css');
        }
    }

    public function hookHeader() {
        $this->context->controller->addJS($this->_path.'/views/js/front.js');
        $this->context->controller->addCSS($this->_path.'/views/css/front.css');
    }

    public function hookDisplayHome() {
        /* Codigo Aqui */
    }
}
