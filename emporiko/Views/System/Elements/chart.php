<canvas id="<?= $name ?>"<?= !empty($class) ? ' class="'.$class.'"' : '' ?>></canvas>

<script>
<?php if (!empty($datalabels) && ($datalabels || is_array($datalabels))) :?>
//Chart.register(ChartDataLabels);
<?php endif ?>
var <?= $name ?>Chart=new Chart("<?= $name ?>", {
type: "<?= $type ?>",
  data: {
    labels: <?= !empty($labels) ? $labels : '[]' ?>,
    datasets: [
    <?php if (!empty($labels) && !$multivalue) :?>
    {
    	backgroundColor: <?= is_array($colors) ? '['."'".implode("','",$colors)."']" : "'".$defcolor."'" ?>,
      	data: ["<?= implode('","',$data)  ?>"]
    }
    <?php else :?>
    <?php foreach ($data as $key => $row) :?>
		{
      	  label: '<?= $key ?>',
          fill:false,
          backgroundColor: '<?= !empty($colors) && is_array($colors) && array_key_exists($key, $colors) ? $colors[$key] : $defcolor ?>',
      	  data: ["<?= is_array($row) ? implode('","',$row) : $row ?>"]
    	},
	<?php endforeach ?>
	<?php endif ?>
    ],
  },
  options: {
  	<?= !empty($horizontal) && $horizontal && $type=='bar' ? "indexAxis: 'y'," : ''?>
  	plugins: {
  			<?php if (!empty($title) && strlen($title) > 0) :?>
  	 		title: {
        		display: true,
        		text: '<?= $title ?>'
    		 },
  			<?php endif ?>
  			<?php if (!empty($datalabels) && ($datalabels || is_array($datalabels))) :?>
  			datalabels: {
  				display: <?= is_array($datalabels) && array_key_exists('display', $datalabels) ? $datalabels['display'] : 'true' ?>,
            	anchor: <?= is_array($datalabels) && array_key_exists('anchor', $datalabels) ? '"'.$datalabels['anchor'].'"' : '"end"' ?>,
            	align: <?= is_array($datalabels) && array_key_exists('align', $datalabels) ? '"'.$datalabels['align'].'"' : '"top"' ?>,
            	formatter: <?= is_array($datalabels) && array_key_exists('formatter', $datalabels) ? $datalabels['formatter'] : 'Math.round' ?>,
            	font: {
                	weight: 'bold'
            	}
        	},
        	<?php endif ?>
            legend: {
                display: <?= $legend ?>,
            }
        }
  }
});
function <?= $name ?>_export_to_csv(name=null){
		var obj=JSON.parse(atob('<?= base64_encode(json_encode($data)) ?>'));
		var arr = Object.keys(obj).map(function (key) { return obj[key]; });
		var keys=Object.keys(obj).map(function (key) { return key; });
		arr=keys+"\r\n"+arr;
		name=name==null ? '<?= $name ?>' : name;
		name=name.replace(/\ /g, '_');
		exportCSV(name.toLowerCase(),arr);
}

<?php if (!empty($dataimport) && !$dataimport) :?>
function <?= $name ?>_getdata(){
	return JSON.parse(atob('<?= base64_encode(json_encode($data)) ?>'));
}
<?php endif ?>

<?php if (!empty($printable) && $printable) :?>
function <?= $name ?>_pdf(autoprint=false){
	 var canvas = document.querySelector("#<?= $name ?>");
    var canvas_img = canvas.toDataURL("image/png",1.0); //JPEG will not match background color
    var pdf = new jsPDF('landscape','in', 'letter'); //orientation, units, page size
    pdf.addImage(canvas_img, 'png', .5, 1.75, 10, 5); //image, type, padding left, padding top, width, height
    if (autoprint){
    	pdf.autoPrint();
    }
     //print window automatically opened with pdf
    var blob = pdf.output("bloburl");
    window.open(blob);
}
<?php endif ?>

</script>