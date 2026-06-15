    </main> <!-- Fim .container -->

    <footer style="text-align: center; padding: 2rem 0; margin-top: 2rem; border-top: 1px solid var(--border-color); color: var(--white-color); background-color: var(--primary-color); margin-top: 10px;">
        <p>&copy; <?= date('Y') ?> Clínica Prev Dentistas. Todos os direitos reservados.</p>
        <div style="margin-top: 10px; font-size: 0.8rem; opacity: 0.7;">
                Responsável Técnico: Dra. Luciana Farias
        </div>
    </footer>

    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Script existente do parcelamento
            const selectPagamento = document.getElementById('forma_pagamento');
            const divParcelas = document.getElementById('div_parcelas');
            if(selectPagamento) {
                selectPagamento.addEventListener('change', function() {
                    divParcelas.style.display = (this.value === 'credito') ? 'block' : 'none';
                });
                divParcelas.style.display = (selectPagamento.value === 'credito') ? 'block' : 'none';
            }

            // 1. Inicializar DataTables globalmente
            if ($.fn.DataTable) {
                $('.table').DataTable({
                    "language": {
                        "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/pt-BR.json"
                    },
                    "responsive": true,
                    "pageLength": 10
                });
            }

            // 2. Interceptar exclusões e usar SweetAlert
            document.querySelectorAll('.btn-delete').forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    const href = this.getAttribute('href');
                    
                    Swal.fire({
                        title: 'Tem certeza?',
                        text: "Você não poderá reverter esta ação!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Sim, excluir!',
                        cancelButtonText: 'Cancelar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = href;
                        }
                    });
                });
            });

            // 3. Checar a URL por ?msg ou ?erro e mostrar SweetAlert
            const urlParams = new URLSearchParams(window.location.search);
            
            // Tratamento de Sucesso
            if (urlParams.has('msg')) {
                let msg = urlParams.get('msg');
                let texto = "Operação realizada com sucesso!";
                
                if (msg === 'sucesso_exclusao') texto = "Registro excluído com sucesso!";
                if (msg === 'sucesso_pagamento') texto = "Pagamento confirmado!";
                
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: texto,
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true
                });
            }

            // Tratamento de Erros
            if (urlParams.has('erro')) {
                let erro = urlParams.get('erro');
                let texto = "Ocorreu um erro durante a operação.";
                
                if (erro === 'pagamento_falhou') texto = "Falha ao processar o pagamento.";
                if (erro === 'vinculo_existente') texto = "Não é possível excluir pois há registros vinculados.";
                
                Swal.fire({
                    title: 'Ops...',
                    text: texto,
                    icon: 'error',
                    confirmButtonColor: '#3085d6'
                });
            }
        });
    </script>
</body>
</html>
