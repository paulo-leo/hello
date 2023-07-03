{include 'layout.head'}

<?php

$items = ['id|#','name|Nome','email|E-mail'];
$show = ['id|#','name|Nome','email|E-mail'];

$itemList  = null;
$itemHeader = null;
$itemShow = null;

$items = $items ?? [];
$show = $show ?? [];


foreach($items as $index => $key)
{
   $key = explode('|',$key);
   $name = $key[0];
   $label = $key[1] ?? $name;
 
  $itemHeader .= "<th scope='col'>{$label}</th>";
  if($index == 0) $itemList .= "<th scope='row'>@{{item.{$name}}}</th>";
  else $itemList .= "<td>@{{item.{$name}}}</td>";
   
}

foreach($show as $key)
{
   $key = explode('|',$key);
   $name = $key[0];
   $label = $key[1] ?? $name;
 
   $itemShow .= "<tr>
                   <th scope='row'>{$label}</th>
                   <td>@{{item.{$name}}}</td>
               </tr>";
   

}

?>

<body>
  {include '@panel.parts.header'}
  <main class="container-fluid mt-5" id="app">
 
  <nav class="navbar navbar-light bg-light">
  <div class="container-fluid">
    <span class="navbar-brand">Usuários</span>
    <form class="d-flex">
      <input class="form-control me-2" type="search" placeholder="Buscar" aria-label="Search">
      <button class="btn btn-primary" type="submit"><i class="bi-search"></i></button>
    </form>
  </div>
</nav>
   
   <table class="table">
   <thead>
    <tr>
      {!$itemHeader!}
      <th scope="col">Opções</th>
    </tr>
  </thead>
  <tbody>
    <tr v-if="loading">
    <td colspan="999">
    <div class="d-flex justify-content-center">
       <div class="spinner-border" role="status" style="width: 3rem; height: 3rem;">
             <span class="visually-hidden">Loading...</span>
       </div>
    </div>
       </td>
    </tr>
    <tr v-for="item in items" :key="item.id" v-if="!loading">
      <!---<th scope="row">@{{item.id}}</th>-->
      {!$itemList!}
      <td>
      <div class="btn-group" role="group" aria-label="Basic example">
        <button class="btn btn-sm btn-primary" data-bs-toggle="offcanvas" data-bs-target="#offcanvasRight" aria-controls="offcanvasRight" @click="record(item.id)">
            <i class="bi-eye"></i>
        </button>
        <button class="btn btn-sm btn-danger"><i class="bi-trash"></i></button>
      </div>
      </td>
    </tr>
  </tbody>
</table>
<nav v-if="next || previous">
  <ul class="pagination">

    <li class="page-item" v-if="previous">
      <button class="page-link" @click="records(previous)" aria-label="Previous">
        <span aria-hidden="true">&laquo;</span>
      </button>
    </li>

    <li v-for="link in links" class="page-item">
        <button class="page-link" 
          @click="records(link.link)" 
             v-bind:disabled="link.current_page"
             v-bind:class="{active:link.current_page}">
            @{{link.label}}
        </button>
    </li>

    <li class="page-item" v-if="next">
      <button class="page-link" @click="records(next)" aria-label="Next">
        <span aria-hidden="true">&raquo;</span>
      </button>
    </li>
  </ul>
</nav>



<div class="offcanvas offcanvas-end offcanvas-wide" tabindex="-1" id="offcanvasRight" aria-labelledby="offcanvasRightLabel">
  <div class="offcanvas-header">
    <h5 id="offcanvasRightLabel" v-if="!loadingShow">@{{showTitle}}</h5>
    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <div class="offcanvas-body">
   
  <div class="text-center" v-if="loadingShow">
  <div class="spinner-border" role="status" style="width: 5rem; height: 5rem;">
    <span class="visually-hidden">Loading...</span>
  </div>
  <h4>Carregando...</h4>
</div>
   
  <table class="table" v-if="!loadingShow">
  <tbody>
      {!$itemShow!}
   </tbody>
</table>

  </div>
</div>


  </main>

  <script>
        const app = Vue.createApp({
            data() {
                return {
                    items:[],
                    links:[],
                    next:null,
                    previous:null,
                    loading:false,
                    showTitle:'Detalhes do usuário',
                    loadingShow:false,
                    item:{}
                }
            },
            mounted() {
                this.records(); 
            },
            methods: {
                records(url=null){
                    this.loading = true;
                    url = url ? url : '{{url("panel/users/all")}}';

                    axios.get(url).then(res => {

                         this.items = res.data.items;
                         this.links = res.data.links;
                         this.previous = res.data.previous;
                         this.next = res.data.next;

                    }).catch(error => {
                        console.error(error);
                    }).finally(() => {
                        this.loading = false;
                    });
                },
                record(id){
                    this.loadingShow = true;
                    let url = '{{url("panel/users/")}}'+id;
                    axios.get(url).then(res => {
                
                      this.item = res.data;


                    }).catch(error => {
                        console.error(error);
                    }).finally(() => {
                        this.loadingShow = false;
                    });
                }
            }
        });
        app.mount('#app');
    </script>

  {include 'layout.footer'}
</body>
</html>