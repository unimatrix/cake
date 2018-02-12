<?php

namespace Unimatrix\Cake\View\Helper;

use Cake\View\Helper\FormHelper as CakeFormHelper;
use Cake\View\View;

/**
 * Form helper
 * Overwrite default Form Helper and pass the view automatically
 * to custom widgets that need it to load assets
 *
 * @author Flavius
 * @version 1.0
 */
class FormHelper extends CakeFormHelper
{
    /**
     * Custom widgets container
     * @var array
     */
    protected $_customWidgets = [];

    /**
     * {@inheritDoc}
     * @see \Cake\View\Helper\FormHelper::__construct()
     */
    public function __construct(View $View, array $config = []) {
        // defined custom widgets? add them to our container
        if(isset($config['widgets']) && $config['widgets'])
            $this->_customWidgets = array_keys($config['widgets']);

        // continue as normal
        parent::__construct($View, $config);
    }

    /**
     * {@inheritDoc}
     * @see \Cake\View\Helper\FormHelper::control()
     */
    public function control($fieldName, array $options = []) {
        // pass the view to the needy widget
        if(isset($options['type']) && in_array($options['type'], $this->_customWidgets))
            $options['view'] = $this->getView();

        // continue as normal
        return parent::control($fieldName, $options);
    }
}
