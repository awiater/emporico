<script>
    function printTile(container,title,header=null){
         header=header==null ? title : header;
        if ($(container).is('canvas')){
            var canvas = document.querySelector(container);
            var canvas_img = canvas.toDataURL("image/png",1.0); //JPEG will not match background color
            var pdf = new jsPDF('landscape','in', 'letter'); //orientation, units, page size
            pdf.addImage(canvas_img, 'png', .5, 1.75, 10, 5); //image, type, padding left, padding top, width, height
            pdf.autoPrint(); //print window automatically opened with pdf
            var blob = pdf.output("bloburl");
            window.open(blob);
        }else{      
            $(container).printThis({
                debug: false,              
                importCSS: true,             
                importStyle: true,         
                printContainer: true, 
                pageTitle: title,             
                removeInline: false,        
                printDelay: 133,            
                header: '<h3>'+header+'</h3>',             
                formValues: true 
            });
        }
    }
</script>