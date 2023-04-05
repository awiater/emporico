<div class="col-12 card">
	<div class="card-header">
		<h4>Pre Deliveries</h4>
  	</div>
  	<div class="mt-2 ml-2 d-flex">
  		<div class="">
			
				<!---->
		</div>
		<a href="<?= url('Reports') ?>" class="btn btn-danger btn-sm">
			<i class="far fa-arrow-alt-circle-left mr-2"></i><?= lang('system.buttons.back') ?>	
		</a>
		<button type="button" class="btn btn-secondary btn-sm ml-5" onclick="printChart();">
			<i class="fas fa-print mr-2"></i><?= lang('system.buttons.printbtn') ?>
		</button>
		<button type="button" class="btn btn-primary btn-sm ml-1" onclick="downloadChart();">
			<i class="fas fa-file-csv mr-2"></i><?= lang('system.buttons.exportbtn') ?>
		</button>
		<button type="button" class="btn btn-warning btn-sm ml-1" onclick="downloadChart();">
			<i class="far fa-file-pdf mr-2"></i><?= lang('system.buttons.exportpdfbtn') ?>
		</button>
		
  	</div>
  	<div class="card-body row">
  		<div class="col-4" id="filtersDialog">
  			<?= $form ?>
  		</div>
		<?= $currentView->getChartObject('KPI') ?>
	</div>
</div>
<script>
	$(function(){
		$('#id_formview_cancel').addClass('d-none');
		$('#filtersDialog .card-header').html('<h5 class="modal-title"><?= lang('system.reports.repfilters') ?></h5>');
		$('#filtersDialog .col-xs-12').removeClass("col-xs-12").removeClass("col-md-8").addClass('col-12');
	});
	function downloadChart(){
		var a = document.createElement('a');
a.href = KPIChart.toBase64Image();
a.download = 'my_file_name.png';

// Trigger the download
a.click()
	}
	function downloadChart1(){
		var canvas = document.getElementById('KPI');
var context = canvas.getContext('2d');



var imgData = canvas.toDataURL("image/jpeg", 1.0);
  var pdf = new jsPDF();

  pdf.addImage(imgData, 'JPEG', 0, 0);
  pdf.save("download.pdf");
	}
	
	function printChart(){
		const dataUrl = document.getElementById('KPI').toDataURL(); 

		let windowContent = '<!DOCTYPE html>';
		windowContent += '<html>';
		windowContent += '<head><title>Print canvas</title></head>';
		windowContent += '<body>';
		windowContent += '<img src="' + dataUrl + '">';
		windowContent += '</body>';
		windowContent += '</html>';

		const printWin = window.open('', '', 'width=' + screen.availWidth + ',height=' + screen.availHeight);
		printWin.document.open();
		printWin.document.write(windowContent); 

		printWin.document.addEventListener('load', function() {
    		printWin.focus();
    		printWin.print();
    		printWin.document.close();
    		printWin.close();            
		}, true);
	}
</script>

