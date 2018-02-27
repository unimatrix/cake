<!DOCTYPE html>
<html>
<head>
    <?php
        // charset
        echo $this->Html->charset();

        // title
        echo "<title>{$this->fetch('title')}</title>";

        // icon and meta
        echo $this->Html->meta('icon');
        echo $this->fetch('meta');

        // viewport
        echo $this->Html->meta('viewport', 'width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, user-scalable=no');

        // style
        echo $this->Html->css('Unimatrix/Cake.error500.css');
    ?>
</head>
<body>
    <?= $this->fetch('content') ?>
</body>
</html>
