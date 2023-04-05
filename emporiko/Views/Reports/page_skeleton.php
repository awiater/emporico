<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= !empty($title) ? $title : '' ?></title>
  <?= !empty($css_url) ? $css_url : '' ?>
  <style>
    <?= !empty($style) ? $style : '' ?>
    @media print
    {
        .page-break { page-break-after:always }
        tr    { page-break-inside:avoid; page-break-after:auto }
        td    { page-break-inside:avoid; page-break-after:auto;padding:0px!important; }
        thead { display:table-header-group }
        tfoot { display:table-footer-group }
    }
    td p  {margin:0px!important;}
  </style>
  <script>
  	
  </script>
</head>
<body style="height:100%;width:100%<?= $autoprint ? '" onload="window.print();':''?>">
<?= !empty($scripts) ? $scripts : '' ?>
<?= !empty($content) ? $content : $this->renderSection('content') ?>
</body>
</html>

