<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
<head>
    <title>Debug Mail</title>
    <style>
        body {
            margin: 12px;
            background: black;
            color: lime;
        }

        hr {
            display: block;
            height: 2px;
            border: 0;
            border-bottom: 1px dashed #666;
            background: black;
            margin: -10px 0px 12px 0px;
            text-align: left;
        }
    </style>
</head>
<body>
    <?= $this->fetch('content') ?>
</body>
</html>
