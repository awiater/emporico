{
    type:'table',
    style:{'box-sizing':'border-box',height:'150px',margin:'0 auto 10px auto',padding:'5px',width:'100%',border:'1px solid'},
    components:[
        {
            type:'thead',
            components:[
                {
                    type:'row',
                    components:[
                       {
                         type : 'cell',
                         style:{height:'30px',border:'1px solid'},
                       } 
                    ],
                }
            ],
        },
        {
            type:'tbody',
            attributes:{type:'datacontainer'},
            components:[
                {
                    type:'row',
                    components:[
                       {
                         type : 'cell',
                         style:{height:'30px',border:'1px solid'},
                       } 
                    ],
                }
            ],
        }
    ],
}

