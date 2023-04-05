{
    tagName:'div',
    droppable : true,
    style:{width:'100%',height:'100%',padding:'2px'},
    components:[
        {
            tagName:'div',
            droppable : true,
            style:{width:'100%',height:'10%',padding:'2px'},
            attributes:{id:'doc_header'}
        },
        {
            tagName:'div',
            droppable : true,
            style:{width:'100%',height:'80%',padding:'2px'},
            attributes:{id:'doc_body'}
        },
        {
            tagName:'div',
            droppable : true,
            style:{width:'100%',height:'10%',padding:'2px'},
            attributes:{id:'doc_footer'},
            components:[
                {
                    tagName:'img',
                    style:{height:'60px'},
                    attributes:{src:'<?= parsePath($theme_logo,'rel')?>'}
                }
            ],
        }
    ],
}