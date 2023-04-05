<?= !empty('$content') ? $content : ''?>
<script>
    function printTile(element,header){
        $(element).printThis({
            debug: false,              
            importCSS: true,             
            importStyle: true,         
            printContainer: true, 
            pageTitle: header,             
            removeInline: false,        
            printDelay: 133,            
            header: '<h3>'+header+'</h3>',             
            formValues: true 
        });
    }   
</script>