<?= $this->extend('System/form') ?>
<?= $this->section('form_header') ?>
<div class="row">
	<div class="col-12">
		<a href="<?= url('Reports') ?>" class="btn btn-danger btn-sm">
			<i class="far fa-arrow-alt-circle-left mr-2"></i><?= lang('system.buttons.back') ?>
		</a>
	</div>
</div>
<?= $this->endSection() ?>

<?= $this->section('form_body') ?>
<div class="container col-12">
	<div class="row">
		<div class="col-4">
			<div class="row">
				<div class="col-12">
					<div class="card">
						<div class="card-header p-1">
							<h3 class="card-title"><?= lang('system.buttons.filter') ?></h3>
						</div>
						<div class="card-body p-2">
							<?= $currentView->includeView('System/form_fields',['fields'=>array_slice($fields, 0,4)]); ?>
						</div>
						<div class="card-footer d-flex" id="filters_footer"></div>
					</div>
				</div>
			</div>
		</div>
		<div class="col-8">
			<div class="card">
				<div class="card-header p-1 d-flex">
					<h3 class="card-title"><?= lang('system.reports.kpi_lastevents',[$filters['user_full']]) ?></h3>
					<button class="btn btn-sm btn-primary ml-auto" type="button" id="id_csv_lastevents">
						<i class="fas fa-file-csv mr-2"></i>CSV
					</button>
				</div>
				<div class="card-body p-2">
					<table class="table" id="ops_movements_table">
						<thead>
							<tr>
								<?php $lastevents_csv=lang('system.movements.mhtype') ?>
								<th><?= lang('system.movements.mhtype') ?></th>
								
								<?php $lastevents_csv.=','.lang('system.movements.mhdate') ?>
								<th><?= lang('system.movements.mhdate') ?></th>
								
								<?php $lastevents_csv.=','.lang('system.movements.mhfrom') ?>
								<th><?= lang('system.movements.mhfrom') ?></th>
								
								<?php $lastevents_csv.=','.lang('system.movements.mhto') ?>
								<th><?= lang('system.movements.mhto') ?></th>
								
								<?php $lastevents_csv.=','.lang('system.movements.mhref').PHP_EOL ?>
								<th><?= lang('system.movements.mhref') ?></th>
							</tr>
						</thead>
						<tbody>
							<?php for ($i=0; $i <5 ; $i++) :?>
							<?php if (array_key_exists($i, $lastevents)) :?>
							<tr>
								<?php $value=$lastevents[$i]; ?>
								<?php $lastevents_csv.=lang($value['mhtype']) ?>
								<td><?= lang($value['mhtype']) ?></td>
								
								<?php $lastevents_csv.=','.convertDate($value['mhdate'],'DB','d M Y H:i')?>
								<td><?= convertDate($value['mhdate'],'DB','d M Y H:i') ?></td>
								
								<?php $lastevents_csv.=','.$value['mhfrom'] ?>
								<td><?= $value['mhfrom'] ?></td>
								
								<?php $lastevents_csv.=','.$value['mhto'] ?>
								<td><?= $value['mhto'] ?></td>
								
								<?php $lastevents_csv.=','.$value['mhref'].PHP_EOL ?>
								<td><?= $value['mhref'] ?></td>
							</tr>
							<?php endif ?>
							<?php endfor ?>
							<?php if(count($lastevents) < 1) :?>
							<tr>
								<td colspan="5">
									<?= lang('system.reports.kpi_lastevents_empty') ?>
								</td>		
							</tr>
							<?php endif ?>
						</tbody>
					</table>
				</div>
			</div>
			<div class="card" id="kpi_1_card">
				<div class="card-header p-1 d-flex">
					<div class="ml-auto">
						<button class="btn btn-sm btn-secondary mr-2" type="button" onclick="kpi_1_pdf(true)"><!-- <i class="far fa-file-pdf mr-2"></i> -->
							<i class="fas fa-print mr-2"></i><?= lang('system.buttons.printbtn') ?>
						</button>
						<button class="btn btn-sm btn-primary" type="button" onclick="kpi_1_export_to_csv('<?= lang('system.reports.kpi_totalevents',[$filters['user_full']]) ?>')">
							<i class="fas fa-file-csv mr-2"></i>CSV
						</button>
					</div>
				</div>
				<div class="card-body p-2" id="kpi_1_card_body">
					<?= $currentView->getChartObject('kpi_1'); ?>
				</div>
			</div>
			<div class="card" id="kpi_2_card">
				<div class="card-header p-1 d-flex">
					<div class="ml-auto">
						<button class="btn btn-sm btn-secondary mr-2" type="button" onclick="kpi_2_pdf(true)"><!-- <i class="far fa-file-pdf mr-2"></i> -->
							<i class="fas fa-print mr-2"></i><?= lang('system.buttons.printbtn') ?>
						</button>
						<button class="btn btn-sm btn-primary" type="button" onclick="kpi_2_export_to_csv('<?= lang('system.reports.kpi_usercompareevents',[$filters['kpi2_field']]) ?>')">
							<i class="fas fa-file-csv mr-2"></i>CSV
						</button>
					</div>
				</div>
				<div class="card-body p-2" id="kpi_2_card_body">
					<?= $currentView->getChartObject('kpi_2'); ?>
				</div>
			</div>
		</div>
	</div>				
</div>
<script>
	$(function(){
		$("#form_container").removeClass('col-xs-12').removeClass('col-md-8').addClass('col-12 p-0');
		$("#form_container").find('.card-body').addClass('p-1');
		$("#id_formview_submit").addClass("ml-auto btn-sm btn-secondary").removeClass('btn-success').html('<i class="fas fa-filter mr-1"></i><?= lang('system.buttons.search') ?>');
		$("#filters_footer").html($("#id_formview_submit"));
		$(".form-control").addClass('form-control-sm');
	});
	
	$("#id_csv_lastevents").on("click",function(){
		var obj=JSON.parse(atob('<?= base64_encode(json_encode($lastevents)) ?>'));
		var arr=Object.keys(obj).map(function (key) { var sarr=Object.keys(obj[key]).map(function (kkey) {return obj[key][kkey];}); return sarr.join(","); });
		arr=arr.join("\r\n");
		var obj=JSON.parse(atob('<?= base64_encode(json_encode(is_array($lastevents) && count($lastevents) > 0 ? $lastevents[0] : [])) ?>'));
		
		var aar=Object.keys(obj).map(function (key){return key});
		aar=aar.join(",");
		arr=aar+"\r\n"+arr;
		exportCSV('<?= str_replace(' ', '_', strtolower(lang('system.reports.kpi_lastevents',[$filters['user_full']]))) ?>',arr);
	});
	
	
	function exportCSV(title,data){
		var downloadLink = document.createElement("a");
  		var blob = new Blob(["\ufeff", data]);
  		var url = URL.createObjectURL(blob);
  		downloadLink.href = url;
  		downloadLink.download = title+".csv";

 		document.body.appendChild(downloadLink);
  		downloadLink.click();
  		document.body.removeChild(downloadLink);
	}
</script>
<?= $this->endSection() ?>