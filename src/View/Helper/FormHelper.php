<?php

namespace Unimatrix\Cake\View\Helper;

use Cake\View\Helper\FormHelper as CakeFormHelper;
use Cake\View\View;

/**
 * Form helper
 * Overwrite default Form Helper and pass the view automatically
 * to some of our widgets that need it to load assets
 *
 * @author Flavius
 * @version 0.1
 */
class FormHelper extends CakeFormHelper
{
    /**
     * Our widgets that require view ot be passed
     * @var array
     */
    protected $_unimatrixViewWidgets= ['captcha'];

    /**
     * {@inheritDoc}
     * @see \Cake\View\Helper\FormHelper::control()
     */
    public function control($fieldName, array $options = []) {
        // pass the view to the needy widget
        if(isset($options['type']) && in_array($options['type'], $this->_unimatrixViewWidgets))
            $options['view'] = $this->getView();

        // continue as normal
        return parent::control($fieldName, $options);
    }
}
