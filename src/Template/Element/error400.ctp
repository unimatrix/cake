<?php

use Cake\Core\Configure;
use Cake\Error\Debugger;

if (Configure::read('debug')) :
    $this->layout = 'dev_error';

    $this->assign('title', $message);
    $this->assign('templateName', 'error400.ctp');

    $this->start('file');
?>
<?php if (!empty($error->queryString)) : ?>
    <p class="notice">
        <strong>SQL Query: </strong>
        <?= h($error->queryString) ?>
    </p>
<?php endif; ?>
<?php if (!empty($error->params)) : ?>
        <strong>SQL Query Params: </strong>
        <?php Debugger::dump($error->params) ?>
<?php endif; ?>
<?= $this->element('auto_table_warning') ?>
<?php
if (extension_loaded('xdebug')) :
    xdebug_print_function_stack();
endif;

$this->end();
endif;
?>
<h1><?= __d('Unimatrix/cake', 'Error 404') ?></h1>
<p>
    <b><?= __d('Unimatrix/cake', "It looks like \"{0}\" doesn't exist or has expired.", $url) ?></b><br /><br />
    <?= __d('Unimatrix/cake', "If you've entered the url manually, make sure it is correct.") ?><br />
    <?= __d('Unimatrix/cake', "If you've followed a hyperlink to this page, the hyperlink is no longer available.") ?><br /><br />
    <?= __d('Unimatrix/cake', 'Go back to the {0} or visit the {1}.',
        $this->Html->link(__d('Unimatrix/cake', 'previous page'), 'javascript:history.go(-1)'),
        $this->Html->link(__d('Unimatrix/cake', 'homepage'), $this->Url->build('/', true))) ?>
</p>
