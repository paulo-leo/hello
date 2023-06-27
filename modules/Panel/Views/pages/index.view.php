{include 'layout.head'}
<body>
  {include '@panel.parts.header'}
  <main class="container mt-5">
  <div class="container col-md-8 mx-auto text-center">
   

   <div id="app">
        @{{ message }}
    </div>

    <script>
        const app = Vue.createApp({
            data() {
                return {
                    message: 'Teste'
                }
            }
        });

        app.mount('#app');
    </script>

  </div>
  </main>
  {include 'layout.footer'}
</body>
</html>