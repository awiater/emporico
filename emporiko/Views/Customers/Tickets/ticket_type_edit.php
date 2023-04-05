<?= $currentView->IncludeView('System/form') ?>
<script>
    $(function(){
        $('.select2').select2({theme: 'bootstrap4',templateResult:select2FormatSearch,templateSelection:select2FormatSelected});
        function select2FormatSearch(item) {
            return $('<span><i class="mr-2 '+item.text+'"></i>'+item.text+'</span>');
        }
        
        function select2FormatSelected(item) {return $('<span><i class="mr-2 '+item.text+'"></i>'+item.text+'</span>');}
    });
</script>
