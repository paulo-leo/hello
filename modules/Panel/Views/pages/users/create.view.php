{include 'layout.head'}


<?php

function renderForm($inputs)
{

  $input = null;

  foreach($inputs as $key=>$val){
    $type = $val['type'] ?? 'input';
    $label = $val['label'] ?? $key;
    $id = "{$key}-{$type}";
    $placeholder = $val['placeholder'] ?? $label; 
    $col = $val['col'] ?? 'mb-3';
    $required = isset($val['required']) ? 'required' : '';

    $input .= "<div class='{$col}'>
       <label for='{$id}' class='form-label'>{$label}</label>
       <input {$required} type='{$type}' class='form-control' id='{$id}' placeholder='{$placeholder}' name='{$key}'>
    </div>";
  }

  $template = " <form ref='myForm' @submit.prevent='saveRecord()'>
                    {$input}
                    <div class='alert' v-bind:class='{\"alert-danger\":danger,\"alert-primary\":success}' role='alert'>@{{msg}}</div>
                    <div class='d-flex justify-content-end'>
                    <div class='btn-group'>
                     <button type='button' class='btn btn-outline-danger' v-if='!loading'><i class='bi-x-circle'></i> Cancelar</button>
                     <button type='submit' class='btn btn-success' v-if='!loading'><i class='bi-save'></i> Salvar</button>
                     <button class='btn btn-success' type='button' disabled v-if='loading'>
                    <span class='spinner-border spinner-border-sm' role='status' aria-hidden='true'></span>
                    Salvando...</button>
                    </div></div>
               </form>";


return $template;
}

?>

<body>
  {include '@panel.parts.header'}
  <main class="container-fluid mt-5 p-4" id="app">
 
    <h2>Criar usuário</h2>
    {!renderForm([
        'name'=>['label'=>'Nome','required'=>true],
        'email'=>['label'=>'E-mail','required'=>true,'type'=>'email'],
        'password'=>['label'=>'Senha','required'=>true]
      ])!}


  </main>

  <script>
        const app = Vue.createApp({
            data() {
                return {
                  loading: false,
                  msg:'',
                  danger:false,
                  success:false
                }
            },
            mounted() {
               
            },
            methods: {
              saveRecord(){
                this.loading = true;
                const form = this.$refs.myForm;
                const formData = new FormData(form);
                formData.append('_token', '{{csrf_token()}}');
              
                axios.post('{{url("panel/users")}}', formData)
               .then(res => {
                
                   if(res.data.type == 'success')
                   {
                     this.success = true;
                     this.danger = false;
                   }else{
                     this.success = false;
                     this.danger = true;
                   }

                   this.msg = res.data.msg;

                }).catch(error => {
                   console.log(error);
                }).finally(() => {
                        this.loading = false;
                    });
                }
            }
        });
        app.mount('#app');
    </script>

  {include 'layout.footer'}
</body>
</html>