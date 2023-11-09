<?php

$this->title = 'Информация о конфигурации';

ob_start();
phpinfo();
$pinfo = ob_get_contents();
ob_end_clean();

$pinfo = preg_replace('%^.*<body>(.*)</body>.*$%ms', '$1', $pinfo);
?>
<div id="phpinfo">
    <?php echo $pinfo; ?>
</div>
<style>
    #phpinfo pre {
        margin: 0;
        font-family: monospace;
    }

    #phpinfo a:link {
        color: var(--indigo);
        text-decoration: none;
        background-color: var(--white);
    }

    #phpinfo a:hover {
        text-decoration: underline;
    }

    #phpinfo table {
        border-collapse: collapse;
        border: 0;
        width: 934px;
        card-shadow: 1px 2px 3px var(--light);
    }

    #phpinfo .center {
        text-align: center;
    }

    #phpinfo .center table {
        margin: 1em auto;
        text-align: left;
    }

    #phpinfo .center th {
        text-align: center !important;
    }

    #phpinfo td, th {
        border: 1px solid var(--dark);
        font-size: 75%;
        vertical-align: baseline;
        padding: 4px 5px;
    }

    #phpinfo th {
        position: sticky;
        top: 0;
        background: inherit;
    }

    #phpinfo h1 {
        font-size: 150%;
    }

    #phpinfo h2 {
        font-size: 125%;
    }

    #phpinfo .p {
        text-align: left;
    }

    #phpinfo .e {
        background-color: var(--pink);
        width: 300px;
        font-weight: bold;
    }

    #phpinfo .h {
        background-color: var(--purple);
        font-weight: bold;
    }

    #phpinfo .v {
        background-color: var(--light);
        max-width: 300px;
        overflow-x: auto;
        word-wrap: break-word;
    }

    #phpinfo .v i {
        color: var(--gray);
    }

    #phpinfo img {
        float: right;
        border: 0;
    }

    #phpinfo hr {
        width: 934px;
        background-color: var(--light);
        border: 0;
        height: 1px;
    }
</style>
